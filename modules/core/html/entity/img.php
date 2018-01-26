<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * img entity
 *
 * @package HostCMS
 * @subpackage Core\Html
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Core_Html_Entity_Img extends Core_Html_Entity
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'align',
		'alt',
		'border',
		'height',
		'hspace',
		'ismap',
		'longdesc',
		'src',
		'vspace',
		'width',
		'usemap'
	);

	/**
	 * Skip properties
	 * @var array
	 */
	protected $_skipProperies = array(
		'value'
	);

	/**
	 * Executes the business logic.
	 */
	public function execute()
	{
		$aAttr = $this->getAttrsString();
		
		echo PHP_EOL;
		
		?><img <?php echo implode(' ', $aAttr) ?> /><?php
	}
}