<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * textarea entity
 *
 * @package HostCMS
 * @subpackage Core\Html
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Core_Html_Entity_Textarea extends Core_Html_Entity
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'value',
		'accesskey',
		'cols',
		'disabled',
		'name',
		'readonly',
		'rows',
		'tabindex',
		'wrap',
		'form'
	);

	/**
	 * Skip properties
	 * @var array
	 */
	protected $_skipProperties = array(
		'value' // идет в значение <textarea>
	);
	
	/**
	 * Executes the business logic.
	 */
	public function execute()
	{
		$aAttr = $this->getAttrsString();

		echo PHP_EOL;
		
		?><textarea <?php echo implode(' ', $aAttr) ?>><?php echo htmlspecialchars((string) $this->value)?></textarea><?php
	}
}