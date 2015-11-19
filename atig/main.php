<?php
if (php_sapi_name() !== 'cli')
	die('Unsupported mode, use cli mode');

require_once 'loader.php';

$app = new Application('bc8d1b20be02269616068e1a0ca15832');
$api = new \Ychuperka\AtiApi();

//echo $api->parseIdList() . PHP_EOL;

//$api->saveItemsFromList();
