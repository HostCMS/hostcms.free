<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Online shop.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
 */
class Shop_Filter_Group_Controller
{
	/**
	 * @var Shop_Model
	 */
	protected $_oShop = NULL;

	/**
	 * Constructor.
	 * @param Shop_Model $oShop shop
	 */
	public function __construct(Shop_Model $oShop)
	{
		$this->_oShop = $oShop;
	}

	/**
	 * Create shop filter group table
	 * @return self
	 */
	public function createTable()
	{
		$aColumns = array(
			"`shop_group_id` int(11) NOT NULL DEFAULT '0'",
			"`child_id` int(11) NOT NULL DEFAULT '0'"
		);

		// Indexes
		$aIndexes = array(
			'PRIMARY KEY (`shop_group_id`,`child_id`)'
		);

		$sColumns = implode(', ', $aColumns);

		$sIndexes = implode(', ', $aIndexes);

		$oCore_DataBase = Core_DataBase::instance();
		$aConfig = $oCore_DataBase->getConfig();

		$sEngine = isset($aConfig['storageEngine'])
			? $aConfig['storageEngine']
			: 'MyISAM';

		$query = "CREATE TABLE IF NOT EXISTS `" . $this->getTableName() . "` (" .
			"\n{$sColumns}," .
			"\n{$sIndexes}" .
			"\n) ENGINE={$sEngine} DEFAULT CHARSET=utf8";

		$oCore_DataBase->query($query);

		return $this;
	}

	/*
	 * Get table name
	 * @return string
	 */
	public function getTableName()
	{
		return 'shop_filter_group' . $this->_oShop->id;
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
	 * Groups tree
	 * @var mixed
	 */
	protected $_tree = NULL;

	/**
	 * Fill groups tree
	 * @return self
	 */
	protected function _fillTree()
	{
		if (is_null($this->_tree))
		{
			$offset = 0;
			$limit = 1000;

			do {
				$oCore_QueryBuilder_Select = Core_QueryBuilder::select('id', 'parent_id', 'shortcut_id');
				$oCore_QueryBuilder_Select
					->from('shop_groups')
					->where('shop_groups.shop_id', '=', $this->_oShop->id)
					->where('shop_groups.active', '=', 1)
					// ->where('shop_groups.shortcut_id', '=', 0)
					->where('shop_groups.deleted', '=', 0)
					->clearOrderBy()
					->orderBy('shop_groups.id', 'ASC')
					->limit($limit)
					->offset($offset);

				$offset += $limit;

				$aRows = $oCore_QueryBuilder_Select->execute()->asAssoc()->result();

				foreach ($aRows as $aRow)
				{
					// Если у группы нет подгрупп, то создаем пустой массив, чтобы при заполнении упомянуть саму группу в своих же потомках
					if (!$aRow['shortcut_id'] && !isset($this->_tree[$aRow['id']]))
					{
						$this->_tree[$aRow['id']] = array();
					}

					$this->_tree[$aRow['parent_id']][] = $aRow['shortcut_id']
						? $aRow['shortcut_id']
						: $aRow['id'];
				}
			} while(count($aRows) == $limit);
		}

		return $this;
	}

	/**
	 * Rebuild groups
	 * @return self
	 */
	public function rebuild()
	{
		$this->_fillTree();

		if (isset($this->_tree[0]))
		{
			foreach ($this->_tree[0] as $shop_group_id)
			{
				$this->fill($shop_group_id);
			}
		}

		return $this;
	}

	/**
	 * Fill table rows
	 * @param object $oShop_Group Shop_Group_Model object
	 * @return self
	 */
	public function fill($shop_group_id)
	{
		$shop_group_id = intval($shop_group_id);

		$aValues = array();

		if ($shop_group_id)
		{
			$tableName = $this->getTableName();

			$this->_fillTree();

			// echo "<pre>";
			// var_dump($this->_tree);
			// echo "</pre>";

			$aValues[] = $shop_group_id;

			if (isset($this->_tree[$shop_group_id]))
			{
				foreach ($this->_tree[$shop_group_id] as $child_id)
				{
					//$aValues[] = $child_id;
					$aValues = array_merge($aValues, $this->fill($child_id));
				}

				$aValues = array_unique($aValues);
			}
			
			// Remove before insert
			Core_QueryBuilder::delete($tableName)
				->where('shop_group_id', '=', $shop_group_id)
				->execute();

			// Insert
			$query = "INSERT IGNORE INTO `{$tableName}` (`shop_group_id`, `child_id`) VALUES ({$shop_group_id}, " . implode("), ({$shop_group_id}, ", $aValues) . ')';
			Core_DataBase::instance()->query($query);
		}

		return $aValues;
	}
}