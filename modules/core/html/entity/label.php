<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * label entity
 *
 * @package HostCMS
 * @subpackage Core\Html
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Core_Html_Entity_Label extends Core_Html_Entity
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'accesskey',
		'for'
	);

	/**
	 * Skip properties
	 * @var array
	 */
	protected $_skipProperies = array(
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

		// htmlspecialchars($this->value) - acronym
		?><label <?php echo implode(' ', $aAttr) ?>><?php
		parent::execute();
		?><?php echo $this->value?></label><?php
	}
}