<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Chartaccount Module.
 *
 * Типы документов:
 * 70 - Chartaccount_Operation_Model
 *
 * @package HostCMS
 * @subpackage Chartaccount
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Chartaccount_Module extends Core_Module
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
	public $date = '2023-07-17';

	/**
	 * Module name
	 * @var string
	 */
	protected $_moduleName = 'chartaccount';

	/**
	 * Get Module's Menu
	 * @return array
	 */
	public function getMenu()
	{
		$this->menu = array(
			array(
				'sorting' => 50,
				'block' => 1,
				'ico' => 'fa-solid fa-file-invoice',
				'name' => Core::_('Chartaccount.menu'),
				'href' => "/admin/chartaccount/index.php",
				'onclick' => "$.adminLoad({path: '/admin/chartaccount/index.php'}); return false"
			)
		);

		return parent::getMenu();
	}
}