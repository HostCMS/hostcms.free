<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Tag Module.
 *
 * @package HostCMS
 * @subpackage Tag
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Tag_Module extends Core_Module
{
	/**
	 * Module version
	 * @var string
	 */
	public $version = '7.0';

	/**
	 * Module date
	 * @var date
	 */
	public $date = '2022-08-05';

	/**
	 * Module name
	 * @var string
	 */
	protected $_moduleName = 'tag';
	
	/**
	 * Get Module's Menu
	 * @return array
	 */
	public function getMenu()
	{
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

		return parent::getMenu();
	}
}