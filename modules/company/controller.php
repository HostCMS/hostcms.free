<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Company_Controller
 *
 * @package HostCMS
 * @subpackage Company
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Company_Controller
{
	/**
	 * Build company array
	 * @param int $iSiteId site ID
	 * @return array
	 */
	static public function fillCompanies($iSiteId)
	{
		$aReturn = array();

		$iSiteId = intval($iSiteId);

		$oCompanies = Core_Entity::factory('Site', $iSiteId)->Companies;
		$oCompanies->queryBuilder()
			->orderBy('companies.sorting', 'ASC')
			->orderBy('companies.name', 'ASC');

		$aCompanies = $oCompanies->findAll(FALSE);
		foreach ($aCompanies as $oCompany)
		{
			$aReturn[$oCompany->id] = $oCompany->name;
		}

		return $aReturn;
	}

	/**
	 * Show popover
	 * @param object $object
	 * @param array $args
	 * @param array $options
	 */
	static public function onAfterShowContentPopover($object, $args, $options)
	{
		//$windowId = $oAdmin_Form_Controller->getWindowId();
		$windowId = $options[0]->getWindowId();

		?><script>
		$('#<?php echo $windowId?> [data-popover="hover"]').showCompanyPopover('<?php echo $windowId?>');
		</script><?php
	}
}