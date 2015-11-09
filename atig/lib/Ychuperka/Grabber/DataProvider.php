<?php
/**
 * Created by PhpStorm.
 * User: ychuperka
 * Date: 07.06.14
 * Time: 5:12
 */

namespace Ychuperka\Grabber;


use Ychuperka\Grabber\Exception\DataProviderException;

abstract class DataProvider {

    public abstract function getData();

    /**
     * Prepare cookies file
     *
     * @param string $cookiesFilePath
     * @throws Exception\DataProviderException
     */
    protected function _prepareCookiesFile($cookiesFilePath)
    {
        if (strlen($cookiesFilePath) == 0) {
            throw new DataProviderException('Cookies file path is empty');
        }

        if (!file_exists($cookiesFilePath)) {
            if (!touch($cookiesFilePath)) {
                throw new DataProviderException('Cookies file not found and can not be created');
            }
        }

        if (!is_writable($cookiesFilePath)) {
            throw new DataProviderException('Cookies file not writable');
        }
    }
} 