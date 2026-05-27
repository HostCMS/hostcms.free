<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Lib_Controller_Delete_File.
 *
 * @package HostCMS
 * @subpackage Lib
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
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
			$lib_property_id = Core_Array::getGet('lib_property_id', 0, 'int');

			$oLib_Property = Core_Entity::factory('Lib_Property')->getById($lib_property_id, FALSE);

			if (!is_null($oLib_Property) && strlen($oLib_Property->varible_name))
			{
				$aJson = json_decode($this->_object->options, TRUE);

				$varible_name = $oLib_Property->varible_name;

				$parent_varible_name = NULL;

				$isArrayFilepath = FALSE;

				$position = Core_Array::getGet('position', 0, 'int');

				$oLib_Property_Parent = $oLib_Property->parent_id
					? $oLib_Property->Lib_Property
					: NULL;

				if (!is_null($oLib_Property_Parent) && $oLib_Property_Parent->type == 10)
				{
					$bMultiply = isset($aJson[$oLib_Property_Parent->varible_name][$operation]) && is_array($aJson[$oLib_Property_Parent->varible_name]);

					$filepath = $bMultiply
						? $aJson[$oLib_Property_Parent->varible_name][$operation][$varible_name]
						: (
							isset($aJson[$oLib_Property_Parent->varible_name][$varible_name])
								? $aJson[$oLib_Property_Parent->varible_name][$varible_name]
								: NULL
						);

					$parent_varible_name = $oLib_Property_Parent->varible_name;

					if (is_array($filepath))
					{
						$filepath = $filepath[$position];

						$isArrayFilepath = TRUE;
					}
				}
				else
				{
					$bMultiply = isset($aJson[$varible_name][$operation]) && is_array($aJson[$varible_name]);

					$filepath = $bMultiply
						? $aJson[$varible_name][$operation]
						: (
							isset($aJson[$varible_name])
								? $aJson[$varible_name]
								: NULL
						);
				}

				if (is_scalar($filepath) && strlen($filepath))
				{
					try {
						Core_File::delete(CMS_FOLDER . ltrim($filepath, '/'));
					} catch (Core_Exception $e) {}

					if ($bMultiply)
					{
						unset($aJson[$varible_name][$operation]);

						if (!is_null($parent_varible_name) && !$isArrayFilepath)
						{
							unset($aJson[$parent_varible_name][$operation][$varible_name]);
						}

						if ($isArrayFilepath && isset($aJson[$parent_varible_name][$operation][$varible_name][$position]))
						{
							unset($aJson[$parent_varible_name][$operation][$varible_name][$position]);
						}
					}
					else
					{
						unset($aJson[$varible_name]);

						if (!is_null($parent_varible_name))
						{
							unset($aJson[$parent_varible_name][$varible_name]);
						}
					}

					$this->_object->options(json_encode($aJson));
					$this->_object->save();
				}
			}
		}

		return TRUE;
	}
}