<?php
/**
 * Grabber
 *
 * @author Yegor Chuperka <ychuperka@gmail.com>
 */

namespace Ychuperka;

use Ychuperka\Grabber\DataProvider\HttpDataProvider;
use Ychuperka\Grabber\DataProvider\PhantomJsDataProvider;
use Ychuperka\Grabber\Exception;

class Grabber {

    const REGEX_PATTERN_ICQ_NUMBER = '/[0-9]{5,}/';
    const REGEX_PATTERN_PHONE_NUMBER = '/\+7\([0-9]{3,4}\)[0-9]{6,}/';
    const REGEX_PATTERN_DATE_PATTERN = '/[0-9]{4}\-[0-9]{2}\-[0-9]{2}/';

    /**
     * Options (params for request)
     *
     * @var array
     */
    private $_options;

    /**
     * Scripts directory
     *
     * @var string
     */
    private $_scriptsDirPath;

    /**
     * PhantomJS data provider
     *
     * @var Grabber\DataProvider\PhantomJsDataProvider
     */
    private $_phantomJsDataProvider;

    /**
     * Last page number (can change when grabber work)
     *
     * @var int
     */
    private $_lastPageNumber;

    /**
     * Cache for geo codes
     *
     * @var array
     */
    private $_geoCodes;

    /**
     * Constructor
     */
    public function __construct($cookiesDirectoryPath)
    {
        // Initial options
        $this->_options = array(
            'EntityType' => 'Load',
            'PageSize' => 100
        );

        $this->_scriptsDirPath = realpath(
            dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'phantom_scripts'
        ) . DIRECTORY_SEPARATOR;
        $this->_lastPageNumber = 1;
        $this->_pageLinks = array();
        $this->_geoCodes = array();

        // Check cookies directory
        if (!is_dir($cookiesDirectoryPath) || !is_writable($cookiesDirectoryPath)) {
            throw new Exception('Cookies directory not exists or not writable');
        }

        $cookiesFilePath = $cookiesDirectoryPath . DIRECTORY_SEPARATOR . 'phantomjs-cookies.txt';

        $this->_phantomJsDataProvider = new PhantomJsDataProvider(null, $cookiesFilePath);
    }

    /**
     * Run
     *
     * @return array
     */
    public function run()
    {
        return $this->_getItems();
    }

    /**\
     * Get last page number
     *
     * @return int
     */
    public function getLastPageNumber()
    {
        return $this->_lastPageNumber;
    }

    /**
     * Set from geo
     *
     * @param string $fromGeo
     * @return $this
     */
    public function setFromGeo($fromGeo)
    {
        $this->_options['FromGeo'] = $this->_convertCityNameToCode($fromGeo);
        return $this;
    }

    /**
     * Set to geo
     *
     * @param string $toGeo
     * @return $this
     */
    public function setToGeo($toGeo)
    {
        $this->_options['ToGeo'] = $this->_convertCityNameToCode($toGeo);
        return $this;
    }

    /**
     * Set from radius
     *
     * @param int $radius
     * @return $this
     */
    public function setFromRadius($radius)
    {
        $this->_options['FromGeoRadius'] = $radius;
        return $this;
    }

    /**
     * Set to radius
     *
     * @param int $radius
     * @return $this
     */
    public function setToRadius($radius)
    {
        $this->_options['ToGeoRadius'] = $radius;
        return $this;
    }

    /**
     * Set car type
     *
     * @param string $carType
     * @return $this
     */
    public function setCarType($carType)
    {
        $this->_options['CarType'] = $carType;
        return $this;
    }

    /**
     * Set first date
     *
     * @param string $firstDate
     * @return $this
     * @throws Exception
     */
    public function setFirstDate($firstDate)
    {
        if (strlen($firstDate) == 0 || !preg_match_all(self::REGEX_PATTERN_DATE_PATTERN, $firstDate)) {
            throw new Exception('Invalid date, should be in format "0000-00-00"');
        }

        $this->_options['FirstDate'] = $firstDate;
        return $this;
    }

    /**
     * Set from weight
     *
     * @param int $weight
     * @return $this
     */
    public function setFromWeight($weight)
    {
        $this->_options['Weight'] = $weight;
        return $this;
    }

    /**
     * Set to weight
     *
     * @param int $weight
     * @return $this
     */
    public function setToWeight($weight)
    {
        $this->_options['Weight2'] = $weight;
        return $this;
    }

    /**
     * Set from volume
     *
     * @param int $volume
     * @return $this
     */
    public function setFromVolume($volume)
    {
        $this->_options['Volume'] = $volume;
        return $this;
    }

    /**
     * Set to volume
     *
     * @param int $volume
     * @return $this
     */
    public function setToVolume($volume)
    {
        $this->_options['Volume2'] = $volume;
        return $this;
    }

    /**
     * Set from length
     *
     * @param int $length
     * @return $this
     */
    public function setFromLength($length)
    {
        $this->_options['Length'] = $length;
        return $this;
    }

    /**
     * Set to length
     *
     * @param int $length
     * @return $this
     */
    public function setToLength($length)
    {
        $this->_options['Length2'] = $length;
        return $this;
    }

    /**
     * Set from width
     *
     * @param int $width
     * @return $this
     */
    public function setFromWidth($width)
    {
        $this->_options['Width'] = $width;
        return $this;
    }

    /**
     * Set to width
     *
     * @param int $width
     * @return $this
     */
    public function setToWidth($width)
    {
        $this->_options['Width2'] = $width;
        return $this;
    }

    /**
     * Set from height
     *
     * @param int $height
     * @return $this
     */
    public function setFromHeight($height)
    {
        $this->_options['Height'] = $height;
        return $this;
    }

    /**
     * Set to height
     *
     * @param int $height
     * @return $this
     */
    public function setToHeight($height)
    {
        $this->_options['Height2'] = $height;
        return $this;
    }

    /**
     * Set page number
     *
     * @param int $number
     * @return $this
     */
    public function setPageNumber($number)
    {
        $this->_options['PageNumber'] = $number;
        return $this;
    }

    /**
     * Set captcha od
     *
     * @param string $value
     * @return $this
     */
    public function setCaptchaId($value)
    {
        $this->_options['cid'] = $value;
        return $this;
    }

    /**
     * Set captcha code
     *
     * @param string $value
     * @return $this
     */
    public function setCaptchaCode($value)
    {
        $this->_options['txt'] = $value;
        return $this;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->_options;
    }

    /**
     * Get options as args
     *
     * @return array
     */
    protected function _getOptionsAsArgs()
    {
        $args = array();
        foreach ($this->_options as $key => $value) {
            $args[] = "$key=$value";
        }

        return $args;
    }

    /**
     * Get items
     *
     * @return array
     * @throws Grabber\Exception
     */
    protected function _getItems()
    {
        // Send request
        $data = $this->_phantomJsDataProvider->setArguments(
            $this->_getOptionsAsArgs()
        )
        ->setScriptPath($this->_scriptsDirPath . 'load_items_page.js')
        ->getData();

        // Check response

        // Not authorized
        if (substr_count($data, 'ERROR') > 0 && substr_count($data, 'MESSAGE: Not authorized') > 0) {

            // If not authorized, then authorize
            if (!$this->_authorize()) {
                throw new Exception('Can`t authorize');
            }

            // Send request again
            $data = $this->_phantomJsDataProvider->setArguments(
                $this->_getOptionsAsArgs()
            )
            ->setScriptPath($this->_scriptsDirPath . 'load_items_page.js')
            ->getData();

        }

        // Captcha
        if (substr_count($data, 'ERROR') > 0 && substr_count($data, 'MESSAGE: Can`t validate captcha') > 0) {

            // Try to get captcha id
            $captchaDataPos = strpos($data, 'CAPTCHA_DATA: ');
            if ($captchaDataPos > -1) {

                $captchaData = substr($data, $captchaDataPos);
                $captchaData = str_replace(array('CAPTCHA_DATA:', 'ERROR'), null, $captchaData);
                $captchaData = json_decode($captchaData, true);

                throw new Exception\CaptchaException(
                    'Captcha found', Exception\CaptchaException::CODE_FOUND_WITH_ID,
                    null, $captchaData['captcha_id'], $captchaData['captcha_image_data_base64']
                );

            } else {

                throw new Exception\CaptchaException(
                    'Captcha found without id', Exception\CaptchaException::CODE_FOUND
                );

            }
        }

        if (substr_count($data, 'ERROR')) {

            throw new Exception($data);

        }

        // Parse response
        $contentPos = strpos($data, 'CONTENT:') + strlen('CONTENT:');
        $content = substr($data, $contentPos);

        return $this->_prepareItems($content);
    }

    /**
     * Prepare items
     *
     * @param string $content
     * @return array
     */
    protected function _prepareItems($content)
    {
        $dom = str_get_html($content);

        // Get last page number
        $this->_lastPageNumber = $this->_getLastPageNumber($dom);

        // Get table rows and process
        $result = array();
        $tableRows = $dom->find('div#pnlTable table.atiTables tr.firstRow');
        $trsCnt = count($tableRows);
        for ($i = 0; $i < $trsCnt; $i ++) {
            $row = $tableRows[$i];

            $item = $this->_prepareItem($row);
            if (count($item) == 0) {
                continue;
            }

            if (isset($result[$item['id']])) {
                continue;
            }

            $result[$item['id']] = $item;
        }

        return $result;
    }

    /**
     * Prepare item
     *
     * @param \simple_html_dom_node $row
     * @return array
     * @throws Grabber\Exception
     */
    protected function _prepareItem(\simple_html_dom_node $row)
    {
        $item = array();

        // Get second id and company id
        $combinedId = $row->combinedid;
        $parts = explode('_', $combinedId);
        $item['second_id'] = $parts[0];
        $item['company_id'] = $parts[1];
        unset($parts, $combinedId);

        // Process columns
        $columns = $row->find('td');
        $count = count($columns);
        for($i = 0; $i < $count; $i ++) {

            $column = $columns[$i];

            if ($i == 0) {

                $idAnchor = $column->find('a', 0);
                if ($idAnchor) {

                    $item['id'] = $idAnchor->name;

                }

                continue;
            }

            $methodName = '_processColumn' . $i;

            if (method_exists($this, $methodName)) {
                $this->{$methodName}($column, $item);
            }
        }

        // Process contacts
        $this->_processContacts($row, $item);

        // Prepare data
        foreach ($item as $key => $value) {

            if ($key == 'contacts') {
                continue;
            }

            $value = trim($value);
            $value = str_replace(
                array(
                    "\t",
                    "\n",
                    "\r",
                    '&nbsp;'
                ),
                null,
                $value
            );

            if (strlen($value) == 0) {
                unset($item[$key]);
            } else {
                $item[$key] = $value;
            }
        }

        if (isset($item['contacts']['customer']['info'])) {
            $item['contacts']['customer']['info'] = str_replace(
                array(
                    "\t",
                    "\n",
                    "\r",
                    '&nbsp;'
                ),
                null,
                trim($item['contacts']['customer']['info'])
            );
        }

        return $item;
    }

    /**
     * Process column #1
     *
     * @param \simple_html_dom_node $column
     * @param array $item
     */
    protected function _processColumn1(\simple_html_dom_node $column, array& $item)
    {
        $directionTextBlock = $column->find('div.directionText', 0);
        if (!$directionTextBlock) {
            return;
        }

        $directionBlock = $directionTextBlock->find('div[style=font-weight: bold;]', 0);
        if ($directionBlock) {
            $item['direction'] = $directionBlock->plaintext;
        }

        $distanceAnchor = $directionTextBlock->find('a[style=color:#358B0A;]', 0);
        if ($distanceAnchor) {
            $item['distance'] = $distanceAnchor->plaintext;
        }

        $pathAnchor = $directionTextBlock->find('a[style=color:Red;white-space: nowrap;]', 0);
        if ($pathAnchor) {
            $item['path'] = $pathAnchor->plaintext;
        }
    }

    /**
     * Process column #2
     *
     * @param \simple_html_dom_node $column
     * @param array $item
     */
    protected function _processColumn2(\simple_html_dom_node $column, array& $item)
    {
        $transportBlock = $column->find('div.gridCell', 0);
        if (!$transportBlock) {
            return;
        }

        if ($transportBlock) {
            $item['transport'] = $transportBlock->plaintext;
        }
    }

    /**
     * Process column #3
     *
     * @param \simple_html_dom_node $column
     * @param array $item
     */
    protected function _processColumn3(\simple_html_dom_node $column, array& $item)
    {
        $dataBlock = $column->find('div.gridCell', 0);
        if (!$dataBlock) {
            return;
        }

        $weightAndVolume = $dataBlock->find('div[style=display: table-cell;] b');
        $wavCnt = count($weightAndVolume);
        if ($wavCnt) {
            for ($i = 0; $i < $wavCnt; $i ++) {
                switch ($i) {
                    case 0:
                        $item['weight'] = $weightAndVolume[$i]->plaintext;
                        break;
                    case 1:
                        $item['volume'] = $weightAndVolume[$i]->plaintext;
                        break;
                }
            }
        }

        $cargoBlock = $dataBlock->find('div[style=float: left; padding-right: 5px;]', 0);
        if ($cargoBlock) {
            $item['cargo'] = $cargoBlock->plaintext;
        }

        $cargoNoteBlock = $dataBlock->find('div.noteText', 0);
        if ($cargoNoteBlock) {
            $item['cargo_note'] = $cargoNoteBlock->plaintext;
        }
    }

    /**
     * Process column #4
     *
     * @param \simple_html_dom_node $column
     * @param array $item
     */
    protected function _processColumn4(\simple_html_dom_node $column, array& $item)
    {
        $dataBlock = $column->find('div.gridCell', 0);
        if (!$dataBlock) {
            return;
        }

        $item['load_data'] = $dataBlock->plaintext;
    }

    /**
     * Process columns #5
     *
     * @param \simple_html_dom_node $column
     * @param array $item
     */
    protected function _processColumn5(\simple_html_dom_node $column, array& $item)
    {
        $dataBlock = $column->find('div.gridCell', 0);
        if (!$dataBlock) {
            return;
        }

        $item['unload_data'] = $dataBlock->plaintext;
    }

    /**
     * Process column #6
     *
     * @param \simple_html_dom_node $column
     * @param array $item
     */
    protected function _processColumn6(\simple_html_dom_node $column, array& $item)
    {
        $dataBlock = $column->find('div.gridCell', 0);
        if (!$dataBlock) {
            return;
        }

        $item['price_data'] = $dataBlock->plaintext;
    }

    /**
     * Process contacts
     *
     * @param \simple_html_dom_node $row
     * @param array $item
     */
    protected function _processContacts(\simple_html_dom_node $row, array& $item)
    {
        // Try to get contacts row (next after item data row)
        $contactsRow = $row->next_sibling();
        if (!$contactsRow) {
            return;
        }

        // Try to get contacts block
        $contactsBlock = $contactsRow->find('div.firmContacts', 0);
        if (!$contactsBlock) {
            return;
        }

        // Try to get note
        $noteCell = $contactsBlock->find('td.noteText[style=color:#333333;padding-left: 5px;]', 0);
        if ($noteCell) {
            $item['contacts']['note'] = $noteCell->plaintext;
        }

        // Try to get customer data
        $customerTable = $contactsBlock->find('table[id*=tblFirmData]', 0);
        if ($customerTable) {

            // Get customer data
            $item['contacts']['customer']['info'] = $customerTable->plaintext;

            // Get customer rating
            $ratingSpan = $customerTable->find('span[id*=starReliabilityName]', 0);
            if ($ratingSpan) {

                $rateDescription = $ratingSpan->ratedescription;
                $matches = array();
                preg_match_all(
                    '/:\s([0-9,]+)\./',
                    $rateDescription,
                    $matches
                );
                if (count($matches) > 0 && count($matches[1]) > 0) {
                    $item['contacts']['customer']['rating'] = str_replace(',', '.', $matches[1][0]);
                }
            }
        }

        // Try to get icq, skype
        $contactsDataTables = $contactsBlock->find('table[id*=tblContactsData]');
        if (count($contactsDataTables) > 0) {

            foreach ($contactsDataTables as $table) {

                // Try to get skype login
                $skypeAnchors = $table->find('a[id*=hlkSkype]');
                if (count($skypeAnchors) > 0) {
                    foreach ($skypeAnchors as $sa) {
                        $skypeLogin = str_replace('callto:', null, $sa->href);
                        if (strlen($skypeLogin) > 0) {
                            $item['contacts']['skype'][] = $skypeLogin;
                        }
                    }
                }

                // Try to get icq number
                $icqImgs = $table->find('a[id*=hlkICQ] img');
                if (count($icqImgs) > 0) {
                    foreach ($icqImgs as $img) {

                        $src = $img->src;
                        $matches = array();
                        preg_match_all(self::REGEX_PATTERN_ICQ_NUMBER, $src, $matches);
                        if (count($matches) > 0) {
                            $item['contacts']['icq'][] = $matches[0][0];
                        }

                    }
                }
            }
        }

        // Try to get phone numbers
        $text = str_replace(
            array(
                "\n",
                "\t",
                "\r",
                ' '
            ),
            null, $contactsBlock->plaintext
        );

        $matches = array();
        preg_match_all(self::REGEX_PATTERN_PHONE_NUMBER, $text, $matches);

        if (count($matches) > 0) {

            foreach ($matches[0] as $phoneNumber) {

                $item['contacts']['phone_number'][] = $phoneNumber;

            }

        }

        // Try to get name
        $lastCommaPos = strrpos($text, ',') + 1;
        if ($lastCommaPos != -1) {
            $name = substr($text, $lastCommaPos);
            if (!preg_match(self::REGEX_PATTERN_PHONE_NUMBER, $name)) {
                $item['contacts']['name'] = $name;
            }
        }
    }

    /**
     * Get last page number
     *
     * @param \simple_html_dom $dom
     * @return int
     */
    protected function _getLastPageNumber(\simple_html_dom $dom)
    {
        // Get all anchors in pagination
        $anchors = $dom->find('div#cphMain_hlpTop_pnlPager a');
        if (count($anchors) == 0) {
            return 1;
        }

        foreach ($anchors as $item) {

            if ($item->plaintext === 'след.') {
                $lastPageAnchor = $item->prev_sibling();
                return  (int)$lastPageAnchor->plaintext;
            }

        }

        return 0;
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
        ->setScriptPath($this->_scriptsDirPath . 'auth.js')
        ->getData();

        return substr_count($data, 'OK') > 0;
    }

    /**
     * Convert city name to code
     *
     * @param string $cityName
     * @return string
     * @throws Grabber\Exception
     */
    protected function _convertCityNameToCode($cityName)
    {
        if (isset($this->_geoCodes[$cityName])) {
            return $this->_geoCodes[$cityName];
        }

        // Prepare payload
        $payload = array(
            'prefixText' => $cityName,
            'count' => 10,
            'contextKey' => 'All_1$Rus'
        );
        $payload = json_encode($payload);

        // Prepare http data provider
        $httpDataProvider = new HttpDataProvider();

        // Get city code
        $httpDataProvider->setRawPayload($payload)
            ->setCurlOption(
                CURLOPT_HTTPHEADER,
                array(
                    'Content-Type: application/json'
                )
            );

        $response = $httpDataProvider->setUrl('http://ati.su/Trace/ATIGeoService.asmx/GetGeoCompletionList')
            ->setRequestType(HttpDataProvider\Request::RT_POST)
            ->getData();

        $response = mb_convert_encoding($response, 'UTF-8');
        $decoded = json_decode($response, true);
        if (!$decoded) {
            throw new Exception('Can`t decode response');
        }

        if (count($decoded['d']) == 0) {
            throw new Exception("Can`t find code for name \"$cityName\"");
        }

        $codeData = $decoded['d'][0];
        $this->_geoCodes[$cityName] = $codeData['Value'];

        return $codeData['Value'];
    }
}