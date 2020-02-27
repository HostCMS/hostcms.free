<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Warehouse_Regrade_Model
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2020 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Warehouse_Regrade_Model extends Core_Entity
{
	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'shop_warehouse' => array(),
		'user' => array(),
	);

	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		'shop_warehouse_regrade_item' => array(),
	);

	/**
	 * Column consist item's name
	 * @var string
	 */
	protected $_nameColumn = 'number';

	const TYPE = 3;

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
			$this->_preloadValues['posted'] = 1;
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
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function dataManagerBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		ob_start();

		$this->User->id && $this->User->showAvatarWithName();

		return ob_get_clean();
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

		$this->Shop_Warehouse_Regrade_Items->deleteAll(FALSE);

		$aShop_Warehouse_Entries = Core_Entity::factory('Shop_Warehouse_Entry')->getByDocument($this->id, self::TYPE);
		foreach ($aShop_Warehouse_Entries as $oShop_Warehouse_Entry)
		{
			$oShop_Warehouse_Entry->delete();
		}

		if (Core::moduleIsActive('revision'))
		{
			Revision_Controller::delete($this->getModelName(), $this->id);
		}

		return parent::delete($primaryKey);
	}

	public function post()
	{
		if (!$this->posted)
		{
			$oShop_Warehouse = $this->Shop_Warehouse;

			$aShop_Warehouse_Entries = $oShop_Warehouse->Shop_Warehouse_Entries->getByDocument($this->id, self::TYPE);

			$aTmp = array();

			$aTmpPair = array();

			foreach ($aShop_Warehouse_Entries as $oShop_Warehouse_Entry)
			{
				$aTmpPair[] = $oShop_Warehouse_Entry;

				if (count($aTmpPair) == 2)
				{
					$oTmpWriteoff = $aTmpPair[0];
					$oTmpIncoming = $aTmpPair[1];
					$aTmp[$oTmpWriteoff->id . ',' . $oTmpIncoming->id] = $aTmpPair;

					$aTmpPair = array();
				}
			}

			unset($aShop_Warehouse_Entries);

			$limit = 500;
			$offset = 0;

			do {
				$oShop_Warehouse_Regrade_Items = $this->Shop_Warehouse_Regrade_Items;
				$oShop_Warehouse_Regrade_Items->queryBuilder()
					->limit($limit)
					->offset($offset)
					->clearOrderBy()
					->orderBy('id', 'ASC');

				$aShop_Warehouse_Regrade_Items = $oShop_Warehouse_Regrade_Items->findAll(FALSE);
				foreach ($aShop_Warehouse_Regrade_Items as $oShop_Warehouse_Regrade_Item)
				{
					// Удаляем все накопительные значения с датой больше, чем дата документа
					Shop_Warehouse_Entry_Accumulate_Controller::deleteEntries($oShop_Warehouse_Regrade_Item->writeoff_shop_item_id, $oShop_Warehouse->id, $this->datetime);
					Shop_Warehouse_Entry_Accumulate_Controller::deleteEntries($oShop_Warehouse_Regrade_Item->incoming_shop_item_id, $oShop_Warehouse->id, $this->datetime);

					$key = $oShop_Warehouse_Regrade_Item->writeoff_shop_item_id . ',' . $oShop_Warehouse_Regrade_Item->incoming_shop_item_id;

					if (isset($aTmp[$key]))
					{
						$oShop_Warehouse_Entry_Writeoff = $aTmp[$key][0];
						$oShop_Warehouse_Entry_Incoming = $aTmp[$key][1];
					}
					else
					{
						$oShop_Warehouse_Entry_Writeoff = Core_Entity::factory('Shop_Warehouse_Entry');
						$oShop_Warehouse_Entry_Writeoff->setDocument($this->id, self::TYPE);
						$oShop_Warehouse_Entry_Writeoff->shop_item_id = $oShop_Warehouse_Regrade_Item->writeoff_shop_item_id;

						$oShop_Warehouse_Entry_Incoming = Core_Entity::factory('Shop_Warehouse_Entry');
						$oShop_Warehouse_Entry_Incoming->setDocument($this->id, self::TYPE);
						$oShop_Warehouse_Entry_Incoming->shop_item_id = $oShop_Warehouse_Regrade_Item->incoming_shop_item_id;
					}

					$oShop_Warehouse_Entry_Writeoff->shop_warehouse_id = $oShop_Warehouse->id;
					$oShop_Warehouse_Entry_Writeoff->datetime = $this->datetime;
					$oShop_Warehouse_Entry_Writeoff->value = -$oShop_Warehouse_Regrade_Item->count;
					$oShop_Warehouse_Entry_Writeoff->save();

					$oShop_Warehouse_Entry_Incoming->shop_warehouse_id = $oShop_Warehouse->id;
					$oShop_Warehouse_Entry_Incoming->datetime = $this->datetime;
					$oShop_Warehouse_Entry_Incoming->value = $oShop_Warehouse_Regrade_Item->count;
					$oShop_Warehouse_Entry_Incoming->save();

					$writeoffRest = $oShop_Warehouse->getRest($oShop_Warehouse_Regrade_Item->writeoff_shop_item_id);
					if (!is_null($writeoffRest))
					{
						// Recount
						$oShop_Warehouse->setRest($oShop_Warehouse_Regrade_Item->writeoff_shop_item_id, $writeoffRest);
					}

					$incomingRest = $oShop_Warehouse->getRest($oShop_Warehouse_Regrade_Item->incoming_shop_item_id);
					if (!is_null($incomingRest))
					{
						// Recount
						$oShop_Warehouse->setRest($oShop_Warehouse_Regrade_Item->incoming_shop_item_id, $incomingRest);
					}
				}

				$offset += $limit;
			}
			while (count($aShop_Warehouse_Regrade_Items));

			$this->posted = 1;
			$this->save();
		}

		return $this;
	}

	public function unpost()
	{
		if ($this->posted)
		{
			$oShop_Warehouse = $this->Shop_Warehouse;

			$aShop_Warehouse_Entries = $oShop_Warehouse->Shop_Warehouse_Entries->getByDocument($this->id, self::TYPE);

			foreach ($aShop_Warehouse_Entries as $oShop_Warehouse_Entry)
			{
				// Удаляем все накопительные значения с датой больше, чем дата документа
				Shop_Warehouse_Entry_Accumulate_Controller::deleteEntries($oShop_Warehouse_Entry->shop_item_id, $oShop_Warehouse->id, $this->datetime);

				$shop_item_id = $oShop_Warehouse_Entry->shop_item_id;
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
	 * @return string
	 */
	public function printBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		Printlayout_Controller::getBackendPrintButton($oAdmin_Form_Controller, $this->id, 4);
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function shop_warehouse_idBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		return htmlspecialchars($this->Shop_Warehouse->name);
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

			$aShop_Warehouse_Regrade_Items = $this->Shop_Warehouse_Regrade_Items->findAll(FALSE);

			foreach ($aShop_Warehouse_Regrade_Items as $oShop_Warehouse_Regrade_Item)
			{
				$aBackup['items'][] = array(
					'writeoff_shop_item_id' => $oShop_Warehouse_Regrade_Item->writeoff_shop_item_id,
					'writeoff_price' => $oShop_Warehouse_Regrade_Item->writeoff_price,
					'incoming_shop_item_id' => $oShop_Warehouse_Regrade_Item->incoming_shop_item_id,
					'incoming_price' => $oShop_Warehouse_Regrade_Item->incoming_price,
					'count' => $oShop_Warehouse_Regrade_Item->count
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
					$this->Shop_Warehouse_Regrade_Items->deleteAll(FALSE);

					// Создаем новые
					foreach ($aAllItems as $aShop_Warehouse_Regrade_Items)
					{
						$oShop_Warehouse_Regrade_Item = Core_Entity::factory('Shop_Warehouse_Regrade_Item');
						$oShop_Warehouse_Regrade_Item->shop_warehouse_regrade_id = $this->id;
						$oShop_Warehouse_Regrade_Item->writeoff_shop_item_id = Core_Array::get($aShop_Warehouse_Regrade_Items, 'writeoff_shop_item_id');
						$oShop_Warehouse_Regrade_Item->writeoff_price = Core_Array::get($aShop_Warehouse_Regrade_Items, 'writeoff_price');
						$oShop_Warehouse_Regrade_Item->incoming_shop_item_id = Core_Array::get($aShop_Warehouse_Regrade_Items, 'incoming_shop_item_id');
						$oShop_Warehouse_Regrade_Item->incoming_price = Core_Array::get($aShop_Warehouse_Regrade_Items, 'incoming_price');
						$oShop_Warehouse_Regrade_Item->count = Core_Array::get($aShop_Warehouse_Regrade_Items, 'count');
						$oShop_Warehouse_Regrade_Item->save();
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
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function count_itemsBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		$count = $this->Shop_Warehouse_Regrade_Items->getCount();
		$count && Core::factory('Core_Html_Entity_Span')
			->class('badge badge-maroon badge-square')
			->value($count)
			->execute();
	}

	public function getPrintlayoutReplaces()
	{
		$aReplace = array(
			// Core_Meta
			'this' => $this,
			'company' => $this->Shop_Warehouse->shop_company_id ? $this->Shop_Warehouse->Shop_Company : $this->Shop_Warehouse->Shop->Shop_Company,
			'shop_warehouse' => $this->Shop_Warehouse,
			'shop' => $this->Shop_Warehouse->Shop,
			'user' => $this->User,
			'total_count' => 0,
			'Items' => array(),
		);

		$position = 1;

		$aShop_Warehouse_Regrade_Items = $this->Shop_Warehouse_Regrade_Items->findAll();

		foreach ($aShop_Warehouse_Regrade_Items as $oShop_Warehouse_Regrade_Item)
		{
			$oShop_Item_Writeoff = Core_Entity::factory('Shop_Item')->getById($oShop_Warehouse_Regrade_Item->writeoff_shop_item_id);
			$oShop_Item_Incoming = Core_Entity::factory('Shop_Item')->getById($oShop_Warehouse_Regrade_Item->incoming_shop_item_id);

			if (!is_null($oShop_Item_Writeoff) && !is_null($oShop_Item_Incoming))
			{
				$oShop_Item_Writeoff = $oShop_Item_Writeoff->shortcut_id
					? $oShop_Item_Writeoff->Shop_Item
					: $oShop_Item_Writeoff;

				$oShop_Item_Incoming = $oShop_Item_Incoming->shortcut_id
					? $oShop_Item_Incoming->Shop_Item
					: $oShop_Item_Incoming;

				$aWriteoffBarcodes = array();

				$aWriteoff_Shop_Item_Barcodes = $oShop_Item_Writeoff->Shop_Item_Barcodes->findAll(FALSE);
				foreach ($aWriteoff_Shop_Item_Barcodes as $oShop_Item_Barcode)
				{
					$aWriteoffBarcodes[] = $oShop_Item_Barcode->value;
				}

				$aIncomingBarcodes = array();

				$aIncoming_Shop_Item_Barcodes = $oShop_Item_Incoming->Shop_Item_Barcodes->findAll(FALSE);
				foreach ($aIncoming_Shop_Item_Barcodes as $oShop_Item_Barcode)
				{
					$aIncomingBarcodes[] = $oShop_Item_Barcode->value;
				}

				$node = new stdClass();

				$node->position = $position++;
				$node->writeoff_item = $oShop_Item_Writeoff;
				$node->writeoff_name = htmlspecialchars($oShop_Item_Writeoff->name);
				$node->writeoff_measure = htmlspecialchars($oShop_Item_Writeoff->Shop_Measure->name);
				$node->writeoff_currency = htmlspecialchars($oShop_Item_Writeoff->Shop_Currency->name);
				$node->writeoff_price = $oShop_Warehouse_Regrade_Item->writeoff_price;
				$node->writeoff_barcodes = implode(', ', $aWriteoffBarcodes);
				$node->incoming_item = $oShop_Item_Incoming;
				$node->incoming_name = htmlspecialchars($oShop_Item_Incoming->name);
				$node->incoming_measure = htmlspecialchars($oShop_Item_Incoming->Shop_Measure->name);
				$node->incoming_currency = htmlspecialchars($oShop_Item_Incoming->Shop_Currency->name);
				$node->incoming_price = $oShop_Warehouse_Regrade_Item->incoming_price;
				$node->incoming_barcodes = implode(', ', $aIncomingBarcodes);
				$node->count = $oShop_Warehouse_Regrade_Item->count;

				$aReplace['Items'][] = $node;

				$aReplace['total_count']++;
			}
		}

		return $aReplace;
	}
}