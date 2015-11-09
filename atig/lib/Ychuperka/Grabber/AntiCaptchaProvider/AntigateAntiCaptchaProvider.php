<?php
/**
 * Antigate anticaptcha provider
 */

namespace Ychuperka\Grabber\AntiCaptchaProvider;

use Ychuperka\Grabber\AntiCaptchaProvider;
use Ychuperka\Grabber\DataProvider\HttpDataProvider;
use Ychuperka\Grabber\Exception\AntiCaptchaProviderException;
use Ychuperka\Grabber\Exception\DataProviderException\AntigateAntiCaptchaProviderException;

class AntigateAntiCaptchaProvider extends AntiCaptchaProvider {

    const INTERVAL_CAPTCHA_LISTEN = 5;
    const TIMEOUT_CAPTCHA_CAN_BE_READY = 10;
    const REGEX_PATTERN_API_KEY = '/[a-z0-9]{32}/';

    /**
     * Api key
     *
     * @var string
     */
    private $_apiKey;

    /**
     * Http data provider
     *
     * @var \Ychuperka\Grabber\DataProvider\HttpDataProvider
     */
    private $_httpDataProvider;

    /**
     * Additional captcha request options
     *
     * @var array
     */
    private $_options;

    /**
     * Constructor
     *
     * @param string $apiKey
     * @throws AntigateAntiCaptchaProviderException
     */
    public function __construct($apiKey)
    {
        // Check api key
        if (strlen($apiKey) == 0 || !preg_match(self::REGEX_PATTERN_API_KEY, $apiKey)) {
            throw new AntigateAntiCaptchaProviderException('Invalid api key');
        }
        $this->_apiKey = $apiKey;

        $this->_httpDataProvider = new HttpDataProvider();
        $this->_options = array();
    }

    /**
     * Crack captcha
     *
     * @param string $captchaImageDataBase64
     * @return string
     * @throws \Ychuperka\Grabber\Exception\DataProviderException\AntigateAntiCaptchaProviderException
     */
    public function crack($captchaImageDataBase64)
    {
        // Check captcha file path
        if (strlen($captchaImageDataBase64) == 0) {
            throw new AntigateAntiCaptchaProviderException('Empty captcha image data');
        }

        // Send captcha
        $captchaRemoteId = $this->_sendCaptcha($captchaImageDataBase64);

        // Wait
        sleep(self::TIMEOUT_CAPTCHA_CAN_BE_READY);

        // Listen result
        do {

            $text = trim($this->_getText($captchaRemoteId));

            // Check for errors
            if (substr_count($text, 'ERROR') > 0) {
                throw new AntigateAntiCaptchaProviderException('Error occured: ' . $text);
            }

            // Check captcha status
            $isCaptchaNotReady = $text === 'CAPCHA_NOT_READY';
            if ($isCaptchaNotReady) {
                sleep(self::INTERVAL_CAPTCHA_LISTEN);
            }

        } while ($isCaptchaNotReady);

        return $text;
    }

    /**
     * Add option
     *
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function addOption($key, $value)
    {
        $this->_options[$key] = $value;
        return $this;
    }

    /**
     * Remove option
     *
     * @param string $key
     * @throws \Ychuperka\Grabber\Exception\DataProviderException\AntigateAntiCaptchaProviderException
     */
    public function removeOption($key)
    {
        if (!isset($this->_options[$key])) {
            throw new AntigateAntiCaptchaProviderException("Option \"$key\" not found");
        }

        unset($this->_options[$key]);
    }

    /**
     * Send captcha
     *
     * Returns captcha remote id
     *
     * @param string $encodedBinaryData
     * @return string
     * @throws \Ychuperka\Grabber\Exception\DataProviderException\AntigateAntiCaptchaProviderException
     */
    protected function _sendCaptcha($encodedBinaryData)
    {
        // Send captcha data

        $requestParams = array(
            'method' => 'base64',
            'key' => $this->_apiKey,
            'body' => $encodedBinaryData
        );

        if (count($this->_options) > 0) {
            $requestParams = array_merge($requestParams, $this->_options);
        }

        $response = $this->_httpDataProvider->setUrl('http://antigate.com/in.php')
            ->setRequestType(HttpDataProvider\Request::RT_POST)
            ->setParams($requestParams)
            ->getData();

        // Check response
        if (substr_count($response, 'ERROR') > 0) {
            throw new AntigateAntiCaptchaProviderException('Can`t send captcha data, reason: ' . $response);
        }

        if (substr_count($response, 'OK') == 0) {
            throw new AntigateAntiCaptchaProviderException('Can`t send captcha data, unknown response: ' . $response);
        }

        // Get remote id
        return $this->_getSuccessResponseBody($response);
    }

    /**
     * Get text
     *
     * @param string $captchaRemoteId
     * @return string
     * @throws \Ychuperka\Grabber\Exception\DataProviderException\AntigateAntiCaptchaProviderException
     */
    protected function _getText($captchaRemoteId)
    {
        // Send captcha remote id
        $response = $this->_httpDataProvider->setUrl(
            'http://antigate.com/res.php'
        )
        ->setRequestType(HttpDataProvider\Request::RT_GET)
        ->setParams(
            array(
                'key' => $this->_apiKey,
                'action' => 'get',
                'id' => $captchaRemoteId
            )
        )
        ->getData();

        // Check response
        if (substr_count($response, 'ERROR') > 0) {
            throw new AntigateAntiCaptchaProviderException('Can`t get captcha text, reason: ' . $response);
        }

        if (substr_count($response, 'CAPCHA_NOT_READY') > 0) {
            return $response;
        }

        if (substr_count($response, 'OK') == 0) {
            throw new AntigateAntiCaptchaProviderException('Can`t get captcha text, unknown response: ' . $response);
        }

        return $this->_getSuccessResponseBody($response);
    }

    /**
     * Get success response body
     *
     * @param string $rawResponseBody
     * @return string
     * @throws \Ychuperka\Grabber\Exception\AntiCaptchaProviderException
     */
    protected function _getSuccessResponseBody($rawResponseBody)
    {
        $delimiterPos = strpos($rawResponseBody, '|');
        if ($delimiterPos == -1) {
            throw new AntiCaptchaProviderException('Delimiter not found in body: ' . $rawResponseBody);
        }

        return substr($rawResponseBody, $delimiterPos + 1);
    }
} 