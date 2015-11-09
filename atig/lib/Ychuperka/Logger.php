<?php
/**
 * Logger
 *
 * @author Yegor Chuperka <ychuperka@gmail.com>
 */

namespace Ychuperka;


class Logger {

    private $_outHandle;

    /**
     * Constructor
     *
     * @param $outFilePath
     * @throws \InvalidArgumentException
     */
    public function __construct($outFilePath)
    {
        if (strlen($outFilePath) == 0) {
            echo('Output file path is empty');
        }

         if (!file_exists($outFilePath) && !touch($outFilePath)) {
             echo 'Output file not exists and can not be created';
         }

        if (!is_writable($outFilePath)) {
            echo('Output file is not writable');
        }

			$this->_outHandle = fopen($outFilePath, 'a+');
        
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        fclose($this->_outHandle);
    }

    /**
     * Write line
     *
     * @param $line
     */
    public function write($line)
    {
        $dateTime = date('Y-m-d H:i:s');
        $line = "$dateTime: $line";
        fputs($this->_outHandle, $line . PHP_EOL);
    }



} 