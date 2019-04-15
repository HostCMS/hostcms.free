<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Warehouse_Movement_Model
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2019 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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
	 * Constructor.
	 * @param int $id entity ID
	 */
	public function __construct($id = NULL)
	{
		parent::__construct($id);

		if (is_null($id) && !$this->loaded())
		{
			$oUserCurrent = Core_Entity::factory('User', 0)->getCurrent();
			$this->_preloadValues['user_id'] = is_null($oUserCurrent) ? 0 : $oUserCurrent->id;
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

		// Ответственный по сделке
		$oUser = $this->User;

		echo '<div class="contracrot"><div class="user-image"><img class="contracrot-ico" src="' . $oUser->getAvatar() .'" /></div><div class="user-name" style="margin-top: 8px;"><a class="darkgray" href="/admin/user/index.php?hostcms[action]=view&hostcms[checked][0][' . $oUser->id . ']=1" onclick="$.modalLoad({path: \'/admin/user/index.php\', action: \'view\', operation: \'modal\', additionalParams: \'hostcms[checked][0][' . $oUser->id . ']=1\', windowId: \'id_content\'}); return false">' . htmlspecialchars($oUser->getFullName()) . '</a></div></div>';

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

		$aShop_Warehouse_Entries = Core_Entity::factory('Shop_Warehouse_Entry')->getByDocument($this->id, 4);
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
			$oSource_Shop_Warehouse = $this->Source_Shop_Warehouse;
			$oDestination_Shop_Warehouse = $this->Destination_Shop_Warehouse;

			$aShop_Warehouse_Entries = Core_Entity::factory('Shop_Warehouse_Entry')->getByDocument($this->id, 4);

			$aTmp = array();

			foreach ($aShop_Warehouse_Entries as $oShop_Warehouse_Entry)
			{
				$aTmp[$oShop_Warehouse_Entry->shop_warehouse_id][$oShop_Warehouse_Entry->shop_item_id][] = $oShop_Warehouse_Entry;
			}

			unset($aShop_Warehouse_Entries);

			$aShop_Warehouse_Movement_Items = $this->Shop_Warehouse_Movement_Items->findAll(FALSE);
			foreach ($aShop_Warehouse_Movement_Items as $oShop_Warehouse_Movement_Item)
			{
				if (isset($aTmp[$oSource_Shop_Warehouse->id][$oShop_Warehouse_Movement_Item->shop_item_id]) && count($aTmp[$oSource_Shop_Warehouse->id][$oShop_Warehouse_Movement_Item->shop_item_id]))
				{
					$oShop_Warehouse_Entry_Source = array_unshift($aTmp[$oSource_Shop_Warehouse->id][$oShop_Warehouse_Movement_Item->shop_item_id]);
				}
				else
				{
					$oShop_Warehouse_Entry_Source = Core_Entity::factory('Shop_Warehouse_Entry');
					$oShop_Warehouse_Entry_Source->setDocument($this->id, 4);
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
					$oShop_Warehouse_Entry_Destination->setDocument($this->id, 4);
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

			$this->posted = 1;
			$this->save();
		}

		return $this;
	}

	public function unpost()
	{
		if ($this->posted)
		{
			$oSource_Shop_Warehouse = $this->Source_Shop_Warehouse;
			$oDestination_Shop_Warehouse = $this->Destination_Shop_Warehouse;

			$aShop_Warehouse_Entries = Core_Entity::factory('Shop_Warehouse_Entry')->getByDocument($this->id, 4);
			foreach ($aShop_Warehouse_Entries as $oShop_Warehouse_Entry)
			{
				$shop_item_id = $oShop_Warehouse_Entry->shop_item_id;
				$oShop_Warehouse_Entry->delete();

				$restSource = $oSource_Shop_Warehouse->getRest($shop_item_id);
				if (!is_null($restSource))
				{
					// Recount
					$oSource_Shop_Warehouse->setRest($shop_item_id, $restSource);
				}

				$restDestination = $oDestination_Shop_Warehouse->getRest($shop_item_id);
				if (!is_null($restDestination))
				{
					// Recount
					$oDestination_Shop_Warehouse->setRest($shop_item_id, $restDestination);
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
		Printlayout_Controller::getBackendPrintButton($oAdmin_Form_Controller, $this->id, 5);
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
}