<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Tpl import controller
 *
 * @package HostCMS
 * @subpackage Tpl
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Tpl_Import_Controller extends Admin_Form_Action_Controller
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'content',
		'tpl_dir_id'
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
				foreach ($aContent as $aTpl)
				{
					$this->_import($aTpl);
				}
			}
		}

		return $this;
	}

	/**
	 * Import
	 * @param array $aContent
	 * @return self
	 */
	protected function _import(array $aContent = array())
	{
		$aExplodeDir = explode('/', $aContent['dirName']);

		$iParent_Id = $this->tpl_dir_id;

		foreach ($aExplodeDir as $sDirName)
		{
			if ($sDirName != '')
			{
				$oTpl_Dirs = Core_Entity::factory('Tpl_Dir');
				$oTpl_Dirs
					->queryBuilder()
					->where('tpl_dirs.parent_id', '=', $iParent_Id);

				$oTpl_Dir = $oTpl_Dirs->getByName($sDirName, FALSE);

				if (is_null($oTpl_Dir))
				{
					$oTpl_Dir = Core_Entity::factory('Tpl_Dir');
					$oTpl_Dir
						->parent_id($iParent_Id)
						->name($sDirName)
						->save();
				}

				$iParent_Id = $oTpl_Dir->id;
			}
		}

		$oTpl = Core_Entity::factory('Tpl');
		$oTpl->name = $aContent['name'];
		$oTpl->tpl_dir_id = $iParent_Id;
		$oTpl->description = $aContent['description'];
		$oTpl->save();

		$oTpl->name = $oTpl->name . " [{$oTpl->id}]";
		$oTpl->save();

		isset($aContent['tpl'])
			&& $oTpl->saveTplFile($aContent['tpl']);

		// Configs
		$aLngs = Tpl_Controller::getLngs();

		if (isset($aContent['configs']))
		{
			foreach ($aContent['configs'] as $lng => $sConfig)
			{
				if (in_array($lng, $aLngs) && $sConfig != '')
				{
					$oTpl->saveLngConfigFile($lng, $sConfig);
				}
			}
		}

		return $this;
	}
}