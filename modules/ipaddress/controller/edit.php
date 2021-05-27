<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Ipaddress Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Ipaddress
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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

		$this->getField('ip')
			// clear standart url pattern
			->format(array('lib' => array()));

		return $this;
	}


	/**
	 * Processing of the form. Apply object fields.
	 * @hostcms-event Ipaddress_Controller_Edit.onAfterRedeclaredApplyObjectProperty
	 */
	protected function _applyObjectProperty()
	{
		parent::_applyObjectProperty();

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
			$windowId = $oAdmin_Form_Controller->getWindowId();

			$oIp
				->add(
					Core::factory('Core_Html_Entity_Span')
						->class('input-group-addon')
						->onclick('$.blockIp({ ip: "' . $ip . '", comment: "' . Core_Str::escapeJavascriptVariable($comment) . '" })')
						->add(
							Core::factory('Core_Html_Entity_Span')
								->class('fa fa-ban')
						)
				);
		}
	}
}