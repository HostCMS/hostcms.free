<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * label entity
 *
 * @package HostCMS
 * @subpackage Core\Html
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Core_Html_Entity_Label extends Core_Html_Entity
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'value',
		'accesskey',
		'for'
	);

	/**
	 * Skip properties
	 * @var array
	 */
	protected $_skipProperties = array(
		'value' // идет в значение <label>
	);

	/**
	 * Executes the business logic.
	 */
	public function execute()
	{
		$aAttr = $this->getAttrsString();

		echo PHP_EOL;

		/**
		 * Синтаксис
		 * <input id="идентификатор"><label for="идентификатор">Текст</label>
		 * <label><input type="..."> Текст</label>
		*/

		// htmlspecialchars((string) $this->value) - acronym
		?><label <?php echo implode(' ', $aAttr) ?>><?php
		parent::execute();
		?><?php echo $this->value?></label><?php
	}
}