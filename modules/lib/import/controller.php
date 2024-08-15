<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Lib import controller
 *
 * @package HostCMS
 * @subpackage Lib
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
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

	protected function _import(array $aContent = array())
	{
		$oLib = Core_Entity::factory('Lib');
		$oLib->name = $aContent['name'];
		$oLib->description = $aContent['description'];
		$oLib->lib_dir_id = $this->lib_dir_id;
		$oLib->save();

		isset($aContent['lib'])
			&& $oLib->saveLibFile($aContent['lib']);

		isset($aContent['lib_config'])
			&& $oLib->saveLibConfigFile($aContent['lib_config']);

		if (isset($aContent['options']))
		{
			foreach ($aContent['options'] as $aOptions)
			{
				$oLib_Property = Core_Entity::factory('Lib_Property');
				$oLib_Property
					->lib_id($oLib->id)
					->name(strval($aOptions['name']))
					->varible_name(strval($aOptions['varible_name']))
					->type(intval($aOptions['type']))
					->default_value(isset($aOptions['default_value']) ? $aOptions['default_value'] : '')
					->multivalue(intval($aOptions['multivalue']))
					->sorting(strval($aOptions['sorting']))
					->sql_request(isset($aOptions['sql_request']) ? $aOptions['sql_request'] : '')
					->sql_caption_field(isset($aOptions['sql_caption_field']) ? $aOptions['sql_caption_field'] : '')
					->sql_value_field(isset($aOptions['sql_value_field']) ? $aOptions['sql_value_field'] : '')
					->save();

				if (isset($aOptions['values']))
				{
					foreach ($aOptions['values'] as $aValue)
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
		}

		return $this;
	}
}