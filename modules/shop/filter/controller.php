<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Online shop.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2019 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Filter_Controller extends Core_Servant_Properties
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'limit',
		'max_time',
		'total',
		'position'
	);

	/**
	 * Shop order object
	 * @var Shop_Model
	 */
	protected $_oShop = NULL;

	/**
	 * Constructor.
	 * @param Shop_Model $oShop shop
	 */
	public function __construct(Shop_Model $oShop)
	{
		parent::__construct();

		$this->limit = 5;
		$this->max_time = 20;
		$this->position = 0;

		$this->total = $oShop->Shop_Items->getCount();

		$this->_oShop = $oShop;
	}

	/**
	 * Get property sql string
	 * @param Property_Model $oProperty
	 * @return array array('column' => ..., 'index' => ...)
	 */
	protected function _getPropertySql(Property_Model $oProperty)
	{
		switch ($oProperty->type)
		{
			case 0: // Целое число
			case 3: // Список
				$sColumn = "`property{$oProperty->id}` int(11) NOT NULL DEFAULT '0'";
				$sIndex = "`p{$oProperty->id}` (`property{$oProperty->id}`) USING BTREE";
			break;
			case 1: // Строка
			default:
				$sColumn = "`property{$oProperty->id}` varchar(255) DEFAULT NULL";
				$sIndex = "`p{$oProperty->id}` (`property{$oProperty->id}`(10)) USING BTREE";
			break;
			case 7: // Флажок
				$sColumn = "`property{$oProperty->id}` tinyint(1) NOT NULL DEFAULT '0'";
				$sIndex = "`p{$oProperty->id}` (`property{$oProperty->id}`) USING BTREE";
			break;
			case 8: // Дата
				$sColumn = "`property{$oProperty->id}` date NOT NULL DEFAULT '0000-00-00'";
				$sIndex = "`p{$oProperty->id}` (`property{$oProperty->id}`) USING BTREE";
			break;
			case 9: // Дата-время
				$sColumn = "`property{$oProperty->id}` datetime NOT NULL DEFAULT '0000-00-00 00:00:00'";
				$sIndex = "`p{$oProperty->id}` (`property{$oProperty->id}`) USING BTREE";
			break;
			case 11: // Число с плавающей запятой
				$sColumn = "`property{$oProperty->id}` double NOT NULL DEFAULT '0'";
				$sIndex = "`p{$oProperty->id}` (`property{$oProperty->id}`) USING BTREE";
			break;
		}

		return array(
			'column' => $sColumn,
			'index' => $sIndex
		);
	}

	/**
	 * Create shop filter table
	 * @return self
	 */
	public function createTable()
	{
		$aColumns = array(
			"`id` int(11) NOT NULL AUTO_INCREMENT",
			"`shop_item_id` int(11) NOT NULL DEFAULT '0'",
			"`modification_id` int(11) NOT NULL DEFAULT '0'",
			"`shop_group_id` int(11) NOT NULL DEFAULT '0'",
			"`shop_producer_id` int(11) NOT NULL DEFAULT '0'",
			"`shop_currency_id` int(11) NOT NULL DEFAULT '0'",
			"`price` decimal(12,2) NOT NULL DEFAULT '0.00'",
			"`price_absolute` decimal(12,2) NOT NULL DEFAULT '0.00'",
			"`available` tinyint(1) NOT NULL DEFAULT '0'"
		);

		// Indexes
		$aIndexes = array(
			'PRIMARY KEY (`id`)',
			'KEY `shop_item_id` (`shop_item_id`)',
			'KEY `shop_group_id` (`shop_group_id`)',
			'KEY `shop_currency_id` (`shop_currency_id`)',
			'KEY `price` (`price_absolute`) USING BTREE',
			'KEY `producer` (`shop_producer_id`) USING BTREE'
		);

		$oShop_Item_Properties = $this->_oShop->Shop_Item_Properties;
		$oShop_Item_Properties->queryBuilder()
			->where('shop_item_properties.filter', '!=', 0)
			->clearOrderBy()
			->orderBy('shop_item_properties.property_id');

		$aShop_Item_Properties = $oShop_Item_Properties->findAll(FALSE);

		foreach($aShop_Item_Properties as $oShop_Item_Property)
		{
			$oProperty = $oShop_Item_Property->Property;

			$aPropertySql = $this->_getPropertySql($oProperty);

			$aColumns[] = $aPropertySql['column'];
			$aIndexes[] = 'KEY ' . $aPropertySql['index'];
		}

		$sColumns = implode(', ', $aColumns);
		$sIndexes = implode(', ', $aIndexes);

		$oCore_DataBase = Core_DataBase::instance();
		$aConfig = $oCore_DataBase->getConfig();

		$sEngine = isset($aConfig['storageEngine'])
			? $aConfig['storageEngine']
			: 'MyISAM';

		$query = "
			CREATE TABLE IF NOT EXISTS `shop_filter{$this->_oShop->id}` (
			  {$sColumns}
			  , {$sIndexes}
			) ENGINE={$sEngine} DEFAULT CHARSET=utf8 AUTO_INCREMENT=0;
		";

		$oCore_DataBase->query($query);

		return $this;
	}

	/**
	 * Add property column and index into table
	 * @param Property_Model $oProperty
	 * @return self
	 */
	public function addProperty(Property_Model $oProperty)
	{
		if (in_array($oProperty->type, $this->_aAvailablePropertyTypes))
		{
			$aPropertySql = $this->_getPropertySql($oProperty);

			Core_DataBase::instance()->query("ALTER TABLE `shop_filter{$this->_oShop->id}` ADD {$aPropertySql['column']}");
			Core_DataBase::instance()->query("ALTER TABLE `shop_filter{$this->_oShop->id}` ADD INDEX {$aPropertySql['index']}");
		}

		return $this;
	}

	/**
	 * Remove property column and index from table
	 * @param Property_Model $oProperty
	 * @return self
	 */
	public function removeProperty(Property_Model $oProperty)
	{
		if (in_array($oProperty->type, $this->_aAvailablePropertyTypes))
		{
			// Core_DataBase::instance()->query("ALTER TABLE `shop_filter{$this->_oShop->id}` DROP INDEX `p{$oProperty->id}`");
			Core_DataBase::instance()->query("ALTER TABLE `shop_filter{$this->_oShop->id}` DROP `property{$oProperty->id}`");
		}

		return $this;
	}

	protected $_cacheTableColumns = NULL;

	protected function _getTableColumns()
	{
		if (is_null($this->_cacheTableColumns))
		{
			$oCore_DataBase = Core_DataBase::instance();

			$this->_cacheTableColumns = array();

			$aTableColumns = $oCore_DataBase->getColumns('shop_filter' . $this->_oShop->id);

			foreach ($aTableColumns as $key => $aTableColumn)
			{
				$key != 'id'
					&& $this->_cacheTableColumns[$key] = $oCore_DataBase->quoteColumnName($key);
			}
		}

		return $this->_cacheTableColumns;
	}

	protected $_cachePropertyIDs = NULL;

	protected function _getPropertyIDs()
	{
		if (is_null($this->_cachePropertyIDs))
		{
			$this->_cachePropertyIDs = array();

			$aTableColumns = $this->_getTableColumns();

			foreach ($aTableColumns as $key => $aTableColumn)
			{
				if (strpos($key, 'property') === 0)
				{
					$property_id = substr($key, 8);
					$this->_cachePropertyIDs[$property_id] = $property_id;
				}
			}
		}

		return $this->_cachePropertyIDs;
	}

	protected $_aAvailablePropertyTypes = array(0, 1, 3, 7, 8, 9, 11);

	/**
	 * Fill table rows
	 * @param object $oProperty Property_Model object
	 * @return self
	 */
	public function fill(Shop_Item_Model $oShop_Item)
	{
		$tableName = 'shop_filter' . $this->_oShop->id;

		// remove
		Core_QueryBuilder::delete($tableName)
			->where('shop_item_id', '=', $oShop_Item->id)
			->execute();

		$oDefaultCurrency = Core_Entity::factory('Shop_Currency')->getDefault();

		$fCurrencyCoefficient = $oShop_Item->Shop_Currency->id > 0 && $oDefaultCurrency->id > 0
			? Shop_Controller::instance()->getCurrencyCoefficientInShopCurrency(
				$oShop_Item->Shop_Currency, $oDefaultCurrency
			)
			: 0;

		// warehouse rest
		$available = $oShop_Item->getRest() > 0 ? 1 : 0;

		// prices
		$aPrices = $oShop_Item->getPrices();

		$aBaseInserts = array(
			$oShop_Item->id,
			$oShop_Item->modification_id,
			$oShop_Item->shop_group_id,
			$oShop_Item->shop_producer_id,
			$oShop_Item->shop_currency_id,
			$aPrices['price_discount'],
			$aPrices['price_discount'] * $fCurrencyCoefficient, // price_absolute
			$available
		);

		$aPropertyIds = $this->_getPropertyIDs();

		// Collect values by property_id
		$aProperty_Values = $oShop_Item->getPropertyValues();
		$aTmp = array();
		foreach($aProperty_Values as $oProperty_Value)
		{
			$oProperty = $oProperty_Value->Property;

			if (in_array($oProperty->type, $this->_aAvailablePropertyTypes))
			{
				if (isset($aPropertyIds[$oProperty->id]))
				{
					$aTmp[$oProperty->id][] = $oProperty_Value->value;
				}
			}
		}

		$aPV = array();
		foreach ($aPropertyIds as $property_id)
		{
			$aPV[] = isset($aTmp[$property_id]) ? $aTmp[$property_id] : array($this->_getDefaultValue($property_id));
		}

		// get all posible combinations
		$aCombinations = $this->_combinations($aPV);

		$oCore_DataBase = Core_DataBase::instance();

		$aInserts = array();

		if (count($aCombinations))
		{
			foreach ($aCombinations as $aCombination)
			{
				$aValues = array_merge($aBaseInserts, $aCombination);
				$aValues = array_map(array($oCore_DataBase, 'quote'), $aValues);

				$aInserts[] = '(' . implode(', ', $aValues) . ')';
			}
		}
		else
		{
			$aValues = array_map(array($oCore_DataBase, 'quote'), $aBaseInserts);
			$aInserts[] = '(' . implode(', ', $aValues) . ')';
		}

		$aTableColumnNames = $this->_getTableColumns();
		$sTableColumnNames = implode(',', $aTableColumnNames);

		$query = "INSERT INTO `{$tableName}` ({$sTableColumnNames}) VALUES " . implode(",\n", $aInserts);

		$oCore_DataBase->query($query);
	}

	protected function _getDefaultValue($property_id)
	{
		$oProperty = Core_Entity::factory('Property', $property_id);

		switch ($oProperty->type)
		{
			case 0:
			case 3:
			case 7:
			case 11:
				$defaultValue = 0;
			break;
			case 1:
				$defaultValue = '';
			break;
			case 8:
				$defaultValue = '0000-00-00';
			break;
			case 9:
				$defaultValue = '0000-00-00 00:00:00';
			break;
		}

		return $defaultValue;
	}

	protected function _combinations($arrays, $i = 0)
	{
		if (!isset($arrays[$i]))
		{
			return array();
		}

		if ($i == count($arrays) - 1)
		{
			return $arrays[$i];
		}

		// get combinations from subsequent arrays
		$tmp = $this->_combinations($arrays, $i + 1);

		$result = array();

		// concat each array from tmp with each element from $arrays[$i]
		foreach ($arrays[$i] as $v)
		{
			foreach ($tmp as $t)
			{
				$result[] = is_array($t)
					? array_merge(array($v), $t)
					: array($v, $t);
			}
		}

		return $result;
	}

	public function checkPropertyExist($property_id)
	{
		$aTableColumns = $this->_getTableColumns();

		foreach ($aTableColumns as $key => $aTableColumn)
		{
			if (strpos($key, strval($property_id)))
			{
				return TRUE;
			}
		}

		return FALSE;
	}

	public function rebuild()
	{
		if ($this->position == 0)
		{
			//Drop table
			Core_DataBase::instance()->query("DROP TABLE IF EXISTS `shop_filter{$this->_oShop->id}`");

			$this->createTable();
		}

		/*$limit = 1000;
		$offset = 0;

		do {
			$oShop_Items = $this->_oShop->Shop_Items;
			$oShop_Items->queryBuilder()
				->limit($limit)
				->offset($offset)
				->clearOrderBy()
				->orderBy('shop_items.id');

			$aShop_Items = $oShop_Items->findAll(FALSE);

			foreach($aShop_Items as $oShop_Item)
			{
				$this->fill($oShop_Item);
			}

			$offset += $limit;
		}
		while($aShop_Items);*/

		try
		{
			$timeout = Core::getmicrotime();

			if ($this->total)
			{
				$iCounter = 0;

				$aShop_Items = $this->_oShop->Shop_Items->findAll(FALSE);

				while ((Core::getmicrotime() - $timeout + 3 < $this->max_time)
					&& $iCounter < $this->limit
					&& isset($aShop_Items[$this->position]))
				{
					$oShop_Item = $aShop_Items[$this->position];

					$this->fill($oShop_Item);

					$iCounter++;
					$this->position = $this->position + 1;
				}
			}

			if ($this->position >= $this->total)
			{
				Core_Message::show(Core::_('Shop_Filter.rebuild_end'));

				return TRUE;
			}
		}
		catch (Exception $e){
			Core_Message::show($e->getMessage(), 'error');
			return NULL;
		}

		return FALSE;
	}
}