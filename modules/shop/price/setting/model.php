<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Price_Setting_Model
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2019 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Price_Setting_Model extends Core_Entity
{
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
			$oUserCurrent = Core_Entity::factory('User', 0)->getCurrent();
			$this->_preloadValues['user_id'] = is_null($oUserCurrent) ? 0 : $oUserCurrent->id;
			$this->_preloadValues['datetime'] = Core_Date::timestamp2sql(time());
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

		$this->Shop_Price_Setting_Items->deleteAll(FALSE);

		$aShop_Price_Entries = Core_Entity::factory('Shop_Price_Entry')->getByDocument($this->id, 0);
		foreach ($aShop_Price_Entries as $oShop_Price_Entry)
		{
			$oShop_Price_Entry->delete();
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
echo "post";
			$aShop_Price_Entries = Core_Entity::factory('Shop_Price_Entry')->getByDocument($this->id, 0);

			$Shop_Price_Entry_Controller = new Shop_Price_Entry_Controller();

			$aTmp = array();

			foreach ($aShop_Price_Entries as $oShop_Price_Entry)
			{
				$aTmp[$oShop_Price_Entry->shop_price_id][$oShop_Price_Entry->shop_item_id] = $oShop_Price_Entry;
			}

			unset($aShop_Price_Entries);

			$aShop_Price_Setting_Items = $this->Shop_Price_Setting_Items->findAll(FALSE);
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
}