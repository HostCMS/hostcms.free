<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Revision Module.
 *
 * @package HostCMS
 * @subpackage Revision
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Revision_Module extends Core_Module_Abstract
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
	public $date = '2024-07-09';

	/**
	 * Module name
	 * @var string
	 */
	protected $_moduleName = 'revision';

	/**
	 * Options
	 * @var array
	 */
	protected $_options = array(
		'storeDays' => array(
			'type' => 'int',
			'default' => 60
		)
	);

	/**
	 * Get Module's Menu
	 * @return array
	 */
	public function getMenu()
	{
		$this->menu = array(
			array(
				'sorting' => 260,
				'block' => 3,
				'ico' => 'fa fa-mail-reply-all',
				'name' => Core::_('Revision.menu'),
				'href' => "/admin/revision/index.php",
				'onclick' => "$.adminLoad({path: '/admin/revision/index.php'}); return false"
			)
		);

		return parent::getMenu();
	}
}