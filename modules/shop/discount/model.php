<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Discount_Model
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
 class Shop_Discount_Model extends Core_Entity
{
	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		'shop_item_discount' => array()
	);

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'shop' => array(),
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
	 * Backend property
	 * @var int
	 */
	public $img = 0;

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
	 * Check if discount active is
	 * @return boolean
	 */
	public function isActive()
	{
		$time = time();
		$dayFieldName = 'day' . date('N');

		return $this->active
			&& Core_Date::sql2timestamp($this->start_datetime) <= $time
			&& Core_Date::sql2timestamp($this->end_datetime) >= $time
			&& $time >= strtotime($this->start_time)
			&& $time <= strtotime($this->end_time)
			&& $this->$dayFieldName == 1;
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
			: strftime($oShop->format_datetime, Core_Date::sql2timestamp($this->start_datetime)));

		$this->addXmlTag('end_datetime', $this->end_datetime == '0000-00-00 00:00:00'
			? $this->end_datetime
			: strftime($oShop->format_datetime, Core_Date::sql2timestamp($this->end_datetime)));

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
		$oCore_Html_Entity_Div = Core::factory('Core_Html_Entity_Div')->value(
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
					Core::factory('Core_Html_Entity_I')->class('fa fa-clock-o black')
				);
		}

		if ($this->coupon && strlen($this->coupon_text))
		{
			$oCore_Html_Entity_Div->add(
				Core::factory('Core_Html_Entity_Span')
					->class('label label-sky label-sm')
					->value(htmlspecialchars($this->coupon_text))
			);
		}

		if ($this->start_time != '00:00:00' || $this->end_time != '23:59:59')
		{
			$oCore_Html_Entity_Div->add(
				Core::factory('Core_Html_Entity_Span')
					->class('label label-orange label-sm')
					->value($this->start_time . ' – ' . $this->end_time)
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
				Core::factory('Core_Html_Entity_Span')
					->class('label label-palegreen label-sm')
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
						Core::factory('Core_Html_Entity_Span')
							->class('label label-palegreen label-sm')
							->value(Core::_('Shop_Discount.' . $fieldName))
						);
				}
			}
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
	public function valueBadge($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		echo $this->type == 0
			? '%'
			: ' ' . htmlspecialchars($this->Shop->Shop_Currency->name);
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