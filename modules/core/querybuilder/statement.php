<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Abstract class for statements
 *
 * @package HostCMS
 * @subpackage Core\Querybuilder
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
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
	 * Get DataBase
	 * @return Core_DataBase
	 */
	public function getDataBase()
	{
		return $this->_dataBase;
	}

	/**
	 * Execute query
	 *
	 * @param $sql SQL query
	 * @return Core_DataBase
	 */
	public function execute($sql = NULL)
	{
		is_null($sql)
			&& $sql = $this->build();

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
		foreach ($array as $key => $column)
		{
			if (is_array($column) && count($column) == 2 && $this->_isObjectSelect($column[0]))
			{
				$array[$key] = '(' . $column[0]->build() . ') AS ' . $this->_dataBase->quoteTableName($column[1]);
			}
			elseif ($this->_isObjectSelect($column))
			{
				$array[$key] = '(' . $column->build() . ')';
			}
			else
			{
				$array[$key] = $this->_dataBase->quoteColumnName($column);
			}
		}

		// prohibited, since the required order of the columns sometimes can be violated
		//$array = array_unique($array);
		
		return $array;
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
	 * Comment --
	 * @var array
	 */
	protected $_comment = array();

	/**
	 * Add comment
	 *
	 * http://dev.mysql.com/doc/refman/5.7/en/comments.html
	 *
	 * @param string $comment
	 * @return self
	 */
	public function comment($comment)
	{
		$this->_comment[] = $comment;
		
		return $this;
	}

	/**
	 * Build Comment
	 *
	 * @param array $aComment
	 * @return string|NULL The SQL query
	 */
	protected function _buildComment(array $aComment)
	{
		if (!empty($aComment))
		{
			$commentSql = '';
			
			foreach ($aComment as $sComment)
			{
				$commentSql .= "-- " . str_replace(array("\r", "\n", "\0", ''), '', $sComment) . "\n";
			}

			return $commentSql;
		}
	}

	/**
	 * Get Comment
	 * @return array
	 */
	public function getComment()
	{
		return $this->_comment;
	}

	/**
	 * Clear Comment
	 * @return self
	 */
	public function clearComment()
	{
		$this->_comment = NULL;
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