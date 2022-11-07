<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * strong entity
 *
 * @package HostCMS
 * @subpackage Core\Html
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Core_Html_Entity_Strong extends Core_Html_Entity
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'value',
	);

	/**
	 * Skip properties
	 * @var array
	 */
	protected $_skipProperties = array(
		'value' // идет в значение <strong>
	);

	/**
	 * Executes the business logic.
	 */
	public function execute()
	{
		$aAttr = $this->getAttrsString();

		echo PHP_EOL;

		?><strong <?php echo implode(' ', $aAttr) ?>><?php echo $this->value?><?php
		parent::execute();
		?></strong><?php
	}
}