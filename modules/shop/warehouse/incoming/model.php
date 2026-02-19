<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Warehouse_Incoming_Model
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Shop_Warehouse_Incoming_Model extends Core_Entity
{
	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'shop_warehouse' => array(),
		'siteuser' => array(),
		'siteuser_company' => array(),
		'user' => array(),
	);

	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		'shop_warehouse_incoming_item' => array(),
	);

	/**
	 * Column consist item's name
	 * @var string
	 */
	protected $_nameColumn = 'number';

	/**
	 * Backend property
	 * @var mixed
	 */
	public $rollback = 0;

	/**
	 * TYPE
	 * @var int
	 */
	const TYPE = 1;

	/**
	 * Get Entity Type
	 * @return int
	 */
	public function getEntityType()
	{
		return self::TYPE;
	}

	/**
	 * Constructor.
	 * @param int $id entity ID
	 */
	public function __construct($id = NULL)
	{
		parent::__construct($id);

		if (is_null($id) && !$this->loaded())
		{
			$oUser = Core_Auth::getCurrentUser();
			$this->_preloadValues['user_id'] = is_null($oUser) ? 0 : $oUser->id;
			$this->_preloadValues['datetime'] = Core_Date::timestamp2sql(time());
			//$this->_preloadValues['posted'] = 0;
		}
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function postedBackend()
	{
		return $this->posted
			? '<i class="fa fa-check-circle-o green">'
			: '<i class="fa fa-times-circle-o red">';
	}

	/**
	 * Backend callback method
	 * @param Admin_Form_Field_Model $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function dataManagerBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		$oUser = $this->User;
		return $oUser->id
			? $oUser->showAvatarWithName()
			: '';
	}

	public function date()
	{
		return Core_Date::sql2date($this->datetime);
	}

	/**
	 * Mark entity as deleted
	 * @return Core_Entity
	 */
	public function markDeleted()
	{
		$this->unpost();

		return parent::markDeleted();
	}

	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return Core_Entity
	 * @hostcms-event shop_warehouse.onBeforeRedeclaredDelete
	 */
	public function delete($primaryKey = NULL)
	{
		if (is_null($primaryKey))
		{
			$primaryKey = $this->getPrimaryKey();
		}

		$this->id = $primaryKey;

		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredDelete', $this, array($primaryKey));

		$this->Shop_Warehouse_Incoming_Items->deleteAll(FALSE);

		Core_Entity::factory('Shop_Warehouse_Entry')->deleteByDocument($this->id, $this->getEntityType());

		if (Core::moduleIsActive('revision'))
		{
			Revision_Controller::delete($this->getModelName(), $this->id);
		}

		return parent::delete($primaryKey);
	}

	/**
	 * Add entries
	 * @return self
	 */
	public function post()
	{
		if (!$this->posted)
		{
			$oShop_Warehouse = $this->Shop_Warehouse;
			
			$oShop = $oShop_Warehouse->Shop;
			// Fast filter
			if ($oShop->filter)
			{
				$oShop_Filter_Controller = new Shop_Filter_Controller($oShop);
			}

			$aShop_Warehouse_Entries = $oShop_Warehouse->Shop_Warehouse_Entries->getByDocument($this->id, $this->getEntityType());

			$aTmp = array();

			foreach ($aShop_Warehouse_Entries as $oShop_Warehouse_Entry)
			{
				$aTmp[$oShop_Warehouse_Entry->shop_item_id][] = $oShop_Warehouse_Entry;
			}

			unset($aShop_Warehouse_Entries);

			$limit = 500;
			$offset = 0;

			do {
				$oShop_Warehouse_Incoming_Items = $this->Shop_Warehouse_Incoming_Items;
				$oShop_Warehouse_Incoming_Items->queryBuilder()
					->limit($limit)
					->offset($offset)
					->clearOrderBy()
					->orderBy('id', 'ASC');

				$aShop_Warehouse_Incoming_Items = $oShop_Warehouse_Incoming_Items->findAll(FALSE);
				foreach ($aShop_Warehouse_Incoming_Items as $oShop_Warehouse_Incoming_Item)
				{
					// Удаляем все накопительные значения с датой больше, чем дата документа
					Shop_Warehouse_Entry_Accumulate_Controller::deleteEntries($oShop_Warehouse_Incoming_Item->shop_item_id, $oShop_Warehouse->id, $this->datetime);

					if (isset($aTmp[$oShop_Warehouse_Incoming_Item->shop_item_id]) && count($aTmp[$oShop_Warehouse_Incoming_Item->shop_item_id]))
					{
						$oShop_Warehouse_Entry = array_shift($aTmp[$oShop_Warehouse_Incoming_Item->shop_item_id]);
					}
					else
					{
						$oShop_Warehouse_Entry = Core_Entity::factory('Shop_Warehouse_Entry');
						$oShop_Warehouse_Entry->setDocument($this->id, $this->getEntityType());
						$oShop_Warehouse_Entry->shop_item_id = $oShop_Warehouse_Incoming_Item->shop_item_id;
					}

					$oShop_Warehouse_Entry->shop_warehouse_id = $oShop_Warehouse->id;
					$oShop_Warehouse_Entry->datetime = $this->datetime;
					$oShop_Warehouse_Entry->value = $oShop_Warehouse_Incoming_Item->count;
					$oShop_Warehouse_Entry->save();

					$rest = $oShop_Warehouse->getRest($oShop_Warehouse_Incoming_Item->shop_item_id);

					if (!is_null($rest))
					{
						// Recount
						$oShop_Warehouse->setRest($oShop_Warehouse_Incoming_Item->shop_item_id, $rest);
						
						// Fast filter
						$oShop->filter
							&& $oShop_Filter_Controller->fill($oShop_Warehouse_Incoming_Item->Shop_Item);
					}
				}

				$offset += $limit;
			}
			while (count($aShop_Warehouse_Incoming_Items));

			$this->posted = 1;
			$this->save();
		}

		return $this;
	}

	/**
	 * Remove all shop warehouse entries by document
	 * @return self
	 */
	public function unpost()
	{
		if ($this->posted)
		{
			$aShop_Warehouse_Entries = Core_Entity::factory('Shop_Warehouse_Entry')->getByDocument($this->id, $this->getEntityType());

			foreach ($aShop_Warehouse_Entries as $oShop_Warehouse_Entry)
			{
				// Удаляем все накопительные значения с датой больше, чем дата документа
				Shop_Warehouse_Entry_Accumulate_Controller::deleteEntries($oShop_Warehouse_Entry->shop_item_id, $oShop_Warehouse_Entry->shop_warehouse_id, $this->datetime);

				$shop_item_id = $oShop_Warehouse_Entry->shop_item_id;
				$oShop_Warehouse = $oShop_Warehouse_Entry->Shop_Warehouse;

				// Delete Entry
				$oShop_Warehouse_Entry->delete();

				$rest = $oShop_Warehouse->getRest($shop_item_id);

				if (!is_null($rest))
				{
					// Recount
					$oShop_Warehouse->setRest($shop_item_id, $rest);
				}
			}

			$this->posted = 0;
			$this->save();
		}

		return $this;
	}

    /**
     * Backend callback method
     * @param Admin_Form_Field_Model $oAdmin_Form_Field
     * @param Admin_Form_Controller $oAdmin_Form_Controller
     */
	public function printBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		Core::moduleIsActive('printlayout')
			&& Printlayout_Controller::getBackendPrintButton($oAdmin_Form_Controller, $this->id, $this->getEntityType());
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function shop_warehouse_idBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		return $this->Shop_Warehouse->id ? htmlspecialchars((string) $this->Shop_Warehouse->name) : '';
	}

	/**
	 * Backup revision
	 * @return self
	 */
	public function backupRevision()
	{
		if (Core::moduleIsActive('revision'))
		{
			$aBackup = array(
				'shop_warehouse_id' => $this->shop_warehouse_id,
				'number' => $this->number,
				'description' => $this->description,
				'datetime' => $this->datetime,
				'posted' => $this->posted,
				'shop_price_id' => $this->shop_price_id,
				'user_id' => $this->user_id,
				'items' => array()
			);

			$aShop_Warehouse_Incoming_Items = $this->Shop_Warehouse_Incoming_Items->findAll(FALSE);

			foreach ($aShop_Warehouse_Incoming_Items as $oShop_Warehouse_Incoming_Item)
			{
				$aBackup['items'][] = array(
					'shop_item_id' => $oShop_Warehouse_Incoming_Item->shop_item_id,
					'count' => $oShop_Warehouse_Incoming_Item->count,
				);
			}

			Revision_Controller::backup($this, $aBackup);
		}

		return $this;
	}

	/**
	 * Rollback Revision
	 * @param int $revision_id Revision ID
	 * @return self
	 */
	public function rollbackRevision($revision_id)
	{
		if (Core::moduleIsActive('revision'))
		{
			$oRevision = Core_Entity::factory('Revision', $revision_id);

			$aBackup = json_decode($oRevision->value, TRUE);

			if (is_array($aBackup))
			{
				$this->unpost();

				$this->shop_warehouse_id = Core_Array::get($aBackup, 'shop_warehouse_id');
				$this->number = Core_Array::get($aBackup, 'number');
				$this->description = Core_Array::get($aBackup, 'description');
				$this->datetime = Core_Array::get($aBackup, 'datetime');
				$this->posted = 0;
				$this->shop_price_id = Core_Array::get($aBackup, 'shop_price_id');
				$this->user_id = Core_Array::get($aBackup, 'user_id');

				$aAllItems = Core_Array::get($aBackup, 'items');

				if (count($aAllItems))
				{
					// Удаляем все товары
					$this->Shop_Warehouse_Incoming_Items->deleteAll(FALSE);

					// Создаем новые
					foreach ($aAllItems as $aShop_Warehouse_Incoming_Item)
					{
						$oShop_Warehouse_Incoming_Item = Core_Entity::factory('Shop_Warehouse_Incoming_Item');
						$oShop_Warehouse_Incoming_Item->shop_warehouse_incoming_id = $this->id;
						$oShop_Warehouse_Incoming_Item->shop_item_id = Core_Array::get($aShop_Warehouse_Incoming_Item, 'shop_item_id');
						$oShop_Warehouse_Incoming_Item->count = Core_Array::get($aShop_Warehouse_Incoming_Item, 'count');
						$oShop_Warehouse_Incoming_Item->save();
					}
				}

				$this->save();

				Core_Array::get($aBackup, 'posted') && $this->post();
			}
		}

		return $this;
	}

	/**
	 * Backend badge
	 */
	public function count_itemsBackend()
	{
		$count = $this->Shop_Warehouse_Incoming_Items->getCount();
		$count && Core_Html_Entity::factory('Span')
			->class('badge badge-danger badge-square')
			->value($count)
			->execute();
	}

	/**
	 * Get printlayout replaces
	 * @return array
	 * @hostcms-event shop_warehouse_incoming.onAfterGetPrintlayoutReplaces
	 */
	public function getPrintlayoutReplaces()
	{
		$aReplace = array(
			// Core_Meta
			'this' => $this,
			'company' => $this->Shop_Warehouse->shop_company_id ? $this->Shop_Warehouse->Shop_Company : $this->Shop_Warehouse->Shop->Shop_Company,
			'shop_warehouse' => $this->Shop_Warehouse,
			'shop' => $this->Shop_Warehouse->Shop,
			'user' => $this->User,
			'type' => Core::_('Shop_Warehouse_Incoming.title'),
			'total_count' => 0,
			'Items' => array(),
		);

		$position = 1;

		$aShop_Warehouse_Incoming_Items = $this->Shop_Warehouse_Incoming_Items->findAll();
		foreach ($aShop_Warehouse_Incoming_Items as $oShop_Warehouse_Incoming_Item)
		{
			$oShop_Item = $oShop_Warehouse_Incoming_Item->Shop_Item;

			$amount = Shop_Controller::instance()->round($oShop_Warehouse_Incoming_Item->count * $oShop_Warehouse_Incoming_Item->price);

			$aBarcodes = array();

			$aShop_Item_Barcodes = $oShop_Item->Shop_Item_Barcodes->findAll(FALSE);
			foreach ($aShop_Item_Barcodes as $oShop_Item_Barcode)
			{
				$aBarcodes[] = $oShop_Item_Barcode->value;
			}

			$node = new stdClass();

			$node->position = $position++;
			$node->item = $oShop_Item;
			$node->name = htmlspecialchars($oShop_Item->name);
			$node->measure = $oShop_Item->shop_measure_id ? htmlspecialchars((string) $oShop_Item->Shop_Measure->name) : '';
			$node->currency = $oShop_Item->shop_currency_id ? htmlspecialchars((string) $oShop_Item->Shop_Currency->sign) : '';
			$node->price = $oShop_Warehouse_Incoming_Item->price;
			$node->quantity = $oShop_Warehouse_Incoming_Item->count;
			$node->amount = $amount;
			$node->barcodes = implode(', ', $aBarcodes);

			$aReplace['Items'][] = $node;

			$aReplace['total_count']++;
		}

		$aReplace['year'] = date('Y');
		$aReplace['month'] = date('m');
		$aReplace['day'] = date('d');

		Core_Event::notify($this->_modelName . '.onAfterGetPrintlayoutReplaces', $this, array($aReplace));
		$eventResult = Core_Event::getLastReturn();

		return !is_null($eventResult)
			? $eventResult
			: $aReplace;
	}

	/**
	 * Get Related Site
	 * @return Site_Model|NULL
	 * @hostcms-event shop_warehouse_incoming.onBeforeGetRelatedSite
	 * @hostcms-event shop_warehouse_incoming.onAfterGetRelatedSite
	 */
	public function getRelatedSite()
	{
		Core_Event::notify($this->_modelName . '.onBeforeGetRelatedSite', $this);

		$oSite = $this->Shop_Warehouse->Shop->Site;

		Core_Event::notify($this->_modelName . '.onAfterGetRelatedSite', $this, array($oSite));

		return $oSite;
	}
}