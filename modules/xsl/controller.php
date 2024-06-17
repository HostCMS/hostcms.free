<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * XSL
 *
 * @package HostCMS
 * @subpackage Xsl
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Xsl_Controller
{
	/**
	 * Get Languages
	 * @return array
	 */
	static public function getLngs()
	{
		$aConfig = Core_Config::instance()->get('xsl_config', array()) + array(
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