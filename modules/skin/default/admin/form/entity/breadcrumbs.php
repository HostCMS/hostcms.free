<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Admin forms.
 *
 * @package HostCMS
 * @subpackage Skin
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
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
	 * @hostcms-event Skin_Default_Admin_Form_Entity_Breadcrumbs.onBeforeExecute
	 * @hostcms-event Skin_Default_Admin_Form_Entity_Breadcrumbs.onAfterExecute
	 */
	public function execute()
	{
		Core_Event::notify(get_class($this) . '.onBeforeExecute', $this);

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

		Core_Event::notify(get_class($this) . '.onAfterExecute', $this);
	}
}