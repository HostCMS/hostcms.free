<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Chartaccount_Cashflow_Controller.
 *
 * @package HostCMS
 * @subpackage Chartaccount
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Chartaccount_Cashflow_Controller
{
	/**
	 * Fill cashflow list
	 * @return array
	 */
	static public function fillCashflowList()
	{
		$aReturn = array(' … ');

		$oSite = Core_Entity::factory('Site', CURRENT_SITE);

		$oChartaccount_Cashflows = $oSite->Chartaccount_Cashflows;
		$oChartaccount_Cashflows->queryBuilder()
			->clearOrderBy()
			->orderBy('chartaccount_cashflows.id');

		$aChartaccount_Cashflows = $oChartaccount_Cashflows->findAll(FALSE);
		foreach ($aChartaccount_Cashflows as $oChartaccount_Cashflow)
		{
			$aReturn[$oChartaccount_Cashflow->id] = $oChartaccount_Cashflow->name . ' [' . $oChartaccount_Cashflow->id . ']';
		}

		return $aReturn;
	}
}