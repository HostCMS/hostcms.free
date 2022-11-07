<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Admin_Form_Controller_View
 *
 * @package HostCMS
 * @subpackage Admin
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Admin_Form_Controller_View extends Core_Servant_Properties
{
	/**
	 * Form controller
	 * @var Admin_Form_Controller
	 * @ignore
	 */
	protected $_Admin_Form_Controller = NULL;

	/**
	 * Constructor
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 */
	public function __construct($oAdmin_Form_Controller)
	{
		parent::__construct();

		$this->_Admin_Form_Controller = $oAdmin_Form_Controller;

		$this->showFilter = $this->showChangeViews = $this->showPageSelector = $this->showPageNavigation = TRUE;
	}

	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'showFilter',
		'showChangeViews',
		'showPageSelector',
		'showPageNavigation'
	);
}