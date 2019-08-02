<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * th entity
 *
 * @package HostCMS
 * @subpackage Core\Html
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2019 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Core_Html_Entity_Th extends Core_Html_Entity_Td
{
	/**
	 * Executes the business logic.
	 */
	public function execute()
	{
		$aAttr = $this->getAttrsString();
		
		echo PHP_EOL;

		?><th <?php echo implode(' ', $aAttr) ?>><?php echo $this->value?><?php
		$this->executeChildren();
		?></th><?php
	}
}