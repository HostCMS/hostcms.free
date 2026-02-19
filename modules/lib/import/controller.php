<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Lib import controller
 *
 * @package HostCMS
 * @subpackage Lib
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Lib_Import_Controller extends Admin_Form_Action_Controller
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'content',
		'lib_dir_id'
	);

	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 * @return self
	 */
	public function execute($operation = NULL)
	{
		$oUser = Core_Auth::getCurrentUser();
		if (!$oUser->superuser && $oUser->only_access_my_own)
		{
			return FALSE;
		}

		$aContent = json_decode($this->content, TRUE);

		if (is_array($aContent))
		{
			if (isset($aContent['name']))
			{
				$this->_import($aContent);
			}
			else
			{
				foreach ($aContent as $aLib)
				{
					$this->_import($aLib);
				}
			}
		}

		return $this;
	}

	/**
	 * Import libs
	 * @param array $aContent
	 * @return self
	 */
	protected function _import(array $aContent = array())
	{
		$aExplodeDir = explode('/', $aContent['dirName']);

		$iParent_Id = $this->lib_dir_id;

		foreach ($aExplodeDir as $sDirName)
		{
			if ($sDirName != '')
			{
				$oLib_Dirs = Core_Entity::factory('Lib_Dir');
				$oLib_Dirs
					->queryBuilder()
					->where('lib_dirs.parent_id', '=', $iParent_Id);

				$oLib_Dir = $oLib_Dirs->getByName($sDirName, FALSE);

				if (is_null($oLib_Dir))
				{
					$oLib_Dir = Core_Entity::factory('Lib_Dir');
					$oLib_Dir
						->parent_id($iParent_Id)
						->name($sDirName)
						->save();
				}

				$iParent_Id = $oLib_Dir->id;
			}
		}

		$oLib = Core_Entity::factory('Lib');
		$oLib->lib_dir_id = $iParent_Id;
		$oLib->name = $aContent['name'];
		$oLib->description = $aContent['description'];
		$oLib->type = $aContent['type'];
		$oLib->class = $aContent['class'];
		$oLib->style = $aContent['style'];
		$oLib->save();

		if (isset($aContent['file']) && $aContent['file'] != ''
			&& isset($aContent['file_data']) && $aContent['file_data'] != '')
		{
			if (Core_File::isValidExtension($aContent['file'], array('jpg', 'jpeg', 'gif', 'png', 'webp', 'swf')))
			{
				$file_data = $aContent['file_data'];

				if (preg_match('/^data:([^;]+);base64,(.+)$/', $file_data, $matches))
				{
					if (isset($matches[2]))
					{
						$oLib->createDir();

						// $mimeType = $matches[1];
						$base64Data = $matches[2];

						$fileContent = base64_decode($base64Data);

						$oLib->file = $oLib->id . '.' . Core_File::getExtension(basename($aContent['file']));
						$oLib->save();

						Core_File::write($oLib->getFilePath(), $fileContent);
					}
				}
			}
		}

		isset($aContent['lib'])
			&& $oLib->saveLibFile($aContent['lib']);

		isset($aContent['lib_config'])
			&& $oLib->saveLibConfigFile($aContent['lib_config']);

		// Массив для сопоставления старых ID новым
		$aOldIdNewId = array();

		if (isset($aContent['options']))
		{
			// Сначала сортируем свойства так, чтобы родительские были раньше дочерних
			// Для этого можно отсортировать по parent_id (NULL или 0 - корневые)
			$aOptions = $aContent['options'];

			usort($aOptions, function($a, $b) {
				$parentA = isset($a['parent_id'])
					? intval($a['parent_id'])
					: 0;

				$parentB = isset($b['parent_id'])
					? intval($b['parent_id'])
					: 0;

				// Если parent_id равны, сортируем по sorting или id
				if ($parentA == $parentB)
				{
					$sortA = isset($a['sorting']) ? intval($a['sorting']) : (isset($a['id']) ? intval($a['id']) : 0);
					$sortB = isset($b['sorting']) ? intval($b['sorting']) : (isset($b['id']) ? intval($b['id']) : 0);

					return $sortA - $sortB;
				}

				return $parentA - $parentB;
			});

			foreach ($aOptions as $aOptionsItem)
			{
				$oLib_Property = Core_Entity::factory('Lib_Property');
				$oLib_Property
					->lib_id($oLib->id)
					->name(strval($aOptionsItem['name']))
					->varible_name(strval($aOptionsItem['varible_name']))
					->type(intval($aOptionsItem['type']))
					->default_value(isset($aOptionsItem['default_value']) ? $aOptionsItem['default_value'] : '')
					->multivalue(intval($aOptionsItem['multivalue']))
					->sorting(intval($aOptionsItem['sorting']))
					->sql_request(isset($aOptionsItem['sql_request']) ? $aOptionsItem['sql_request'] : '')
					->sql_caption_field(isset($aOptionsItem['sql_caption_field']) ? $aOptionsItem['sql_caption_field'] : '')
					->sql_value_field(isset($aOptionsItem['sql_value_field']) ? $aOptionsItem['sql_value_field'] : '');

				// Устанавливаем parent_id, если он есть и уже был импортирован
				if (isset($aOptionsItem['parent_id']) && $aOptionsItem['parent_id'] > 0)
				{
					$iOldParentId = intval($aOptionsItem['parent_id']);

					if (isset($aOldIdNewId[$iOldParentId]))
					{
						$oLib_Property->parent_id = $aOldIdNewId[$iOldParentId];
					}
				}

				$oLib_Property->save();

				// Сохраняем соответствие старого ID новому
				if (isset($aOptionsItem['id']))
				{
					$aOldIdNewId[intval($aOptionsItem['id'])] = $oLib_Property->id;
				}

				if (isset($aOptionsItem['values']))
				{
					foreach ($aOptionsItem['values'] as $aValue)
					{
						$oLib_Property_List_Value = Core_Entity::factory('Lib_Property_List_Value');
						$oLib_Property_List_Value
							->lib_property_id($oLib_Property->id)
							->name($aValue['name'])
							->value($aValue['value'])
							->save();
					}
				}
			}

			// Второй проход для обработки свойств, у которых родительские были созданы позже
			if (isset($aContent['options']))
			{
				foreach ($aContent['options'] as $aOptionsItem)
				{
					if (isset($aOptionsItem['parent_id']) && $aOptionsItem['parent_id'] > 0 && isset($aOptionsItem['id']))
					{
						$iOldId = intval($aOptionsItem['id']);
						$iOldParentId = intval($aOptionsItem['parent_id']);

						// Если оба ID есть в маппинге, обновляем parent_id
						if (isset($aOldIdNewId[$iOldId]) && isset($aOldIdNewId[$iOldParentId]))
						{
							$oLibProperty = Core_Entity::factory('Lib_Property', $aOldIdNewId[$iOldId]);
							if ($oLibProperty->parent_id != $aOldIdNewId[$iOldParentId])
							{
								$oLibProperty->parent_id = $aOldIdNewId[$iOldParentId];
								$oLibProperty->save();
							}
						}
					}
				}
			}
		}

		return $this;
	}
}