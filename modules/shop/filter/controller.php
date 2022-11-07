<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Online shop.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Filter_Controller
{
	/**
	 * @var Shop_Model
	 */
	protected $_oShop = NULL;

	/**
	 * @var Shop_Item_Controller
	 */
	protected $_oShop_Item_Controller = NULL;

	protected $_aAvailablePropertyTypes = array(0, 1, 3, 5, 7, 8, 9, 11, 12, 13, 14);

	/**
	 * Constructor.
	 * @param Shop_Model $oShop shop
	 */
	public function __construct(Shop_Model $oShop)
	{
		$this->_oShop = $oShop;

		$this->_oShop_Item_Controller = new Shop_Item_Controller();
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
			case 5: // Элемент информационной системы
			case 12: // Товар интернет-магазина
			case 13: // Группа информационной системы
			case 14: // Группа интернет-магазина
				$sColumn = "`property{$oProperty->id}` int(11) DEFAULT NULL";
				$sIndex = "`p{$oProperty->id}` (`property{$oProperty->id}`, `shop_group_id`, `price_absolute`)";
			break;
			case 1: // Строка
			default:
				$sColumn = "`property{$oProperty->id}` varchar(255) DEFAULT NULL";
				$sIndex = "`p{$oProperty->id}` (`property{$oProperty->id}`(10))";
			break;
			case 7: // Флажок
				$sColumn = "`property{$oProperty->id}` tinyint(1) DEFAULT '0'";
				$sIndex = "`p{$oProperty->id}` (`property{$oProperty->id}`, `shop_group_id`, `price_absolute`)";
			break;
			case 8: // Дата
				$sColumn = "`property{$oProperty->id}` date DEFAULT '0000-00-00'";
				$sIndex = "`p{$oProperty->id}` (`property{$oProperty->id}`, `shop_group_id`, `price_absolute`)";
			break;
			case 9: // Дата-время
				$sColumn = "`property{$oProperty->id}` datetime DEFAULT '0000-00-00 00:00:00'";
				$sIndex = "`p{$oProperty->id}` (`property{$oProperty->id}`, `shop_group_id`, `price_absolute`)";
			break;
			case 11: // Число с плавающей запятой
				$sColumn = "`property{$oProperty->id}` double DEFAULT NULL";
				$sIndex = "`p{$oProperty->id}` (`property{$oProperty->id}`, `shop_group_id`, `price_absolute`)";
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
		// ALTER TABLE `shop_filter1` ADD `primary` TINYINT(1) NOT NULL AFTER `shop_item_id`;
		$aColumns = array(
			"`id` INT(11) NOT NULL AUTO_INCREMENT",
			"`shop_item_id` INT(11) NOT NULL DEFAULT '0'", // идентификатор товара
			"`primary` TINYINT(1) NOT NULL DEFAULT '0'",
			"`modification_id` INT(11) NOT NULL DEFAULT '0'", // идентификатор родительского товара, которому принадлежит модификация
			"`shop_group_id` INT(11) NOT NULL DEFAULT '0'",
			"`shop_producer_id` INT(11) NOT NULL DEFAULT '0'",
			"`shop_currency_id` INT(11) NOT NULL DEFAULT '0'",
			"`price` DECIMAL(12,2) NOT NULL DEFAULT '0.00'",
			"`price_absolute` DECIMAL(12,2) NOT NULL DEFAULT '0.00'",
			"`available` TINYINT(1) NOT NULL DEFAULT '0'"
		);

		// Indexes
		// 'KEY `shop_group_id` (`shop_group_id`,`modification_id`,`price_absolute`)',
		$aIndexes = array(
			'PRIMARY KEY (`id`)',
			'KEY `shop_item_id` (`shop_item_id`)',
			'KEY `modification_id` (`modification_id`)',
			'KEY `shop_group_id` (`shop_group_id`,`primary`,`modification_id`)',
			'KEY `shop_currency_id` (`shop_currency_id`)',
			'KEY `price` (`price_absolute`,`primary`,`shop_group_id`)',
			'KEY `producer` (`shop_producer_id`)'
		);

		$oShop_Item_Properties = $this->_oShop->Shop_Item_Properties;
		$oShop_Item_Properties->queryBuilder()
			->select('shop_item_properties.*')
			->join('properties', 'properties.id', '=', 'shop_item_properties.property_id')
			->where('shop_item_properties.filter', '!=', 0)
			->where('properties.deleted', '=', 0)
			->clearOrderBy()
			->orderBy('shop_item_properties.property_id');

		$aShop_Item_Properties = $oShop_Item_Properties->findAll(FALSE);
		foreach ($aShop_Item_Properties as $oShop_Item_Property)
		{
			$oProperty = $oShop_Item_Property->Property;

			$aPropertySql = $this->_getPropertySql($oProperty);

			$aColumns[] = $aPropertySql['column'];
			$aIndexes[] = 'KEY ' . $aPropertySql['index'];
		}

		$sColumns = implode(', ', $aColumns);

		// A table can contain a maximum of 64 secondary indexes, 6 of them already added
		$sIndexes = implode(', ', array_slice($aIndexes, 0, 58));

		$oCore_DataBase = Core_DataBase::instance();
		$aConfig = $oCore_DataBase->getConfig();

		$sEngine = isset($aConfig['storageEngine'])
			? $aConfig['storageEngine']
			: 'MyISAM';

		$query = "CREATE TABLE IF NOT EXISTS `" . $this->getTableName() . "` (" .
			"\n{$sColumns}," .
			"\n{$sIndexes}" .
			"\n) ENGINE={$sEngine} DEFAULT CHARSET=utf8 AUTO_INCREMENT=0";

		$oCore_DataBase->query($query);

		return $this;
	}

	public function getTableName()
	{
		return 'shop_filter' . $this->_oShop->id;
	}

	/**
	 * Remove shop filter table
	 * @return self
	 */
	public function dropTable()
	{
		Core_DataBase::instance()->query("DROP TABLE IF EXISTS `" . $this->getTableName() . "`");

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
			$sTableName = $this->getTableName();
			$aPropertySql = $this->_getPropertySql($oProperty);

			Core_DataBase::instance()->setQueryType(5)->query("ALTER TABLE `{$sTableName}` ADD {$aPropertySql['column']}");

			// Check exists indexes
			$aIndexes = Core_DataBase::instance()->getIndexes($sTableName);

			// A table can contain a maximum of 64 secondary indexes.
			if (count($aIndexes) < 64)
			{
				Core_DataBase::instance()->setQueryType(5)->query("ALTER TABLE `{$sTableName}` ADD INDEX {$aPropertySql['index']}");
			}
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
			$sTableName = $this->getTableName();

			// Check exists indexes
			$aIndexes = Core_DataBase::instance()->getIndexes($sTableName);
			$sIndexName = "p{$oProperty->id}";

			if (isset($aIndexes[$sIndexName]))
			{
				Core_DataBase::instance()->query("ALTER TABLE `{$sTableName}` DROP INDEX `{$sIndexName}`");
			}

			Core_DataBase::instance()->query("ALTER TABLE `{$sTableName}` DROP `property{$oProperty->id}`");
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

			$sTableName = $this->getTableName();

			$aTableColumns = $oCore_DataBase->getColumns($sTableName);

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

	/**
	 * Remove table rows
	 * @param Shop_Item_Model $oShop_Item
	 * @return self
	 */
	public function remove(Shop_Item_Model $oShop_Item)
	{
		$tableName = $this->getTableName();

		// Remove All Rows For Item
		Core_QueryBuilder::delete($tableName)
			->where('shop_item_id', '=', $oShop_Item->id)
			->execute();

		return $this;
	}

	/**
	 * Fill table rows
	 * @param Shop_Item_Model $oShop_Item
	 * @return self
	 */
	public function fill(Shop_Item_Model $oShop_Item)
	{
		$tableName = $this->getTableName();

		// Remove All Rows For Item
		$this->remove($oShop_Item);

		if ($oShop_Item->active
			&& (!$oShop_Item->modification_id || $oShop_Item->Modification->active)
		)
		{
			/*Core_Log::instance()->clear()
				->status(Core_Log::$MESSAGE)
				->write('Filter Fill ' . $oShop_Item->id);*/

			$oDefaultCurrency = Core_Entity::factory('Shop_Currency')->getDefault();

			$fCurrencyCoefficient = $oShop_Item->Shop_Currency->id > 0 && $oDefaultCurrency->id > 0
				? Shop_Controller::instance()->getCurrencyCoefficientInShopCurrency(
					$oShop_Item->Shop_Currency, $oDefaultCurrency
				)
				: 0;

			// warehouse rest
			$available = $oShop_Item->getRest() > 0 ? 1 : 0;

			// prices
			// $aPrices = $oShop_Item->getPrices();
			$aPrices = $this->_oShop_Item_Controller->calculatePriceInItemCurrency($oShop_Item->price, $oShop_Item);

			$shop_group_id = $oShop_Item->modification_id
				? $oShop_Item->Modification->shop_group_id
				: $oShop_Item->shop_group_id;

			$aBaseInserts = array(
				$oShop_Item->id,
				1, // primary
				$oShop_Item->modification_id,
				$shop_group_id,
				$oShop_Item->shop_producer_id,
				$oShop_Item->shop_currency_id,
				$aPrices['price_discount'],
				$aPrices['price_discount'] * $fCurrencyCoefficient, // price_absolute
				$available
			);

			$aPropertyIds = $this->_getPropertyIDs();

			// Collect values by property_id
			$aProperty_Values = $oShop_Item->getPropertyValues(FALSE);
			$aTmp = array();
			foreach ($aProperty_Values as $oProperty_Value)
			{
				$oProperty = $oProperty_Value->Property;

				if (in_array($oProperty->type, $this->_aAvailablePropertyTypes))
				{
					if (isset($aPropertyIds[$oProperty->id]))
					{
						// Свойство множественное или ранее для этого единичного значения не было значения
						if ($oProperty->multiple || !isset($aTmp[$oProperty->id]))
						{
							$aTmp[$oProperty->id][] = $oProperty_Value->value;
						}
					}
				}
			}

			$aPV = array();
			foreach ($aPropertyIds as $property_id)
			{
				$aPV[] = isset($aTmp[$property_id]) ? $aTmp[$property_id] : array($this->_getDefaultValue($property_id));
			}
			unset($aTmp);

			if (count($aPV))
			{
				// Get All Posible Combinations
				//$aCombinations = count($aPV) > 1 ? $this->_combinations($aPV) : $aPV;
				if (count($aPV) > 1)
				{
					$aCombinations = $this->_combinations($aPV);
				}
				else
				{
					$aCombinations = array();
					foreach ($aPV[0] as $scalar)
					{
						$aCombinations[] = array($scalar);
					}
				}
				unset($aPV);
			}
			else
			{
				$aCombinations = array();
			}

			$oCore_DataBase = Core_DataBase::instance();

			$sTableColumnNames = implode(',', $this->_getTableColumns());

			$aInserts = array();

			if (count($aCombinations))
			{
				foreach ($aCombinations as $key => $aCombination)
				{
					$aValues = array_merge($aBaseInserts, $aCombination);
					$aValues = array_map(array($oCore_DataBase, 'quote'), $aValues);

					$aInserts[] = '(' . implode(', ', $aValues) . ')';

					if (count($aInserts) > 50)
					{
						$this->_insert($tableName, $sTableColumnNames, $aInserts);

						$aInserts = array();
					}

					// reset primary to 0
					$key == 0
						&& $aBaseInserts[1] = 0;
				}
			}
			else
			{
				$aValues = array_map(array($oCore_DataBase, 'quote'), $aBaseInserts);
				$aInserts[] = '(' . implode(', ', $aValues) . ')';
			}

			count($aInserts)
				&& $this->_insert($tableName, $sTableColumnNames, $aInserts);
		}
	}

	protected function _combinations($aPV, $i = 0)
	{
		if (!isset($aPV[$i]))
		{
			return array();
		}

		if ($i == count($aPV) - 1)
		{
			return $aPV[$i];
		}

		// get combinations from subsequent arrays
		$tmp = $this->_combinations($aPV, $i + 1);

		$result = array();

		// concat each array from tmp with each element from $aPV[$i]
		foreach ($aPV[$i] as $v)
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

	protected function _insert($tableName, $sTableColumnNames, array $aValues)
	{
		Core_DataBase::instance()->query(
			"INSERT INTO `{$tableName}` ({$sTableColumnNames}) VALUES " . implode(",\n", $aValues)
		);

		return $this;
	}

	/**
	 * Get property default value
	 * @param int $property_id property id
	 * @return mixed int|string
	 */
	protected function _getDefaultValue($property_id)
	{
		$oProperty = Core_Entity::factory('Property', $property_id);

		switch ($oProperty->type)
		{
			//case 0: // int
			//case 3: // список
			case 7: // флажок
			//case 11: // float
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
			default:
				$defaultValue = NULL;
		}

		return $defaultValue;
	}

	/**
	 * Check property exist
	 * @param int $property_id property id
	 * @return bool TRUE|FALSE
	 */
	public function checkPropertyExist($property_id)
	{
		$aTableColumns = $this->_getTableColumns();

		return isset($aTableColumns['property' . strval($property_id)]);
	}
}