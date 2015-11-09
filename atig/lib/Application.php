<?php

/**
 * Class Application
 *
 * @author Yegor Chuperka <ychuperka@gmail.com>
 */

class Application {

    /**
     * Anticapcha provider
     *
     * @var Ychuperka\Grabber\AnticaptchaProvider\AntigateAntiCaptchaProvider
     */
    private $_antiCaptchaProvider;

    /**
     * Logger
     *
     * @var \Ychuperka\Logger
     */
    private $_logger;

    /**
     * Constructor
     */
    public function __construct($antigateKey)
    {
        $this->_antiCaptchaProvider = new \Ychuperka\Grabber\AntiCaptchaProvider\AntigateAntiCaptchaProvider(
            $antigateKey
        );

        $logFilePath = realpath(
            dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'logs'
        ) . DIRECTORY_SEPARATOR . 'log_' . date('Y_m_d') . '.txt';
        $this->_logger = new \Ychuperka\Logger($logFilePath);
    }

    /**
     * Run
     */
    public function run()
    {
        // Create grabber instance and set search parameters
        $grabber = new \Ychuperka\Grabber(
            realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'cookies')
        );
        $grabber->setFromGeo('Москва')
            ->setFromLength(1)
            ->setToLength(100);

        ob_start();
        print_r($grabber->getOptions());
        $optionsAsString = ob_get_contents();
        ob_end_clean();
        $this->_showMessageAndMakeLogRecord('Work started!' . PHP_EOL . $optionsAsString);

        // Current page, will be equals "one" at start
        $currentPage = 0;

        // Work loop
        do {

            // Set next page number
            $grabber->setPageNumber(++$currentPage);
            $this->_showMessageAndMakeLogRecord("Processing page number \"$currentPage\"");

            // Try to process page
            try {

                $items = $grabber->run();
                $this->_showMessageAndMakeLogRecord('Processed ' . count($items) . ' items');

                // Here you can do anything with items!!! <======

            } catch (\Ychuperka\Grabber\Exception\CaptchaException $ex) {

                // Captcha detected!

                // Get exception code
                $code = $ex->getCode();
                if ($code == \Ychuperka\Grabber\Exception\CaptchaException::CODE_FOUND) {
                    // Found captcha, without id.. WTF!?
                    $this->_showMessageAndMakeLogRecord('Captcha without id found!');
                } else {

                    // Found captcha with id, can try to process
                    $captchaId = $ex->getCaptchaId();
                    $this->_showMessageAndMakeLogRecord("Captcha found! ID: \"$captchaId\" Trying to crack it...");

                    // Crack captcha
                    $text = $this->_antiCaptchaProvider
                        ->addOption('is_numeric', 1)
                        ->crack($ex->getCaptchaImageDataBase64());

                    // Important!
                    $text = str_replace(' ', null, $text);

                    $this->_showMessageAndMakeLogRecord('Cracking result: ' . $text);


                    // Put captcha data into the grabber
                    $grabber->setCaptchaId($captchaId)
                        ->setCaptchaCode($text);

                    // Decrement current page number, to process it again
                    --$currentPage;
                }

            } catch (\Exception $ex) {
                // All other exceptions, just log it
                $this->_showMessageAndMakeLogRecord('Exception occured: ' . $ex->getMessage() . ', file: ' . $ex->getFile() . ', line:' . $ex->getLine());
            }

            // If last page number bigger than current page number, then need to sleep
            if ($grabber->getLastPageNumber() > $currentPage) {

                // Show current last page number
                $this->_showMessageAndMakeLogRecord("Last page number #{$grabber->getLastPageNumber()}");

                $waitTime = rand(4, 8);
                $this->_showMessageAndMakeLogRecord("Waiting $waitTime s.");
                sleep($waitTime);

            } else {
                // Else work is finished!
                $this->_showMessageAndMakeLogRecord('Finished! Good bye.');
            }

            // Iterate while last page number bigger than current page number
        } while($grabber->getLastPageNumber() > $currentPage);
    }

    /**
     * Show message and make log record
     *
     * @param string $message
     */
    protected function _showMessageAndMakeLogRecord($message)
    {
        echo $message . PHP_EOL;
        $this->_logger->write($message);
    }

}