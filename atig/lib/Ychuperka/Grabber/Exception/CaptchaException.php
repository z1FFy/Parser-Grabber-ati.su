<?php
/**
 * Captcha exception
 */

namespace Ychuperka\Grabber\Exception;


use Ychuperka\Grabber\Exception;

class CaptchaException extends Exception {

    const CODE_FOUND = 1;
    const CODE_FOUND_WITH_ID = 2;

    /**
     * Captcha id
     *
     * @var string
     */
    private $_captchaId;

    /**
     * Captcha image data as base64
     *
     * @var string
     */
    private $_captchaImageDataBase64;

    /**
     * Constructor
     *
     * @param string $message
     * @param int $code
     * @param Exception $previous
     * @param null $captchaId
     * @param $captchaImageDataBase64
     */
    public function __construct($message = "", $code = 0, Exception $previous = null,
                                $captchaId = null, $captchaImageDataBase64) {
        parent::__construct($message, $code, $previous);

        $this->_captchaId = $captchaId;
        $this->_captchaImageDataBase64 = $captchaImageDataBase64;
    }

    /**
     * Get captcha id
     *
     * @return string
     */
    public function getCaptchaId()
    {
        return $this->_captchaId;
    }

    /**
     * Get captcha image data
     *
     * @return string
     */
    public function getCaptchaImageDataBase64()
    {
        return $this->_captchaImageDataBase64;
    }

} 