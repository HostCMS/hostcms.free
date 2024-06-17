<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Online shop.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Shop_Item_Export_Csv_Controller extends Core_Servant_Properties
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'shopId',
		'separator',
		'encoding',
		'exportToFile',
		'fileName'
	);

	/**
	 * Constructor.
	 * @param int $iShopId shop ID
	 */
	public function __construct($iShopId)
	{
		parent::__construct();

		$this->shopId = $iShopId;

		// Устанавливаем лимит времени выполнения в 1 час
		(!defined('DENY_INI_SET') || !DENY_INI_SET)
			&& function_exists('set_time_limit') && ini_get('safe_mode') != 1 && @set_time_limit(3600);
	}

	/** 
	 * Get File Name
	 * @var string|NULL
	 */
	//protected $_fileName = NULL;

	/** 
	 * Get File Name
	 * @return string
	 */
	/*public function getFileName()
	{
		return $this->_fileName;
	}*/

	/**
	 * Array of titile line
	 * @var array
	 */
	protected $_aCurrentRow = array();

	/**
	 * Get Current Row
	 * @return array
	 */
	public function getCurrentRow()
	{
		return $this->_aCurrentRow;
	}

	/**
	 * Set Current Row
	 * @param array $array
	 * @return self
	 */
	public function setCurrentRow(array $array)
	{
		$this->_aCurrentRow = $array;
		return $this;
	}

	protected $_cacheGetListValue = array();

	protected function _getListValue($list_item_id)
	{
		if ($list_item_id && Core::moduleIsActive('list'))
		{
			if (!isset($this->_cacheGetListValue[$list_item_id]))
			{
				$oList_Item = Core_Entity::factory('List_Item')->getByid($list_item_id, FALSE);

				$this->_cacheGetListValue[$list_item_id] = $oList_Item ? $oList_Item->value : '';
			}

			return $this->_cacheGetListValue[$list_item_id];
		}

		return '';
	}

	/**
	 * Get value of Property_Value
	 * @param Property_Model $oProperty
	 * @param mixed $oProperty_Value
	 * @param mixed $object
	 * @return string
	 * @hostcms-event Shop_Item_Export_Csv_Controller.onGetPropertyValueDefault
	 */
	protected function _getPropertyValue($oProperty, $oProperty_Value, $object)
	{
		switch ($oProperty->type)
		{
			case 0: // Int
			case 1: // String
			case 4: // Textarea
			case 6: // Wysiwyg
			case 7: // Checkbox
			case 10: // Hidden field
			case 11: // Float
				$result = $oProperty_Value->value;
			break;
			case 2: // File
				$href = method_exists($object, 'getItemHref')
					? $object->getItemHref()
					: $object->getGroupHref();
				
				$result = $oProperty_Value->file == ''
					? ''
					: $oProperty_Value
						->setHref($href)
						->getLargeFileHref();
			break;
			case 3: // List
				$result = $this->_getListValue($oProperty_Value->value);
			break;
			case 5: // Informationsystem
				$result = $oProperty_Value->value
					? $oProperty_Value->Informationsystem_Item->name
					: '';
			break;
			case 8: // Date
				$result = Core_Date::sql2date($oProperty_Value->value);
			break;
			case 9: // Datetime
				$result = Core_Date::sql2datetime($oProperty_Value->value);
			break;
			case 12: // Shop
				$result = $oProperty_Value->value
					? $oProperty_Value->Shop_Item->name
					: '';
			break;
			default:
				$result = $oProperty_Value->value;

				Core_Event::notify(get_class($this) . '.onGetPropertyValueDefault', $this, array($oProperty, $oProperty_Value, $object));

				if (!is_null(Core_Event::getLastReturn()))
				{
					$result = Core_Event::getLastReturn();
				}
		}

		return $result;
	}

	/**
	 * Prepare string
	 * @param string $string
	 * @return string
	 */
	public function prepareString($string)
	{
		return str_replace('"', '""', trim((string) $string));
	}

	/**
	 * Prepare cell
	 * @param string $string
	 * @return string
	 */
	public function prepareCell($string)
	{
		return sprintf('"%s"', $this->prepareString($string));
	}

	/**
	 * Prepare float
	 * @param mixed $string
	 * @return string
	 */
	public function prepareFloat($string)
	{
		return str_replace('.', ',', $string);
	}

	protected $_content = '';

	/**
	 * Print array
	 * @param array $aData
	 * @return self
	 */
	protected function _printRow($aData)
	{
		$str = Core_Str::iconv('UTF-8', $this->encoding, implode($this->separator, $aData) . "\n");

		if ($this->exportToFile)
		{
			$this->_content .= $str;

			if (strlen($this->_content) > 1000000)
			{
				$this->_saveToFile();
			}
		}
		else
		{
			echo $str;
		}

		return $this;
	}

	protected function _saveToFile()
	{
		file_put_contents(CMS_FOLDER . TMP_DIR . $this->fileName, $this->_content, FILE_APPEND);
		$this->_content = '';
	}

	/**
	 * Save rest data
	 * @return self
	 */
	protected function _finish()
	{
		if ($this->exportToFile && strlen($this->_content))
		{
			$this->_saveToFile();
		}
	}
}