<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * ul entity
 *
 * @package HostCMS
 * @subpackage Core\Html
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Core_Html_Entity_Ul extends Core_Html_Entity
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'type'
	);

	/**
	 * Executes the business logic.
	 */
	public function execute()
	{
		$aAttr = $this->getAttrsString();

		echo PHP_EOL;

		?><ul <?php echo implode(' ', $aAttr) ?>><?php
		parent::execute();
		?></ul><?php
	}
}