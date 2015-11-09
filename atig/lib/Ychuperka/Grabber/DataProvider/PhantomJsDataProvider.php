<?php
/**
 * PhantomJS data provider
 */

namespace Ychuperka\Grabber\DataProvider;


use Ychuperka\Grabber\DataProvider;
use Ychuperka\Grabber\Exception\DataProviderException;

class PhantomJsDataProvider extends DataProvider {

    /**
     * PhantomJS path
     *
     * @var string
     */
    private $_phantomJsPath;

    /**
     * Script path
     *
     * @var string
     */
    private $_scriptPath;

    /**
     * Cookies path
     *
     * @var string
     */
    private $_cookiesPath;

    /**
     * Arguments
     *
     * @var array
     */
    private $_args;

    /**
     * Constructor
     *
     * @param string $scriptPath
     * @param string $cookiesPath
     */
    public function __construct($scriptPath, $cookiesPath)
    {
        $this->_args = array();
        $this->_phantomJsPath = $this->_getPhantomJsPath();
        $this->_scriptPath = $scriptPath;

        // Prepare cookies
        $this->_cookiesPath = $cookiesPath;
        $this->_prepareCookiesFile($cookiesPath);
    }

    /**
     * Get data
     *
     * @return string
     */
    public function getData()
    {
        $request = new DataProvider\PhantomJsDataProvider\Request(
            $this->_phantomJsPath, $this->_scriptPath, $this->_cookiesPath, $this->_args
        );
        return $request->run();
    }

    /**
     * Set script path
     *
     * @param string $scriptPath
     * @return $this
     */
    public function setScriptPath($scriptPath)
    {
        $this->_scriptPath = $scriptPath;
        return $this;
    }

    /**
     * Set arguments
     *
     * @param array $args
     * @return $this
     */
    public function setArguments(array $args = null)
    {
        $this->_args = $args;
        return $this;
    }

    /**
     * Add argument
     *
     * @param string $arg
     * @return $this
     */
    public function addArgument($arg)
    {
        $this->_args[] = $arg;
        return $this;
    }

    /**
     * Get PhantomJS path
     *
     * @return string
     */
    protected function _getPhantomJsPath()
    {
        return 'C:/phantom/bin/phantomjs.exe';
    }
} 