<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Lib_Controller_Delete_File.
 *
 * @package HostCMS
 * @subpackage Lib
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
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

				$bMultiply = isset($aJson[$varible_name][$operation]) && is_array($aJson[$varible_name]);

				$filepath = $bMultiply
					? $aJson[$varible_name][$operation]
					: $aJson[$varible_name];

				if (strlen($filepath))
				{
					try {
						Core_File::delete(CMS_FOLDER . ltrim($filepath, '/'));
					} catch (Core_Exception $e) {}

					if ($bMultiply)
					{
						unset($aJson[$varible_name][$operation]);
					}
					else
					{
						unset($aJson[$varible_name]);
					}

					$this->_object->options(json_encode($aJson));
					$this->_object->save();
				}
			}
		}

		return TRUE;
	}
}