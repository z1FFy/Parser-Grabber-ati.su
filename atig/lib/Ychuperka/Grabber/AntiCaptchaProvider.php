<?php
/**
 * AntiCaptcha provider
 *
 * @author Yegor Chuperka <ychuperka@gmail.com>
 */

namespace Ychuperka\Grabber;


abstract class AntiCaptchaProvider {

    abstract public function crack($captchaFilePath);

}