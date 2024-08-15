<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Warrant_Model
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Shop_Warrant_Model extends Core_Entity
{
	public $siteuserCompanyContract = NULL;

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'shop' => array(),
		'user' => array(),
		'siteuser' => array(),
		'company' => array(),
		'chartaccount_cashflow' => array(),
		'company_cashbox' => array(),
		'company_account' => array(),
		'chartaccount' => array(),
	);

	/**
	 * Counterparty
	 * @var mixed
	 */
	// public $counterparty = NULL;

	/**
	 * TYPE
	 * @var int
	 */
	const TYPE = 30;

	/**
	 * Get Entity Type
	 * @return int
	 */
	public function getEntityType()
	{
		return self::TYPE + $this->type;
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
			$oUser = Core_Auth::getCurrentUser();
			$this->_preloadValues['user_id'] = is_null($oUser) ? 0 : $oUser->id;
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
		$oUser = $this->User;
		return $oUser->id
			? $oUser->showAvatarWithName()
			: '';
	}

	/**
	 * Backend callback method
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function counterpartyBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		if (Core::moduleIsActive('chartaccount'))
		{
			$oSiteuser_Company = Chartaccount_Controller::getEntity($this, 2);

			if (Core::moduleIsActive('siteuser') && !is_null($oSiteuser_Company))
			{
				return '<div class="profile-container tickets-container counterparty-block"><ul class="tickets-list">' . $oSiteuser_Company->getProfileBlock('') . '</ul></div>';
			}
		}

		return '';
	}

	/**
	 * Backend callback method
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function typeBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		switch ($this->type)
		{
			case 0:
			default:
				$color = '#ed4e2a';
				$icon = 'fa-regular fa-circle-up';
			break;
			case 1:
				$color = '#2dc3e8';
				$icon = 'fa-regular fa-circle-down';
			break;
			case 2:
				$color = '#2dc3e8';
				$icon = 'fa-solid fa-angles-down';
			break;
			case 3:
				$color = '#ed4e2a';
				$icon = 'fa-solid fa-angles-up';
			break;
		}

		return '<i class="' . $icon . '" title="' . Core::_('Shop_Warrant.type' . $this->type) . '" style="color: ' . $color . '"></i>';
	}

	/**
	 * Backend callback method
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function numberBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		return '<span class="semi-bold">' . htmlspecialchars($this->number) . '</span>';
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
	public function amountBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		return $this->Shop->shop_currency_id
			? $this->Shop->Shop_Currency->formatWithCurrency($this->amount)
			: '';
	}

	/**
	 * Backend callback method
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function amountBadge($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		$related_amount = $this->getShopDocumentRelatedAmount();

		if ($related_amount && $this->amount != $related_amount)
		{
			Core_Html_Entity::factory('I')
				->class('fa fa-exclamation-triangle darkorange')
				->title(Core::_('Shop_Warrant.wrong_amount'))
				->execute();
		}
	}

	public function getShopDocumentRelatedAmount()
	{
		$document_id = Shop_Controller::getDocumentId($this->id, $this->getEntityType());

		$amount = 0;

		$aShop_Document_Relations = Core_Entity::factory('Shop_Document_Relation')->getAllByDocument_id($document_id, FALSE);
		foreach ($aShop_Document_Relations as $oShop_Document_Relation)
		{
			/*$oObject = $oShop_Document_Relation->getObject();

			if (!is_null($oObject) && method_exists($oObject, 'getAmount'))
			{
				$amount += $oObject->getAmount();
			}*/

			$amount += $oShop_Document_Relation->paid;
		}

		return $amount;
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function siteuserCompanyContractBackend()
	{
		if (Core::moduleIsActive('chartaccount'))
		{
			$oSiteuser_Company_Contract = Chartaccount_Controller::getEntity($this, 7);
			return !is_null($oSiteuser_Company_Contract)
				? $oSiteuser_Company_Contract->name
				: '';
		}

		return '';
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function cashbox_or_accountBackend()
	{
		$return = '';

		if ($this->company_cashbox_id)
		{
			$return = htmlspecialchars((string) $this->Company_Cashbox->name);
		}
		elseif ($this->company_account_id)
		{
			$return = htmlspecialchars((string) $this->Company_Account->name);
		}

		return $return;
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

		$document_id = Shop_Controller::getDocumentId($this->id, $this->getEntityType());

		Core::moduleIsActive('chartaccount')
			&& Chartaccount_Entry_Controller::deleteEntriesByDocumentId($document_id);

		$aShop_Document_Relations = Core_Entity::factory('Shop_Document_Relation')->getAllByDocument_id($document_id);
		foreach ($aShop_Document_Relations as $oShop_Document_Relation)
		{
			$oShop_Document_Relation->delete();
		}

		return parent::delete($primaryKey);
	}

	/**
	 * Add entries
	 * @return self
	 */
	public function post()
	{
		if (/*!$this->posted && */$this->company_id)
		{
			$document_id = Shop_Controller::getDocumentId($this->id, $this->getEntityType());

			$aEntry = array();

			switch ($this->type)
			{
				case 0: // Расходный ордер
					$aEntry['debit'] = $this->Chartaccount->code;
					$aEntry['debit_sc'] = array();

					for ($i = 0; $i < 3; $i++)
					{
						$fieldName = 'sc' . $i;
						$aEntry['debit_sc'][$this->Chartaccount->$fieldName] = $this->$fieldName;
					}

					$aEntry['credit'] = '50.1'; // Касса организации
					$aEntry['credit_sc'] = array(29 => $this->company_cashbox_id, 11 => $this->chartaccount_cashflow_id);
				break;
				case 1: // Приходный ордер
					$aEntry['debit'] = '50.1'; // Касса организации
					$aEntry['debit_sc'] = array(29 => $this->company_cashbox_id, 11 => $this->chartaccount_cashflow_id);

					$aEntry['credit'] = $this->Chartaccount->code;
					$aEntry['credit_sc'] = array();

					for ($i = 0; $i < 3; $i++)
					{
						$fieldName = 'sc' . $i;
						$aEntry['credit_sc'][$this->Chartaccount->$fieldName] = $this->$fieldName;
					}
				break;
				case 2: // Входящий платеж
					$aEntry['debit'] = '51'; // Расчетные счета
					$aEntry['debit_sc'] = array(12 => $this->company_account_id, 11 => $this->chartaccount_cashflow_id);

					$aEntry['credit'] = $this->Chartaccount->code;
					$aEntry['credit_sc'] = array();

					for ($i = 0; $i < 3; $i++)
					{
						$fieldName = 'sc' . $i;
						$aEntry['credit_sc'][$this->Chartaccount->$fieldName] = $this->$fieldName;
					}
				break;
				case 3: // Исходящий платеж
					$aEntry['debit'] = $this->Chartaccount->code;
					$aEntry['debit_sc'] = array();

					for ($i = 0; $i < 3; $i++)
					{
						$fieldName = 'sc' . $i;
						$aEntry['debit_sc'][$this->Chartaccount->$fieldName] = $this->$fieldName;
					}

					$aEntry['credit'] = '51'; // Расчетные счета
					$aEntry['credit_sc'] = array(12 => $this->company_account_id, 11 => $this->chartaccount_cashflow_id);
				break;
			}

			$aEntry['amount'] = $this->amount;
			$aEntry['datetime'] = $this->datetime;

			Chartaccount_Entry_Controller::insertEntries($document_id, $this->company_id, array($aEntry));

			$this->posted = 1;
			$this->save();
		}
		else
		{
			$this->unpost();
		}

		return $this;
	}

	/**
	 * Remove all  entries by document
	 * @return self
	 */
	public function unpost()
	{
		if ($this->posted)
		{
			$document_id = Shop_Controller::getDocumentId($this->id, $this->getEntityType());

			Chartaccount_Entry_Controller::deleteEntriesByDocumentId($document_id);

			$this->posted = 0;
			$this->save();
		}

		return $this;
	}

	/**
	 * Get document full name
	 * @return string
	 */
	public function getDocumentFullName($oAdmin_Form_Controller)
	{
		$color = Core_Str::createColor($this->getEntityType());

		$href = $oAdmin_Form_Controller->getAdminActionLoadHref(array('path' => '/admin/shop/warrant/index.php', 'action' => 'edit', 'operation' => 'modal', 'additionalParams' => "type={$this->type}", 'datasetKey' => 0, 'datasetValue' => $this->id));

		$onclick = $oAdmin_Form_Controller->getAdminActionModalLoad(array('path' => '/admin/shop/warrant/index.php', 'action' => 'edit', 'operation' => 'modal', 'additionalParams' => "type={$this->type}", 'datasetKey' => 0, 'datasetValue' => $this->id, 'window' => '', 'width' => '90%'));

		ob_start();

		?><span class="badge badge-round badge-max-width" style="border-color: <?php echo $color?>; background-color: <?php echo Core_Str::hex2lighter($color, 0.88)?>;"><a style="color: <?php echo Core_Str::hex2darker($color, 0.2)?>" href="<?php echo $href?>" onclick="<?php echo $onclick?>"><?php echo Core::_('Shop_Warrant.type' . $this->type)?> № <?php echo htmlspecialchars($this->number)?></a></span><?php

		return ob_get_clean();
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function printBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		Core::moduleIsActive('printlayout')
			&& Printlayout_Controller::getBackendPrintButton($oAdmin_Form_Controller, $this->id, $this->getEntityType());
	}

	/**
	 * Get printlayout replaces
	 * @return array
	 * @hostcms-event shop_warrant.onAfterGetPrintlayoutReplaces
	 */
	public function getPrintlayoutReplaces()
	{
		$oCompany = $this->company_id
			? $this->Company
			: $this->Shop->Shop_Company;

		$oCompany_Cashbox = $this->company_cashbox_id
			? $this->Company_Cashbox
			: Core_Entity::factory('Company_Cashbox')->getDefault();

		$oCompany_Account = $this->company_account_id
			? $this->Company_Account
			: Core_Entity::factory('Company_Account')->getDefault();

		$aReplace = array(
			// Core_Meta
			'this' => $this,
			'company' => !is_null($oCompany) ? $oCompany : new Core_Meta_Empty(),
			'shop' => $this->Shop,
			'company_cashbox' => !is_null($oCompany_Cashbox) ? $oCompany_Cashbox : new Core_Meta_Empty(),
			'company_account' => !is_null($oCompany_Account) ? $oCompany_Account : new Core_Meta_Empty(),
			'user' => $this->User,
			'date' => Core_Date::sql2date($this->datetime),
			'tax' => Shop_Controller::instance()->round($this->tax),
			'amount' => Shop_Controller::instance()->round($this->amount),
			'amount_with_hyphen' => str_replace('.', '-', Shop_Controller::instance()->round($this->amount)),
			'code' => Core::moduleIsActive('chartaccount') && $this->chartaccount_id ? htmlspecialchars((string) $this->Chartaccount->code) : ''
		);

		$oChartaccount_Cashflow = $this->chartaccount_cashflow_id
			? $this->Chartaccount_Cashflow
			: new Core_Meta_Empty();

		$aReplace['shop_cashflow'] = !is_null($oChartaccount_Cashflow)
			? $oChartaccount_Cashflow
			: new Core_Meta_Empty();

		$oSiteuser_Company = Core::moduleIsActive('chartaccount') ? Chartaccount_Controller::getEntity($this, 2) : NULL;
		$aReplace['siteuser_company'] = Core::moduleIsActive('siteuser') && !is_null($oSiteuser_Company)
			? $oSiteuser_Company
			: new Core_Meta_Empty();

		$aReplace['amount_in_words'] = '';

		if ($this->Shop->shop_currency_id)
		{
			$lng = $this->Shop->Site->lng;

			$aReplace['amount_in_words'] = Core_Inflection::available($lng)
				? Core_Str::ucfirst(Core_Inflection::instance($lng)->currencyInWords($aReplace['amount'], $this->Shop->Shop_Currency->code))
				: $aReplace['amount'] . ' ' . $this->Shop_Currency->sign;
		}

		$amount_integer = floor($this->amount);
		$amount_fractional = floor((ceil(($this->amount - $amount_integer) * 100) / 100) * 100);

		$aReplace['amount_integer'] = $amount_integer;
		$aReplace['amount_fractional'] = $amount_fractional;

		$aReplace['year'] = date('Y');
		$aReplace['month'] = date('m');
		$aReplace['day'] = date('d');

		Core_Event::notify($this->_modelName . '.onAfterGetPrintlayoutReplaces', $this, array($aReplace));
		$eventResult = Core_Event::getLastReturn();

		return !is_null($eventResult)
			? $eventResult
			: $aReplace;
	}

	/**
	 * Get Related Site
	 * @return Site_Model|NULL
	 * @hostcms-event shop_warrant.onBeforeGetRelatedSite
	 * @hostcms-event shop_warrant.onAfterGetRelatedSite
	 */
	public function getRelatedSite()
	{
		Core_Event::notify($this->_modelName . '.onBeforeGetRelatedSite', $this);

		$oSite = $this->Shop->Site;

		Core_Event::notify($this->_modelName . '.onAfterGetRelatedSite', $this, array($oSite));

		return $oSite;
	}
}