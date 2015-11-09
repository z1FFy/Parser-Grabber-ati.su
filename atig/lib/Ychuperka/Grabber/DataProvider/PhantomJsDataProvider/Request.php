<?php
/**
 * PhantomJS request
 *
 * @author Yegor Chuperka <ychuperka@gmail.com>
 */

namespace Ychuperka\Grabber\DataProvider\PhantomJsDataProvider;

use Ychuperka\Grabber\Exception\DataProviderException;

class Request extends \Ychuperka\Grabber\DataProvider\Request {

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
    private $_scripPath;

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
     * @param string $phantomJsPath
     * @param string $scriptPath
     * @param string $cookiesPath
     * @param array $args
     *
     * @throws DataProviderException\PhantomJsDataProviderException
     */
    public function __construct($phantomJsPath, $scriptPath, $cookiesPath, array $args = null)
    {
        // Get and check PhantomJS path
        if (!file_exists($phantomJsPath)) {
            throw new DataProviderException\PhantomJsDataProviderException("PhantomJS not found, path: $phantomJsPath");
        }
        if (!$this->_isValidPhantomJSPath($phantomJsPath)) {
            throw new DataProviderException\PhantomJsDataProviderException("Invalid PhantomJS path: $phantomJsPath");
        }
        $this->_phantomJsPath = $phantomJsPath;

        // Get and check script path
        $this->_scripPath = $scriptPath;
        if (!file_exists($scriptPath)) {
            throw new DataProviderException\PhantomJsDataProviderException("Script not found, path: $scriptPath");
        }

        // Get and check cookies path
        $this->_cookiesPath = $cookiesPath;
        if (!file_exists($cookiesPath)) {
            throw new DataProviderException\PhantomJsDataProviderException("Cookies file not found, path: $cookiesPath");
        }
        if (!is_writable($cookiesPath)) {
            throw new DataProviderException\PhantomJsDataProviderException('Cookies file not writable');
        }

        // Get arguments
        $this->_args = $args;
    }

    /**
     * Run request
     *
     * @return string
     * @throws \Ychuperka\Grabber\Exception\DataProviderException\PhantomJsDataProviderException
     */
    public function run()
    {
        // Prepare command
        $command = $this->_phantomJsPath . ' --cookies-file=' . $this->_cookiesPath . ' ' . $this->_scripPath;
        if ($this->_args !== null) {
            foreach ($this->_args as $arg) {
                $arg = escapeshellarg($arg);
                $command .= " $arg";
            }
        }

        // Execute
        $output = array();
        $code = null;
        exec($command, $output, $code);

        // Check result
        if ($code != 0) {
            throw new DataProviderException\PhantomJsDataProviderException('Executed process exit code "' . $code . '"');
        }

        return join("\n", $output);
    }

    /**
     *
     * Is valid PhantomJS path?
     *
     * @param string $path
     * @return bool
     */
    protected function _isValidPhantomJSPath($path)
    {
        $output = array();
        exec($path . ' -h', $output);
        $output = join('', $output);

        return substr_count($output, 'phantomjs.org') > 0;
    }
} 