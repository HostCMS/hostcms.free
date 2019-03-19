<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Source_Model
 *
 * @package HostCMS
 * @subpackage Source
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2019 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Source_Model extends Core_Entity
{
	/**
	 * Disable markDeleted()
	 * @var mixed
	 */
	protected $_marksDeleted = NULL;
}