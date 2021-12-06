<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Warehouse_Movement_Model
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Warehouse_Movement_Model extends Core_Entity
{
	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'source_shop_warehouse' => array('model' => 'Shop_Warehouse', 'foreign_key' => 'source_shop_warehouse_id'),
		'destination_shop_warehouse' => array('model' => 'Shop_Warehouse', 'foreign_key' => 'destination_shop_warehouse_id'),
		'user' => array(),
	);

	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		'shop_warehouse_movement_item' => array(),
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

	const TYPE = 4;

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
			$this->_preloadValues['posted'] = 0;
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

		$this->Shop_Warehouse_Movement_Items->deleteAll(FALSE);

		Core_Entity::factory('Shop_Warehouse_Entry')->deleteByDocument($this->id, self::TYPE);

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
			$oSource_Shop_Warehouse = $this->Source_Shop_Warehouse;
			$oDestination_Shop_Warehouse = $this->Destination_Shop_Warehouse;

			$aShop_Warehouse_Entries = Core_Entity::factory('Shop_Warehouse_Entry')->getByDocument($this->id, self::TYPE);

			$aTmp = array();

			foreach ($aShop_Warehouse_Entries as $oShop_Warehouse_Entry)
			{
				$aTmp[$oShop_Warehouse_Entry->shop_warehouse_id][$oShop_Warehouse_Entry->shop_item_id][] = $oShop_Warehouse_Entry;
			}

			unset($aShop_Warehouse_Entries);

			$limit = 500;
			$offset = 0;

			do {
				$oShop_Warehouse_Movement_Items = $this->Shop_Warehouse_Movement_Items;
				$oShop_Warehouse_Movement_Items->queryBuilder()
					->limit($limit)
					->offset($offset)
					->clearOrderBy()
					->orderBy('id', 'ASC');

				$aShop_Warehouse_Movement_Items = $oShop_Warehouse_Movement_Items->findAll(FALSE);
				foreach ($aShop_Warehouse_Movement_Items as $oShop_Warehouse_Movement_Item)
				{
					// Удаляем все накопительные значения с датой больше, чем дата документа
					Shop_Warehouse_Entry_Accumulate_Controller::deleteEntries($oShop_Warehouse_Movement_Item->shop_item_id, $oSource_Shop_Warehouse->id, $this->datetime);
					Shop_Warehouse_Entry_Accumulate_Controller::deleteEntries($oShop_Warehouse_Movement_Item->shop_item_id, $oDestination_Shop_Warehouse->id, $this->datetime);

					if (isset($aTmp[$oSource_Shop_Warehouse->id][$oShop_Warehouse_Movement_Item->shop_item_id]) && count($aTmp[$oSource_Shop_Warehouse->id][$oShop_Warehouse_Movement_Item->shop_item_id]))
					{
						$oShop_Warehouse_Entry_Source = array_shift($aTmp[$oSource_Shop_Warehouse->id][$oShop_Warehouse_Movement_Item->shop_item_id]);
					}
					else
					{
						$oShop_Warehouse_Entry_Source = Core_Entity::factory('Shop_Warehouse_Entry');
						$oShop_Warehouse_Entry_Source->setDocument($this->id, self::TYPE);
						$oShop_Warehouse_Entry_Source->shop_item_id = $oShop_Warehouse_Movement_Item->shop_item_id;
					}

					$oShop_Warehouse_Entry_Source->shop_warehouse_id = $oSource_Shop_Warehouse->id;
					$oShop_Warehouse_Entry_Source->datetime = $this->datetime;
					$oShop_Warehouse_Entry_Source->value = -$oShop_Warehouse_Movement_Item->count;
					$oShop_Warehouse_Entry_Source->save();

					if (isset($aTmp[$oDestination_Shop_Warehouse->id][$oShop_Warehouse_Movement_Item->shop_item_id]))
					{
						$oShop_Warehouse_Entry_Destination = $aTmp[$oDestination_Shop_Warehouse->id][$oShop_Warehouse_Movement_Item->shop_item_id];
					}
					else
					{
						$oShop_Warehouse_Entry_Destination = Core_Entity::factory('Shop_Warehouse_Entry');
						$oShop_Warehouse_Entry_Destination->setDocument($this->id, self::TYPE);
						$oShop_Warehouse_Entry_Destination->shop_item_id = $oShop_Warehouse_Movement_Item->shop_item_id;
					}

					$oShop_Warehouse_Entry_Destination->shop_warehouse_id = $oDestination_Shop_Warehouse->id;
					$oShop_Warehouse_Entry_Destination->datetime = $this->datetime;
					$oShop_Warehouse_Entry_Destination->value = $oShop_Warehouse_Movement_Item->count;
					$oShop_Warehouse_Entry_Destination->save();

					$restSource = $oSource_Shop_Warehouse->getRest($oShop_Warehouse_Movement_Item->shop_item_id);

					if (!is_null($restSource))
					{
						// Recount
						$oSource_Shop_Warehouse->setRest($oShop_Warehouse_Movement_Item->shop_item_id, $restSource);
					}

					$restDestination = $oDestination_Shop_Warehouse->getRest($oShop_Warehouse_Movement_Item->shop_item_id);

					if (!is_null($restDestination))
					{
						// Recount
						$oDestination_Shop_Warehouse->setRest($oShop_Warehouse_Movement_Item->shop_item_id, $restDestination);
					}
				}

				$offset += $limit;
			}
			while (count($aShop_Warehouse_Movement_Items));

			$this->posted = 1;
			$this->save();
		}

		return $this;
	}

	/**
	 * Remove entries
	 * @return self
	 */
	public function unpost()
	{
		if ($this->posted)
		{
			$aShop_Warehouse_Entries = Core_Entity::factory('Shop_Warehouse_Entry')->getByDocument($this->id, self::TYPE);

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
	 * @return string
	 */
	public function printBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		Core::moduleIsActive('printlayout')
			&& Printlayout_Controller::getBackendPrintButton($oAdmin_Form_Controller, $this->id, 5);
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function source_shop_warehouse_idBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		return htmlspecialchars($this->Source_Shop_Warehouse->name);
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function destination_shop_warehouse_idBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		return htmlspecialchars($this->Destination_Shop_Warehouse->name);
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
				'source_shop_warehouse_id' => $this->source_shop_warehouse_id,
				'destination_shop_warehouse_id' => $this->destination_shop_warehouse_id,
				'number' => $this->number,
				'description' => $this->description,
				'datetime' => $this->datetime,
				'posted' => $this->posted,
				'user_id' => $this->user_id,
				'items' => array()
			);

			$aShop_Warehouse_Movement_Items = $this->Shop_Warehouse_Movement_Items->findAll(FALSE);

			foreach ($aShop_Warehouse_Movement_Items as $oShop_Warehouse_Movement_Item)
			{
				$aBackup['items'][] = array(
					'shop_item_id' => $oShop_Warehouse_Movement_Item->shop_item_id,
					'count' => $oShop_Warehouse_Movement_Item->count,
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

				$this->source_shop_warehouse_id = Core_Array::get($aBackup, 'source_shop_warehouse_id');
				$this->destination_shop_warehouse_id = Core_Array::get($aBackup, 'destination_shop_warehouse_id');
				$this->number = Core_Array::get($aBackup, 'number');
				$this->description = Core_Array::get($aBackup, 'description');
				$this->datetime = Core_Array::get($aBackup, 'datetime');
				$this->posted = 0;
				$this->user_id = Core_Array::get($aBackup, 'user_id');

				$aAllItems = Core_Array::get($aBackup, 'items');

				if (count($aAllItems))
				{
					// Удаляем все товары
					$this->Shop_Warehouse_Movement_Items->deleteAll(FALSE);

					// Создаем новые
					foreach ($aAllItems as $aShop_Warehouse_Movement_Items)
					{
						$oShop_Warehouse_Movement_Item = Core_Entity::factory('Shop_Warehouse_Movement_Item');
						$oShop_Warehouse_Movement_Item->shop_warehouse_movement_id = $this->id;
						$oShop_Warehouse_Movement_Item->shop_item_id = Core_Array::get($aShop_Warehouse_Movement_Items, 'shop_item_id');
						$oShop_Warehouse_Movement_Item->count = Core_Array::get($aShop_Warehouse_Movement_Items, 'count');
						$oShop_Warehouse_Movement_Item->save();
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
		$count = $this->Shop_Warehouse_Movement_Items->getCount();
		$count && Core::factory('Core_Html_Entity_Span')
			->class('badge badge-success badge-square')
			->value($count)
			->execute();
	}

	/**
	 * Get printlayout replaces
	 * @return array
	 * @hostcms-event shop_warehouse_movement.onAfterGetPrintlayoutReplaces
	 */
	public function getPrintlayoutReplaces()
	{
		$aReplace = array(
			// Core_Meta
			'this' => $this,
			'company' => $this->Source_Shop_Warehouse->shop_company_id ? $this->Source_Shop_Warehouse->Shop_Company : $this->Source_Shop_Warehouse->Shop->Shop_Company,
			'shop_warehouse' => $this->Source_Shop_Warehouse,
			'destination_shop_warehouse' => $this->Destination_Shop_Warehouse,
			'shop' => $this->Source_Shop_Warehouse->Shop,
			'user' => $this->User,
			'type' => Core::_('Shop_Warehouse_Movement.title'),
			'total_count' => 0,
			'Items' => array(),
		);

		$position = 1;
		$total_quantity = 0;

		$Shop_Price_Entry_Controller = new Shop_Price_Entry_Controller();
		$Shop_Item_Controller = new Shop_Item_Controller();

		$aShop_Warehouse_Movement_Items = $this->Shop_Warehouse_Movement_Items->findAll(FALSE);

		foreach ($aShop_Warehouse_Movement_Items as $oShop_Warehouse_Movement_Item)
		{
			$oShop_Item = $oShop_Warehouse_Movement_Item->Shop_Item;

			$price = $Shop_Price_Entry_Controller->getPrice(0, $oShop_Item->id, $this->datetime);
			is_null($price) && $price = $oShop_Item->price;

			$aPrices = $Shop_Item_Controller->calculatePriceInItemCurrency($price, $oShop_Item);

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
			$node->measure = htmlspecialchars($oShop_Item->Shop_Measure->name);
			$node->currency = htmlspecialchars($oShop_Item->Shop_Currency->name);
			$node->price = $aPrices['price_tax'];
			$node->quantity = $oShop_Warehouse_Movement_Item->count;
			$node->amount = Shop_Controller::instance()->round($node->quantity * $node->price);
			$node->barcodes = implode(', ', $aBarcodes);

			$aReplace['Items'][] = $node;

			$aReplace['total_count']++;

			$total_quantity += $oShop_Warehouse_Movement_Item->count;
		}

		$aReplace['quantity'] = $total_quantity;

		Core_Event::notify($this->_modelName . '.onAfterGetPrintlayoutReplaces', $this, array($aReplace));
		$eventResult = Core_Event::getLastReturn();

		return !is_null($eventResult)
			? $eventResult
			: $aReplace;
	}

	/**
	 * Get Related Site
	 * @return Site_Model|NULL
	 * @hostcms-event shop_warehouse_movement.onBeforeGetRelatedSite
	 * @hostcms-event shop_warehouse_movement.onAfterGetRelatedSite
	 */
	public function getRelatedSite()
	{
		Core_Event::notify($this->_modelName . '.onBeforeGetRelatedSite', $this);

		$oSite = $this->Source_Shop_Warehouse->Shop->Site;

		Core_Event::notify($this->_modelName . '.onAfterGetRelatedSite', $this, array($oSite));

		return $oSite;
	}
}