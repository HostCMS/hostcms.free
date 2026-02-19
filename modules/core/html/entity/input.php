<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * input entity
 *
 * @package HostCMS
 * @subpackage Core\Html
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Core_Html_Entity_Input extends Core_Html_Entity
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'name',
		'align',
		'alt',
		'border',
		'checked',
		'disabled',
		'max',
		'maxlength',
		'min',
		'pattern',
		'placeholder',
		'readonly',
		'required',
		'size',
		'src',
		'tabindex',
		'type',
		'value',
		'form'
	);

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->type('text');
	}

	/**
	 * Executes the business logic.
	 */
	public function execute()
	{
		$aAttr = $this->getAttrsString();

		echo PHP_EOL;

		?><input <?php echo implode(' ', $aAttr) ?>/><?php
	}
}