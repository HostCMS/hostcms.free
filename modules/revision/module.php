<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Revision Module.
 *
 * @package HostCMS
 * @subpackage Revision
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Revision_Module extends Core_Module
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
	public $date = '2017-06-14';

	/**
	 * Module name
	 * @var string
	 */
	protected $_moduleName = 'revision';

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();

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
	}
}