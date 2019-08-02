<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Template.
 * Типовой контроллер загрузки свойст типовой дин. страницы для виджет
 *
 * @package HostCMS
 * @subpackage Template
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2019 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Template_Section_Lib_Controller_Libproperties extends Lib_Controller_Libproperties
{
	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 */
	public function execute($operation = NULL)
	{
		if (is_null($this->_libId))
		{
			throw new Core_Exception('libId is NULL.');
		}

		ob_start();

		$oLib = Core_Entity::factory('Lib')->find($this->_libId);

		if (is_null($oLib->id))
		{
			Core_Message::show(
				Core::_('Structure.lib_contains_no_parameters')
			);
		}
		else
		{
			$LA = $this->_object->id
				? json_decode($this->_object->options, TRUE)
				: array();

			!is_array($LA) && $LA = array();
			// $LA = json_decode($LA, TRUE);

			$this->getOptionsList($LA);
		}

		Core::showJson(ob_get_clean());
	}
}