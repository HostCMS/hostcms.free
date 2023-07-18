<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Ipaddress_Useragent_Model
 *
 * @package HostCMS
 * @subpackage Ipaddress
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Ipaddress_Useragent_Model extends Core_Entity
{
	/**
	 * Change active
	 * @return self
	 * @hostcms-event ipaddress_useragent.onBeforeChangeActive
	 * @hostcms-event ipaddress_useragent.onAfterChangeActive
	 */
	public function changeActive()
	{
		Core_Event::notify($this->_modelName . '.onBeforeChangeActive', $this);

		$this->active = 1 - $this->active;
		$this->save();

		Core_Event::notify($this->_modelName . '.onAfterChangeActive', $this);

		return $this;
	}

	/**
	 * Backend badge
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function conditionBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		$aConditions = Ipaddress_Useragent_Controller::getConditions();

		$color = Core_Str::createColor($this->condition);

		return isset($aConditions[$this->condition])
			? '<span class="badge badge-round badge-max-width margin-left-5" title="' . $aConditions[$this->condition] . '" style="border-color: ' . $color . '; color: ' . Core_Str::hex2darker($color, 0.2) . '; background-color: ' . Core_Str::hex2lighter($color, 0.88) . '">'
				. $aConditions[$this->condition]
				. '</span>'
			: '';
	}
}