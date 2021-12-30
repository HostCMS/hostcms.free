<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Lib_Controller_Delete_File.
 *
 * @package HostCMS
 * @subpackage Lib
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Lib_Controller_Delete_File extends Admin_Form_Action_Controller
{
	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 */
	public function execute($operation = NULL)
	{
		if (!is_null($operation))
		{
			$varible_name = Core_Array::getGet('varible_name');

			if (strlen($varible_name))
			{
				$aJson = json_decode($this->_object->options, TRUE);

				if (isset($aJson[$varible_name][$operation]))
				{
					try {
						// Core_File::delete($this->_object->getLibFilePath() . $aJson[$varible_name][$operation]);
						Core_File::delete(CMS_FOLDER . $aJson[$varible_name][$operation]);

						unset($aJson[$varible_name][$operation]);

						$this->_object->options(json_encode($aJson));
						$this->_object->save();
					} catch (Core_Exception $e) {}
				}
			}
		}

		return TRUE;
	}
}