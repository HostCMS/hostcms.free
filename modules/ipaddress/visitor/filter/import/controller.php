<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Ipaddress_Visitor_Filter_Import_Controller
 *
 * @package HostCMS
 * @subpackage Ipaddress
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
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
		$oIpaddress_Visitor_Filter = Core_Entity::factory('Ipaddress_Visitor_Filter');
		$oIpaddress_Visitor_Filter->name = $aContent['name'];
		$oIpaddress_Visitor_Filter->ipaddress_visitor_filter_dir_id = $this->ipaddress_visitor_filter_dir_id;
		$oIpaddress_Visitor_Filter->json = $aContent['json'];
		$oIpaddress_Visitor_Filter->active = $aContent['active'];
		$oIpaddress_Visitor_Filter->mode = $aContent['mode'];
		$oIpaddress_Visitor_Filter->banned = $aContent['banned'];
		$oIpaddress_Visitor_Filter->ban_hours = $aContent['ban_hours'];
		$oIpaddress_Visitor_Filter->block_mode = $aContent['block_mode'];
		$oIpaddress_Visitor_Filter->sorting = $aContent['sorting'];
		$oIpaddress_Visitor_Filter->save();

		return $this;
	}
}