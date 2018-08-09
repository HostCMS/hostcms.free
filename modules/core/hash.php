<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Hash algorithm
 *
 * @package HostCMS
 * @subpackage Core\Hash
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
abstract class Core_Hash
{
	/**
	 * Calculate hash
	 * @param string $value
	 * @return string
	 */
	abstract public function hash($value);

	/**
	 * Hash salt
	 */
	protected $_salt = NULL;

	/**
	 * Set salt
	 * @param string $salt hash salt
	 * @return Core_Hash
	 */
	public function salt($salt)
	{
		$this->_salt = $salt;
		return $this;
	}

	/**
	 * Create and return a Hash object for type $type
	 * @param string $type hash function name
	 * <code>
	 * $oCore_Hash_Sha1 = Core_Hash::factory('sha1');
	 * </code>
	 * @return object
	 */
	static public function factory($type)
	{
		$hash = __CLASS__ . '_' . ucfirst($type);
		return new $hash();
	}

	/**
	 * The singleton instance.
	 * @var mixed
	 */
	static protected $_instance = NULL;

	/**
	 * Register an existing instance as a singleton.
	 * @return object
	 */
	static public function instance()
	{
		$aConfig = Core::$config->get('core_hash');

		$hashName = Core_Array::get($aConfig, 'hash');
		$salt = Core_Array::get($aConfig, 'salt');
		self::$_instance = self::factory($hashName)->salt($salt);

		return self::$_instance;
	}
}