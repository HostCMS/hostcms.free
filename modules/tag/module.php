<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Tag Module.
 *
 * @package HostCMS
 * @subpackage Tag
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Tag_Module extends Core_Module
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
	public $date = '2017-09-07';

	/**
	 * Module name
	 * @var string
	 */
	protected $_moduleName = 'tag';
	
	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		
		$this->menu = array(
			array(
				'sorting' => 200,
				'block' => 3,
				'ico' => 'fa fa-tags',
				'name' => Core::_('Tag.menu'),
				'href' => "/admin/tag/index.php",
				'onclick' => "$.adminLoad({path: '/admin/tag/index.php'}); return false"
			)
		);
	}
}