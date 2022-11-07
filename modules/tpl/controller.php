<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Tpl
 *
 * @package HostCMS
 * @subpackage Tpl
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Tpl_Controller
{
	/**
	 * Get Languages
	 * @return array
	 */
	static public function getLngs()
	{
		$aConfig = Core_Config::instance()->get('tpl_config', array()) + array(
			'lngs' => array()
		);

		$aLngs = $aConfig['lngs'];

		$aRows = Site_Controller::instance()->getLngList();
		foreach ($aRows as $aRow)
		{
			if (!in_array($aRow['lng'], $aLngs))
			{
				$aLngs[] = $aRow['lng'];
			}
		}

		return $aLngs;
	}
}