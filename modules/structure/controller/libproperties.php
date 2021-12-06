<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Structure.
 * Типовой контроллер загрузки свойст типовой дин. страницы для структуры
 *
 * @package HostCMS
 * @subpackage Structure
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Structure_Controller_Libproperties extends Lib_Controller_Libproperties
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
				? (
					$this->_object->options != ''
						? json_decode($this->_object->options, TRUE)
						: array()
				)
				: array();

			$this->getOptionsList($LA, $this->_object);
		}

		$aJson = array(
			'id' => $oLib->id,
			'editHref' => $this->_libId
				? $this->_Admin_Form_Controller->getAdminActionLoadHref('/admin/lib/index.php', 'edit', NULL, 1, $this->_libId, 'lib_dir_id=' . intval($oLib->lib_dir_id))
				: '',
			'optionsHtml' => ob_get_clean()
		);

		Core::showJson($aJson);
	}
}