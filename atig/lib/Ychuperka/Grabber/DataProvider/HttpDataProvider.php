<?php
/**
 * Http data provider
 *
 * @author Yegor Chuperka <ychuperka@gmail.com>
 */

namespace Ychuperka\Grabber\DataProvider;

use Ychuperka\Grabber\DataProvider;
use Ychuperka\Grabber\Exception\DataProviderException;
use Ychuperka\Grabber\DataProvider\HttpDataProvider\Request;

class HttpDataProvider extends DataProvider {

    /**
     * Curl handle
     *
     * @var resource
     */
    private $_curlHandle;

    /**
     * Url
     *
     * @var string
     */
    private $_url;

    /**
     * Request type
     *
     * @var int
     */
    private $_requestType;

    /**
     * Params
     *
     * @var array
     */
    private $_params;

    /**
     * Raw payload
     *
     * @var string
     */
    private $_rawPayload;

    /**
     * Use cookies?
     *
     * @var bool
     */
    private $_isNeedUseCookies;

    /**
     * Constructor
     */
    public function __construct($isNeedToUseCookies = false, $cookiesFilePath = null)
    {
        // Check curl
        if (!function_exists('curl_init')) {
            throw new DataProviderException('Curl not found');
        }

        // Init curl handle
        $this->_curlHandle = curl_init();
        if ($this->_curlHandle === false) {
            throw new DataProviderException('Curl found, but can`t be initialized');
        }
        curl_setopt_array(
            $this->_curlHandle,
            array(
                CURLOPT_AUTOREFERER => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_USERAGENT => 'Mozilla/5.0 (X11; Linux i686; rv:13.0) Gecko/13.0 Firefox/13.0'
            )
        );

        // Init cookies, if need
        $this->_isNeedUseCookies = $isNeedToUseCookies;
        if ($isNeedToUseCookies) {

            if ($cookiesFilePath === null) {
                $cookiesFilePath = tempnam(sys_get_temp_dir(), 'ychuperka_grabber_cookies');
            }
            $this->_prepareCookiesFile($cookiesFilePath);

            curl_setopt_array(
                $this->_curlHandle,
                array(
                    CURLOPT_COOKIEFILE => $cookiesFilePath,
                    CURLOPT_COOKIEJAR => $cookiesFilePath
                )
            );
        }

    }

    /**
     * Set curl option
     *
     * @param int $key
     * @param mixed $value
     * @return $this
     */
    public function setCurlOption($key, $value)
    {
        curl_setopt($this->_curlHandle, $key, $value);
        return $this;
    }

    /**
     * Set url
     *
     * @param string $url
     * @return $this
     */
    public function setUrl($url)
    {
        $this->_url = $url;
        return $this;
    }

    /**
     * Set request type
     *
     * @param int $requestType
     * @return $this
     */
    public function setRequestType($requestType)
    {
        $this->_requestType = $requestType;
        return $this;
    }

    /**
     * Set params
     *
     * @param array $params
     * @return $this
     */
    public function setParams(array $params)
    {
        $this->_params = $params;
        return $this;
    }

    /**
     * Set raw payload
     *
     * @param $value
     * @return $this
     */
    public function setRawPayload($value){
        $this->_rawPayload = $value;
        return $this;
    }

    /**
     * Get data
     *
     * @return mixed
     */
    public function getData()
    {
        $request = new Request($this->_curlHandle, $this->_url, $this->_requestType, $this->_params, $this->_rawPayload);
        return $request->run();
    }

} 