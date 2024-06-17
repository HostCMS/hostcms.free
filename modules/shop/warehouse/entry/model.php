<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Warehouse_Entry_Model
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
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

	/**
	 * Get uniq document ID
	 * @param int $document_id document ID
	 * @param int $type document type
	 * @return int
	 */
	protected function _getDocumentId($document_id, $type)
	{
		return Shop_Controller::getDocumentId($document_id, $type);
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

	/**
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

	/**
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

	/**
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

	/**
	 * Object
	 * @var object|NULL
	 */
	protected $_object = NULL;

	/**
	 * Get object
	 * @return object|NULL
	 */
	protected function _getObject()
	{
		if (is_null($this->_object))
		{
			$this->_object = Shop_Controller::getDocument($this->document_id);
		}

		return $this->_object;
	}

	/**
	 * Get document type
	 * @return int|NULL
	 */
	public function getDocumentType()
	{
		return Shop_Controller::getDocumentType($this->document_id);
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function valueBackend()
	{
		$class = $this->value > 0
			? 'success'
			: 'darkorange';

		$this->value == 0 && $class = '';

		Core_Html_Entity::factory('Span')
			->class($class)
			->value($this->value)
			->execute();
	}

	/**
	 * Get item's name
	 * @return string
	 */
	public function dataNameBackend()
	{
		return $this->Shop_Item->nameBackend();
	}

	/**
	 * Backend callback method
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function userBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		$oObject = $this->_getObject();

		return !is_null($oObject) && isset($oObject->user_id) && $oObject->user_id
			? $oObject->User->showAvatarWithName()
			: '';
	}

	/**
	 * Get item's name
	 * @return string
	 */
	public function shop_warehouse_idBackend()
	{
		return $this->Shop_Warehouse->id ? htmlspecialchars((string) $this->Shop_Warehouse->name) : '';
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function documentBackend()
	{
		$type = Shop_Controller::getDocumentType($this->document_id);

		$color = Core_Str::createColor($type);

		/* Типы документов:
		* 0 - Shop_Warehouse_Inventory_Model
		* 1 - Shop_Warehouse_Incoming_Model
		* 2 - Shop_Warehouse_Writeoff_Model
		* 3 - Shop_Warehouse_Regrade_Model
		* 4 - Shop_Warehouse_Movement_Model
		* 5 - Shop_Order_Model
		* 6 - Shop_Warehouse_Purchaseorder_Model
		* 7 - Shop_Warehouse_Invoice_Model
		* 8 - Shop_Warehouse_Supply_Model
		* 9 - Shop_Warehouse_Purchasereturn_Model
		*/
		switch ($type)
		{
			case 0:
				$path = '/admin/shop/warehouse/inventory/index.php';
			break;
			case 1:
				$path = '/admin/shop/warehouse/incoming/index.php';
			break;
			case 2:
				$path = '/admin/shop/warehouse/writeoff/index.php';
			break;
			case 3:
				$path = '/admin/shop/warehouse/regrade/index.php';
			break;
			case 4:
				$path = '/admin/shop/warehouse/movement/index.php';
			break;
			case 5:
				$path = '/admin/shop/order/index.php';
			break;
			case 6:
				$path = '/admin/shop/warehouse/purchaseorder/index.php';
			break;
			case 7:
				$path = '/admin/shop/warehouse/invoice/index.php';
			break;
			case 8:
				$path = '/admin/shop/warehouse/supply/index.php';
			break;
			case 9:
				$path = '/admin/shop/warehouse/purchasereturn/index.php';
			break;
			default:
				$path = '';
		}

		$id = $this->document_id >> 8;

		if ($path != '' && $id)
		{
			return '<span class="badge badge-round badge-max-width" style="border-color: ' . $color . '; color: ' . Core_Str::hex2darker($color, 0.2) . '; background-color: ' . Core_Str::hex2lighter($color, 0.88) . '"><a style="color: ' . $color . '" href="' . $path . '?hostcms[action]=edit&hostcms[checked][0][' . $id . ']=1" onclick="$.modalLoad({path: \'' . $path . '\', action: \'edit\', operation: \'modal\', additionalParams: \'hostcms[checked][0][' . $id . ']=1\', windowId: \'modal' . $this->document_id . '\', width: \'90%\'}); return false">' . Core::_('Shop_Document_Relation.type' . $type) . ' ' . $id . '</a></span>';
		}
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