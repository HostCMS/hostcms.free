<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Discount_Model
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
 class Shop_Discount_Model extends Core_Entity
{
	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		'shop_item_discount' => array(),
		'shop_discount_siteuser_group' => array()
	);

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'shop' => array(),
		'shop_discount_dir' => array(),
		'shop_item' =>array(),
		'user' => array()
	);

	/**
	 * List of preloaded values
	 * @var array
	 */
	protected $_preloadValues = array(
		'value' => 0,
		'active' => 1,
		'type' => 0
	);

	/**
	 * Forbidden tags. If list of tags is empty, all tags will be shown.
	 * @var array
	 */
	protected $_forbiddenTags = array(
		'deleted',
		'user_id',
		'start_datetime',
		'end_datetime',
	);

	/**
	 * Default sorting for models
	 * @var array
	 */
	protected $_sorting = array(
		'shop_discounts.sorting' => 'ASC'
	);

	/**
	 * Backend property
	 * @var int
	 */
	public $img = 0;

	/**
	 * Callback property_id
	 * @var int
	 */
	public $shop_items = 0;

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
			$this->_preloadValues['guid'] = Core_Guid::get();
		}
	}

	/**
	 * Cache isActive()
	 * @var NULL|boolean
	 */
	protected $_cacheIsActive = NULL;

	/**
	 * Check if discount active is
	 * @return boolean
	 */
	public function isActive()
	{
		if (is_null($this->_cacheIsActive))
		{
			$time = time();
			$dayFieldName = 'day' . date('N');

			$active = $this->active
				&& Core_Date::sql2timestamp($this->start_datetime) <= $time
				&& Core_Date::sql2timestamp($this->end_datetime) >= $time
				&& $time >= strtotime($this->start_time)
				&& $time <= strtotime($this->end_time)
				&& $this->$dayFieldName == 1;

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

				$oShop_Discount_Siteuser_Groups = $this->Shop_Discount_Siteuser_Groups;
				$oShop_Discount_Siteuser_Groups->queryBuilder()
					->where('shop_discount_siteuser_groups.siteuser_group_id', 'IN', $aSiteuser_Group_IDs);

				$iCount = $oShop_Discount_Siteuser_Groups->getCount();

				!$iCount && $active = FALSE;
			}

			$this->_cacheIsActive = $active;
		}

		return $this->_cacheIsActive;
	}

	/**
	 * Change discount status
	 * @return self
	 */
	public function changeStatus()
	{
		$this->active = 1 - $this->active;
		$this->save();
		return $this;
	}

	/**
	 * Copy object
	 * @return Core_Entity
	 * @hostcms-event shop_discount.onAfterRedeclaredCopy
	 */
	public function copy()
	{
		$newObject = parent::copy();

		$newObject->guid = Core_Guid::get();
		$newObject->save();

		$aShop_Discount_Siteuser_Groups = $this->Shop_Discount_Siteuser_Groups->findAll(FALSE);
		foreach ($aShop_Discount_Siteuser_Groups as $oShop_Discount_Siteuser_Group)
		{
			$newObject->add(clone $oShop_Discount_Siteuser_Group);
		}

		Core_Event::notify($this->_modelName . '.onAfterRedeclaredCopy', $newObject, array($this));

		return $newObject;
	}

	/**
	 * Move discount to another dir
	 * @param int $iShopDiscountDirId target dir id
	 * @return Core_Entity
	 * @hostcms-event shop_discount.onBeforeMove
	 * @hostcms-event shop_discount.onAfterMove
	 */
	public function move($iShopDiscountDirId)
	{
		Core_Event::notify($this->_modelName . '.onBeforeMove', $this, array($iShopDiscountDirId));

		$this->shop_discount_dir_id = $iShopDiscountDirId;
		$this->save();

		Core_Event::notify($this->_modelName . '.onAfterMove', $this);

		return $this;
	}

	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return self
	 * @hostcms-event shop_discount.onBeforeRedeclaredDelete
	 */
	public function delete($primaryKey = NULL)
	{
		if (is_null($primaryKey))
		{
			$primaryKey = $this->getPrimaryKey();
		}

		$this->id = $primaryKey;

		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredDelete', $this, array($primaryKey));

		$this->Shop_Item_Discounts->deleteAll(FALSE);
		$this->Shop_Discount_Siteuser_Groups->deleteAll(FALSE);

		return parent::delete($primaryKey);
	}

	/**
	 * Get XML for entity and children entities
	 * @return string
	 * @hostcms-event shop_discount.onBeforeRedeclaredGetXml
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
	 * @hostcms-event shop_discount.onBeforeRedeclaredGetStdObject
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
		$oShop = $this->Shop;

		$this->clearXmlTags();

		$this->addXmlTag('start_datetime', $this->start_datetime == '0000-00-00 00:00:00'
			? $this->start_datetime
			: Core_Date::strftime($oShop->format_datetime, Core_Date::sql2timestamp($this->start_datetime)));

		$this->addXmlTag('end_datetime', $this->end_datetime == '0000-00-00 00:00:00'
			? $this->end_datetime
			: Core_Date::strftime($oShop->format_datetime, Core_Date::sql2timestamp($this->end_datetime)));

		$this->type == 0
			? $this->addXmlTag('percent', $this->value)
			: $this->addXmlTag('amount', $this->value);

		return $this;
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function nameBackend()
	{
		$oCore_Html_Entity_Div = Core_Html_Entity::factory('Div');

		$oCore_Html_Entity_Div->add(
			$Core_Html_Entity_Span = Core_Html_Entity::factory('Span')->value(
				'<i class="fa fa-circle" style="color: ' . ($this->color ? htmlspecialchars($this->color) : '#aebec4') . '"></i> '
			)
		);

		$oCore_Html_Entity_Div->add(
			$Core_Html_Entity_Span = Core_Html_Entity::factory('Span')->value(
				htmlspecialchars($this->name)
			)
		);

		$bRightTime = ($this->start_datetime == '0000-00-00 00:00:00' || time() > Core_Date::sql2timestamp($this->start_datetime))
			&& ($this->end_datetime == '0000-00-00 00:00:00' || time() < Core_Date::sql2timestamp($this->end_datetime));

		!$bRightTime && $Core_Html_Entity_Span->class('wrongTime');

		// Зачеркнут в зависимости от статуса родительского товара или своего статуса
		if (!$this->active)
		{
			$Core_Html_Entity_Span->class('line-through');
		}
		elseif (!$bRightTime)
		{
			$Core_Html_Entity_Span
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

		if ($this->start_time != '00:00:00' || $this->end_time != '23:59:59')
		{
			$oCore_Html_Entity_Div->add(
				Core_Html_Entity::factory('Span')
					->class('badge badge-square badge-orange inverted badge-sm')
					->value($this->start_time . ' – ' . $this->end_time)
			);
		}

		if ($this->not_apply_purchase_discount)
		{
			$oCore_Html_Entity_Div->add(
				Core_Html_Entity::factory('Span')
					->class('fa-stack')
					->style('font-size: 0.7em;')
					->title(Core::_('Shop_Discount.not_apply_purchase_discount'))
					->value('<i class="fas fa-percent fa-stack-1x"></i><i class="fas fa-ban fa-stack-2x danger"></i>')
			);
		}

		// Check if necessary show days
		$allDays = TRUE;
		for ($i = 1; $i <= 7; $i++)
		{
			$fieldName = 'day' . $i;
			if (!$this->$fieldName)
			{
				$allDays = FALSE;
				break;
			}
		}

		if ($allDays)
		{
			$oCore_Html_Entity_Div->add(
				Core_Html_Entity::factory('Span')
					->class('badge badge-square badge-palegreen inverted badge-sm')
					->value(Core::_('Shop_Discount.all_days'))
				);
		}
		else
		{
			// Show days
			for ($i = 1; $i <= 7; $i++)
			{
				$fieldName = 'day' . $i;
				if ($this->$fieldName)
				{
					$oCore_Html_Entity_Div->add(
						Core_Html_Entity::factory('Span')
							->class('badge badge-square badge-palegreen inverted badge-sm')
							->value(Core::_('Shop_Discount.' . $fieldName))
						);
				}
			}
		}

		$oShop_Discount_Siteuser_Groups = $this->Shop_Discount_Siteuser_Groups;
		
		$oShop_Discount_Siteuser_Groups->queryBuilder()
			->clearOrderBy()
			->orderBy('siteuser_group_id', 'ASC');
		
		$aShop_Discount_Siteuser_Groups = $oShop_Discount_Siteuser_Groups->findAll(FALSE);
		if (count($aShop_Discount_Siteuser_Groups))
		{
			foreach ($aShop_Discount_Siteuser_Groups as $oShop_Discount_Siteuser_Group)
			{
				$siteuserGroupName = $oShop_Discount_Siteuser_Group->siteuser_group_id
					? htmlspecialchars($oShop_Discount_Siteuser_Group->Siteuser_Group->name)
					: Core::_('Shop_Discount.all');

				$oCore_Html_Entity_Div->add(
					Core_Html_Entity::factory('Span')
						->class('badge badge-square badge-hostcms')
						->value('<i class="fa fa-users darkgray"></i> ' . $siteuserGroupName)
					);
				
				// Если "Все", то прерываем формирование списка
				if (!$oShop_Discount_Siteuser_Group->siteuser_group_id)
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
	 * Get options for select
	 * @return array
	 */
	public function getOptions()
	{
		$aReturn = array(" … ");

		$name = $this->name;
		$attr = array();

		$bRightTime = ($this->start_datetime == '0000-00-00 00:00:00' || time() > Core_Date::sql2timestamp($this->start_datetime))
			&& ($this->end_datetime == '0000-00-00 00:00:00' || time() < Core_Date::sql2timestamp($this->end_datetime));

		if (!$this->active || !$bRightTime)
		{
			$attr = array('class' => 'gray');
		}

		if ($this->coupon)
		{
			$name .= ' [' . htmlspecialchars($this->coupon_text) . ']';
			$attr = array('class' => 'sky');
		}

		$aReturn = array(
			'value' => htmlspecialchars($name),
			'attr' => $attr
		);

		return $aReturn;
	}

	/**
	 * Backend badge
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function valueBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		if ($this->value > 80 && $this->type == 0)
		{
			$return = '<i class="fa fa-exclamation-triangle warning" title="More than 80%"></i> ';
		}
		elseif($this->value == 0)
		{
			$return = '<i class="fa fa-exclamation-triangle warning" title="Zero Discount"></i> ';
		}
		else
		{
			$return = '';
		}

		return $return . ($this->type == 0
			? Core_Str::hideZeros($this->value) . '%'
			: htmlspecialchars($this->Shop->Shop_Currency->formatWithCurrency($this->value))
		);
	}

	/**
	 * Backend badge
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function shop_itemsBadge($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		$count = $this->Shop_Item_Discounts->getCount(FALSE);
		$count && Core_Html_Entity::factory('Span')
			->class('badge badge-ico badge-darkorange white')
			->value($count < 100 ? $count : '∞')
			->title($count)
			->execute();
	}

	/**
	 * Backend callback method
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function siteuser_idBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		$sResult = '';

		if (Core::moduleIsActive('siteuser') && $this->siteuser_id)
		{
			$oSiteuser = $this->Siteuser;

			$aSiteuserCompanies = $oSiteuser->Siteuser_Companies->findAll();
			$aSiteuserPersons = $oSiteuser->Siteuser_People->findAll();

			if (count($aSiteuserCompanies) || count($aSiteuserPersons))
			{
				$sResult .= '<div class="profile-container tickets-container"><ul class="tickets-list">';

				foreach ($aSiteuserCompanies as $oSiteuserCompany)
				{
					$oSiteuserCompany->id
						&& $sResult .= $oSiteuserCompany->getProfileBlock();
				}

				foreach ($aSiteuserPersons as $oSiteuserPerson)
				{
					$oSiteuserPerson->id
						&& $sResult .= $oSiteuserPerson->getProfileBlock();
				}

				$sResult .= '</ul></div>';
			}
		}

		return $sResult;
	}

	/**
	 * Get Related Site
	 * @return Site_Model|NULL
	 * @hostcms-event shop_discount.onBeforeGetRelatedSite
	 * @hostcms-event shop_discount.onAfterGetRelatedSite
	 */
	public function getRelatedSite()
	{
		Core_Event::notify($this->_modelName . '.onBeforeGetRelatedSite', $this);

		$oSite = $this->Shop->Site;

		Core_Event::notify($this->_modelName . '.onAfterGetRelatedSite', $this, array($oSite));

		return $oSite;
	}
}