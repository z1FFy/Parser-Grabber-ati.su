<?php

define('LOADER_DIR', dirname(__FILE__));

spl_autoload_register(
    function($name) {

        $path = realpath(
            LOADER_DIR . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . str_replace('\\', '/', $name) . '.php'
        );

        require_once $path;

    }
);

// Require some old-style libs
require_once LOADER_DIR . DIRECTORY_SEPARATOR . 'third_party' . DIRECTORY_SEPARATOR . 'simple_html_dom.php';