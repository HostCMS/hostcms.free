<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Warehouse_Item_Model
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Warehouse_Item_Model extends Core_Entity
{
	/**
	 * Disable markDeleted()
	 * @var mixed
	 */
	protected $_marksDeleted = NULL;

	/**
	 * Triggered by calling isset() or empty() on inaccessible properties
	 * @param string $property property name
	 * @return boolean
	 */
	public function __isset($property)
	{
		return strtolower($property) == 'adminprice'
			? TRUE
			: parent::__isset($property);
	}

	/**
	 * Run when writing data to inaccessible properties
	 * @param string $property property name
	 * @param string $value property value
	 * @return self
	 */
	public function __set($property, $value)
	{
		if ($property == 'adminPrice')
		{
			$this->adminPrice($value);
			return $this;
		}

		return parent::__set($property, $value);
	}

	/**
	 * Utilized for reading data from inaccessible properties
	 * @param string $property property name
	 * @return mixed
	 */
	public function __get($property)
	{
		return strtolower($property) == 'adminprice'
			? ($this->Shop_Item->shortcut_id
				? Core_Entity::factory('Shop_Item', $this->Shop_Item->shortcut_id)->price
				: $this->Shop_Item->price)
			: parent::__get($property);
	}

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'shop_item' => array(),
		'shop_warehouse' => array(),
		'user' => array()
	);

	/**
	 * Forbidden tags. If list of tags is empty, all tags will be shown.
	 * @var array
	 */
	protected $_forbiddenTags = array(
		'user_id',
	);

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
		}
	}

	/**
	 * Get reserved items
	 * @return int
	 * @hostcms-event shop_warehouse_item_model.onAfterGetReserved
	 */
	public function getReserved()
	{
		$oShop_Item_Reserveds = Core_Entity::factory('Shop_Item_Reserved');
		$oShop_Item_Reserveds->queryBuilder()
			->where('shop_item_id', '=', $this->shop_item_id)
			->where('shop_warehouse_id', '=', $this->shop_warehouse_id)
			->where('datetime', '>', Core_Date::timestamp2sql(time() - $this->Shop_Item->Shop->reserve_hours * 60 * 60));

		$aShop_Item_Reserveds = $oShop_Item_Reserveds->findAll();

		$reserved = 0;
		foreach ($aShop_Item_Reserveds as $oShop_Item_Reserved)
		{
			$reserved += $oShop_Item_Reserved->count;
		}

		Core_Event::notify($this->_modelName . '.onAfterGetReserved', $this, array($reserved));
		
		$eventResult = Core_Event::getLastReturn();

		return !is_null($eventResult)
			? $eventResult
			: $reserved;
	}

	/**
	 * Get XML for entity and children entities
	 * @return string
	 * @hostcms-event shop_warehouse_item.onBeforeRedeclaredGetXml
	 */
	public function getXml()
	{
		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredGetXml', $this);

		$this->_prepareData();

		return parent::getXml();
	}

	/**
	 * Get stdObject for entity and children entities
	 * @return stdObject
	 * @hostcms-event shop_warehouse_item.onBeforeRedeclaredGetStdObject
	 */
	public function getStdObject($attributePrefix = '_')
	{
		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredGetStdObject', $this);

		$this->_prepareData();

		return parent::getStdObject($attributePrefix);
	}

	/**
	 * Prepare entity and children entities
	 * @return self
	 */
	protected function _prepareData()
	{
		$reserved = $this->getReserved();

		$this
			->clearXmlTags()
			->addEntity(
				Core::factory('Core_Xml_Entity')
					->name('reserved')
					->value($reserved)
			);

		return $this;
	}

	/**
	 * Get item count by item ID
	 * @param int $shop_item_id item ID
	 * @param boolean $bCache cache mode
	 * @return self|NULL
	 */
	public function getByShopItemId($shop_item_id, $bCache = TRUE)
	{
		$this->queryBuilder()
			//->clear()
			->where('shop_item_id', '=', $shop_item_id)
			->limit(1);

		$aShop_Warehouse_Items = $this->findAll($bCache);

		return isset($aShop_Warehouse_Items[0])
			? $aShop_Warehouse_Items[0]
			: NULL;
	}

	/**
	 * Get item count by warehouse ID
	 * @param int $shop_warehouse_id warehouse ID
	 * @param boolean $bCache cache mode
	 * @return self|NULL
	 */
	public function getByWarehouseId($shop_warehouse_id, $bCache = TRUE)
	{
		$this->queryBuilder()
			//->clear()
			->where('shop_warehouse_id', '=', $shop_warehouse_id)
			->limit(1);

		$aShop_Warehouse_Items = $this->findAll($bCache);

		return isset($aShop_Warehouse_Items[0])
			? $aShop_Warehouse_Items[0]
			: NULL;
	}

	/**
	 * Backend callback method
	 * @param object $value value
	 * @return string
	 * @hostcms-event shop_warehouse_item.onBeforeAdminPrice
	 * @hostcms-event shop_warehouse_item.onAfterAdminPrice
	 */
	public function adminPrice($value = NULL)
	{
		// Relation before __construct does not exist!
		if (isset($this->Shop_Item) && $this->Shop_Item->price != $value)
		{
			Core_Event::notify($this->_modelName . '.onBeforeAdminPrice', $this->Shop_Item);

			$this->Shop_Item->price = $value;
			$this->Shop_Item->save();

			Core_Event::notify($this->_modelName . '.onAfterAdminPrice', $this->Shop_Item);
		}

		return $this;
	}

	/**
	 * Backend badge
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function adminPriceBadge($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		$this->Shop_Item->type == 3 && Core_Html_Entity::factory('Span')
			->class('badge badge-ico badge-purple white')
			->style('padding-left: 1px;')
			->value('<i class="fa fa-archive fa-fw"></i>')
			->execute();
	}

	/**
	 * Get item's currency
	 * @return string
	 */
	public function adminCurrency()
	{
		$oShop_Item = $this->Shop_Item->shortcut_id
			? Core_Entity::factory('Shop_Item', $this->Shop_Item->shortcut_id)
			: $this->Shop_Item;

		return htmlspecialchars($oShop_Item->Shop_Currency->sign);
	}

	/**
	 * Get item's measure
	 * @return string
	 */
	public function adminMeasure()
	{
		$oShop_Item = $this->Shop_Item->shortcut_id
			? Core_Entity::factory('Shop_Item', $this->Shop_Item->shortcut_id)
			: $this->Shop_Item;

		return htmlspecialchars($oShop_Item->Shop_Measure->name);
	}

	/**
	 * Get item's name
	 * @return string
	 */
	public function name()
	{
		$oShop_Item = $this->Shop_Item;

		$oCore_Html_Entity_Div = Core_Html_Entity::factory('Div')->value(
			htmlspecialchars($oShop_Item->name)
		);

		$bRightTime = ($oShop_Item->start_datetime == '0000-00-00 00:00:00' || time() > Core_Date::sql2timestamp($oShop_Item->start_datetime))
			&& ($oShop_Item->end_datetime == '0000-00-00 00:00:00' || time() < Core_Date::sql2timestamp($oShop_Item->end_datetime));

		!$bRightTime && $oCore_Html_Entity_Div->class('wrongTime');

		// Зачеркнут в зависимости от статуса родительского товара или своего статуса
		if (!$oShop_Item->active)
		{
			$oCore_Html_Entity_Div->class('inactive');
		}
		elseif ($bRightTime)
		{
			$oCurrentAlias = $oShop_Item->Shop->Site->getCurrentAlias();

			if ($oCurrentAlias)
			{
				$href = ($oShop_Item->Shop->Structure->https ? 'https://' : 'http://')
					. $oCurrentAlias->name
					. $oShop_Item->Shop->Structure->getPath()
					. $oShop_Item->getPath();

				$oCore_Html_Entity_Div
				->add(
					Core_Html_Entity::factory('A')
						->href($href)
						->target('_blank')
						->add(
							Core_Html_Entity::factory('I')->class('fa fa-external-link')
						)
				);
			}
		}
		elseif (!$bRightTime)
		{
			$oCore_Html_Entity_Div
				->add(
					Core_Html_Entity::factory('I')->class('fa fa-clock-o black')
				);
		}

		$oCore_Html_Entity_Div->execute();
	}

	/**
	 * Get item's name
	 * @return string
	 */
	public function marking()
	{
		$oShopItem = $this->Shop_Item->shortcut_id
			? Core_Entity::factory('Shop_Item', $this->Shop_Item->shortcut_id)
			: $this->Shop_Item;

		return htmlspecialchars($oShopItem->marking);
	}

	/**
	 * Backend badge
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function adminCurrencyBadge($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		$oShop_Item = $this->Shop_Item->shortcut_id
			? Core_Entity::factory('Shop_Item', $this->Shop_Item->shortcut_id)
			: $this->Shop_Item;

		$oShop_Item->shop_currency_id == 0 && Core_Html_Entity::factory('Span')
			->class('badge badge-ico badge-darkorange white')
			->value('<i class="fa fa-exclamation fa-fw"></i>')
			->title(Core::_('Shop_Item.shop_item_not_currency'))
			->execute();
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function imgBackend()
	{
		if ($this->Shop_Item->shortcut_id)
		{
			return '<i class="fa fa-link"></i>';
		}
		elseif (strlen($this->Shop_Item->image_small))
		{
			$dataContent = '<img class="backend-preview" src="' . htmlspecialchars($this->Shop_Item->getSmallFileHref()) . '" />';

			return '<img data-toggle="popover" data-trigger="hover" data-html="true" data-placement="top" data-content="' . htmlspecialchars($dataContent) . '" class="backend-thumbnail" src="' . htmlspecialchars($this->Shop_Item->getSmallFileHref()) . '" />';
		}
		else
		{
			return '<i class="fa fa-file-text-o"></i>';
		}
	}

	/**
	 * Backend badge
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	/*public function countBadge($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		$this->count == '0.00' && Core_Html_Entity::factory('Span')
			->class('badge badge-ico badge-darkorange white')
			->value('<i class="fa fa-exclamation"></i>')
			->execute();
	}*/

	/**
	 * Backend callback method
	 * @return string
	 */
	public function countBackend()
	{
		$class = $this->count > 0
			? 'green'
			: 'darkorange';

		$this->count == 0 && $class = '';

		Core_Html_Entity::factory('Span')
			->class($class)
			->value($this->count)
			->execute();
	}

	/**
	 * Get Related Site
	 * @return Site_Model|NULL
	 * @hostcms-event shop_warehouse_item.onBeforeGetRelatedSite
	 * @hostcms-event shop_warehouse_item.onAfterGetRelatedSite
	 */
	public function getRelatedSite()
	{
		Core_Event::notify($this->_modelName . '.onBeforeGetRelatedSite', $this);

		$oSite = $this->Shop_Warehouse->Shop->Site;

		Core_Event::notify($this->_modelName . '.onAfterGetRelatedSite', $this, array($oSite));

		return $oSite;
	}
}