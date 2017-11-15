<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * XSL Module.
 *
 * @package HostCMS
 * @subpackage Xsl
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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
	public $date = '2017-11-10';

	/**
	 * Module name
	 * @var string
	 */
	protected $_moduleName = 'xsl';
	
	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();

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
	}
}