<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Admin forms.
 *
 * @package HostCMS
 * @subpackage Skin
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2019 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Skin_Default_Admin_Form_Entity_Breadcrumbs extends Admin_Form_Entity
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'separator'
	);

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();

		$this->separator = '&nbsp;<span class="arrow_path">&#8594;</span>&nbsp;';
	}

	/**
	 * Executes the business logic.
	 */
	public function execute()
	{
		?><p><?php
		$count = count($this->_children);

		foreach ($this->_children as $key => $oAdmin_Form_Entity)
		{
			$oAdmin_Form_Entity->execute();

			if ($key < $count - 1)
			{
				echo $this->separator;
			}
		}
		?></p><?php
	}
}