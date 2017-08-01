<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Seo Site Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Form
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Seo_Site_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
{
	/**
	 * Set object
	 * @param object $object object
	 * @return self
	 */
	public function setObject($object)
	{
		$title = $object->id
			? Core::_('Seo_Site.edit_title')
			: Core::_('Seo_Site.add_title');

		$this
			->addSkipColumn('last_update')
			->addSkipColumn('expired_in');

		parent::setObject($object);

		$oMainTab = $this->getTab('main');
		$oAdditionalTab = $this->getTab('additional');

		$oMainTab
			->add($oMainRow0 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow4 = Admin_Form_Entity::factory('Div')->class('row'));

		$sAlertText = Core::_('Seo_Site.alert_text');

		$sAlertTextShow = "<div class=\"col-xs-12\"><div class=\"alert alert-info fade in\"><i class=\"fa-fw fa fa-info\"></i>{$sAlertText}</div></div>";

		$oMainRow0->add(
			Admin_Form_Entity::factory('Code')->html($sAlertTextShow)
		);

		// Удаляем стандартный <input>
		$oAdditionalTab->delete($this->getField('seo_driver_id'));

		$aSeoDriverOptions = $aSeoDriverTokens = $aSeoDriverTokensJS = array();

		$aSeo_Drivers = Core_Entity::factory('Seo_Driver')->findAll(FALSE);
		foreach($aSeo_Drivers as $oSeo_Driver)
		{
			try {
				$oSeo_Driver_Controller = Seo_Controller::instance($oSeo_Driver->driver);

				$aSeoDriverTokens[$oSeo_Driver->id] =  $oSeo_Driver_Controller->getTokenUrl();
				$aSeoDriverTokensJS[] ='"' . $oSeo_Driver->id . '": \'' . Core_Str::escapeJavascriptVariable($oSeo_Driver_Controller->getTokenUrl()) . '\'';
				$aSeoDriverOptions[$oSeo_Driver->id] = $oSeo_Driver->name;
			}
			catch (Exception $e){}
		}

		$oSelect_Drivers = Admin_Form_Entity::factory('Select')
			->options($aSeoDriverOptions)
			->name('seo_driver_id')
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'))
			->value($this->_object->seo_driver_id)
			->onchange("$('#getToken').attr('href', seoTokens[this.value])")
			->caption(Core::_('Seo_Site.seo_driver_id'));

		$oMainRow1
			->add($oSelect_Drivers)
			->add(
				Admin_Form_Entity::factory('Code')
					->html('<script type="text/javascript">var seoTokens = {' . implode(", ", $aSeoDriverTokensJS) . '}</script>')
			);

		$sTokenUrl = $this->_object->seo_driver_id
			? $aSeoDriverTokens[$this->_object->seo_driver_id]
			: reset($aSeoDriverTokens);

		$oTokenLink = Admin_Form_Entity::factory('Link');
		$oTokenLink
			->divAttr(array('class' => 'large-link form-group col-xs-12 col-sm-3 margin-top-21'))
			->a
				->id('getToken')
				->class('btn btn-labeled btn-sky')
				->href($sTokenUrl)
				->target('_blank')
				->value(Core::_('Seo.getNewToken'));

		$oTokenLink
			->icon
				->class('btn-label fa fa-code-fork');

		$oMainRow1->add($oTokenLink);

		$oMainTab
			->move($this->getField('token')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow3)
			->move($this->getField('active')->divAttr(array('class' => 'form-group col-xs-12 col-sm-3')), $oMainRow4);

		$this->title($title);

		return $this;
	}
}