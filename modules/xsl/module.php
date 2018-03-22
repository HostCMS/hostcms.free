<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * XSL Module.
 *
 * @package HostCMS
 * @subpackage Xsl
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Xsl_Module extends Core_Module
{
	/**
	 * Module version
	 * @var string
	 */
	public $version = '6.7';

	/**
	 * Module date
	 * @var date
	 */
	public $date = '2018-03-02';

	/**
	 * Module name
	 * @var string
	 */
	protected $_moduleName = 'xsl';

	/**
	 * Get Module's Menu
	 * @return array
	 */
	public function getMenu()
	{
		$this->menu = array(
			array(
				'sorting' => 100,
				'block' => 0,
				'ico' => 'fa fa-code',
				'name' => Core::_('Xsl.menu'),
				'href' => "/admin/xsl/index.php",
				'onclick' => "$.adminLoad({path: '/admin/xsl/index.php'}); return false"
			)
		);

		return parent::getMenu();
	}
}