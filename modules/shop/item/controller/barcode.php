<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Item_Controller_Barcode
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Shop_Item_Controller_Barcode extends Core_Servant_Properties
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'type',
		'prefix'
	);

	/**
	 * Shop model
	 * @var object
	 */
	protected $_oShop = NULL;

	/**
	 * Constructor.
	 * @param Shop_Model $oShop print layout
	 */
	public function __construct(Shop_Model $oShop)
	{
		parent::__construct();

		$this->_oShop = $oShop;
	}

	/**
	 * Get barcode by ID
	 * @param string $id
	 * @return string
	 */
	protected function _getBarcode($id)
	{
		switch ($this->type)
		{
			case 1: // EAN-8
				$barcode = Core_Barcode::generateEAN8($this->prefix, $id);
			break;
			case 2: // EAN-13
				$barcode = Core_Barcode::generateEAN13($this->prefix, $id);
			break;
			default:
				$barcode = NULL;
		}

		return $barcode;
	}

	/**
	 * Check unique
	 * @param string $barcode
	 * @return boolean
	 */
	protected function _checkUnique($barcode)
	{
		$oCheckBarcodes = $this->_oShop->Shop_Items;
		$oCheckBarcodes->queryBuilder()
			->select('shop_items.*')
			->join('shop_item_barcodes', 'shop_item_barcodes.shop_item_id', '=', 'shop_items.id', array(
				array('AND' => array('shop_item_barcodes.deleted', '=', 0))
			))
			->where('shop_item_barcodes.value', '=', $barcode)
			->where('shop_item_barcodes.type', '=', $this->type);

		return $oCheckBarcodes->getCount(FALSE) == 0;
	}

	/**
	 * Execute business logic.
	 */
	public function execute()
	{
		// var_dump($this->type);

		$limit = 500;
		//$offset = 0;

		$oShop_Items = $this->_oShop->Shop_Items;
		$oShop_Items->queryBuilder()
			->select('shop_items.*')
			->leftJoin('shop_item_barcodes', 'shop_item_barcodes.shop_item_id', '=', 'shop_items.id', array(
				array('AND' => array('shop_item_barcodes.deleted', '=', 0)),
				array('AND' => array('shop_item_barcodes.type', '=', $this->type))
			))
			// ->where('shop_items.active', '=', 1)
			->where('shop_items.shortcut_id', '=', 0)
			->where('shop_items.modification_id', '=', 0)
			->where('shop_item_barcodes.value', 'IS', NULL);

		do {
			// Вставка влияет на товары, выбираемые выше, поэтому всегда выбираем с 0
			$oShop_Items->queryBuilder()
				->limit($limit)
				->offset(0);

			$aShop_Items = $oShop_Items->findAll(FALSE);

			foreach ($aShop_Items as $oShop_Item)
			{
				$barcode = $this->_getBarcode($oShop_Item->id);

				if (!is_null($barcode))
				{
					if (!$this->_checkUnique($barcode))
					{
						$id = abs(Core::crc32($oShop_Item->id));

						$barcode = $this->_getBarcode($id);
					}

					// Проверяем штрихкод на уникальность
					if (!is_null($barcode) && $this->_checkUnique($barcode))
					{
						$oShop_Item_Barcode = Core_Entity::factory('Shop_Item_Barcode');
						$oShop_Item_Barcode
							->value($barcode)
							->shop_item_id($oShop_Item->id)
							->setType()
							->save();
					}
				}
			}

			//$offset += $limit;
		} while (count($aShop_Items) == $limit);

		return $this;
	}
}