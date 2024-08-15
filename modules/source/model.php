<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Source_Model
 *
 * @package HostCMS
 * @subpackage Source
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Source_Model extends Core_Entity
{
	/**
	 * Disable markDeleted()
	 * @var mixed
	 */
	protected $_marksDeleted = NULL;
}