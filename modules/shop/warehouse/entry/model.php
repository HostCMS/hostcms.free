<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Warehouse_Entry_Model
 *
 * Типы документов:
 * - 0 - Инвентаризация
 * - 1 - Приход
 * - 2 - Списание
 * - 3 - Пересортица
 * - 4 - Перемещение
 * - 5 - Заказ
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Warehouse_Entry_Model extends Core_Entity
{
	/**
	 * Disable markDeleted()
	 * @var mixed
	 */
	protected $_marksDeleted = NULL;

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'shop_warehouse' => array(),
		'shop_item' => array()
	);

	/**
	 * Column consist item's name
	 * @var string
	 */
	protected $_nameColumn = 'datetime';

	/**
	 * Constructor.
	 * @param int $id entity ID
	 */
	public function __construct($id = NULL)
	{
		parent::__construct($id);

		if (is_null($id) && !$this->loaded())
		{
			$this->_preloadValues['datetime'] = Core_Date::timestamp2sql(time());
		}
	}

	/*
	 * Get uniq document ID
	 * @param int $document_id document ID
	 * @param int $type document type
	 * @return int
	 */
	protected function _getDocumentId($document_id, $type)
	{
		return ($document_id << 8) | $type;
	}

	/*
	 * Set uniq document ID
	 * @param int $document_id document ID
	 * @param int $type document type
	 * @return self
	 */
	public function setDocument($document_id, $type)
	{
		$this->document_id = $this->_getDocumentId($document_id, $type);

		return $this;
	}

	/*
	 * Get entries by document_id
	 * @param int $document_id document ID
	 * @param int $type document type
	 * @param boolean $bCache cache
	 * @return array
	 */
	public function getByDocument($document_id, $type, $bCache = FALSE)
	{
		return $this->getAllBydocument_id($this->_getDocumentId($document_id, $type), $bCache);
	}

	/*
	 * Delete entries by document id
	 * @param $document_id document ID
	 * @param $type document type
	 * @return self
	 */
	public function deleteByDocument($document_id, $type)
	{
		Core_QueryBuilder::delete('shop_warehouse_entries')
			->where('document_id', '=', $this->_getDocumentId($document_id, $type))
			->execute();

		return $this;
	}

	/*
	 * Get entries by document_id and shop_item_id
	 * @param int $document_id document ID
	 * @param int $type document type
	 * @param int|array $shop_item_id document type
	 * @param boolean $bCache cache
	 * @return array
	 */
	public function getByDocumentAndShopItem($document_id, $type, $shop_item_id, $bCache = FALSE)
	{
		$this->queryBuilder()
			->where('shop_warehouse_entries.document_id', '=', $this->_getDocumentId($document_id, $type))
			->where('shop_warehouse_entries.shop_item_id', is_array($shop_item_id) ? 'IN' : '=', $shop_item_id)
			->clearOrderBy()
			->orderBy('shop_warehouse_entries.id', 'ASC');

		return $this->findAll($bCache);
	}

	/*
	 * Get document type
	 * @return int|NULL
	 */
	public function getDocumentType()
	{
		return $this->document_id
			? Core_Bit::extractBits($this->document_id, 8, 1)
			: NULL;
	}

	/**
	 * Get Related Site
	 * @return Site_Model|NULL
	 * @hostcms-event shop_warehouse_entry.onBeforeGetRelatedSite
	 * @hostcms-event shop_warehouse_entry.onAfterGetRelatedSite
	 */
	public function getRelatedSite()
	{
		Core_Event::notify($this->_modelName . '.onBeforeGetRelatedSite', $this);

		$oSite = $this->Shop_Warehouse->Shop->Site;

		Core_Event::notify($this->_modelName . '.onAfterGetRelatedSite', $this, array($oSite));

		return $oSite;
	}
}