<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * form entity
 *
 * @package HostCMS
 * @subpackage Core\Html
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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
		'target'
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