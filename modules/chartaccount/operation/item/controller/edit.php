<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Chartaccount Type Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Chartaccount
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Chartaccount_Operation_Item_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
{
	/**
	 * Set object
	 * @param object $object object
	 * @return self
	 */
	public function setObject($object)
	{
		$this
			->addSkipColumn('dsc0')
			->addSkipColumn('dsc1')
			->addSkipColumn('dsc2')
			->addSkipColumn('csc0')
			->addSkipColumn('csc1')
			->addSkipColumn('csc2')
			;

		$object->chartaccount_operation_id = Core_Array::getGet('chartaccount_operation_id', 0, 'int');

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

		$this->title(
			$this->_object->id
				? Core::_('Chartaccount_Operation_Item.edit_title')
				: Core::_('Chartaccount_Operation_Item.add_title')
		);

		$windowId = $this->_Admin_Form_Controller->getWindowId();

		$company_id = Core_Array::getGet('company_id', 0, 'int');

		$oMainTab = $this->getTab('main');
		$oAdditionalTab = $this->getTab('additional');

		$oMainTab
			->add(Admin_Form_Entity::factory('Div')->class('row')
				->add($oLeftBlock = Admin_Form_Entity::factory('Div')->class('col-xs-12 col-sm-6'))
				->add($oRightBlock = Admin_Form_Entity::factory('Div')->class('col-xs-12 col-sm-6'))
			);

		$oLeftBlock
			->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oDSubcounts = Admin_Form_Entity::factory('Div')->class('debit-subcounts')->add(Admin_Form_Entity::factory('Span')->value('	')));

		$oRightBlock
			->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oCSubcounts = Admin_Form_Entity::factory('Div')->class('credit-subcounts')->add(Admin_Form_Entity::factory('Span')->value('	')));

		$oMainTab
			->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'))
			;

		$aDebitOptions = $this->_object->id
			? Chartaccount_Controller::getOptions(array('dchartaccount_id' => $this->_object->dchartaccount_id))
			: Chartaccount_Controller::getOptions();

		$aCreditOptions = $this->_object->id
			? Chartaccount_Controller::getOptions(array('cchartaccount_id' => $this->_object->cchartaccount_id))
			: Chartaccount_Controller::getOptions();

		$oAdditionalTab->delete($this->getField('dchartaccount_id'));

		$oDebitSelect = Admin_Form_Entity::factory('Select')
			->caption(Core::_('Chartaccount_Operation_Item.dchartaccount_id'))
			->class('form-control input-lg')
			->options($aDebitOptions)
			->name('dchartaccount_id')
			->value($this->_object->dchartaccount_id)
			->onchange("$.loadChartaccounts('{$windowId}', $(this).val(), 'd'); $.loadSubcounts('{$windowId}', $(this).val(), {company_id: {$company_id}}, '.debit-subcounts', 'd')");

		$oMainRow1->add($oDebitSelect);

		$oAdditionalTab->delete($this->getField('cchartaccount_id'));

		$oCreditSelect = Admin_Form_Entity::factory('Select')
			->caption(Core::_('Chartaccount_Operation_Item.cchartaccount_id'))
			->class('form-control input-lg')
			->options($aCreditOptions)
			->name('cchartaccount_id')
			->value($this->_object->cchartaccount_id)
			->onchange("$.loadChartaccounts('{$windowId}', $(this).val(), 'c'); $.loadSubcounts('{$windowId}', $(this).val(), {company_id: {$company_id}}, '.credit-subcounts', 'c')");

		$oMainRow2->add($oCreditSelect);

		$oMainTab
			->move($this->getField('amount')->divAttr(array('class' => 'form-group col-xs-12 col-lg-3')), $oMainRow3);

		$oMainRow3
			->add(
				Admin_Form_Entity::factory('Input')
					->divAttr(array('class' => 'hidden'))
					->type('hidden')
					->id('company_id')
					->name('company_id')
					->value($company_id)
			);

		if ($this->_object->id)
		{
			if ($this->_object->dchartaccount_id)
			{
				$aSubcounts = array();

				$oDChartaccount = $this->_object->Chartaccount_Debit;

				for ($i = 0; $i < 3; $i++)
				{
					$subcountName = 'sc' . $i;
					$debitSubcountName = 'd' . $subcountName;

					if ($oDChartaccount->$subcountName)
					{
						$aSubcounts[$oDChartaccount->$subcountName] = $this->_object->$debitSubcountName;
					}
				}

				Chartaccount_Controller::showSubcounts($aSubcounts, $this->_object->dchartaccount_id, $oDSubcounts, $this->_Admin_Form_Controller, array('company_id' => $company_id), 'd');
			}

			if ($this->_object->cchartaccount_id)
			{
				$aSubcounts = array();

				$oCChartaccount = $this->_object->Chartaccount_Credit;

				for ($i = 0; $i < 3; $i++)
				{
					$subcountName = 'sc' . $i;
					$creditSubcountName = 'c' . $subcountName;

					if ($oCChartaccount->$subcountName)
					{
						$aSubcounts[$oCChartaccount->$subcountName] = $this->_object->$creditSubcountName;
					}
				}

				Chartaccount_Controller::showSubcounts($aSubcounts, $this->_object->cchartaccount_id, $oCSubcounts, $this->_Admin_Form_Controller, array('company_id' => $company_id), 'c');
			}
		}

		return $this;
	}

	/**
	 * Processing of the form. Apply object fields.
	 * @hostcms-event Chartaccount_Operation_Item_Controller_Edit.onAfterRedeclaredApplyObjectProperty
	 */
	protected function _applyObjectProperty()
	{
		parent::_applyObjectProperty();

// echo "<pre>";
// var_dump($_POST);
// echo "</pre>";

		if ($this->_object->dchartaccount_id)
		{
			$oDChartaccount = $this->_object->Chartaccount_Debit;

			for ($i = 0; $i < 3; $i++)
			{
				$subcountName = 'sc' . $i;
				$debitSubcountName = 'd' . $subcountName;
				$scValue = Core_Array::getPost($debitSubcountName, 0, 'strval');

				switch ($oDChartaccount->$subcountName)
				{
					case 2:
						$aExplodeCompany = explode('_', $scValue);
						$scValue = isset($aExplodeCompany[1]) ? intval($aExplodeCompany[1]) : 0;
					break;
				}

				$this->_object->$debitSubcountName = $scValue;
			}

			$this->_object->save();
		}

		if ($this->_object->cchartaccount_id)
		{
			$oDChartaccount = $this->_object->Chartaccount_Credit;

			for ($i = 0; $i < 3; $i++)
			{
				$subcountName = 'sc' . $i;
				$creditSubcountName = 'c' . $subcountName;
				$scValue = Core_Array::getPost($creditSubcountName, 0, 'strval');

				switch ($oDChartaccount->$subcountName)
				{
					case 2:
						$aExplodeCompany = explode('_', $scValue);
						$scValue = isset($aExplodeCompany[1]) ? intval($aExplodeCompany[1]) : 0;
					break;
				}

				$this->_object->$creditSubcountName = $scValue;
			}

			$this->_object->save();
		}

		Core_Event::notify(get_class($this) . '.onAfterRedeclaredApplyObjectProperty', $this, array($this->_Admin_Form_Controller));
	}

	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 * @return mixed
	 */
	public function execute($operation = NULL)
	{
		$chartaccount_operation_id = Core_Array::getGet('chartaccount_operation_id', 0, 'int');
		$company_id = Core_Array::getPost('company_id', 0, 'int');

		// $parentWindowId = preg_replace('/[^A-Za-z0-9_-]/', '', Core_Array::getGet('parentWindowId', '', 'str'));
		// $windowId = $parentWindowId ? $parentWindowId : $this->_Admin_Form_Controller->getWindowId();

		$modalWindowId = preg_replace('/[^A-Za-z0-9_-]/', '', Core_Array::getGet('modalWindowId', '', 'str'));
		$windowId = $modalWindowId ? $modalWindowId : $this->_Admin_Form_Controller->getWindowId();

		// $windowId = 'id_content';

		$sJsRefresh = '<script>
			console.log("' . $windowId . '");
			if ($("#' . $windowId . '").length)
			{
				// console.log(1111);
				$.adminLoad({ path: \'/admin/chartaccount/operation/item/index.php\', additionalParams: \'company_id=' . $company_id . '&chartaccount_operation_id=' . $chartaccount_operation_id . '\', windowId: \'' . $windowId . '\' });
			}
		</script>';

		switch ($operation)
		{
			case 'save':
			case 'saveModal':
			case 'apply':
			case 'applyModal':

				$operation == 'saveModal' && $this->addMessage($sJsRefresh);
				$operation == 'applyModal' && $this->addContent($sJsRefresh);
			break;
			/*case 'markDeleted':
				$this->_object->markDeleted();
				$this->addContent($sJsRefresh);
			break;*/
		}

		return parent::execute($operation);
	}
}