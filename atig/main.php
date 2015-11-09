<?php

if (php_sapi_name() !== 'cli')
    die('Unsupported mode, use cli mode');

require_once 'loader.php';


$app = new Application('bc8d1b20be02269616068e1a0ca15832');
//$app->run();


$api = new \Ychuperka\AtiApi();
//echo $api->getEmail('221243', 'Визжачих Максим Михайлович') . PHP_EOL;

//echo $api->parseIdList() . PHP_EOL;

$fileList = $api->getFileList();
$list = array();
$i=0;
foreach ($fileList as $key => $item) {
	foreach ($item as $key2 => $item2) {
		$list[$i]=$key2;
		$i++;
	}
}

//var_dump($list);

//
echo $api->getItem($list) . PHP_EOL;
