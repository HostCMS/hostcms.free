<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Typograph Module.
 *
 * @package HostCMS
 * @subpackage Typograph
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Typograph_Module extends Core_Module
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
	public $date = '2023-03-01';

	/**
	 * Module name
	 * @var string
	 */
	protected $_moduleName = 'typograph';

	/**
	 * Options
	 * @var array
	 */
	protected $_options = array(
		'typograph' => array(
			'type' => 'checkbox',
			'default' => TRUE
		),
		'trailing_punctuation' => array(
			'type' => 'checkbox',
			'default' => TRUE
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
				'ico' => 'fa fa-paragraph',
				'name' => Core::_('typograph.menu'),
				'href' => "/admin/typograph/index.php",
				'onclick' => "$.adminLoad({path: '/admin/typograph/index.php'}); return false"
			)
		);

		return parent::getMenu();
	}
}