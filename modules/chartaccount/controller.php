<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Chartaccount_Controller.
 *
 * @package HostCMS
 * @subpackage Chartaccount
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Chartaccount_Controller
{
	static protected $_Admin_Form_Controller = NULL;

	/**
	 * Set admin form controller
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 */
	static public function setAdminFormController($oAdmin_Form_Controller)
	{
		self::$_Admin_Form_Controller = $oAdmin_Form_Controller;
	}

	/**
	 * Get subcounts list
	 * @return array
	 */
	static public function getSubcountList()
	{
		return array(
			0 => ' ... ',
			1 => Core::_('Chartaccount.subcount1'),
			2 => Core::_('Chartaccount.subcount2'),
			3 => Core::_('Chartaccount.subcount3'),
			4 => Core::_('Chartaccount.subcount4'),
			5 => Core::_('Chartaccount.subcount5'),
			6 => Core::_('Chartaccount.subcount6'),
			7 => Core::_('Chartaccount.subcount7'),
			8 => Core::_('Chartaccount.subcount8'),
			9 => Core::_('Chartaccount.subcount9'),
			10 => Core::_('Chartaccount.subcount10'),
			11 => Core::_('Chartaccount.subcount11'),
			12 => Core::_('Chartaccount.subcount12'),
			13 => Core::_('Chartaccount.subcount13'),
			14 => Core::_('Chartaccount.subcount14'),
			15 => Core::_('Chartaccount.subcount15'),
			16 => Core::_('Chartaccount.subcount16'),
			17 => Core::_('Chartaccount.subcount17'),
			18 => Core::_('Chartaccount.subcount18'),
			19 => Core::_('Chartaccount.subcount19'),
			20 => Core::_('Chartaccount.subcount20'),
			21 => Core::_('Chartaccount.subcount21'),
			22 => Core::_('Chartaccount.subcount22'),
			23 => Core::_('Chartaccount.subcount23'),
			24 => Core::_('Chartaccount.subcount24'),
			25 => Core::_('Chartaccount.subcount25'),
			26 => Core::_('Chartaccount.subcount26'),
			27 => Core::_('Chartaccount.subcount27'),
			28 => Core::_('Chartaccount.subcount28'),
			29 => Core::_('Chartaccount.subcount29'),
		);
	}

	/**
	 * Get code
	 * @param Chartaccount_Model $oChartaccount
	 * @return string
	 */
	static public function getCode(Chartaccount_Model $oChartaccount)
	{
		return '<div><span class="semi-bold">' . htmlspecialchars((string) $oChartaccount->code). '</span> <span class="small darkgray">' . htmlspecialchars((string) $oChartaccount->name) . '</span></div>';
	}

	/**
	 * Get correct entries
	 * @param int $chartaccount_id
	 * @return array
	 */
	static protected function _getCorrectEntries($chartaccount_id)
	{
		$oChartaccount_Correct_Entries = Core_Entity::factory('Chartaccount_Correct_Entry');
		$oChartaccount_Correct_Entries->queryBuilder()
			->open()
				->where('chartaccount_correct_entries.debit', '=', $chartaccount_id)
				->setOr()
				->where('chartaccount_correct_entries.credit', '=', $chartaccount_id)
			->close();

		return $oChartaccount_Correct_Entries->findAll(FALSE);
	}

	/**
	 * Get options
	 * @param array $aParams
	 * @param boolean $ajax
	 * @return array
	 */
	static public function getOptions(array $aParams = array(), $ajax = FALSE)
	{
		$aOptions = array('...');

		if (Core::moduleIsActive('chartaccount'))
		{
			$aChartaccounts = Core_Entity::factory('Chartaccount')->findAll(FALSE);

			if (count($aParams))
			{
				if (isset($aParams['dchartaccount_id']) && $aParams['dchartaccount_id'])
				{
					$aChartaccount_Correct_Entries = self::_getCorrectEntries($aParams['dchartaccount_id']);

					if (count($aChartaccount_Correct_Entries))
					{
						$aChartaccounts = array();

						foreach ($aChartaccount_Correct_Entries as $oChartaccount_Correct_Entry)
						{
							$aChartaccounts[] = $oChartaccount_Correct_Entry->Credit_Chartaccount;
						}
					}
				}

				if (isset($aParams['cchartaccount_id']) && $aParams['cchartaccount_id'])
				{
					$aChartaccount_Correct_Entries = self::_getCorrectEntries($aParams['cchartaccount_id']);

					if (count($aChartaccount_Correct_Entries))
					{
						$aChartaccounts = array();

						foreach ($aChartaccount_Correct_Entries as $oChartaccount_Correct_Entry)
						{
							$aChartaccounts[] = $oChartaccount_Correct_Entry->Debit_Chartaccount;
						}
					}
				}
			}

			foreach ($aChartaccounts as $oChartaccount)
			{
				$name = $oChartaccount->code . ' ' . $oChartaccount->name;

				if ($oChartaccount->folder)
				{
					if ($ajax)
					{
						$aOptions[] = array(
							'value' => $oChartaccount->id,
							'name' => $name,
							'disabled' => 'disabled'
						);
					}
					else
					{
						$aOptions[$oChartaccount->id] = array(
							'value' => $name,
							'attr' => array('disabled' => 'disabled')
						);
					}
				}
				else
				{
					$aOptions[$oChartaccount->id] = $name;
				}
			}
		}

		return $aOptions;
	}

	/**
	 * Get entity
	 * @param object $oObject
	 * @param int $type
	 * @return object
	 */
	static public function getEntity($oObject, $type)
	{
		$oReturn = NULL;

		if (Core::moduleIsActive('chartaccount') && isset($oObject->chartaccount_id) && $oObject->chartaccount_id)
		{
			$oChartaccount = $oObject->Chartaccount;

			if (!is_null($oChartaccount))
			{
				for ($i = 0; $i < 3; $i++)
				{
					$subcountName = 'sc' . $i;

					switch ($oChartaccount->$subcountName)
					{
						case 2:
							$oReturn = Core_Entity::factory('Siteuser_Company')->getById($oObject->$subcountName);
						break;
						case 7:
							$oReturn = Core_Entity::factory('Siteuser_Company_Contract')->getById($oObject->$subcountName);
						break;
					}

					if ($oChartaccount->$subcountName == $type)
					{
						break;
					}
				}
			}
		}

		return $oReturn;
	}

	/**
	 * Show subcounts
	 * @param array $aValues
	 * @param int $chartaccount_id
	 * @param object $oParentObject
	 * @param object $oAdmin_Form_Controller
	 * @param array $aParams
	 * @param string $prefix
	 * @return void
	 */
	static public function showSubcounts($aValues, $chartaccount_id, $oParentObject, $oAdmin_Form_Controller, array $aParams = array(), $prefix = '')
	{
		$chartaccount_id = intval($chartaccount_id);
// print_r($aValues);
		$windowId = $oAdmin_Form_Controller->getWindowId();

		$aTmp = array();

		$aSubcountList = self::getSubcountList();

		$oChartaccount = Core_Entity::factory('Chartaccount')->getById($chartaccount_id);
		if (!is_null($oChartaccount))
		{
			$oChartaccount->sc0 && isset($aSubcountList[$oChartaccount->sc0])
				&& $aTmp['sc0'] = $oChartaccount->sc0;

			$oChartaccount->sc1 && isset($aSubcountList[$oChartaccount->sc1])
				&& $aTmp['sc1'] = $oChartaccount->sc1;

			$oChartaccount->sc2 && isset($aSubcountList[$oChartaccount->sc2])
				&& $aTmp['sc2'] = $oChartaccount->sc2;

			foreach ($aTmp as $sc => $type)
			{
				$oParentObject->add(
					$oMainRow2 = Admin_Form_Entity::factory('Div')->class('row')
				);

				$value = Core_Array::get($aValues, $type);

				$sc = $prefix . $sc;

				switch ($type)
				{
					case 2: // Контрагенты
						if (Core::moduleIsActive('siteuser'))
						{
							// company_XXX => XXX
							!is_null($value) && strpos($value, '_') !== FALSE
								&& list(, $value) = explode('_', $value);
							// var_dump($value);

							$aMasSiteusers = array();

							$oSiteuserCompany = Core_Entity::factory('Siteuser_Company')->getById($value);
							$oSiteuser = !is_null($oSiteuserCompany)
								? $oSiteuserCompany->Siteuser
								: NULL;

							if ($oSiteuser)
							{
								$oOptgroupSiteuser = new stdClass();
								$oOptgroupSiteuser->attributes = array('label' => $oSiteuser->login, 'class' => 'siteuser');

								if ($oSiteuserCompany)
								{
									$tin = !empty($oSiteuserCompany->tin)
										? ' ➤ ' . $oSiteuserCompany->tin
										: '';

									$oOptgroupSiteuser->children['company_' . $oSiteuserCompany->id] = array(
										'value' => $oSiteuserCompany->name . $tin . ' 👤 ' . $oSiteuser->login . '%%%' . $oSiteuserCompany->getAvatar(),
										'attr' => array('class' => 'siteuser-company')
									);
								}

								$aMasSiteusers[$oSiteuser->id] = $oOptgroupSiteuser;
							}

							$siteuser_company_contract_id = 0;
							$siteuser_company_contract_name = '';

							// Договоры, тип 7
							if (in_array(7, $aTmp))
							{
								$key = array_search(7, $aTmp);

								$siteuser_company_contract_id = isset($aValues[7]) ? $aValues[7] : 0;
								// $siteuser_company_contract_name = $key;
								$siteuser_company_contract_name = $prefix . $key;
							}

							$onchange = "$.fillSiteuserCompanyContract('{$windowId}', " . intval($siteuser_company_contract_id) . ", '{$sc}', '{$siteuser_company_contract_name}');";

							$oSelectSiteusers = Admin_Form_Entity::factory('Select')
								->id($sc)
								->options($aMasSiteusers)
								->name($sc)
								->value('company_' . $value)
								->data('type', $type)
								->caption(Core::_('Chartaccount.subcount' . $type))
								->style("width: 100%")
								->divAttr(array('class' => 'col-xs-12'))
								->onchange($onchange);

							$oScriptSiteusers = Admin_Form_Entity::factory('Script')
								->value('
									$("#' . $windowId . ' #' . $sc . '").select2({
										dropdownParent: $("#' . $windowId . '"),
										minimumInputLength: 1,
										placeholder: "",
										allowClear: true,
										// multiple: true,
										ajax: {
											url: hostcmsBackend + "/siteuser/index.php?loadSiteusers&types[]=company",
											dataType: "json",
											type: "GET",
											processResults: function (data) {
												var aResults = [];
												$.each(data, function (index, item) {
													aResults.push(item);
												});
												return {
													results: aResults
												};
											}
										},
										templateResult: $.templateResultItemSiteusers,
										escapeMarkup: function(m) { return m; },
										templateSelection: $.templateSelectionItemSiteusers,
										language: "' . Core_I18n::instance()->getLng() . '",
										width: "100%"
									})
									.on("select2:opening select2:closing", function(e){

										var $searchfield = $(this).parent().find(".select2-search__field");

										if (!$searchfield.data("setKeydownHeader"))
										{
											$searchfield.data("setKeydownHeader", true);

											$searchfield.on("keydown", function(e) {

												var $this = $(this);

												if ($this.val() == "" && e.key == "Backspace")
												{
													$this
														.parents("ul.select2-selection__rendered")
														.find("li.select2-selection__choice")
														.filter(":last")
														.find(".select2-selection__choice__remove")
														.trigger("click");

													e.stopImmediatePropagation();
													e.preventDefault();
												}
											});
										}
									})
									.val("company_' . $value . '")
									.trigger("change.select2");
								');

							$oMainRow2
								->add(
									Admin_Form_Entity::factory('Div')
										->class('form-group col-xs-12 col-sm-6 no-padding')
										->add($oSelectSiteusers)
										->add($oScriptSiteusers)
								);
						}
					break;
					case 7: // Договоры
						if (Core::moduleIsActive('siteuser'))
						{
							$oSelectContracts = Admin_Form_Entity::factory('Select')
								->options(array())
								->id($sc)
								->name($sc)
								->data('type', $type)
								->caption(Core::_('Chartaccount.subcount' . $type))
								->divAttr(array('class'=>'col-xs-12'));

							$oMainRow2
								->add(
									Admin_Form_Entity::factory('Div')
										->class('form-group col-xs-12 col-sm-6 no-padding')
										->add($oSelectContracts)
								);
						}
					break;
					case 11: // Движение денежных средств
						$oSelectCashflows = Admin_Form_Entity::factory('Select')
						->id($sc)
						->options(Chartaccount_Cashflow_Controller::fillCashflowList())
						->name($sc)
						->value($value)
						->data('type', $type)
						->caption(Core::_('Chartaccount.subcount' . $type))
						->divAttr(array('class'=>'col-xs-12'));

						$oMainRow2->add(
							Admin_Form_Entity::factory('Div')
								->class('form-group col-xs-12 col-sm-6 no-padding')
								->add($oSelectCashflows)
						);
					break;
					case 12: // Банковские счета
						$aOptions = array('...');

						$companyId = Core_Array::get($aParams, 'company_id', 0, 'int');
						if ($companyId)
						{
							$aCompany_Accounts = Core_Entity::factory('Company', $companyId)->Company_Accounts->findAll(FALSE);
							foreach ($aCompany_Accounts as $oCompany_Account)
							{
								$aOptions[$oCompany_Account->id] = $oCompany_Account->name;
							}
						}

						$oSelect_Company_Account = Admin_Form_Entity::factory('Select')
							->options($aOptions)
							->id($sc)
							->name($sc)
							->value($value)
							->data('type', $type)
							->caption(Core::_('Chartaccount.subcount' . $type))
							->divAttr(array('class'=>'col-xs-12'));

						$oMainRow2->add(
							Admin_Form_Entity::factory('Div')
								->class('form-group col-xs-12 col-sm-6 no-padding')
								->add($oSelect_Company_Account)
						);
					break;
					case 29: // Кассы
						$aOptions = array('...');

						$companyId = Core_Array::get($aParams, 'company_id', 0, 'int');
						if ($companyId)
						{
							$aCompany_Cashboxes = Core_Entity::factory('Company', $companyId)->Company_Cashboxes->findAll();
							foreach ($aCompany_Cashboxes as $oCompany_Cashbox)
							{
								$aOptions[$oCompany_Cashbox->id] = $oCompany_Cashbox->name;
							}
						}

						$oSelect_Company_Cashbox = Admin_Form_Entity::factory('Select')
							->options($aOptions)
							->id($sc)
							->name($sc)
							->value($value)
							->data('type', $type)
							->caption(Core::_('Chartaccount.subcount' . $type))
							->divAttr(array('class'=>'col-xs-12'));

						$oMainRow2->add(
							Admin_Form_Entity::factory('Div')
								->class('form-group col-xs-12 col-sm-6 no-padding')
								->add($oSelect_Company_Cashbox)
						);
					break;
				}
			}
		}
	}

	/**
	 * Undocumented function
	 * @param Chartaccount_Entry_Model|Chartaccount_Operation_Item_Model $oEntry
	 * @return array
	 */
	static public function getSubcounts($oEntry)
	{
		$aSubcounts = array();

		// Дебет
		$oChartaccount_Debit = $oEntry->dchartaccount_id
			? $oEntry->Chartaccount_Debit
			: NULL;

		// Кредит
		$oChartaccount_Credit = $oEntry->cchartaccount_id
			? $oEntry->Chartaccount_Credit
			: NULL;

		for ($i = 0; $i < 3; $i++)
		{
			$fieldName = 'sc' . $i;
			$dFieldName = 'd' . $fieldName;
			$cFieldName = 'c' . $fieldName;

			!is_null($oChartaccount_Debit)
				&& $aSubcounts['debit'][$fieldName] = self::_getSubcountValue($oChartaccount_Debit->$fieldName, $oEntry->$dFieldName);

			!is_null($oChartaccount_Credit)
				&& $aSubcounts['credit'][$fieldName] = self::_getSubcountValue($oChartaccount_Credit->$fieldName, $oEntry->$cFieldName);
		}

		return $aSubcounts;
	}

	static protected function _getSubcountValue($type, $value)
	{
		$oUser = Core_Auth::getCurrentUser();
		$oSite = Core_Entity::factory('Site', CURRENT_SITE);

		switch ($type)
		{
			case 2: // Контрагенты
				if (Core::moduleIsActive('siteuser'))
				{
					$oSiteuser_Company = Core_Entity::factory('Siteuser_Company')->getById($value);

					if (!is_null($oSiteuser_Company))
					{
						$onclick = $oUser->checkModuleAccess(array('siteuser'), $oSite)
							? self::$_Admin_Form_Controller->getAdminActionModalLoad(array('path' => '/{admin}/siteuser/representative/index.php', 'action' => 'edit', 'operation' => 'modal', 'additionalParams' => 'show=company', 'datasetKey' => 0, 'datasetValue' => $oSiteuser_Company->id, 'window' => '', 'width' => '90%'))
							: '';

						return array(
							'name' => $oSiteuser_Company->name,
							'href' => self::$_Admin_Form_Controller->getAdminActionLoadHref(array('path' => '/{admin}/siteuser/representative/index.php', 'action' => 'edit', 'operation' => 'modal', 'additionalParams' => 'show=company', 'datasetKey' => 0, 'datasetValue' => $oSiteuser_Company->id)),
							'onclick' => $onclick
						);
					}
				}
			break;
			case 7: // Договоры
				if (Core::moduleIsActive('siteuser'))
				{
					$oSiteuser_Company_Contract = Core_Entity::factory('Siteuser_Company_Contract')->getById($value);

					if (!is_null($oSiteuser_Company_Contract))
					{
						$onclick = $oUser->checkModuleAccess(array('siteuser'), $oSite)
							? self::$_Admin_Form_Controller->getAdminActionModalLoad(array('path' => '/{admin}/siteuser/company/contract/index.php', 'action' => 'edit', 'operation' => 'modal', 'datasetKey' => 0, 'datasetValue' => $oSiteuser_Company_Contract->id, 'window' => '', 'width' => '90%'))
							: '';

						return array(
							'name' => $oSiteuser_Company_Contract->name,
							'href' => self::$_Admin_Form_Controller->getAdminActionLoadHref(array('path' => '/{admin}/siteuser/company/contract/index.php', 'action' => 'edit', 'operation' => 'modal', 'datasetKey' => 0, 'datasetValue' => $oSiteuser_Company_Contract->id)),
							'onclick' => $onclick
						);
					}
				}
			break;
			case 11: // Движение денежных средств
				$oChartaccount_Cashflow = Core_Entity::factory('Chartaccount_Cashflow')->getById($value);

				if (!is_null($oChartaccount_Cashflow))
				{
					return array(
						'name' => $oChartaccount_Cashflow->name,
						'href' => self::$_Admin_Form_Controller->getAdminActionLoadHref(array('path' => '/{admin}/chartaccount/cashflow/index.php', 'action' => 'edit', 'operation' => 'modal', 'datasetKey' => 0, 'datasetValue' => $oChartaccount_Cashflow->id)),
						'onclick' => self::$_Admin_Form_Controller->getAdminActionModalLoad(array('path' => '/{admin}/chartaccount/cashflow/index.php', 'action' => 'edit', 'operation' => 'modal', 'datasetKey' => 0, 'datasetValue' => $oChartaccount_Cashflow->id, 'window' => '', 'width' => '90%')),
						'color' => $oChartaccount_Cashflow->color
					);
				}
			break;
			case 12: // Банковские счета
				$oCompany_Account = Core_Entity::factory('Company_Account')->getById($value);

				if (!is_null($oCompany_Account))
				{
					$onclick = $oUser->checkModuleAccess(array('company'), $oSite)
						? self::$_Admin_Form_Controller->getAdminActionModalLoad(array('path' => '/{admin}/company/account/index.php', 'action' => 'edit', 'operation' => 'modal', 'datasetKey' => 0, 'datasetValue' => $oCompany_Account->id, 'window' => '', 'width' => '90%'))
						: '';

					return array(
						'name' => $oCompany_Account->name,
						'href' => self::$_Admin_Form_Controller->getAdminActionLoadHref(array('path' => '/{admin}/company/account/index.php', 'action' => 'edit', 'operation' => 'modal', 'datasetKey' => 0, 'datasetValue' => $oCompany_Account->id)),
						'onclick' => $onclick
					);
				}
			break;
			case 29: // Кассы
				$oCompany_Cashbox = Core_Entity::factory('Company_Cashbox')->getById($value);

				if (!is_null($oCompany_Cashbox))
				{
					$onclick = $oUser->checkModuleAccess(array('company'), $oSite)
						? self::$_Admin_Form_Controller->getAdminActionModalLoad(array('path' => '/{admin}/company/cashbox/index.php', 'action' => 'edit', 'operation' => 'modal', 'datasetKey' => 0, 'datasetValue' => $oCompany_Cashbox->id, 'window' => '', 'width' => '90%'))
						: '';

					return array(
						'name' => $oCompany_Cashbox->name,
						'href' => self::$_Admin_Form_Controller->getAdminActionLoadHref(array('path' => '/{admin}/company/cashbox/index.php', 'action' => 'edit', 'operation' => 'modal', 'datasetKey' => 0, 'datasetValue' => $oCompany_Cashbox->id)),
						'onclick' => $onclick
					);
				}
			break;
		}

		return NULL;
	}

	/**
	 * Get uniq document ID
	 * @param int $id document ID
	 * @param int $type document type
	 * @return int
	 */
	static public function getDocumentId($id, $type)
	{
		return ($id << 8) | $type;
	}

	/**
	 * Get document type
	 * @return int|NULL
	 */
	static public function getDocumentType($document_id)
	{
		return $document_id
			? Core_Bit::extractBits($document_id, 8, 1)
			: NULL;
	}

	/**
	 * Get document
	 * @return object|NULL
	 */
	static public function getDocument($document_id)
	{
		$type = self::getDocumentType($document_id);

		$id = $document_id >> 8;

		$model = self::getDocumentModel($type);

		// Shop
		is_null($model) && Core::moduleIsActive('shop')
			&& $model = Shop_Controller::getDocumentModel($type);

		return !is_null($model)
			? Core_Entity::factory($model)->getById($id, FALSE)
			: NULL;
	}

	/**
	 * Get Model Name By Type Id
	 * @param int $type
	 *
	 */
	static public function getDocumentModel($type)
	{
		/* Типы документов:
		* 70 - Chartaccount_Operation_Model
		*/
		switch ($type)
		{
			case 70:
				$model = 'Chartaccount_Operation';
			break;
			case 71:
				$model = 'Chartaccount_Closure_Period';
			break;
			default:
				$model = NULL;
		}

		return $model;
	}
}