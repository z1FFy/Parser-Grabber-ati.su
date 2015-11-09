<?php
error_reporting(E_ALL);
if (isset($_GET['from-geo']) && strlen($_GET['from-geo']) > 0) {
    $dirName = dirname(__FILE__);

    // Require libraries
    require_once realpath($dirName . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'loader.php');

    // Data received
    $fromGeo = urldecode(
        filter_var($_GET['from-geo'], FILTER_SANITIZE_STRING)
    );

    // Load logger
    $logger = new \Ychuperka\Logger(
        realpath(
            $dirName . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'logs'
        ) . DIRECTORY_SEPARATOR . 'log_' . date('Y-m-d') . '.txt'
    );
    $logger->write('BEGIN');

    // Load grabber
    $grabber = new \Ychuperka\Grabber(
        realpath(
            $dirName . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'cookies'
        )
    );


    $logger->write('From geo: ' . $fromGeo);

    $grabber->setFromGeo($fromGeo)
        ->setPageNumber(1);

    // Search
    for (;;) {

        try {

            $items = $grabber->run();
					echo $items;
            break;

        } catch (\Ychuperka\Grabber\Exception\CaptchaException $ex) {

            if ($ex->getCode() == \Ychuperka\Grabber\Exception\CaptchaException::CODE_FOUND) {
                $logger->write('Found captcha without id! Can`t continue...');
                break;
            } else {
                $logger->write('Captcha found! ID: ' . $ex->getCaptchaId());
            }

            // Captcha exception
            $antiCaptchaProvider = new \Ychuperka\Grabber\AntiCaptchaProvider\AntigateAntiCaptchaProvider(
                ''
            );
            $captchaCode = $antiCaptchaProvider->crack($ex->getCaptchaImageDataBase64());

            $logger->write('Code: ' . $captchaCode);

            $grabber->setCaptchaCode($captchaCode)
                ->setCaptchaId($ex->getCaptchaId());

            continue;

        } catch (\Ychuperka\Grabber\Exception $ex) {

            $logger->write("Grabber raise exception: {$ex->getMessage()}, file {$ex->getFile()}, line {$ex->getLine()}");
            break;

        } catch (\Exception $ex) {

            $logger->write("Exception: {$ex->getMessage()}, file {$ex->getFile()}, line {$ex->getLine()}");
            break;

        }

    }

    $logger->write('END');
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Search</title>
    </head>
    <body>

        <form action="">

            <label for="from-geo">From geo:</label>
            <input type="text" name="from-geo" id="from-geo">
            <input type="submit">

        </form>

        <?php if (isset($items)): ?>
            <?php print_r($items); ?>
        <?php endif; ?>

    </body>
</html>