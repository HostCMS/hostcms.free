<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Bonus_Model
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
 class Shop_Bonus_Model extends Core_Entity
{
	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		'shop_item_bonus' => array()
	);

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'shop' => array(),
		'shop_bonus_dir' => array(),
		'shop_item' => array(),
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
	 * Backend property
	 * @var int
	 */
	public $img = 1;

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
			$this->_preloadValues['expire_days'] = 365;
		}
	}

	/**
	 * Check if bonus active is
	 * @return boolean
	 */
	public function isActive()
	{
		return $this->active
			&& Core_Date::sql2timestamp($this->start_datetime) <= time()
			&& Core_Date::sql2timestamp($this->end_datetime) >= time();
	}

	/**
	 * Change bonus status
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
	 * @return Core_Entity
     * @hostcms-event shop_bonus.onBeforeRedeclaredDelete
	 */
	public function delete($primaryKey = NULL)
	{
		if (is_null($primaryKey))
		{
			$primaryKey = $this->getPrimaryKey();
		}

		$this->id = $primaryKey;

		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredDelete', $this, array($primaryKey));

		$this->Shop_Item_Bonuses->deleteAll(FALSE);

		return parent::delete($primaryKey);
	}

	/**
	 * Get XML for entity and children entities
	 * @return string
	 * @hostcms-event shop_bonus.onBeforeRedeclaredGetXml
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
	 * @hostcms-event shop_bonus.onBeforeRedeclaredGetStdObject
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
		$this->clearXmlTags();
		$this->type == 0
			? $this->addXmlTag('percent', $this->value)
			: $this->addXmlTag('amount', $this->value);

		return $this;
	}

	/**
	 * Backend badge
	 */
	public function valueBadge()
	{
		echo $this->type == 0
			? '%'
			: ' ' . htmlspecialchars($this->Shop->Shop_Currency->sign);
	}

	/**
	 * Backend badge
	 */
	public function nameBadge()
	{
		if ($this->accrual_date != '0000-00-00 00:00:00')
		{
			$class = 'badge-sky';
			$text = Core::_('Shop_Bonus.badge_from', Core_Date::sql2datetime($this->accrual_date), $this->expire_days);
		}
		else
		{
			$class = 'badge-pink';
			$text = Core::_('Shop_Bonus.badge_through', $this->accrual_days, $this->expire_days);
		}

		?><span class="margin-left-5 badge badge-square <?php echo $class?> badge-sm pull-right"><?php echo $text?></span><?php
	}

	/**
	 * Backend callback method
	 */
	public function min_amountBackend()
	{
		echo $this->min_amount > 0
			? $this->min_amount
			: '—';
	}

	/**
	 * Move bonus to another dir
	 * @param int $iShopBonusDirId target dir id
	 * @return Core_Entity
	 * @hostcms-event shop_bonus.onBeforeMove
	 * @hostcms-event shop_bonus.onAfterMove
	 */
	public function move($iShopBonusDirId)
	{
		Core_Event::notify($this->_modelName . '.onBeforeMove', $this, array($iShopBonusDirId));

		$this->shop_bonus_dir_id = $iShopBonusDirId;
		$this->save();

		Core_Event::notify($this->_modelName . '.onAfterMove', $this);

		return $this;
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

		$aReturn = array(
			'value' => htmlspecialchars($name),
			'attr' => $attr
		);

		return $aReturn;
	}

	/**
	 * Get Related Site
	 * @return Site_Model|NULL
	 * @hostcms-event shop_bonus.onBeforeGetRelatedSite
	 * @hostcms-event shop_bonus.onAfterGetRelatedSite
	 */
	public function getRelatedSite()
	{
		Core_Event::notify($this->_modelName . '.onBeforeGetRelatedSite', $this);

		$oSite = $this->Shop->Site;

		Core_Event::notify($this->_modelName . '.onAfterGetRelatedSite', $this, array($oSite));

		return $oSite;
	}
}