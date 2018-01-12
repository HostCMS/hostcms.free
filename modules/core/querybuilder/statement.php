<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Abstract class for statements
 *
 * @package HostCMS
 * @subpackage Core\Querybuilder
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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
	 * Build JOIN expression
	 *
	 * @param array $aJoins
	 * @return string The SQL query
	 */
	protected function _buildJoin(array $aJoins)
	{
		$sql = array();

		foreach ($aJoins as $aJoin)
		{
			list($type, $table, $column, $expression, $value, $additionalConditions) = $aJoin;

			$table = !is_object($table)
				? $this->_dataBase->quoteColumnName($table)
				: $table->build();

			if (!is_null($column))
			{
				if (is_null($expression) && is_null($value))
				{
					$condition = ' USING (' . $this->_dataBase->quoteColumnName($column) . ')';
				}
				else
				{
					if (is_null($value))
					{
						$expression = $expression !== 'IS' && $expression !== '='
							? 'IS NOT'
							: 'IS';

						// Escape value
						$value = $this->_dataBase->quote($value);
					}
					else
					{
						$value = $this->_dataBase->quoteColumnName($value);
					}

					$condition = ' ON ' . $this->_dataBase->quoteColumnName($column) . ' ' . $expression . ' ' . $value;

					if (is_array($additionalConditions) && count($additionalConditions) > 0)
					{
						reset($additionalConditions[0]);
						$key = key($additionalConditions[0]);

						// Warning: Добавить проверку $key на перечень доступных операций (AND, OR и т.д.)
						$condition .= ' ' . $key . ' ' . $this->_buildExpression($additionalConditions);
					}
				}
			}
			else
			{
				$condition = '';
			}

			$sql[] = $type . ' ' . $table . $condition;
		}

		return implode(" \n", $sql);
	}

	/**
	 * Quote columns
	 * @param array $array
	 * @return array
	 */
	protected function quoteColumns(array $array)
	{
		foreach ($array as $key => $value)
		{
			if (is_array($value) && count($value) == 2 && $this->_isObjectSelect($value[0]))
			{
				$value = '(' . $value[0]->build() . ') AS ' . $this->_dataBase->quoteColumnName($value[1]);
			}
			elseif ($this->_isObjectSelect($value))
			{
				$value = '(' . $value->build() . ')';
			}
			else
			{
				// Escape column name
				$value = $this->_dataBase->quoteColumnName($value);
			}

			$array[$key] = $value;
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
	 * @return Core_QueryBuilder_Select
	 */
	public function clearLimit()
	{
		$this->_limit = NULL;
		return $this;
	}

	/**
	 * Clear OFFSET
	 * @return Core_QueryBuilder_Select
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