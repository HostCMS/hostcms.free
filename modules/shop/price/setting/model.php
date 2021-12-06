<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Price_Setting_Model
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Price_Setting_Model extends Core_Entity
{
	/**
	 * Backend property
	 * @var mixed
	 */
	public $rollback = 0;

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'shop' => array(),
		'user' => array()
	);

	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		'shop_price_setting_item' => array(),
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
			$oUser = Core_Auth::getCurrentUser();
			$this->_preloadValues['user_id'] = is_null($oUser) ? 0 : $oUser->id;
			$this->_preloadValues['datetime'] = Core_Date::timestamp2sql(time());
			$this->_preloadValues['posted'] = 0;
		}
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
	 * @hostcms-event shop_price_setting.onBeforeRedeclaredDelete
	 */
	public function delete($primaryKey = NULL)
	{
		if (is_null($primaryKey))
		{
			$primaryKey = $this->getPrimaryKey();
		}

		$this->id = $primaryKey;

		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredDelete', $this, array($primaryKey));

		//$this->Shop_Price_Setting_Items->deleteAll(FALSE);
		Core_QueryBuilder::delete('shop_price_setting_items')
			->where('shop_price_setting_id', '=', $this->id)
			->execute();

		Core_Entity::factory('Shop_Price_Entry')->deleteByDocument($this->id, 0);

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
			$aShop_Price_Entries = Core_Entity::factory('Shop_Price_Entry')->getByDocument($this->id, 0);

			$Shop_Price_Entry_Controller = new Shop_Price_Entry_Controller();

			$aTmp = array();

			foreach ($aShop_Price_Entries as $oShop_Price_Entry)
			{
				$aTmp[$oShop_Price_Entry->shop_price_id][$oShop_Price_Entry->shop_item_id] = $oShop_Price_Entry;
			}

			unset($aShop_Price_Entries);

			$limit = 500;
			$offset = 0;

			do {
				$oShop_Price_Setting_Items = $this->Shop_Price_Setting_Items;
				$oShop_Price_Setting_Items->queryBuilder()
					->limit($limit)
					->offset($offset)
					->clearOrderBy()
					->orderBy('id', 'ASC');

				$aShop_Price_Setting_Items = $oShop_Price_Setting_Items->findAll(FALSE);

				foreach ($aShop_Price_Setting_Items as $oShop_Price_Setting_Item)
				{
					if (isset($aTmp[$oShop_Price_Setting_Item->shop_item_id]))
					{
						$oShop_Price_Entry = $aTmp[$oShop_Price_Setting_Item->shop_price_id][$oShop_Price_Setting_Item->shop_item_id];
					}
					else
					{
						$oShop_Price_Entry = Core_Entity::factory('Shop_Price_Entry');
						$oShop_Price_Entry->setDocument($this->id, 0);
						$oShop_Price_Entry->shop_item_id = $oShop_Price_Setting_Item->shop_item_id;
					}

					$oShop_Price_Entry->shop_price_id = $oShop_Price_Setting_Item->shop_price_id;
					$oShop_Price_Entry->datetime = $this->datetime;
					$oShop_Price_Entry->value = $oShop_Price_Setting_Item->new_price;
					$oShop_Price_Entry->save();

					// Update price
					$Shop_Price_Entry_Controller->setPrice(
						$oShop_Price_Setting_Item->shop_price_id,
						$oShop_Price_Setting_Item->shop_item_id,
						$Shop_Price_Entry_Controller->getPrice($oShop_Price_Setting_Item->shop_price_id, $oShop_Price_Setting_Item->shop_item_id)
					);
				}

				$offset += $limit;
			}
			while (count($aShop_Price_Setting_Items));

			$this->posted = 1;
			$this->save();
		}

		return $this;
	}

	/**
	 * Delete entries
	 * @return self
	 */
	public function unpost()
	{
		if ($this->posted)
		{
			$aShop_Price_Entries = Core_Entity::factory('Shop_Price_Entry')->getByDocument($this->id, 0);

			$Shop_Price_Entry_Controller = new Shop_Price_Entry_Controller();

			foreach ($aShop_Price_Entries as $oShop_Price_Entry)
			{
				$oldPrice = $Shop_Price_Entry_Controller->getPrice($oShop_Price_Entry->shop_price_id, $oShop_Price_Entry->shop_item_id, $this->datetime);

				if (!is_null($oldPrice))
				{
					// Update price
					$Shop_Price_Entry_Controller->setPrice(
						$oShop_Price_Entry->shop_price_id,
						$oShop_Price_Entry->shop_item_id,
						$oldPrice
					);
				}

				$oShop_Price_Entry->delete();
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
			&& Printlayout_Controller::getBackendPrintButton($oAdmin_Form_Controller, $this->id, 10);
	}

	/**
	 * Backend badge
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function count_itemsBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		$count = $this->Shop_Price_Setting_Items->getCount();
		$count && Core::factory('Core_Html_Entity_Span')
			->class('badge badge-info badge-square')
			->value($count)
			->execute();
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
				'shop_id' => $this->shop_id,
				'number' => $this->number,
				'description' => $this->description,
				'datetime' => $this->datetime,
				'posted' => $this->posted,
				'user_id' => $this->user_id,
				'items' => array()
			);

			$aShop_Price_Setting_Items = $this->Shop_Price_Setting_Items->findAll(FALSE);

			foreach ($aShop_Price_Setting_Items as $oShop_Price_Setting_Item)
			{
				$aBackup['items'][] = array(
					'shop_price_id' => $oShop_Price_Setting_Item->shop_price_id,
					'shop_item_id' => $oShop_Price_Setting_Item->shop_item_id,
					'old_price' => $oShop_Price_Setting_Item->old_price,
					'new_price' => $oShop_Price_Setting_Item->new_price,
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

				$this->shop_id = Core_Array::get($aBackup, 'shop_id');
				$this->number = Core_Array::get($aBackup, 'number');
				$this->description = Core_Array::get($aBackup, 'description');
				$this->datetime = Core_Array::get($aBackup, 'datetime');
				$this->posted = 0;
				$this->user_id = Core_Array::get($aBackup, 'user_id');

				$aAllItems = Core_Array::get($aBackup, 'items');

				if (count($aAllItems))
				{
					// Удаляем все товары
					$this->Shop_Price_Setting_Items->deleteAll(FALSE);

					// Создаем новые
					foreach ($aAllItems as $aShop_Price_Setting_Items)
					{
						$oShop_Price_Setting_Item = Core_Entity::factory('Shop_Price_Setting_Item');
						$oShop_Price_Setting_Item->shop_price_setting_id = $this->id;
						$oShop_Price_Setting_Item->shop_price_id = Core_Array::get($aShop_Price_Setting_Items, 'shop_price_id');
						$oShop_Price_Setting_Item->shop_item_id = Core_Array::get($aShop_Price_Setting_Items, 'shop_item_id');
						$oShop_Price_Setting_Item->old_price = Core_Array::get($aShop_Price_Setting_Items, 'old_price');
						$oShop_Price_Setting_Item->new_price = Core_Array::get($aShop_Price_Setting_Items, 'new_price');
						$oShop_Price_Setting_Item->save();
					}
				}

				$this->save();

				Core_Array::get($aBackup, 'posted') && $this->post();
			}
		}

		return $this;
	}

	/**
	 * Get Related Site
	 * @return Site_Model|NULL
	 * @hostcms-event shop_price_setting.onBeforeGetRelatedSite
	 * @hostcms-event shop_price_setting.onAfterGetRelatedSite
	 */
	public function getRelatedSite()
	{
		Core_Event::notify($this->_modelName . '.onBeforeGetRelatedSite', $this);

		$oSite = $this->Shop->Site;

		Core_Event::notify($this->_modelName . '.onAfterGetRelatedSite', $this, array($oSite));

		return $oSite;
	}
}