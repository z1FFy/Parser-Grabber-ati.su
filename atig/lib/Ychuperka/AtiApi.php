<?php
/**
 * ati.su API
 *
 * @author Yegor Chuperka & Denis Kuschenko
 */

namespace Ychuperka;


use Ychuperka\AtiApi\Exception;
use Ychuperka\Grabber\DataProvider\PhantomJsDataProvider;

class AtiApi {

	/**
	 * Phantom js data provider
	 *
	 * @var Grabber\DataProvider\PhantomJsDataProvider
	 */
	private $_phantomJsDataProvider;

	/**
	 * Script directory path
	 *
	 * @var string
	 */
	private $_scriptDirectoryPath;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		echo '=============================' . PHP_EOL;
		echo 'Hello! ' . PHP_EOL;
		echo 'Ati.su Parser & Grabber has been startded' . PHP_EOL;
		echo '=============================' . PHP_EOL;
		$ds = DIRECTORY_SEPARATOR;

		$cookiesPath = realpath(
				dirname(__FILE__) . $ds . '..' . $ds . '..' . $ds . 'cookies'
			) . $ds . 'phantom-cookies.txt';

		$this->_scriptDirectoryPath = realpath(
				dirname(__FILE__) . $ds . '..' . $ds . 'phantom_scripts'
			) . $ds;

		$this->_phantomJsDataProvider = new PhantomJsDataProvider(null, $cookiesPath);
	}

	/**
	 * Get email
	 *
	 * @param string $companyId Use $item['company_id']
	 * @param string $contactName
	 * @return string
	 * @throws AtiApi\Exception
	 */
	public function getEmail($companyId, $contactName)
	{
		// Send request
		$args = array(
			$companyId,
			$contactName
		);
		$this->_phantomJsDataProvider->setArguments($args)
			->setScriptPath($this->_scriptDirectoryPath . 'get_email.js');

		$response = $this->_phantomJsDataProvider->getData();

		// Check for errors
		$this->_checkForErrorsAndAuthorizeOrRaiseException($response, 'get_email.js', $args);

		// No errors, try to get email
		$emailSignaturePos = strpos($response, 'EMAIL:');
		if ($emailSignaturePos == -1) {
			throw new Exception('Email signature not found');
		}

		$email = trim(
			substr($response, $emailSignaturePos + strlen('EMAIL:') + 1)
		);
		if (strlen($email) == 0) {
			throw new Exception('Empty email');
		}

		return $email;
	}

	/**
	 * Get item
	 *
	 * @param $companysArray
	 * @throws Exception
	 */
	public function getItem($companysArray)
	{
		if (!$this->_authorize()) {
			throw new Exception('Can`t authorize');
		} else {
			print_r('Authorized');
		}

		foreach($companysArray as $key => $i) {
			print_r('Process # ' . $i . PHP_EOL);
			$args = array(
				$i
			);
			$file = 'data/cards/' . $i . '.html';
			if (!file_exists($file)) {
				$data = $this->_phantomJsDataProvider->setArguments($args)
					->setScriptPath($this->_scriptDirectoryPath . 'get_item.js')
					->getData();
//				if (strlen($data) > 1999) {
				echo $i . ' Saved ' . PHP_EOL;
				$outputBuffer = fopen($file, 'w');
				fwrite($outputBuffer, '<meta charset="utf-8">');
				fwrite($outputBuffer, $data);
				fclose($outputBuffer);
//				}

			} else {
				print_r('File isset' . PHP_EOL);
			}
		}
	}

	/**
	 * Парсинг списка id компаний
	 *
	 * @throws Exception
	 */
	public function parseIdList(){
//		if (!$this->_authorize()) {
//			throw new Exception('Can`t authorize');
//		} else {
//			echo 'Authorized' . PHP_EOL;
//		}
		echo 'List companies parsing started' . PHP_EOL;
		$data = $this->_phantomJsDataProvider
			->setScriptPath($this->_scriptDirectoryPath . 'get_id_list.js')
			->getData();
		//echo $data . PHP_EOL;
		if (strlen($data) > 30) {
			$outputBuffer = fopen('data/idlist.json', 'w+');
			fwrite($outputBuffer, $data);
			fclose($outputBuffer);
			echo 'Id list saved' . PHP_EOL;
		} else {
			echo 'Null list, dont saved' . PHP_EOL;
		}

	}

	/**
	 * Получение списка компаний из файла
	 *
	 * @return mixed
	 */
	public function getFileList(){
		$file = file_get_contents('data/idlist.json');
		$fileList = json_decode($file,1);
		$list = array();
		$i=0;
		foreach ($fileList as $key => $item) {
			foreach ($item as $key2 => $item2) {
				$list[$i]=$key2;
				$i++;
			}
		}
		return $list;
	}

	public function saveItemsFromList() {
		$list = $this->getFileList();
		echo $this->getItem($list) . PHP_EOL;
	}

	/**
	 * Set rate
	 *
	 * @param string $itemId Use $item['second_id']
	 * @param string $companyId Use $item['company_id']
	 * @param string $rateValue
	 * @throws AtiApi\Exception
	 * @return bool
	 */
	public function setRate($itemId, $companyId, $rateValue)
	{
		// Check rate value
		if (!is_numeric($rateValue)) {
			throw new Exception('Rate value should be numeric');
		}

		// Send request
		$args = array(
			$itemId, $companyId, $rateValue
		);
		$response = $this->_phantomJsDataProvider->setArguments($args)
			->setScriptPath($this->_scriptDirectoryPath . 'set_rate.js')
			->getData();

		// Check for errors
		$this->_checkForErrorsAndAuthorizeOrRaiseException($response, 'set_rate.js', $args);

		return strpos($response, 'SUCCESS') > -1;
	}

	/**
	 * Check and authorize
	 *
	 * @param string $response
	 * @param string $scriptName
	 * @param array $args
	 * @throws AtiApi\Exception
	 */
	protected function _checkForErrorsAndAuthorizeOrRaiseException($response, $scriptName, array $args = null)
	{
		if (strpos($response, 'ERROR') > -1) {

			// If not authorized, then authorize
			if (strpos($response, 'Not authorized') > -1) {

				if (!$this->_authorize()) {
					throw new Exception('Can`t authorize');
				} else {
					// Authorized, repeat request
					$response = $this->_phantomJsDataProvider->setArguments($args)
						->setScriptPath($this->_scriptDirectoryPath . $scriptName)
						->getData();

					// Error again...
					if (strpos($response, 'ERROR') > -1) {
						throw new Exception($response);
					}

				}

			} else {
				// Authorized, but have error
				throw new Exception($response);
			}

		}
	}

	/**
	 * Authorize
	 *
	 * @return bool
	 */
	protected function _authorize()
	{
		$data = $this->_phantomJsDataProvider->setArguments(
			null
		)
			->setScriptPath($this->_scriptDirectoryPath . 'auth.js')
			->getData();

		return strpos($data, 'OK') > -1;
	}
} 