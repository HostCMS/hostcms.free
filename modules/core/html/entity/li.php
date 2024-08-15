<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * li entity
 *
 * @package HostCMS
 * @subpackage Core\Html
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Core_Html_Entity_Li extends Core_Html_Entity
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'type',
		'value',
		'liValue'
	); 
	
	/**
	 * Skip properties
	 * @var array
	 */
	protected $_skipProperties = array(
		'liValue' // идет в значение <li>
	);
	
	/**
	 * Executes the business logic.
	 */
	public function execute()
	{
		$aAttr = $this->getAttrsString();

		echo PHP_EOL;

		// htmlspecialchars((string) $this->value) - acronym
		?><li <?php echo implode(' ', $aAttr) ?>><?php echo $this->liValue?><?php
		parent::execute();
		?></li><?php
	}
}