<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Warrant Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Warrant_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
{
	/**
	 * Set object
	 * @param object $object object
	 * @return self
	 */
	public function setObject($object)
	{
		$this
			->addSkipColumn('type')
			->addSkipColumn('sc0')
			->addSkipColumn('sc1')
			->addSkipColumn('sc2')
			->addSkipColumn('siteuser_id')
			->addSkipColumn('siteuser_company_id')
			->addSkipColumn('siteuser_company_contract_id')
			;

		if (!$object->id)
		{
			$object->shop_id = Core_Array::getGet('shop_id', 0, 'int');
			$object->type = Core_Array::getGet('type', 0, 'int');
		}

		if ($object->type == 0)
		{
			$this
				->addSkipColumn('tax');
		}

		return parent::setObject($object);
	}

	/**
	 * Prepare backend item's edit form
	 *
	 * @return self
	 */
	protected function _prepareForm()
	{
		parent::_prepareForm();

		$oShop = Core_Entity::factory('Shop', Core_Array::getGet('shop_id', 0));
		$oShop_Group = Core_Entity::factory('Shop_Group', Core_Array::getGet('shop_group_id', 0));

		$oMainTab = $this->getTab('main');
		$oAdditionalTab = $this->getTab('additional');

		$windowId = $this->_Admin_Form_Controller->getWindowId();

		$oMainTab
			->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oCompanyRow = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oSubcounts = Admin_Form_Entity::factory('Div')->class('chartaccount-subcounts')->add(Admin_Form_Entity::factory('Span')))
			// ->add($oSiteuserRow = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'))
			;

		// if ($this->_object->id)
		// {
			$oMainTab->add($oDocumentsBlock = Admin_Form_Entity::factory('Div')->class('well with-header shop-document-relation'));

			$oDocumentsBlock
				->add($oHeaderDiv = Admin_Form_Entity::factory('Div')
					->class('header bordered-palegreen')
					->value(Core::_('Shop_Warrant.document_header'))
				)
				->add($oDocumentsBlockRow1 = Admin_Form_Entity::factory('Div')->class('row'));
		// }

		$oMainTab
			->move($this->getField('number')->divAttr(array('class' => 'form-group col-xs-12 col-sm-3'))->class('form-control input-lg'), $oMainRow1)
			->move($this->getField('datetime')->divAttr(array('class' => 'form-group col-xs-12 col-sm-5 col-lg-4'))->class('form-control input-lg'), $oMainRow1);

		// Печать
		if (Core::moduleIsActive('printlayout') && $this->_object->getEntityType() !== 32)
		{
			$oDivActions = Admin_Form_Entity::factory('Div')
				->class('form-group col-xs-12 col-sm-4 col-lg-5 margin-top-21');

			$printlayoutsButton = '
				<div class="btn-group print-button margin-right-20' . (!$this->_object->id ? ' hidden' : '') . '">
					<a class="btn btn-labeled btn-success" href="javascript:void(0);"><i class="btn-label fa fa-print"></i>' . Core::_('Printlayout.print') . '</a>
					<a class="btn btn-palegreen dropdown-toggle" data-toggle="dropdown" href="javascript:void(0);" aria-expanded="false"><i class="fa fa-angle-down"></i></a>
					<ul class="dropdown-menu dropdown-palegreen">
			';

			$moduleName = $this->_Admin_Form_Controller->module->getModuleName();

			$oModule = Core_Entity::factory('Module')->getByPath($moduleName);

			if (!is_null($oModule))
			{
				$printlayoutsButton .= Printlayout_Controller::getPrintButtonHtml($this->_Admin_Form_Controller, $oModule->id, $this->_object->getEntityType(), 'hostcms[checked][0][' . $this->_object->id . ']=1&shop_id=' . $oShop->id . '&shop_group_id=' . $oShop_Group->id);
			}

			$printlayoutsButton .= '
					</ul>
				</div>
			';

			$oDivActions->add(
				Admin_Form_Entity::factory('Code')->html($printlayoutsButton)
			);

			$oMainRow1->add($oDivActions);
		}

		// Дата документа меняется только если документа не проведен.
		// $this->_object->id && $this->_object->posted
		// 	&& $this->getField('datetime')->readonly('readonly');

		$oAdditionalTab->delete($this->getField('company_id'));

		$oSite = Core_Entity::factory('Site', CURRENT_SITE);

		$aCompanies = $oSite->Companies->findAll();

		$aTmp = [];

		foreach($aCompanies as $oCompany)
		{
			$aTmp[$oCompany->id] = $oCompany->name;
		}

		$oSiteuser_Company_Contract = Core::moduleIsActive('chartaccount') ? Chartaccount_Controller::getEntity($this->_object, 7) : NULL;
		$siteuser_company_contract_id = !is_null($oSiteuser_Company_Contract)
			? $oSiteuser_Company_Contract->id
			: 0;

		/*$oSiteuser_Company = Core::moduleIsActive('chartaccount') ? Chartaccount_Controller::getEntity($this->_object, 2) : NULL;
		$siteuser_company_id = Core::moduleIsActive('siteuser') && !is_null($oSiteuser_Company)
			? $oSiteuser_Company->id
			: 0;*/

		$oSelect_Companies = Admin_Form_Entity::factory('Select')
			->options($aTmp)
			->id('company_id')
			->name('company_id')
			->value($this->_object->company_id)
			->caption(Core::_('Shop_Warrant.company_id'))
			->divAttr(array('class'=>'form-group col-xs-12 col-md-6'))
			->onchange("$.fillSiteuserCompanyContract('{$windowId}', " . intval($siteuser_company_contract_id) . ");$.fillCompanyCashbox($('#{$windowId} #company_cashbox_id'), $(this).val(), $('#{$windowId} #company_cashbox_id').val());$.fillCompanyAccount($('#{$windowId} #company_account_id'), $(this).val(), $('#{$windowId} #company_account_id').val());$.loadSubcounts('{$windowId}', $('#{$windowId} #chartaccount_id').val(), {company_id: $('#{$windowId} #company_id').val(), company_cashbox_id: $('#{$windowId} #company_cashbox_id').val()})");

		$oCompanyRow->add($oSelect_Companies);

		$oAdditionalTab
			->delete($this->getField('company_cashbox_id'))
			->delete($this->getField('company_account_id'));

		switch ($this->_object->type)
		{
			case 0:
			case 1:
				$oSelect_Company_Cashbox = Admin_Form_Entity::factory('Select')
					->options(array())
					->id('company_cashbox_id')
					->name('company_cashbox_id')
					->value($this->_object->company_cashbox_id)
					->caption(Core::_('Shop_Warrant.company_cashbox_id'))
					->divAttr(array('class'=>'form-group col-xs-12 col-md-3'));

				$oCompanyRow->add($oSelect_Company_Cashbox);
			break;
			case 2:
			case 3:
				$oSelect_Company_Account = Admin_Form_Entity::factory('Select')
					->options(array())
					->id('company_account_id')
					->name('company_account_id')
					->value($this->_object->company_account_id)
					->caption(Core::_('Shop_Warrant.company_account_id'))
					->divAttr(array('class'=>'form-group col-xs-12 col-md-3'));

				$oCompanyRow->add($oSelect_Company_Account);
			break;
		}

		$oAdditionalTab->delete($this->getField('chartaccount_id'));

		$aOptions = array();

		if (Core::moduleIsActive('chartaccount'))
		{
			switch ($this->_object->type)
			{
				case 0: // Расходный
				case 1: // Приходный
					$oChartaccount = Core_Entity::factory('Chartaccount')->getByCode('50.1');
					if (!is_null($oChartaccount))
					{
						$aChartaccount_Correct_Entries = $this->_object->type == 0
							? $oChartaccount->Credit_Correct_Entries->findAll()
							: $oChartaccount->Debit_Correct_Entries->findAll();

						foreach ($aChartaccount_Correct_Entries as $oChartaccount_Correct_Entry)
						{
							$id = $this->_object->type == 1
								? $oChartaccount_Correct_Entry->Credit_Chartaccount->id
								: $oChartaccount_Correct_Entry->Debit_Chartaccount->id;

							$aOptions[$id] = ($this->_object->type == 1 ? $oChartaccount_Correct_Entry->Credit_Chartaccount->code : $oChartaccount_Correct_Entry->Debit_Chartaccount->code) . ' ' . ($this->_object->type == 1 ? $oChartaccount_Correct_Entry->Credit_Chartaccount->name : $oChartaccount_Correct_Entry->Debit_Chartaccount->name);
						}
					}
				break;
				case 2: // Входящий платеж
				case 3: // Исходящий платеж
					$oChartaccount = Core_Entity::factory('Chartaccount')->getByCode('51');
					if (!is_null($oChartaccount))
					{
						$aChartaccount_Correct_Entries = $this->_object->type == 3
							? $oChartaccount->Credit_Correct_Entries->findAll()
							: $oChartaccount->Debit_Correct_Entries->findAll();

						foreach ($aChartaccount_Correct_Entries as $oChartaccount_Correct_Entry)
						{
							$id = $this->_object->type == 2
								? $oChartaccount_Correct_Entry->Credit_Chartaccount->id
								: $oChartaccount_Correct_Entry->Debit_Chartaccount->id;

							$aOptions[$id] = ($this->_object->type == 2 ? $oChartaccount_Correct_Entry->Credit_Chartaccount->code : $oChartaccount_Correct_Entry->Debit_Chartaccount->code) . ' ' . ($this->_object->type == 2 ? $oChartaccount_Correct_Entry->Credit_Chartaccount->name : $oChartaccount_Correct_Entry->Debit_Chartaccount->name);
						}
					}
				break;
			}
		}

		asort($aOptions);

		$oSelect_Chartaccount = Admin_Form_Entity::factory('Select')
			->options($aOptions)
			->id('chartaccount_id')
			->name('chartaccount_id')
			->value($this->_object->chartaccount_id)
			->caption(Core::_('Shop_Warrant.chartaccount_id'))
			->divAttr(array('class'=>'form-group col-xs-12 col-md-3'))
			->onchange("$.loadSubcounts('{$windowId}', $(this).val(), {company_id: $('#{$windowId} #company_id').val(), company_cashbox_id: $('#{$windowId} #company_cashbox_id').val(), company_account_id: $('#{$windowId} #company_account_id').val()})");

		$oCompanyRow->add($oSelect_Chartaccount);

		if (Core::moduleIsActive('chartaccount') && $this->_object->chartaccount_id)
		{
			$aSubcounts = array();

			for ($i = 0; $i < 3; $i++)
			{
				$subcountName = 'sc' . $i;

				if ($this->_object->Chartaccount->$subcountName)
				{
					//$aSubcounts[$this->_object->Chartaccount->$subcountName] = Core_Array::getPost($subcountName, 0, 'int');
					$aSubcounts[$this->_object->Chartaccount->$subcountName] = $this->_object->$subcountName;
				}
			}

			Chartaccount_Controller::showSubcounts($aSubcounts, $this->_object->chartaccount_id, $oSubcounts, $this->_Admin_Form_Controller, array('company_id' => $this->_object->company_id));
		}

		$oAdditionalTab->delete($this->getField('chartaccount_cashflow_id'));

		$oSelectCashflows = Admin_Form_Entity::factory('Select')
			->id('chartaccount_cashflow_id')
			->options(Chartaccount_Cashflow_Controller::fillCashflowList())
			->name('chartaccount_cashflow_id')
			->value($this->_object->chartaccount_cashflow_id)
			->caption(Core::_('Shop_Warrant.chartaccount_cashflow_id'))
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-3'));

		$oMainRow2->add($oSelectCashflows);

		// Удаляем поле с идентификатором ответственного сотрудника
		$oAdditionalTab->delete($this->getField('user_id'));

		$oSite = Core_Entity::factory('Site', CURRENT_SITE);

		$aSelectResponsibleUsers = $oSite->Companies->getUsersOptions();

		$oSelectResponsibleUsers = Admin_Form_Entity::factory('Select')
			->id('user_id')
			->options($aSelectResponsibleUsers)
			->name('user_id')
			->value($this->_object->user_id)
			->caption(Core::_('Shop_Warrant.user_id'))
			->divAttr(array('class' => ''));

		$oScriptResponsibleUsers = Admin_Form_Entity::factory('Script')
			->value('$("#' . $windowId . ' #user_id").selectUser({
					placeholder: "",
					language: "' . Core_I18n::instance()->getLng() . '",
					dropdownParent: $("#' . $windowId . '")
				});'
			);

		$oScript = Admin_Form_Entity::factory('Script')
			->value("$.fillCompanyCashbox($('#{$windowId} #company_cashbox_id'), $('#{$windowId} #company_id').val(), $('#{$windowId} #company_cashbox_id').val());$.fillCompanyAccount($('#{$windowId} #company_account_id'), $('#{$windowId} #company_id').val(), $('#{$windowId} #company_account_id').val());");

		$oMainRow2
			->add(
				Admin_Form_Entity::factory('Div')
					->add($oSelectResponsibleUsers)
					->class('form-group col-xs-12 col-sm-5 col-lg-3')
			)
			->add($oScriptResponsibleUsers)
			->add($oScript);

		$description = $this->_object->type == 2 || $this->_object->type == 3
			? Core::_('Shop_Warrant.purpose_payment')
			: Core::_('Shop_Warrant.description');

		$oMainTab
			->move($this->getField('posted')->divAttr(array('class' => 'form-group col-xs-12 col-lg-3 margin-top-21')), $oMainRow2)
			->move($this->getField('description')->caption($description)->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow1)
			->move($this->getField('reason')->divAttr(array('class' => 'form-group col-xs-12 col-sm-3')), $oMainRow3)
			->move($this->getField('amount')->divAttr(array('class' => 'form-group col-xs-11 col-sm-2')), $oMainRow3);

		$oMainRow3
			->add(
				$oAlertSpan = Admin_Form_Entity::factory('Span')
					->class('form-group col-xs-1 margin-top-30')
			);

		$related_amount = $this->_object->getShopDocumentRelatedAmount();

		$bShowAlert = $related_amount && $this->_object->amount != $related_amount;

		$hiddenClass = !$bShowAlert ? ' hidden' : '';

		$oAlertSpan->add(
			Core_Html_Entity::factory('I')
				->class('fa fa-exclamation-triangle darkorange amount-alert' . $hiddenClass)
				->title(Core::_('Shop_Warrant.wrong_amount'))
		);

		if ($this->_object->id)
		{
			$oRecountLink = Admin_Form_Entity::factory('Link');
			$oRecountLink
				->divAttr(array('class' => 'input-group-addon no-bordered-left'))
				->a
					->id('recount-button')
					->title(Core::_('Shop_Warrant.recount'));
			$oRecountLink
				->icon
					->class('fa-solid fa-arrows-rotate');
			$oRecountLink
				->div
					->onclick($this->_getRecountOnclick());

			$this->getField('amount')
				->add($oRecountLink);
		}

		$this->_object->type && $oMainTab->move($this->getField('tax')->divAttr(array('class' => 'form-group col-xs-12 col-sm-2')), $oMainRow3);

		// if ($this->_object->id)
		// {
			$oDivDocuments = Admin_Form_Entity::factory('Div')
				->id($windowId . '-shop-documents')
				->class('col-xs-12 margin-top-10')
				// ->add($this->_addDocuments());
				->add(
					$this->_object->id
						? $this->_addDocuments()
						: Admin_Form_Entity::factory('Code')->html(
							Core_Message::get(Core::_('Shop_Warrant.enable_after_save'), 'warning')
						)
				);

			$oDocumentsBlockRow1->add($oDivDocuments);
		// }

		switch ($this->_object->type)
		{
			case 0:
			default:
				$title = $this->_object->id
					? Core::_('Shop_Warrant.form_edit', $this->_object->number, FALSE)
					: Core::_('Shop_Warrant.form_add');
			break;
			case 1:
				$title = $this->_object->id
					? Core::_('Shop_Warrant.form_edit_incoming', $this->_object->number, FALSE)
					: Core::_('Shop_Warrant.form_add_incoming');
			break;
			case 2:
				$title = $this->_object->id
					? Core::_('Shop_Warrant.form_edit_incoming_pay', $this->_object->number, FALSE)
					: Core::_('Shop_Warrant.form_add_incoming_pay');
			break;
			case 3:
				$title = $this->_object->id
					? Core::_('Shop_Warrant.form_edit_writeoff_pay', $this->_object->number, FALSE)
					: Core::_('Shop_Warrant.form_add_writeoff_pay');
			break;
		}

		$this->title($title);

		return $this;
	}

	/*
	 *
	 * @return Admin_Form_Entity
	 */
	protected function _getRecountOnclick()
	{
		return 'res = confirm(\'' . Core::_('Shop_Warrant.confirm') . '\'); if (res) { mainFormLocker.unlock(); ' .
			$this->_Admin_Form_Controller->getAdminSendForm(array('action' => 'recountAmount')) .
			'} return res;';
	}

	/*
	 * Add shop documents
	 * @return Admin_Form_Entity
	 */
	protected function _addDocuments()
	{
		$modalWindowId = preg_replace('/[^A-Za-z0-9_-]/', '', Core_Array::getGet('modalWindowId', '', 'str'));
		$windowId = $modalWindowId ? $modalWindowId : $this->_Admin_Form_Controller->getWindowId();

		$document_id = Shop_Controller::getDocumentId($this->_object->id, $this->_object->getEntityType());

		return Admin_Form_Entity::factory('Script')
			->value("$(function (){
				mainFormLocker.unlock();
				$.adminLoad({ path: '/admin/shop/document/relation/index.php', additionalParams: 'document_id=" . $document_id . "&shop_id=" . $this->_object->shop_id . "&parentWindowId=" . $windowId . "&_module=0', windowId: '{$windowId}-shop-documents', loadingScreen: false });
			});");
	}

	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 * @return mixed
	 */
	public function execute($operation = NULL)
	{
		// createFrom=supply&createFromId=123
		$createFrom = Core_Array::getGet('createFrom', '', 'trim');
		$createFromId = Core_Array::getGet('createFromId', 0, 'int');
		$type = Core_Array::getGet('type', 0, 'int');

		switch ($createFrom)
		{
			case 'purchaseorder':
				$oBaseDocument = Core_Entity::factory('Shop_Warehouse_Purchaseorder');
			break;
			case 'invoice':
				$oBaseDocument = Core_Entity::factory('Shop_Warehouse_Invoice');
			break;
			case 'supply':
				$oBaseDocument = Core_Entity::factory('Shop_Warehouse_Supply');
			break;
			default:
				$oBaseDocument = NULL;
		}

		// Создаем документ из формы редактирования заказа поставщику или счета поставщика
		if ($createFromId && !is_null($oBaseDocument) && !$this->_object->id)
		{
			$oBaseDocument = $oBaseDocument->getById($createFromId);

			if ($oBaseDocument)
			{
				$oShop_Warrant = Core_Entity::factory('Shop_Warrant');

				$aFields = array('company_id', 'amount', 'tax', 'description');

				foreach ($aFields as $fieldName)
				{
					isset($oBaseDocument->$fieldName)
						&& $oShop_Warrant->$fieldName = $oBaseDocument->$fieldName;
				}

				isset($oBaseDocument->shop_warehouse_id)
					&& $oShop_Warrant->shop_id = $oBaseDocument->Shop_Warehouse->shop_id;

				$oShop_Warrant->type = $type;

				$this->_object = $oShop_Warrant;
			}
		}

		$bShowCreateDocumentButton = !$this->_object->id && ($operation == 'save' || $operation == 'saveModal');

		$return = parent::execute($operation);

		if ($bShowCreateDocumentButton)
		{
			$sJsRefresh = Shop_Warehouse_Controller::getJsRefresh($this->_Admin_Form_Controller, $this->_object, array('invoice', 'purchasereturn'));
		}

		if (!is_null($oBaseDocument) && ($operation == 'saveModal' || $operation == 'applyModal'))
		{
			$related_document_id = Shop_Controller::getDocumentId($createFromId, $oBaseDocument->getEntityType());
			$document_id = Shop_Controller::getDocumentId($this->_object->id, $this->_object->getEntityType());

			$oShop_Document_Relations = Core_Entity::factory('Shop_Document_Relation');
			$oShop_Document_Relations->queryBuilder()
				->where('shop_document_relations.document_id', '=', $document_id)
				->where('shop_document_relations.related_document_id', '=', $related_document_id);

			$oShop_Document_Relation = $oShop_Document_Relations->getLast(FALSE);

			if (is_null($oShop_Document_Relation))
			{
				$oShop_Document_Relation = Core_Entity::factory('Shop_Document_Relation');
				$oShop_Document_Relation->document_id = $document_id;
				$oShop_Document_Relation->related_document_id = $related_document_id;
				$oShop_Document_Relation->save();
			}

			if ($operation == 'applyModal')
			{
				$sJsRefresh = "<script>
					(function($) {
						bootbox.hideAll();
					})(jQuery);
				</script>";

				$this->_Admin_Form_Controller->addMessage($sJsRefresh);
			}
			elseif($bShowCreateDocumentButton)
			{
				$this->_Admin_Form_Controller->addMessage($sJsRefresh);
			}

			$this->clearContent();

			return TRUE;
		}

		if ($bShowCreateDocumentButton)
		{
			$this->_Admin_Form_Controller->addMessage($sJsRefresh);
		}

		return $return;
	}

	/**
	 * Processing of the form. Apply object fields.
	 * @hostcms-event Shop_Warrant_Controller_Edit.onAfterRedeclaredApplyObjectProperty
	 */
	protected function _applyObjectProperty()
	{
		$this->_formValues['siteuser_id'] = intval(Core_Array::get($this->_formValues, 'siteuser_id'));
		$this->_object->user_id = intval(Core_Array::getPost('user_id'));

		$this->addSkipColumn('posted');

		$bAdd = is_null($this->_object->id);

		parent::_applyObjectProperty();

		if (Core::moduleIsActive('chartaccount') && $this->_object->chartaccount_id)
		{
			$oChartaccount = $this->_object->Chartaccount;

			for ($i = 0; $i < 3; $i++)
			{
				$subcountName = 'sc' . $i;
				$scValue = Core_Array::getPost($subcountName, 0, 'strval');

				switch ($oChartaccount->$subcountName)
				{
					case 2:
						$aExplodeCompany = explode('_', $scValue);
						$scValue = isset($aExplodeCompany[1]) ? intval($aExplodeCompany[1]) : 0;
					break;
				}

				$this->_object->$subcountName = $scValue;
			}

			$this->_object->save();
		}

		if ($this->_object->number == '')
		{
			$this->_object->number = $this->_object->id;
			$this->_object->save();
		}

		$document_id = Shop_Controller::getDocumentId($this->_object->id, $this->_object->getEntityType());

		$aShop_Document_Relations = Core_Entity::factory('Shop_Document_Relation')->getAllByDocument_id($document_id);

		foreach ($aShop_Document_Relations as $oShop_Document_Relation)
		{
			$sInputName = 'apply_check_0_' . $oShop_Document_Relation->id . '_fv_2071';

			$value = Core_Array::getPost($sInputName);

			if (!is_null($value) && $value >= 0)
			{
				$oShop_Document_Relation->paid = $value;
				$oShop_Document_Relation->save();
			}
		}

		if ($bAdd)
		{
			ob_start();
			$this->_addDocuments()->execute();
			$this->_Admin_Form_Controller->addMessage(ob_get_clean());
		}

		if (Core::moduleIsActive('chartaccount'))
		{
			Core_Array::getPost('posted')
				? $this->_object->post()
				: $this->_object->unpost();
		}

		Core_Event::notify(get_class($this) . '.onAfterRedeclaredApplyObjectProperty', $this, array($this->_Admin_Form_Controller));
	}
}