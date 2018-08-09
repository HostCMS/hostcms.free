<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Tag Module.
 *
 * @package HostCMS
 * @subpackage Tag
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Tag_Module extends Core_Module
{
	/**
	 * Module version
	 * @var string
	 */
	public $version = '6.8';

	/**
	 * Module date
	 * @var date
	 */
	public $date = '2018-04-24';

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