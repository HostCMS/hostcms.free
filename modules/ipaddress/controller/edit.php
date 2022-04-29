<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Ipaddress Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Ipaddress
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Ipaddress_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
{
	/**
	 * Set object
	 * @param object $object object
	 * @return self
	 */
	public function setObject($object)
	{
		parent::setObject($object);

		$this->title(
			$this->_object->id
				? Core::_('Ipaddress.edit_title', $this->_object->ip)
				: Core::_('Ipaddress.add_title')
		);


		$oMainTab = $this->getTab('main');
		// $oAdditionalTab = $this->getTab('additional');

		$oMainTab
			->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow4 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow5 = Admin_Form_Entity::factory('Div')->class('row'));

		$this->getField('ip')
			// clear standart url pattern
			->format(array('lib' => array()));

		if (!$this->_object->id)
		{
			// Удаляем стандартный <input>
			$oMainTab->delete($this->getField('ip'));

			$oTextarea_Ips = Admin_Form_Entity::factory('Textarea')
				->cols(140)
				->rows(5)
				->caption(Core::_('Ipaddress.ip'))
				->divAttr(array('class' => 'form-group col-xs-12'))
				->name('ip');

			$oMainRow1->add($oTextarea_Ips);
		}
		else
		{
			$oMainTab
				->move($this->getField('ip')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow1);
		}

		$oMainTab
			->move($this->getField('deny_access')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow2)
			->move($this->getField('deny_backend')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow3)
			->move($this->getField('no_statistic')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow4)
			->move($this->getField('comment')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow5)
			;

		return $this;
	}


	/**
	 * Processing of the form. Apply object fields.
	 * @hostcms-event Ipaddress_Controller_Edit.onAfterRedeclaredApplyObjectProperty
	 */
	protected function _applyObjectProperty()
	{
		$id = $this->_object->id;

		if (!$id)
		{
			$sValue = trim($this->_formValues['ip']);

			// Массив значений списка
			$aIpaddresses = explode("\n", $sValue);

			$this->_formValues['ip'] = trim(array_shift($aIpaddresses));
		}

		parent::_applyObjectProperty();

		if (!$id)
		{
			foreach ($aIpaddresses as $sValue)
			{
				$sValue = trim($sValue);

				if (strlen($sValue))
				{
					$oSameIpaddress = Core_Entity::factory('Ipaddress')->getByIp($sValue, FALSE);

					if (is_null($oSameIpaddress))
					{
						$oNewIpaddress = clone $this->_object;
						$oNewIpaddress->ip = $sValue;
						$oNewIpaddress->save();
					}
				}
			}
		}

		Ipaddress_Controller::instance()->clearCache();

		Core_Event::notify(get_class($this) . '.onAfterRedeclaredApplyObjectProperty', $this, array($this->_Admin_Form_Controller));

		return $this;
	}

	/*
	 * Show ip with button
	 */
	static public function addBlockButton($oIp, $ip, $comment, $oAdmin_Form_Controller)
	{
		if (Core::moduleIsActive('ipaddress'))
		{
			// $windowId = $oAdmin_Form_Controller->getWindowId();

			$oIp
				->add(
					Core_Html_Entity::factory('Span')
						->class('input-group-addon')
						->onclick('$.blockIp({ ip: "' . $ip . '", comment: "' . Core_Str::escapeJavascriptVariable($comment) . '" })')
						->add(
							Core_Html_Entity::factory('Span')
								->class('fa fa-ban')
						)
				);
		}
	}
}