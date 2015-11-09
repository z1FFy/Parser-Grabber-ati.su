<?php
/**
 * Request
 *
 * @author Yegor Chuperka <ychuperka@gmail.com>
 */

namespace Ychuperka\Grabber\DataProvider\HttpDataProvider;

use Ychuperka\Grabber\Exception\DataProviderException\HttpDataProviderException;

class Request extends \Ychuperka\Grabber\DataProvider\Request{

    const RT_GET = 1;
    const RT_POST = 2;

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
     * Request type (RT_GET, RT_POST)
     *
     * @var int
     */
    private $_requestType;

    /**
     * Query string
     *
     * @var string
     */
    private $_queryString;

    /**
     * Constructor
     *
     * @param resource $curlHandle
     * @param string $url
     * @param int $requestType
     * @param array $params
     * @param string $rawPayload
     * @throws HttpDataProviderException
     */
    public function __construct($curlHandle, $url, $requestType, array $params = null, $rawPayload = null)
    {
        // Check curl handle
        if (!is_resource($curlHandle)) {
            throw new HttpDataProviderException('Invalid curl handle');
        }
        $this->_curlHandle = $curlHandle;

        // Check url
        if (!$this->_isUrlValid($url)) {
            throw new HttpDataProviderException('Invalid url');
        }
        $this->_url = $url;

        // Check request type
        if (!$this->_isRequestTypeValid($requestType)) {
            throw new HttpDataProviderException('Invalid request type');
        }
        $this->_requestType = $requestType;

        // Prepare params
        if ($params !== null) {
            $this->_queryString = $this->_prepareParams($params);
        } else if ($rawPayload !== null) {
            $this->_queryString = $rawPayload;
        }
    }

    /**
     * Run request
     *
     * @return mixed
     * @throws \Ychuperka\Grabber\Exception\DataProviderException\HttpDataProviderException
     */
    public function run()
    {
        // Prepare curl
        $isPost = $this->_requestType == self::RT_POST;
        if ($isPost) {

            $url = $this->_url;

        } else {

            if ($this->_isUrlEndsWithQuestionMark($this->_url)) {
                $url = $this->_url . $this->_queryString;
            } else {
                $url = $this->_url . '?' . $this->_queryString;
            }

        }

        $curlOptions = array(
            CURLOPT_URL => $url,
            CURLOPT_POST => $isPost
        );
        if ($isPost && $this->_queryString !== null) {
            $curlOptions[CURLOPT_POSTFIELDS] = $this->_queryString;
        }

        curl_setopt_array($this->_curlHandle, $curlOptions);

        // Make request
        $response = curl_exec($this->_curlHandle);

        $curlErrNo = curl_errno($this->_curlHandle);
        if ($curlErrNo != CURLE_OK) {
            throw new HttpDataProviderException('Curl error #' . $curlErrNo . ', message: ' . curl_error($this->_curlHandle));
        }

        return $response;
    }

    /**
     * Get curl handle
     *
     * @return resource
     */
    protected function _getCurlHandle()
    {
        return $this->_curlHandle;
    }

    /**
     * Get url
     *
     * @return string
     */
    protected function _getUrl()
    {
        return $this->_url;
    }

    /**
     * Get request type
     *
     * @return int
     */
    protected function _getRequestType()
    {
        return $this->_requestType;
    }

    /**
     * Get query string
     *
     * @return string
     */
    protected function _getQueryString()
    {
        return $this->_queryString;
    }

    /**
     * Is url ends with question mark?
     *
     * @param string $url
     * @return bool
     */
    protected function _isUrlEndsWithQuestionMark($url)
    {
        return $url{strlen($url) - 1} === '?';
    }

    /**
     * Is url valid?
     *
     * @param $url
     * @return bool
     */
    protected function _isUrlValid($url)
    {
        return (bool)filter_var($url, FILTER_VALIDATE_URL);
    }

    /**
     * Is request type valid?
     *
     * @param int $requestType
     * @return bool
     */
    protected function _isRequestTypeValid($requestType)
    {
        return in_array($requestType, array(self::RT_GET, self::RT_POST));
    }

    /**
     * Prepare params
     *
     * @param array $params
     * @return string
     */
    protected function _prepareParams(array $params)
    {
        return http_build_query($params);
    }

} 