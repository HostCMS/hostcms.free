<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Ipaddress_Visitor_Filter_Import_Controller
 *
 * @package HostCMS
 * @subpackage Ipaddress
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Ipaddress_Visitor_Filter_Import_Controller extends Admin_Form_Action_Controller
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'content',
		'ipaddress_visitor_filter_dir_id'
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
				foreach ($aContent as $aIpaddress_Visitor_Filter)
				{
					$this->_import($aIpaddress_Visitor_Filter);
				}
			}
		}

		return $this;
	}

	/**
	 * Import filters
	 * @param array $aContent
	 * @return self
	 */
	protected function _import(array $aContent = array())
	{
		$aExplodeDir = explode('/', $aContent['dirName']);

		$iParent_Id = $this->ipaddress_visitor_filter_dir_id;

		foreach ($aExplodeDir as $sDirName)
		{
			if ($sDirName != '')
			{
				$oIpaddress_Visitor_Filter_Dirs = Core_Entity::factory('Ipaddress_Visitor_Filter_Dir');
				$oIpaddress_Visitor_Filter_Dirs
					->queryBuilder()
					->where('ipaddress_visitor_filter_dirs.parent_id', '=', $iParent_Id);

				$oIpaddress_Visitor_Filter_Dir = $oIpaddress_Visitor_Filter_Dirs->getByName($sDirName, FALSE);

				if (is_null($oIpaddress_Visitor_Filter_Dir))
				{
					$oIpaddress_Visitor_Filter_Dir = Core_Entity::factory('Ipaddress_Visitor_Filter_Dir');
					$oIpaddress_Visitor_Filter_Dir
						->parent_id($iParent_Id)
						->name($sDirName)
						->save();
				}

				$iParent_Id = $oIpaddress_Visitor_Filter_Dir->id;
			}
		}


		$oIpaddress_Visitor_Filter = Core_Entity::factory('Ipaddress_Visitor_Filter')->getByName($aContent['name'], FALSE);

		if (is_null($oIpaddress_Visitor_Filter))
		{
			$oIpaddress_Visitor_Filter = Core_Entity::factory('Ipaddress_Visitor_Filter');
			$oIpaddress_Visitor_Filter->ipaddress_visitor_filter_dir_id = $iParent_Id;
			$oIpaddress_Visitor_Filter->banned = 0; //$aContent['banned'];
			$oIpaddress_Visitor_Filter->sorting = $aContent['sorting'];
		}

		$oIpaddress_Visitor_Filter->name = $aContent['name'];
		$oIpaddress_Visitor_Filter->description = isset($aContent['description']) ? $aContent['description'] : '';
		$oIpaddress_Visitor_Filter->json = $aContent['json'];
		$oIpaddress_Visitor_Filter->active = $aContent['active'];
		$oIpaddress_Visitor_Filter->mode = $aContent['mode'];
		$oIpaddress_Visitor_Filter->ban_hours = $aContent['ban_hours'];
		$oIpaddress_Visitor_Filter->block_mode = $aContent['block_mode'];
		$oIpaddress_Visitor_Filter->save();

		return $this;
	}
}