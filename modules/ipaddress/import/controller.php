<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Ipaddress_Import_Controller
 *
 * @package HostCMS
 * @subpackage Ipaddress
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
 */
class Ipaddress_Import_Controller extends Admin_Form_Action_Controller
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'content',
		'ipaddress_dir_id'
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
			if (isset($aContent['ip']))
			{
				$this->_import($aContent);
			}
			else
			{
				foreach ($aContent as $aIpaddress)
				{
					$this->_import($aIpaddress);
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

		$iParent_Id = $this->ipaddress_dir_id;

		foreach ($aExplodeDir as $sDirName)
		{
			if ($sDirName != '')
			{
				$oIpaddress_Dirs = Core_Entity::factory('Ipaddress_Dir');
				$oIpaddress_Dirs
					->queryBuilder()
					->where('ipaddress_dirs.parent_id', '=', $iParent_Id);

				$oIpaddress_Dir = $oIpaddress_Dirs->getByName($sDirName, FALSE);

				if (is_null($oIpaddress_Dir))
				{
					$oIpaddress_Dir = Core_Entity::factory('Ipaddress_Dir');
					$oIpaddress_Dir
						->parent_id($iParent_Id)
						->name($sDirName)
						->save();
				}

				$iParent_Id = $oIpaddress_Dir->id;
			}
		}

		$oIpaddress = Core_Entity::factory('Ipaddress')->getByComment($aContent['comment'], FALSE);

		is_null($oIpaddress)
			&& $oIpaddress = Core_Entity::factory('Ipaddress')->getByIp($aContent['ip'], FALSE);

		if (is_null($oIpaddress))
		{
			$oIpaddress = Core_Entity::factory('Ipaddress');
			$oIpaddress->ipaddress_dir_id = $iParent_Id;
			$oIpaddress->banned = 0; // $aContent['banned'];
		}

		$oIpaddress->ip = $aContent['ip'];
		$oIpaddress->deny_access = $aContent['deny_access'];
		$oIpaddress->deny_backend = $aContent['deny_backend'];
		$oIpaddress->no_statistic = $aContent['no_statistic'];
		$oIpaddress->comment = $aContent['comment'];
		$oIpaddress->save();

		return $this;
	}
}