<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Ipaddress_Filter_Import_Controller
 *
 * @package HostCMS
 * @subpackage Ipaddress
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Ipaddress_Filter_Import_Controller extends Admin_Form_Action_Controller
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'content',
		'ipaddress_filter_dir_id'
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
				foreach ($aContent as $aIpaddress_Filter)
				{
					$this->_import($aIpaddress_Filter);
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
		$oIpaddress_Filter = Core_Entity::factory('Ipaddress_Filter');
		$oIpaddress_Filter->name = $aContent['name'];
		$oIpaddress_Filter->ipaddress_filter_dir_id = $this->ipaddress_filter_dir_id;
		$oIpaddress_Filter->json = $aContent['json'];
		$oIpaddress_Filter->active = $aContent['active'];
		$oIpaddress_Filter->mode = $aContent['mode'];
		$oIpaddress_Filter->banned = $aContent['banned'];
		$oIpaddress_Filter->block_ip = $aContent['block_ip'];
		$oIpaddress_Filter->sorting = $aContent['sorting'];
		$oIpaddress_Filter->save();

		return $this;
	}
}