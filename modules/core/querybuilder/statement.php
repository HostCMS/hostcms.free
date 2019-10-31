<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Abstract class for statements
 *
 * @package HostCMS
 * @subpackage Core\Querybuilder
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2019 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
abstract class Core_QueryBuilder_Statement
{
	/**
	 * DataBase
	 * @var mixed
	 */
	protected $_dataBase = NULL;

	/**
	 * Constructor.
	 * @param array $args list of arguments
	 */
	public function __construct(array $args = array())
	{
		$this->setDataBase(Core_DataBase::instance());
		return $this;
	}

	/**
	 * Query without fetching and buffering the result rows
	 */
	protected $_unbuffered = FALSE;

	/**
	 * Query without fetching and buffering the result rows
	 * @param bool $unbuffered
	 * @return self
	 */
	public function unbuffered($unbuffered)
	{
		$this->_unbuffered = $unbuffered;

		$this->_dataBase
			->unbuffered($this->_unbuffered);

		return $this;
	}

	/**
	 * Set DataBase
	 *
	 * @param Core_DataBase $dataBase
	 * @return Core_QueryBuilder_Statement
	 */
	public function setDataBase(Core_DataBase $dataBase)
	{
		$this->_dataBase = $dataBase;
		return $this;
	}

	/**
	 * Execute query
	 *
	 * @param $sql SQL query
	 * @return Core_DataBase
	 */
	public function execute($sql = NULL)
	{
		if (is_null($sql))
		{
			$sql = $this->build();
		}

		// Set type of query
		$oDataBase = $this->_dataBase
			->setQueryType($this->_queryType)
			->query($sql);

		return $oDataBase;
	}

	/**
	 * Is $object Core_QueryBuilder_Select
	 *
	 * @param $object
	 * @return bool
	 */
	protected function _isObjectSelect($object)
	{
		return (is_object($object) && get_class($object) == 'Core_QueryBuilder_Select');
	}

	/**
	 * Quote columns
	 * @param array $array
	 * @return array
	 */
	protected function _quoteColumns(array $array)
	{
		foreach ($array as $key => $value)
		{
			$array[$key] = $this->_dataBase->quoteColumnName($value);
		}

		return array_unique($array);
	}

	/**
	 * Quote arary of values
	 * @param array $array
	 * @return array
	 */
	protected function _quoteValues(array $array)
	{
		$array = array_map(array($this->_dataBase, 'quote'), $array);

		return $array;
	}

	/**
	 * Clear LIMIT
	 * @return self
	 */
	public function clearLimit()
	{
		$this->_limit = NULL;
		return $this;
	}

	/**
	 * Clear OFFSET
	 * @return self
	 */
	public function clearOffset()
	{
		$this->_offset = NULL;
		return $this;
	}

	/**
	 * Build statement
	 */
	abstract public function build();

	/**
	 * Triggered when invoking inaccessible methods in an object context
	 * @param string $name method name
	 * @param array $arguments arguments
	 * @return mixed
	 */
	public function __call($name, $arguments)
	{
		throw new Core_Exception("The method '%methodName' does not exist in the class '%class'",
			array('%methodName' => $name, '%class' => __CLASS__));
	}

	/**
	 * Run when writing data to inaccessible properties
	 * @param string $property property name
	 * @param string $value property value
	 * @return self
	 */
	public function __set($property, $value)
	{
		throw new Core_Exception("The property '%property' does not exist in the class '%class'",
				array('%property' => $property, '%class' => __CLASS__));
	}
}