<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * form entity
 *
 * @package HostCMS
 * @subpackage Core\Html
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
 */
class Core_Html_Entity_Form extends Core_Html_Entity
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'accept-charset',
		'action',
		'enctype',
		'method',
		'name',
		'novalidate',
		'target',
		'autocomplete'
	);

	/**
	 * Executes the business logic.
	 */
	public function execute()
	{
		$aAttr = $this->getAttrsString();

		echo PHP_EOL;
		
		?><form <?php echo implode(' ', $aAttr) ?>><?php
		parent::execute();
		?></form><?php
	}
}