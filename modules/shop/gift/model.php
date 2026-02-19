<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Gift_Model
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Shop_Gift_Model extends Core_Entity
{
	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'shop' => array(),
		'shop_gift_dir' => array(),
		'shop_currency' => array(),
		'user' => array()
	);

	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		'shop_purchase_discount_coupon' => array('through' => 'shop_gift_coupon'),
		'shop_gift_coupon' => array(),
		'shop_gift_siteuser_group' => array(),
		'shop_gift_entity' => array(),
		'shop_item_gift' => array(),
		'shop_group_gift' => array(),
		'shop_item' => array('through' => 'shop_item_gift'),
		'shop_group' => array('through' => 'shop_group_gift')
	);

	/**
	 * One-to-one relations
	 * @var array
	 */
	protected $_hasOne = array(
		'shop' => array()
	);

	/**
	 * List of preloaded values
	 * @var array
	 */
	protected $_preloadValues = array(
		'active' => 1,
		'value' => 0,
		'min_amount' => 0,
		'max_amount' => 0,
		'min_count' => 0,
		'max_count' => 0
	);

	/**
	 * Backend property
	 * @var int
	 */
	public $img = 3;

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
			$this->_preloadValues['start_datetime'] = Core_Date::timestamp2sql(time());
			$this->_preloadValues['end_datetime'] = Core_Date::timestamp2sql(strtotime("+1 year"));
		}
	}

	/**
	 * Change status of activity for discount
	 * @return self
	 */
	public function changeStatus()
	{
		$this->active = 1 - $this->active;
		return $this->save();
	}

	/**
	 * Copy object
	 * @return Core_Entity
	 * @hostcms-event shop_gift.onAfterRedeclaredCopy
	 */
	public function copy()
	{
		$newObject = parent::copy();

		$aShop_Gift_Siteuser_Groups = $this->Shop_Gift_Siteuser_Groups->findAll(FALSE);
		foreach ($aShop_Gift_Siteuser_Groups as $oShop_Gift_Siteuser_Group)
		{
			$newObject->add(clone $oShop_Gift_Siteuser_Group);
		}

		Core_Event::notify($this->_modelName . '.onAfterRedeclaredCopy', $newObject, array($this));

		return $newObject;
	}

	/**
	 * Move discount to another dir
	 * @param int $iShopGiftDirId target dir id
	 * @return Core_Entity
	 * @hostcms-event shop_gift.onBeforeMove
	 * @hostcms-event shop_gift.onAfterMove
	 */
	public function move($iShopGiftDirId)
	{
		Core_Event::notify($this->_modelName . '.onBeforeMove', $this, array($iShopGiftDirId));

		$this->shop_gift_dir_id = $iShopGiftDirId;
		$this->save();

		Core_Event::notify($this->_modelName . '.onAfterMove', $this);

		return $this;
	}

	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return Core_Entity
	 * @hostcms-event shop_gift.onBeforeRedeclaredDelete
	 */
	public function delete($primaryKey = NULL)
	{
		if (is_null($primaryKey))
		{
			$primaryKey = $this->getPrimaryKey();
		}

		$this->id = $primaryKey;

		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredDelete', $this, array($primaryKey));

		$this->Shop_Gift_Siteuser_Groups->deleteAll(FALSE);
		$this->Shop_Gift_Coupons->deleteAll(FALSE);
		$this->Shop_Item_Gifts->deleteAll(FALSE);
		$this->Shop_Group_Gifts->deleteAll(FALSE);
		$this->Shop_Gift_Entities->deleteAll(FALSE);

		return parent::delete($primaryKey);
	}

	/**
	 * Get XML for entity and children entities
	 * @return string
	 * @hostcms-event shop_gift.onBeforeRedeclaredGetXml
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
	 * @hostcms-event shop_gift.onBeforeRedeclaredGetStdObject
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
		return $this;
	}

	/**
	 * Backend callback method
	 * @param Admin_Form_Field_Model $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 */
	public function valueBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		$max_discount = '';

		/*if ($this->value > 80 && $this->type == 0)
		{
			$return = '<i class="fa fa-exclamation-triangle warning" title="More than 80%"></i> ';
		}
		else*/if($this->value == 0)
		{
			$return = '<i class="fa fa-exclamation-triangle warning" title="Zero Discount"></i> ';
		}
		else
		{
			$return = '';
		}

		if ($this->type == 0 && $this->max_discount > 0)
		{
			$max_discount = Core_Html_Entity::factory('Span')
				->class('badge badge-sky badge-ico white margin-left-5 pull-right')
				->title('Max discount')
				->value('≤' . htmlspecialchars($this->Shop->Shop_Currency->formatWithCurrency($this->max_discount)))
				->execute();
		}

		return $return . ($this->type == 0
			? Core_Str::hideZeros($this->value) . '%' . $max_discount
			: htmlspecialchars($this->Shop->Shop_Currency->formatWithCurrency($this->value))
		);
	}

	/**
	 * Backend callback method
	 */
	public function nameBackend()
	{
		$oCore_Html_Entity_Div = Core_Html_Entity::factory('Div')->value(
			htmlspecialchars($this->name)
		);

		$bRightTime = ($this->start_datetime == '0000-00-00 00:00:00' || time() > Core_Date::sql2timestamp($this->start_datetime))
			&& ($this->end_datetime == '0000-00-00 00:00:00' || time() < Core_Date::sql2timestamp($this->end_datetime));

		!$bRightTime && $oCore_Html_Entity_Div->class('wrongTime');

		// Зачеркнут в зависимости от статуса родительского товара или своего статуса
		if (!$this->active)
		{
			$oCore_Html_Entity_Div->class('inactive');
		}
		elseif (!$bRightTime)
		{
			$oCore_Html_Entity_Div
				->add(
					Core_Html_Entity::factory('I')->class('fa fa-clock-o black')
				);
		}

		if ($this->coupon && strlen($this->coupon_text))
		{
			$oCore_Html_Entity_Div->add(
				Core_Html_Entity::factory('Span')
					->class('badge badge-square badge-sky inverted badge-sm')
					->value(htmlspecialchars($this->coupon_text))
			);
		}

		$oShop_Gift_Siteuser_Groups = $this->Shop_Gift_Siteuser_Groups;

		$oShop_Gift_Siteuser_Groups->queryBuilder()
			->clearOrderBy()
			->orderBy('siteuser_group_id', 'ASC');

		$aShop_Gift_Siteuser_Groups = $oShop_Gift_Siteuser_Groups->findAll(FALSE);
		if (count($aShop_Gift_Siteuser_Groups))
		{
			foreach ($aShop_Gift_Siteuser_Groups as $oShop_Gift_Siteuser_Group)
			{
				$siteuserGroupName = $oShop_Gift_Siteuser_Group->siteuser_group_id
					? htmlspecialchars($oShop_Gift_Siteuser_Group->Siteuser_Group->name)
					: Core::_('Shop_Gift.all');

				$oCore_Html_Entity_Div->add(
					Core_Html_Entity::factory('Span')
						->class('badge badge-square badge-hostcms')
						->value('<i class="fa fa-users darkgray"></i> ' . $siteuserGroupName)
					);

				// Если "Все", то прерываем формирование списка
				if (!$oShop_Gift_Siteuser_Group->siteuser_group_id)
				{
					break;
				}
			}
		}
		else
		{
			$oCore_Html_Entity_Div->add(
				Core_Html_Entity::factory('Span')
					->class('badge badge-darkorange badge-ico white')
					->add(Core_Html_Entity::factory('I')->class('fa fa-exclamation-triangle'))
					->title('Empty group list!')
			);
		}

		$oCore_Html_Entity_Div->execute();
	}

	/**
	 * Backend callback method
	 * @param Admin_Form_Field_Model $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function min_amountBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		return $this->min_amount == 0
			? '—'
			: htmlspecialchars($this->Shop_Currency->formatWithCurrency($this->min_amount));
	}

	/**
	 * Backend callback method
	 * @param Admin_Form_Field_Model $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function max_amountBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		return $this->max_amount == 0
			? '—'
			: htmlspecialchars($this->Shop_Currency->formatWithCurrency($this->max_amount));
	}

	/**
	 * Cache isActive()
	 * @var NULL|boolean
	 */
	protected $_cacheIsActive = NULL;

	/**
	 * Check if gift active is
	 * @return boolean
	 * @hostcms-event shop_gift.onBeforeIsActive
	 */
	public function isActive()
	{
		if (is_null($this->_cacheIsActive))
		{
			Core_Event::notify($this->_modelName . '.onBeforeIsActive', $this);
			$active = Core_Event::getLastReturn();

			if (is_null($active))
			{
				$time = time();
				//$dayFieldName = 'day' . date('N');

				$active = $this->active
					&& Core_Date::sql2timestamp($this->start_datetime) <= $time
					&& Core_Date::sql2timestamp($this->end_datetime) >= $time
					//&& $time >= strtotime($this->start_time)
					//&& $time <= strtotime($this->end_time)
					//&& $this->$dayFieldName == 1
					;

				if ($active)
				{
					$aSiteuser_Group_IDs = array(0);

					if (Core::moduleIsActive('siteuser'))
					{
						$oSiteuser = Core_Entity::factory('Siteuser')->getCurrent();
						if ($oSiteuser)
						{
							$aSiteuser_Groups = $oSiteuser->Siteuser_Groups->findAll();
							foreach ($aSiteuser_Groups as $oSiteuser_Group)
							{
								$aSiteuser_Group_IDs[] = $oSiteuser_Group->id;
							}
						}
					}

					$oShop_Gift_Siteuser_Groups = $this->Shop_Gift_Siteuser_Groups;
					$oShop_Gift_Siteuser_Groups->queryBuilder()
						->where('shop_gift_siteuser_groups.siteuser_group_id', 'IN', $aSiteuser_Group_IDs);

					$iCount = $oShop_Gift_Siteuser_Groups->getCount();

					!$iCount && $active = FALSE;
				}
			}

			$this->_cacheIsActive = $active;
		}

		return $this->_cacheIsActive;
	}

	/**
	 * Get Related Site
	 * @return Site_Model|NULL
	 * @hostcms-event shop_gift.onBeforeGetRelatedSite
	 * @hostcms-event shop_gift.onAfterGetRelatedSite
	 */
	public function getRelatedSite()
	{
		Core_Event::notify($this->_modelName . '.onBeforeGetRelatedSite', $this);

		$oSite = $this->Shop->Site;

		Core_Event::notify($this->_modelName . '.onAfterGetRelatedSite', $this, array($oSite));

		return $oSite;
	}
}