<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Warehouse_Item_Export_Controller
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2020 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Warehouse_Item_Export_Controller
{
	/**
	 * Shop warehouse object
	 * @var Shop_Warehouse_Model
	 */
	private $_Shop_Warehouse = NULL;

	/**
	 * CSV data
	 * @var array
	 */
	private $_aCurrentData = array();

	/**
	 * Constructor.
	 * @param object $oShop_Warehouse Shop_Warehouse_Model object
	 */
	public function __construct(Shop_Warehouse_Model $oShop_Warehouse)
	{
		$this->_Shop_Warehouse = $oShop_Warehouse;

		$this->_aCurrentData[] = array(
			'"' . Core::_('Shop_Exchange.item_marking') . '"',
			'"' . Core::_('Shop_Exchange.item_name') . '"',
			'"' . $this->_prepareString(Core::_("Shop_Item.warehouse_import_field", $oShop_Warehouse->name)) . '"',
			'"' . Core::_('Shop_Exchange.item_price') . '"',
			'"' . Core::_('Shop_Exchange.currency_id') . '"',
		);
	}

	/**
	 * Executes the business logic.
	 */
	public function execute()
	{
		header("Pragma: public");
		header("Content-Description: File Transfer");
		header("Content-Type: application/force-download");
		header("Content-Disposition: attachment; filename = " . 'shop_warehouse_items_' . date("Y_m_d_H_i_s") . '.csv' . ";");
		header("Content-Transfer-Encoding: binary");

		foreach ($this->_aCurrentData as $aData)
		{
			$this->_printRow($aData);
		}

		$offset = 0;
		$limit = 100;

		$oShop = $this->_Shop_Warehouse->Shop;

		do {
			$oShop_Items = $this->_Shop_Warehouse->Shop_Items;
			$oShop_Items->queryBuilder()
				->where('shop_items.shop_id', '=', $oShop->id)
				->clearOrderBy()
				->orderBy('shop_items.id')
				->offset($offset)
				->limit($limit);

			$aShop_Items = $oShop_Items->findAll(FALSE);

			foreach ($aShop_Items as $oShop_Item)
			{
				$oShop_Warehouse_Item = $oShop_Item->Shop_Warehouse_Items->getByShop_item_id($oShop_Item->id, FALSE);
				$count = !is_null($oShop_Warehouse_Item) ? $oShop_Warehouse_Item->count : 0;

				$aData = array(
					sprintf('"%s"', $this->_prepareString($oShop_Item->marking)),
					sprintf('"%s"', $this->_prepareString($oShop_Item->name)),
					sprintf('"%s"', $this->_prepareFloat($count)),
					sprintf('"%s"', $this->_prepareFloat($oShop_Item->price)),
					sprintf('"%s"', $oShop_Item->shop_currency_id),
				);

				$this->_printRow($aData);
			}

			$offset += $limit;
		}
		while (count($aShop_Items));

		exit();
	}

	/**
	 * Prepare string
	 * @param string $string
	 * @return string
	 */
	protected function _prepareString($string)
	{
		return str_replace('"', '""', trim($string));
	}

	/**
	 * Prepare float
	 * @param mixed $string
	 * @return string
	 */
	protected function _prepareFloat($string)
	{
		return str_replace('.', ',', $string);
	}

	/**
	 * Print array
	 * @param array $aData
	 * @return self
	 */
	protected function _printRow($aData)
	{
		echo Core_Str::iconv('UTF-8', 'Windows-1251', implode(';', $aData) . "\n");
		return $this;
	}
}