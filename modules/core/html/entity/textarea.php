<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * textarea entity
 *
 * @package HostCMS
 * @subpackage Core\Html
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Core_Html_Entity_Textarea extends Core_Html_Entity
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'accesskey',
		'cols',
		'disabled',
		'name',
		'readonly',
		'rows',
		'tabindex',
		'wrap'
	);

	/**
	 * Skip properties
	 * @var array
	 */
	protected $_skipProperies = array(
		'value' // идет в значение <textarea>
	);
	
	/**
	 * Executes the business logic.
	 */
	public function execute()
	{
		$aAttr = $this->getAttrsString();

		echo PHP_EOL;
		
		?><textarea <?php echo implode(' ', $aAttr) ?>><?php echo htmlspecialchars($this->value)?></textarea><?php
	}
}