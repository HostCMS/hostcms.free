<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Discountcard_Model
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2019 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
 class Shop_Discountcard_Model extends Core_Entity
{
	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'shop' => array(),
		'siteuser' => array(),
		'shop_discountcard_level' => array(),
		'user' => array()
	);

	/**
	 * Forbidden tags. If list of tags is empty, all tags will be shown.
	 * @var array
	 */
	protected $_forbiddenTags = array(
		'datetime',
		'deleted',
		'user_id',
	);

	/**
	 * Column consist item's name
	 * @var string
	 */
	protected $_nameColumn = 'number';

	/**
	 * Order's discount
	 * Расчитанный размер скидки по карте
	 * @var float
	 */
	protected $_discountAmount = NULL;

	/**
	 * Set discount amount
	 * @param float $discountAmount amount
	 * @return self
	 */
	public function discountAmount($discountAmount)
	{
		$this->_discountAmount = $discountAmount;
		return $this;
	}

	/**
	 * Get discount amount
	 * @return float
	 */
	public function getDiscountAmount()
	{
		return $this->_discountAmount;
	}

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
	 * Generate discount card number.
	 * @return string
	 */
	public function generate()
	{
		$oCore_Templater = new Core_Templater();
		return $oCore_Templater
			->addObject('this', $this)
			->addObject('shop', $this->Shop)
			->addObject('siteuser', $this->Siteuser)
			->setTemplate($this->Shop->discountcard_template)
			->execute();
	}

	public function setSiteuserAmount()
	{
		$fSum = 0;

		$oShop = $this->Shop;
		$oSiteuser = $this->Siteuser;

		$oShop_Orders = $oShop->Shop_Orders;
		$oShop_Orders->queryBuilder()
			->where('shop_orders.siteuser_id', '=', $oSiteuser->id)
			->where('shop_orders.paid', '=', 1);

		$aShop_Orders = $oShop_Orders->findAll(FALSE);

		foreach ($aShop_Orders as $oShop_Order)
		{
			// Определяем коэффициент пересчета
			$fCurrencyCoefficient = $oShop_Order->Shop_Currency->id > 0 && $oShop->Shop_Currency->id > 0
				? Shop_Controller::instance()->getCurrencyCoefficientInShopCurrency(
					$oShop_Order->Shop_Currency, $oShop->Shop_Currency
				)
				: 0;

			$fSum += $oShop_Order->getAmount() * $fCurrencyCoefficient;
		}

		$this->amount = $fSum;

		return $this;
	}

	/**
	 * Backend callback method
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function dataSiteuserBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		return $this->Siteuser->counterpartyBackend($oAdmin_Form_Field, $oAdmin_Form_Controller);
	}

	/**
	 * Backend callback method
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function shop_discountcard_level_idBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		return $this->shop_discountcard_level_id
			? '<i class="fa fa-circle" style="margin-right: 5px; color: '
				. ($this->Shop_Discountcard_Level->color
					? htmlspecialchars($this->Shop_Discountcard_Level->color)
					: '#eee'
				) . '"></i> ' . htmlspecialchars($this->Shop_Discountcard_Level->name)
			: '—';
	}
	
	/**
	 * Backend callback method
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function dataLoginBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		$isOnline = $this->Siteuser->isOnline();

		$sStatus = $isOnline ? 'online' : 'offline';

		$lng = $isOnline ? 'siteuser_active' : 'siteuser_last_activity';

		$sStatusTitle = !is_null($this->Siteuser->last_activity)
			? Core::_('Siteuser.' . $lng, Core_Date::sql2datetime($this->Siteuser->last_activity))
			: '';

		return htmlspecialchars($this->dataLogin)
			. '&nbsp;<span title="' . htmlspecialchars($sStatusTitle) . '" class="' . htmlspecialchars($sStatus) . '"></span>';
	}	

	/**
	 * Backend callback method
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function numberBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		return '<b>' . htmlspecialchars($this->number) . '</b><br /><span class="label label-info">Σ ' . htmlspecialchars($this->amount . ' ' . $this->Shop->Shop_Currency->name);
	}

	public function checkLevel()
	{
		$oShop = $this->Shop;

		$aShop_Discountcard_Levels = $oShop->Shop_Discountcard_Levels->findAll();

		$shop_discountcard_level_id = 0;

		foreach($aShop_Discountcard_Levels as $oShop_Discountcard_Level)
		{
			if ($this->amount >= $oShop_Discountcard_Level->amount)
			{
				$shop_discountcard_level_id = $oShop_Discountcard_Level->id;
			}
			else
			{
				break;
			}
		}

		$this->shop_discountcard_level_id = $shop_discountcard_level_id;
		$this->save();

		return $this;
	}

	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return self
	 * @hostcms-event shop_discountcard.onBeforeRedeclaredDelete
	 */
	public function delete($primaryKey = NULL)
	{
		if (is_null($primaryKey))
		{
			$primaryKey = $this->getPrimaryKey();
		}

		$this->id = $primaryKey;

		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredDelete', $this, array($primaryKey));

		return parent::delete($primaryKey);
	}

	/**
	 * Get XML for entity and children entities
	 * @return string
	 * @hostcms-event shop_discountcard.onBeforeRedeclaredGetXml
	 */
	public function getXml()
	{
		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredGetXml', $this);

		$oShop = $this->Shop;

		!isset($this->_forbiddenTags['date'])
			&& $this->addXmlTag('date', strftime($oShop->format_date, Core_Date::sql2timestamp($this->datetime)));

		$this->addXmlTag('datetime', strftime($oShop->format_datetime, Core_Date::sql2timestamp($this->datetime)));

		if ($this->shop_discountcard_level_id)
		{
			$this->addEntity(
				$this->Shop_Discountcard_Level->clearEntities()
			);
		}

		$this->addXmlTag('discount_amount', $this->_discountAmount);

		return parent::getXml();
	}
}