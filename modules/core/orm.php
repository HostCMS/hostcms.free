<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Object-relational mapping. Implement an Active record pattern.
 *
 * Creating objects
 * <code>
 * // Create an empty object of model Book_Model
 * $object = Core_ORM::factory('Book');
 * </code>
 *
 * <code>
 * // Create an object of model Book_Model and load from database where primary key is 1
 * $object = Core_ORM::factory('Book', 1);
 * </code>
 *
 * Set property, the first way:
 * <code>
 * $object->value(123)->otherValue('my value');
 * </code>
 * The second way:
 * <code>
 * $object->value = 123;
 * $object->otherValue = 'my value';
 * </code>
 *
 * Saving object
 * <code>
 * // Change column and save
 * $object = Core_ORM::factory('Book', 1);
 * $object
 * 	->value(123)
 * 	->save();
 * </code>
 *
 * Finding objects
 * <code>
 * // Find all objects of model Book_Model
 * $aBooks = Core_ORM::factory('Book')->findAll();
 *
 * foreach ($aBooks as $oBook)
 * {
 * 	// do something
 * }
 * </code>
 *
 * <code>
 * // Find objects of model Book_Model with conditions
 * $object = Core_ORM::factory('Book');
 * $object->queryBuilder()
 * 	->where('value', '=', 99);
 * $aBooks = $object->findAll();
 *
 * foreach ($aBooks as $oBook)
 * {
 * 	// do something
 * }
 * </code>
 *
 * @package HostCMS
 * @subpackage Core
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2020 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Core_ORM
{
	/**
	 * Primary key
	 * @var string
	 */
	protected $_primaryKey = 'id';

	/**
	 * Model name, e.g. 'book' for 'Book_Model'
	 * @var mixed
	 */
	protected $_modelName = NULL;

	/**
	 * List of columns in model with values
	 * @var array
	 */
	protected $_modelColumns = array();

	/**
	 * Table name, e.g. 'books' for 'Book_Model'
	 * @var mixed
	 */
	protected $_tableName = NULL;

	/**
	 * List of columns in table
	 * @var array
	 */
	protected $_tableColumns = array();

	/**
	 * List of preloaded values those will set for new object which does not have primary key
	 * @var array
	 */
	protected $_preloadValues = array();

	/**
	 * List of skipped columns from table
	 * @var array
	 */
	protected $_skipColumns = array();

	/**
	 * List of changed columns
	 * @var array
	 */
	protected $_changedColumns = array();

	/**
	 * data-values, e.g. dataMyValue
	 * @var array
	 */
	protected $_dataValues = array();

	/**
	 * List of all relations are created by _relations() based on _hasOne, _hasMany and _belongsTo
	 * array('field_name' =>
			array(
				'type' => Type of relation: one|many
				'model' => Model name
				'foreign_key' => Foreign key
				'primary_key' => Primary key in the parent table
				'through' => Model name for many-to-many relation
				'through_table_name' => Table name for many-to-many relation
				'dependent_key' => Dependent child's key in "through" table
			)
	* )
	* @var array
	*/
	protected $_relations = array();

	/**
	 * One-to-many or many-to-many relations
	 *
	 * One-to-many relation
	 * <code>
	 * // Relation one-to-many for Book-Comments:
	 * protected $_hasMany = array('comment' => array());
	 * </code>
	 *
	 * <code>
	 * // Equivalence relation one-to-many for Book-Comments with detailed conditions:
	 * // comment - Model name
	 * // foreign_key - Foreign key
	 * protected $_hasMany = array('comment' => array(
			'foreign_key' => 'book_id'
	 * 	));
	 * </code>
	 *
	 * Many-to-many relation
	 * <code>
	 * // Relation many-to-many for Book-Comments through model 'books_comment':
	 * protected $_hasMany = array('comment' => array(
			'through' => 'books_comment'
	 * 	));
	 * </code>
	 *
	 * <code>
	 * // Relation many-to-many for Book-Comments through model 'books_comment' with detailed conditions:
	 * protected $_hasMany = array('comment' => array(
			'foreign_key' => 'book_id',
			'through' => 'books_comment',
			'dependent_key' => 'comment_id'
	 * 	));
	 * </code>
	 * @var array
	 */
	protected $_hasMany = array();

	/**
	 * One-to-one relations
	 *
	 * <code>
	 * // Relation one-to-one for Book-Comment:
	 * protected $_hasOne = array('comment' => array());
	 * </code>
	 *
	 * <code>
	 * // Equivalence relation one-to-one for Book-Comment with detailed conditions:
	 * // comment - Model name
	 * // foreign_key - Foreign key
	 * protected $_hasOne = array('comment' => array(
						'foreign_key' => 'book_id'
	 * 	));
	 * </code>
	 * @var array
	 */
	protected $_hasOne = array();

	/**
	 * Belongs to relations
	 *
	 * <code>
	 * // Belongs to relation for Comment-Book:
	 * protected $_belongsTo = array('book' => array());
	 * </code>
	 *
	 * <code>
	 * // Equivalence belongs to relation for Comment-Book with detailed conditions:
	 * // book - Model name
	 * // foreign_key - Foreign key
	 * // primary_key - Primary key in the parent table
	 * protected $_belongsTo = array('book' => array(
	 * 		'foreign_key' => 'book_id'
	 * 	));
	 * </code>
	 * @var array
	 */
	protected $_belongsTo = array();

	/**
	 * ORM config
	 * @var mixed
	 */
	static public $config = NULL;

	/**
	 * ORM cache
	 * @var Core_Cache
	 */
	static public $cache = NULL;

	/**
	 * ORM column cache
	 * @var Core_Cache
	 */
	static public $columnCache = NULL;

	/**
	 * ORM relation cache
	 * @var Core_Cache
	 */
	static public $relationCache = NULL;

	/**
	 * Objects cache for _hasOne and _belongsTo
	 * @var array
	 */
	protected $_relationCache = array();

	/**
	 * Relations cache for models
	 * @var array
	 */
	static protected $_relationModelCache = array();

	/**
	 * Columns cache for models
	 * @var array
	 */
	static protected $_columnCache = array();

	/**
	 * Columns cache for models default values
	 * @var array
	 */
	static protected $_columnCacheDefaultValues = array();

	/**
	 * Core_DataBase object
	 * @var array
	 */
	protected $_database = array();

	/**
	 * Select query builder
	 * @var Core_QueryBuilder_Select
	 */
	protected $_queryBuilder = NULL;

	/**
	 * Preload values have been set
	 */
	protected $_bSetPreloadValues = FALSE;

	/**
	 * Model has already been loaded from database
	 */
	protected $_loaded = FALSE;

	/**
	 * Model has already been saved into database
	 * NULL value used in the __set() for separate value settings through mysql_fetch_object()
	 */
	protected $_saved = NULL;

	/**
	 * Init has already been called
	 * @var boolean
	 */
	private $_init = FALSE;

	/**
	 * Default sorting for models
	 * <code>
	 * protected $_sorting = array(
		'tablename.sorting' => 'ASC'
	 * );
	 * </code>
	 * @var array
	 */
	protected $_sorting = array();

	/**
	 * Get primary key value
	 * @return mixed
	 */
	public function getPrimaryKey()
	{
		return $this->_modelColumns[$this->_primaryKey];
	}

	/**
	 * Get primary key name
	 * @return string
	 */
	public function getPrimaryKeyName()
	{
		return $this->_primaryKey;
	}

	/**
	 * Get table name
	 * @return string
	 */
	public function getTableName()
	{
		return $this->_tableName;
	}

	/**
	 * Delete object from database
	 *
	 * <code>
	 * // Delete object with lazy load
	 * Core_ORM::factory('Book', 1)->delete();
	 * </code>
	 * <code>
	 * // Delete object without load
	 * Core_ORM::factory('Book')->delete(1);
	 * </code>
	 * @param mixed $primaryKey primary key for deleting object
	 * @return Core_ORM
	 * @hostcms-event modelname.onBeforeDelete
	 * @hostcms-event modelname.onAfterDelete
	 */
	public function delete($primaryKey = NULL)
	{
		Core_Event::notify($this->_modelName . '.onBeforeDelete', $this, array($primaryKey));

		if (is_null($primaryKey))
		{
			$primaryKey = $this->getPrimaryKey();
		}

		if (!is_null($primaryKey))
		{
			$oQuery_Builder = Core_QueryBuilder::delete($this->_tableName);

			self::$_databaseDriver != 'default'
				&& $oQuery_Builder->setDataBase($this->getDatabase());

			$oQuery_Builder
				->where($this->_primaryKey, '=', $primaryKey)
				->execute();
		}

		Core_Event::notify($this->_modelName . '.onAfterDelete', $this, array($primaryKey));

		return $this;
	}

	/**
	 * Get Model CallsName
	 * @param $modelName Model name
	 * @return string
	 */
	static public function getClassName($modelName)
	{
		return ucfirst($modelName) . '_Model';
	}

	/**
	 * Create and return an object of model
	 * @param $modelName Model name
	 * @param $primaryKey Primary key
	 */
	static public function factory($modelName, $primaryKey = NULL)
	{
		$modelName = self::getClassName($modelName);

		if (!class_exists($modelName))
		{
			throw new Core_Exception("Model '%modelName' does not exist",
				array('%modelName' => $modelName));
		}

		return new $modelName($primaryKey);
	}

	/**
	 * Find object in database and load one. Use clear() before find() with conditions!
	 * @param mixed $primaryKey default NULL
	 * @param boolean $bCache use cache
	 * <code>
	 * // Find an object and load (without lazy load)
	 * // If an object is not found, primary key sets NULL
	 * $oBook = Core_ORM::factory('Book')->find(1);
	 * </code>
	 * @return Core_ORM
	 */
	public function find($primaryKey = NULL, $bCache = TRUE)
	{
		// Clear relation cache
		$this->clearRelationCache();

		if (!is_null($primaryKey))
		{
			// Clear object
			$this->clear();

			$this->queryBuilder()
				// in $this->clear() // ->clear() // clear if find by PK
				->where($this->_tableName . '.' . $this->_primaryKey, '=', $primaryKey);
		}

		$this->queryBuilder()
			->from($this->_tableName)
			->asAssoc();

		$Core_DataBase = $this->queryBuilder()->execute();

		// May contain primary key. If record not found, primary key will NULL
		if (array_key_exists($this->_primaryKey, $this->_modelColumns))
		{
			unset($this->_modelColumns[$this->_primaryKey]);
		}

		$aCurrent = $Core_DataBase->current($bCache);

		if ($aCurrent !== FALSE)
		{
			/* Use clear() before find() with conditions! */
			$this->setValues($this->_modelColumns + $aCurrent);

			$Core_DataBase->free();

			// Marks saved
			!$this->changed() && $this->_saved = TRUE;

			$this->_loaded = TRUE;
		}
		else
		{
			// If find() hasn't found
			// Load list of columns, set default values, set preload values
			$this->_loadColumns()->_setDefaultValues()->_setPreloadValues();
		}

		return $this;
	}

	/**
	 * Find all objects
	 * @param boolean $bCache use cache, default TRUE
	 * <code>
	 * // Find objects
	 * $aBooks = Core_ORM::factory('Book')->findAll();
	 * foreach ($aBooks as $oBook)
	 * {
	 * 	var_dump($oBook->id);
	 * }
	 * </code>
	 * @return array
	 * @hostcms-event modelname.onBeforeFindAll
	 * @hostcms-event modelname.onAfterFindAll
	 */
	public function findAll($bCache = TRUE)
	{
		Core_Event::notify($this->_modelName . '.onBeforeFindAll', $this);

		$oSelect = $this->queryBuilder()
			->from($this->_tableName)
			->asObject(get_class($this));

		// Sets ORDER BY
		if (!empty($this->_sorting)
			&& !is_null($oSelect->getOrderBy())
			&& !count($oSelect->getOrderBy())
		)
		{
			foreach ($this->_sorting as $column => $direction)
			{
				$oSelect->orderBy($column, $direction);
			}
		}

		$sql = $oSelect->build();

		if ($bCache)
		{
			$inCache = self::$cache->get($sql, __CLASS__);
			if (!is_null($inCache))
			{
				return $inCache;
			}
		}

		$Core_DataBase = $oSelect->execute($sql);
		$result = $Core_DataBase->result($bCache);

		// Cache
		$bCache && self::$cache->set($sql, $result, __CLASS__);

		// moved to result()
		//$Core_DataBase->free();

		Core_Event::notify($this->_modelName . '.onAfterFindAll', $this);

		return $result;
	}

	/**
	 * Retrieve a small chunk and feeds each one into $callback for processing. It stops looping when $callback returns FALSE
	 * @param int $count chunk size
	 * @param callable $callback
	 * @param boolean $bCache use cache
	 * @return boolean
	 */
	public function chunk($count, $callback, $bCache = TRUE)
	{
		$offset = $step = 0;

		do {
			$this
				->queryBuilder()
				->limit($count)
				->offset($offset);

			$aObjects = $this->findAll($bCache);

			if ($callback($aObjects, $step++) === FALSE)
			{
				return FALSE;
			}

			$offset += $count;
		} while (count($aObjects) == $count);

		return TRUE;
	}

	/**
	 * Delete all object
	 * @param boolean $bCache use cache
	 * <code>
	 * Core_ORM::factory('Book')->Comments->deleteAll();
	 * </code>
	 * @return Core_ORM
	 */
	public function deleteAll($bCache = TRUE)
	{
		$limit = 100;

		$this->queryBuilder()
			->limit($limit)
			->offset(0);

		do {
			$aObjects = $this->findAll($bCache);
			foreach ($aObjects as $oObject)
			{
				$oObject->delete();
			}
		} while (count($aObjects) == $limit);

		return $this;
	}

	/**
	 * Get count object
	 * @param boolean $bCache use cache, default TRUE
	 * @param string $fieldName default '*'
	 * @param boolean $distinct default FALSE
	 * @return int|FALSE
	 * <code>
	 * $iCount = Core_ORM::factory('Book')->getCount();
	 * var_dump($iCount);
	 * </code>
	 */
	public function getCount($bCache = TRUE, $fieldName = '*', $distinct = FALSE)
	{
		$aRow = $this->queryBuilder()
			->clearSelect()
			->clearLimit()
			->clearOffset()
			->select(array($fieldName == '*' && !$distinct
					? 'COUNT(*)'
					: Core_QueryBuilder::expression('COUNT(' . ($distinct ? 'DISTINCT ' : '') . $this->getDatabase()->quoteColumnName($fieldName) . ')')
				, 'count')
			)
			->from($this->_tableName)
			->execute()
			->asAssoc()
			->current($bCache);

		return is_array($aRow) ? $aRow['count'] : FALSE;
	}

	/**
	 * Get fist entity, ordered by primary key
	 * @param boolean $bCache use cache, default TRUE
	 * @return NULL|Core_ORM
	 * <code>
	 * $mObject = Core_ORM::factory('Book')->getFirst();
	 * if (!is_null($mObject))
	 * {
	 * 	echo $mObject;
	 * }
	 * </code>
	 */
	public function getFirst($bCache = TRUE)
	{
		$this->queryBuilder()
			->clearOrderBy()
			->orderBy($this->_tableName . '.' . $this->_primaryKey, 'ASC')
			->limit(1);

		$aObjects = $this->findAll($bCache);
		return isset($aObjects[0]) ? $aObjects[0] : NULL;
	}

	/**
	 * Get last entity, ordered by primary key
	 * @param boolean $bCache use cache, default TRUE
	 * @return NULL|Core_ORM
	 * <code>
	 * $mObject = Core_ORM::factory('Book')->getLast();
	 * if (!is_null($mObject))
	 * {
	 * 	echo $mObject;
	 * }
	 * </code>
	 */
	public function getLast($bCache = TRUE)
	{
		$this->queryBuilder()
			->clearOrderBy()
			->orderBy($this->_tableName . '.' . $this->_primaryKey, 'DESC')
			->limit(1);

		$aObjects = $this->findAll($bCache);
		return isset($aObjects[0]) ? $aObjects[0] : NULL;
	}

	/**
	 * Add related object. If main object does not save, it will save.
	 * @param Core_ORM $model
	 * @param string $relation
	 * @return Core_ORM
	 * @hostcms-event modelname.onBeforeAdd
	 * @hostcms-event modelname.onAfterAdd
	 *
	 * <code>
	 * $object = Core_ORM::factory('Main', 1);
	 *
	 * $child = Core_ORM::factory('Child')
	 * $child->value1 = 'some value1';
	 * $child->value2 = 'some value2';
	 *
	 * $object->add($child);
	 * </code>
	 */
	public function add(Core_ORM $model, $relation = NULL)
	{
		Core_Event::notify($this->_modelName . '.onBeforeAdd', $this);

		if (is_null($relation))
		{
			$modelName = $model->getModelName();

			// One-to-one or Belongs to relation
			if (isset($this->_relations[$modelName]))
			{
				$relation = $modelName;
			}
			// One-to-many or Many-to-many relation
			elseif (($modelNamePlural = Core_Inflection::getPlural($modelName))
			&& isset($this->_relations[$modelNamePlural]))
			{
				$relation = $modelNamePlural;
			}
			else
			{
				throw new Core_Exception("The relation '%modelName' does not exist",
					array('%modelName' => $modelName));
			}
		}

		$foreignKey = $this->_relations[$relation]['foreign_key'];

		if ($this->_relations[$relation]['type'] == 'belong')
		{
			$model->save();

			// Set foreign key value
			$this->$foreignKey = $model->getPrimaryKey();
			$this->save();
		}
		else
		{
			// Save main object
			$this->save();

			switch ($this->_relations[$relation]['through'])
			{
				// One-to-one or one-to-many
				case NULL:
					// Set foreign key value
					$model->$foreignKey = $this->getPrimaryKey();
					$model->save();
				break;
				// Many-to-many
				default:
					$model->save();

					$throughModel = self::factory($this->_relations[$relation]['through']);
					$throughModel->$foreignKey = $this->getPrimaryKey();

					$dependent_key = $this->_relations[$relation]['dependent_key'];
					$throughModel->$dependent_key = $model->getPrimaryKey();
					$throughModel->save();
				break;
			}

			$model->clearRelationCache();
		}

		Core_Event::notify($this->_modelName . '.onAfterAdd', $this);

		return $this;
	}

	/**
	 * Remove related object. If main object does not save, it will save.
	 * @param Core_ORM $model
	 * @param string $relation
	 * @return Core_ORM
	 * @hostcms-event modelname.onBeforeRemove
	 * @hostcms-event modelname.onAfterRemove
	 */
	public function remove(Core_ORM $model, $relation = NULL)
	{
		Core_Event::notify($this->_modelName . '.onBeforeRemove', $this);

		if (is_null($relation))
		{
			$modelName = $model->getModelName();

			// One-to-one or Belongs to relation
			if (isset($this->_relations[$modelName]))
			{
				$relation = $modelName;
			}
			// One-to-many or Many-to-many relation
			elseif (($modelNamePlural = Core_Inflection::getPlural($modelName))
			&& isset($this->_relations[$modelNamePlural]))
			{
				$relation = $modelNamePlural;
			}
			else
			{
				throw new Core_Exception("The relation '%modelName' does not exist",
					array('%modelName' => $modelName));
			}
		}

		$foreignKey = $this->_relations[$relation]['foreign_key'];

		if ($this->_relations[$relation]['type'] == 'belong')
		{
			$model->save();

			// Set foreign key value
			$this->$foreignKey = NULL;
			$this->save();
		}
		else
		{
			// Save main object
			$this->save();

			switch ($this->_relations[$relation]['through'])
			{
				// One-to-one or one-to-many
				case NULL:
					// Set foreign key value
					$model->$foreignKey = NULL;
					$model->save();
				break;
				// Many-to-many
				default:
					$model->save();

					$throughModel = self::factory($this->_relations[$relation]['through']);

					$dependent_key = $this->_relations[$relation]['dependent_key'];

					$throughModel->queryBuilder()
						->where($foreignKey, '=', $this->getPrimaryKey())
						->where($dependent_key, '=', $model->getPrimaryKey());

					$throughModel = $throughModel->find();

					if (!is_null($throughModel->id))
					{
						$throughModel->delete();
					}
				break;
			}
		}

		Core_Event::notify($this->_modelName . '.onAfterRemove', $this);

		return $this;
	}

	/**
	 * Check model has been changed.
	 * @return bool
	 */
	public function changed()
	{
		return count($this->_changedColumns) > 0;
	}

	/**
	 * Constructor.
	 * @param string $primaryKey
	 */
	public function __construct($primaryKey = NULL)
	{
		// Initialization
		$this->_init();

		// Calculate relation
		$this->_relations();

		// Use lazy load, set just primary key. May calls after __set
		if (!isset($this->_modelColumns[$this->_primaryKey]))
		{
			$this->_modelColumns[$this->_primaryKey] = $primaryKey;
		}

		// Check saved status
		$this->_saved = !is_null($this->_modelColumns[$this->_primaryKey]);

		// Columns were changed throw mysql_fetch_object()
		$this->changed() && $this->_loaded = TRUE;

		// Clear changed columns because they may have marked by mysql_fetch_object()
		$this->_changedColumns = array();

		/* if object without PK, call _setPreloadValues() will be in _load()
		if ($this->isEmptyPrimaryKey())
		{
			$this->_setPreloadValues();
		}*/

		return $this;
	}

	/**
	 * Calculate model's relation if data does not exist in self::$_relationModelCache.
	 * @return self
	 * @hostcms-event modelname.onAfterLoadModelCache
	 * @hostcms-event modelname.onAfterRelations
	 */
	protected function _relations()
	{
		if (isset(self::$_relationModelCache[$this->_modelName]))
		{
			$this->_relations = self::$_relationModelCache[$this->_modelName];
			//$this->_hasOne = $this->_hasMany = $this->_belongsTo = array();

			Core_Event::notify($this->_modelName . '.onAfterLoadModelCache', $this);

			return $this;
		}

		// Relation cache
		$bCache = Core::moduleIsActive('cache');

		if ($bCache)
		{
			$cacheName = 'Core_ORM_RelationCache';
			$inCache = $bCache
				? self::$relationCache->get($this->_modelName, $cacheName)
				: NULL;

			if (is_array($inCache))
			{
				$this->_relations = self::$_relationModelCache[$this->_modelName] = $inCache;

				// Clear belongs to
				// Forbidden! If call clearRelationModelCache() calculate needs it arrays
				//$this->_hasOne = $this->_hasMany = $this->_belongsTo = array();

				return $this;
			}
		}

		foreach ($this->_hasOne as $modelName => $condition)
		{
			$model = isset($condition['model']) ? strtolower($condition['model']) : $modelName;

			$issetThrough = isset($condition['through']) && !is_null($condition['through']);
			$dependent_key = $issetThrough
				? $model . '_' . $this->_primaryKey
				: NULL;

			$this->_relations[$modelName] = $condition + array(
				'type' => 'one',
				'model' => $model,
				'foreign_key' => $this->_modelName . '_' . $this->_primaryKey,
				'through' => NULL,
				'through_table_name' => $issetThrough ? Core_Inflection::getPlural($condition['through']) : NULL,
				'dependent_key' => $dependent_key
			);
		}

		// Clear has one
		//$this->_hasOne = array();

		foreach ($this->_hasMany as $modelName => $condition)
		{
			$relationName = Core_Inflection::getPlural($modelName);
			$model = isset($condition['model']) ? strtolower($condition['model']) : $modelName;

			$issetThrough = isset($condition['through']) && !is_null($condition['through']);
			$dependent_key = $issetThrough
				? $model . '_' . $this->_primaryKey
				: NULL;

			$this->_relations[$relationName] = $condition + array(
				'type' => 'many',
				'model' => $model,
				'foreign_key' => $this->_modelName . '_' . $this->_primaryKey,
				'through' => NULL,
				'through_table_name' => $issetThrough ? Core_Inflection::getPlural($condition['through']) : NULL,
				'dependent_key' => $dependent_key
			);
		}

		// Clear has many
		//$this->_hasMany = array();

		foreach ($this->_belongsTo as $modelName => $condition)
		{
			$model = isset($condition['model']) ? strtolower($condition['model']) : $modelName;
			$this->_relations[$modelName] = $condition + array(
				'type' => 'belong',
				'model' => $model,
				'primary_key' => $this->_primaryKey,
				'foreign_key' => $model . '_' . $this->_primaryKey,
				'through' => NULL,
				'through_table_name' => NULL
			);
		}

		// Clear belongs to
		//$this->_belongsTo = array();

		$bCache
			&& is_null($inCache)
			&& self::$relationCache->set($this->_modelName, $this->_relations, $cacheName);

		Core_Event::notify($this->_modelName . '.onAfterRelations', $this);

		// Add into cache
		self::$_relationModelCache[$this->_modelName] = $this->_relations;

		return $this;
	}

	/**
	 * Get model name, e.g. 'book' for 'Book_Model'
	 * @return string
	 */
	public function getModelName()
	{
		if (is_null($this->_modelName))
		{
			// Cut "_Model"
			return strtolower(substr(get_class($this), 0, -6));
		}

		return $this->_modelName;
	}

	/**
	 * Database driver's name, default is 'default'
	 * @var string
	 */
	static protected $_databaseDriver = 'default';

	/**
	 * Set name of Database Driver
	 * @param string $name driver's name
	 */
	static public function setDatabaseDriver($name)
	{
		self::$_databaseDriver = $name;
	}

	/**
	 * Get Database
	 * @return Core_DataBase
	 */
	protected function getDatabase()
	{
		if (!isset($this->_database[self::$_databaseDriver]))
		{
			$this->_database[self::$_databaseDriver] = Core_DataBase::instance(self::$_databaseDriver);
		}

		return $this->_database[self::$_databaseDriver];
	}

	/**
	 * Model initialization
	 * @return Core_ORM
	 */
	protected function _init()
	{
		if (!$this->_init)
		{
			/*if (is_null($this->_database))
			{
				$this->_database = Core_DataBase::instance(self::$_databaseDriver);
			}*/

			// is_null() into getModelName()
			$this->_modelName = $this->getModelName();

			if (is_null($this->_tableName))
			{
				$this->_tableName = Core_Inflection::getPlural($this->_modelName);
			}

			if (!empty($this->_skipColumns))
			{
				$this->_skipColumns = array_combine($this->_skipColumns, $this->_skipColumns);
			}

			if (is_null(self::$config))
			{
				self::$config = Core::$config->get('core_orm') + array(
					'cache' => 'memory',
					'columnCache' => 'memory',
					'relationCache' => 'memory'
				);

				self::$cache = Core_Cache::instance(self::$config['cache']);
				self::$columnCache = Core_Cache::instance(self::$config['columnCache']);
				self::$relationCache = Core_Cache::instance(self::$config['relationCache']);
			}

			$this->_init = TRUE;
		}

		return $this;
	}

	/**
	 * Check if model is loaded
	 * @return Core_ORM
	 */
	public function loaded()
	{
		return $this->_loaded;
	}

	/**
	 * Load values from database
	 * @return Core_ORM
	 */
	public function load()
	{
		return $this->_load();
	}

	/**
	 * Load values from database
	 * @return Core_ORM
	 */
	protected function _load()
	{
		if (!$this->loaded())
		{
			$this->_onBeforeLoad();

			if (!$this->isEmptyPrimaryKey())
			{
				$this->_loaded = TRUE;

				// Lazy load may be after __set
				//$this->_changedColumns = array();

				// Sets condition by id here
				$this->queryBuilder()
					->clear() // clear if find by PK
					->where($this->_tableName . '.' . $this->_primaryKey, '=', $this->getPrimaryKey());

				return $this->find();
			}
			else
			{
				// Load list of columns, set default values, set preload values
				$this->_loadColumns()->_setDefaultValues()->_setPreloadValues();
			}

			$this->_onAfterLoad();
		}

		return $this;
	}

	/**
	 * Run before loading of the object
	 */
	protected function _onBeforeLoad(){}

	/**
	 * Run after loading of the object
	 */
	protected function _onAfterLoad(){}

	/**
	 * Get query builder for select
	 * @return Core_QueryBuilder_Select
	 */
	public function queryBuilder()
	{
		if (is_null($this->_queryBuilder))
		{
			$this->_queryBuilder = Core_QueryBuilder::select($this->_tableName . '.*');

			self::$_databaseDriver != 'default'
				&& $this->_queryBuilder->setDataBase($this->getDatabase());
		}

		return $this->_queryBuilder;
	}

	/**
	 * Clear self::$_columnCache and Core_ORM_ColumnCache
	 */
	static public function clearColumnCache()
	{
		self::$_columnCache = self::$_columnCacheDefaultValues = array();

		if (Core::moduleIsActive('cache'))
		{
			self::$columnCache->deleteAll('Core_ORM_ColumnCache');
		}
	}

	/**
	 * Clear self::$_relationModelCache and Core_ORM_RelationCache
	 * @param mixed $modelName default NULL
	 */
	static public function clearRelationModelCache($modelName = NULL)
	{
		if (!is_null($modelName))
		{
			if (isset(self::$_relationModelCache[$modelName]))
			{
				unset(self::$_relationModelCache[$modelName]);
			}
		}
		else
		{
			self::$_relationModelCache = array();

			if (Core::moduleIsActive('cache'))
			{
				self::$relationCache->deleteAll('Core_ORM_RelationCache');
			}
		}
	}

	/**
	 * Load self::$_columnCacheDefaultValues for current model
	 * @return self
	 */
	protected function _loadColumnCacheDefaultValues()
	{
		// Fill self::$_columnCacheDefaultValues
		self::$_columnCacheDefaultValues[$this->_modelName] = array();
		foreach (self::$_columnCache[$this->_modelName] as $key => $aColumn)
		{
			!is_null($aColumn['default'])
				&& $aColumn['default'] != 'CURRENT_TIMESTAMP'
				&& $aColumn['default'] != 'now()'
				&& $aColumn['default'] != 'LOCALTIME'
				&& $aColumn['default'] != 'localtime()'
				&& $aColumn['default'] != 'LOCALTIMESTAMP'
				&& $aColumn['default'] != 'localtimestamp()'
				&& $aColumn['default'] != 'current_timestamp()' // MariaDB
				&& self::$_columnCacheDefaultValues[$this->_modelName][$key] = $aColumn['default'];
		}

		return $this;
	}

	/**
	 * Get tableColumns
	 * @return array
	 */
	public function getTableColumns()
	{
		$this->_loadColumns();

		return $this->_tableColumns;
	}

	/**
	 * Load columns list for model
	 * @return Core_ORM
	 */
	protected function _loadColumns()
	{
		if (empty($this->_tableColumns))
		{
			// If model is called by mysql_fetch_object(), __construct() is called automatically after initialization of an object and it's property
			$this->_init();

			if (isset(self::$_columnCache[$this->_modelName]))
			{
				$this->_tableColumns = self::$_columnCache[$this->_modelName];
			}
			else
			{
				$bCache = Core::moduleIsActive('cache');

				$cacheName = 'Core_ORM_ColumnCache';
				$inCache = $bCache
					? self::$columnCache->get($this->_modelName, $cacheName)
					: NULL;

				self::$_columnCache[$this->_modelName] = $this->_tableColumns = is_array($inCache)
					? $inCache
					: $this->getDatabase()->getColumns($this->_tableName);

				$bCache
					&& is_null($inCache)
					&& self::$columnCache->set($this->_modelName, $this->_tableColumns, $cacheName);

				$this->_loadColumnCacheDefaultValues();
			}

			/*$this->_tableColumns = isset(self::$_columnCache[$this->_modelName])
				? self::$_columnCache[$this->_modelName]
				: self::$_columnCache[$this->_modelName] = $this->getDatabase()->getColumns($this->_tableName);*/
		}

		return $this;
	}

	/**
	 * Set preload values from _preloadValues
	 * @return Core_ORM
	 */
	protected function _setPreloadValues()
	{
		// Предварительно загруженные данные устанавливаются только
		// при отсутствии ID PK, т.к. в противном случае при
		// сохранении модели без загрузки данных, предварительно
		// загруженные данные также пойдут в запрос на сохранение
		if (!$this->_bSetPreloadValues && !empty($this->_preloadValues))
		{
			if (!$this->loaded())
			{
				// Значения по умолчанию могут быть не загружены в случае, когда кроме создания модели не было
				// ни одной операции с ее атрибутами
				//!isset(self::$_columnCacheDefaultValues[$this->_modelName]) && $this->_loadColumnCacheDefaultValues();
				$this->_loadColumns();

				// Do not set values which have changed
				$this->setValues(
					array_diff_key($this->_preloadValues + self::$_columnCacheDefaultValues[$this->_modelName], $this->_changedColumns), $changed = TRUE
				);

				//$this->setValues($this->_preloadValues, $changed = TRUE);
				//$this->_preloadValues = array();
			}
			$this->_bSetPreloadValues = TRUE;
		}

		return $this;
	}

	/**
	 * Set unidentified values as NULL
	 * @return Core_ORM
	 */
	protected function _setDefaultValues()
	{
		$this->setValues(
			$this->_modelColumns + array_fill_keys(array_keys($this->_tableColumns), NULL)
		);
		return $this;
	}

	/**
	 * Set model values
	 * @param array $values list of values
	 * @param boolean $changed default FALSE
	 * @return Core_ORM
	 */
	protected function setValues(array $values, $changed = FALSE)
	{
		foreach ($values as $column => $value)
		{
			if (!isset($this->_skipColumns[$column]))
			{
				$this->_modelColumns[$column] = $value;
				$changed && $this->_changedColumns[$column] = $column;
			}
		}

		return $this;
	}

	/**
	 * Check is primary key NULL
	 */
	protected function isEmptyPrimaryKey()
	{
		return is_null($this->getPrimaryKey());
	}

	/**
	 * Get relation by $lowerProperty
	 * @param string $lowerProperty
	 * @return object
	 */
	protected function _getRelation($lowerProperty)
	{
		switch ($this->_relations[$lowerProperty]['type'])
		{
			case 'one':
				$object = self::factory($this->_relations[$lowerProperty]['model'])
					->clear();

				if (isset($this->_relations[$lowerProperty]['through']))
				{
					//$tableName = Core_Inflection::getPlural($this->_relations[$lowerProperty]['through']);
					$tableName = $this->_relations[$lowerProperty]['through_table_name'];

					$object->queryBuilder()
						// change id on id from joined table
						->select($object->getTableName() . '.*') // select columns just from _tableName
						->join($tableName,
						$object->getTableName() . '.' . $object->getPrimaryKeyName(),
						'=',
						$tableName . '.' . $this->_relations[$lowerProperty]['dependent_key']);
				}
				else
				{
					$tableName = $object->getTableName();
				}

				$object
					->queryBuilder()
					->where(
						$tableName . '.' . $this->_relations[$lowerProperty]['foreign_key'],
						'=',
						$this->getPrimaryKey()
					)
					->limit(1);

				// Load values
				$object->find();

				if (is_null($object->getPrimaryKey()) && !isset($this->_relations[$lowerProperty]['through']))
				{
					$foreignKey = $this->_relations[$lowerProperty]['foreign_key'];

					// Add relation
					$object->$foreignKey = $this->getPrimaryKey();
				}

				// Insert into cache
				$this->_relationCache[$lowerProperty] = $object;
			break;
			case 'belong':
				$object = self::factory($this->_relations[$lowerProperty]['model']);

				$_belongsByPrimary =
					$this->_relations[$lowerProperty]['primary_key'] == $object->getPrimaryKeyName();

				$foreignKey = $this->_relations[$lowerProperty]['foreign_key'];
				$foreignKeyValue = $this->$foreignKey;

				if ($_belongsByPrimary)
				{
					// Apply object because find() may return new object through Core_ObjectWatcher
					$object = $object->find($foreignKeyValue);
				}
				else
				{
					if (!is_null($foreignKeyValue))
					{
						$object
							->clear()
							->queryBuilder()
							->where(
							$object->getTableName() . '.' . $this->_relations[$lowerProperty]['primary_key'],
							'=',
							$foreignKeyValue)
							->limit(1);

						// Load values
						$object = $object->find();
					}
				}

				// Add into cache
				$this->_relationCache[$lowerProperty] = $object;
			break;
			case 'many':
				$object = self::factory($this->_relations[$lowerProperty]['model']);

				$object->queryBuilder()
					->clear();

				if (isset($this->_relations[$lowerProperty]['through']))
				{
					//$tableName = Core_Inflection::getPlural($this->_relations[$lowerProperty]['through']);
					$tableName = $this->_relations[$lowerProperty]['through_table_name'];

					$object->queryBuilder()
						->select($object->getTableName() . '.*')
						->join($tableName,
						$object->getTableName() . '.' . $object->getPrimaryKeyName(),
						'=',
						$tableName . '.' . $this->_relations[$lowerProperty]['dependent_key']);
				}
				else
				{
					$tableName = $object->getTableName();
				}

				$object->queryBuilder()
					->where(
					$tableName . '.' . $this->_relations[$lowerProperty]['foreign_key'],
					'=',
					$this->getPrimaryKey());
			break;
			default:
				throw new Core_Exception("Wrong relation type '%relationType' in the model '%model'",
					array('%relationType' => $this->_relations[$lowerProperty]['type'], '%model' => $this->getModelName()));
		}

		return $object;
	}

	/**
	 * Utilized for reading data from inaccessible properties
	 * @param string $property property name
	 * @return mixed
	 */
	public function __get($property)
	{
		$lowerProperty = strtolower($property);

		if (isset($this->_relationCache[$lowerProperty]))
		{
			return $this->_relationCache[$lowerProperty];
		}

		if (isset($this->_relations[$lowerProperty]))
		{
			return $this->_getRelation($lowerProperty);
		}

		// Property does not exist
		if (!array_key_exists($lowerProperty, $this->_modelColumns))
		{
			$this->_load();
		}

		if (array_key_exists($lowerProperty, $this->_modelColumns))
		{
			return $this->_modelColumns[$lowerProperty];
		}

		// data-property, e.g. dataMyValue
		if (strpos($property, 'data') === 0 && $property != 'data')
		{
			return Core_Array::get($this->_dataValues, $property);
		}

		if (!Core_Event::notify($this->_modelName . '.onCall' . $property, $this))
		{
			throw new Core_Exception("The property '%property' does not exist in the model '%model'",
				array('%property' => $property, '%model' => $this->getModelName()));
		}

		return Core_Event::getLastReturn();
	}

	/**
	 * Verify that the contents of a variable can be called as a function
	 * @param string $methodName method name
	 */
	public function isCallable($methodName)
	{
		return method_exists($this, $methodName)
			|| Core_Event::getCount($this->_modelName . '.onCall' . $methodName);
	}

	/**
	 * Triggered by calling isset() or empty() on inaccessible properties
	 * @param string $property property name
	 * @return boolean
	 */
	public function __isset($property)
	{
		$lowerProperty = strtolower($property);

		if (isset($this->_relationCache[$lowerProperty]) || isset($this->_relations[$lowerProperty]))
		{
			return TRUE;
		}

		// Property does not exist
		if (!array_key_exists($lowerProperty, $this->_modelColumns))
		{
			$this->_load();
		}

		if (array_key_exists($lowerProperty, $this->_modelColumns))
		{
			return TRUE;
		}

		// data-property, e.g. dataMyValue
		if (strpos($property, 'data') === 0 && $property != 'data')
		{
			return TRUE;
		}

		if (Core_Event::getCount($this->_modelName . '.onCall' . $property))
		{
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Run when writing data to inaccessible properties
	 * @param string $property property name
	 * @param string $value property value
	 * @return self
	 */
	public function __set($property, $value)
	{
		$isPrimary = $property === $this->_primaryKey;
		// Change primary key by the same value
		if ($isPrimary && isset($this->_modelColumns[$this->_primaryKey])
		&& $this->_modelColumns[$this->_primaryKey] === $value)
		{
			return $this;
		}

		//$this->_load();
		$this->_loadColumns();

		if (isset($this->_tableColumns[$property]))
		{
			// Change primary key from NULL
			$changed = !$isPrimary
				// isset() will return FALSE if testing a variable that has been set to NULL
				|| isset($this->_modelColumns[$this->_primaryKey]);

			// Convert property. $this->_saved is NULL until call __construct()
			!is_null($this->_saved)
				&& $value = $this->_convertPropertyValue($property, $value);

			$this->setValues(array($property => $value), $changed);

			// If property was changed. _saved is NULL after __construct
			!is_null($this->_saved)
				&& $changed
				&& $this->_saved = FALSE;

			return $this;
		}

		// data-property, e.g. dataMyValue
		if (strpos($property, 'data') === 0 && $property != 'data')
		{
			$this->_dataValues[$property] = $value;
			return $this;
		}

		if (!Core_Event::notify($this->_modelName . '.onCall' . $property, $this, array($value)))
		{
			throw new Core_Exception("The property '%property' does not exist in the model '%model'",
				array('%property' => $property, '%model' => $this->getModelName()));
		}

		return $this;
	}

	/**
	 * Triggered when invoking inaccessible methods in an object context
	 * @param string $name method name
	 * @param array $arguments arguments
	 * @return mixed
	 * @hostcms-event modelname.onCall
	 */
	public function __call($name, $arguments)
	{
		$this->_loadColumns();

		if (isset($this->_tableColumns[$name]))
		{
			return array_key_exists(0, $arguments)
				? $this->__set($name, $arguments[0])
				: $this->__get($name);
		}

		$lowerProperty = strtolower($name);

		if (isset($this->_relationCache[$lowerProperty]))
		{
			return $this->_relationCache[$lowerProperty];
		}

		if (isset($this->_relations[$lowerProperty]))
		{
			return $this->_getRelation($lowerProperty);
		}

		// data-property, e.g. dataMyValue
		if (strpos($name, 'data') === 0 && $name != 'data')
		{
			$this->_dataValues[$name] = $arguments[0];
			return $this;
		}

		if (!Core_Event::notify($this->_modelName . '.onCall' . $name, $this, $arguments))
		{
			throw new Core_Exception("The method '%methodName' does not exist in the model '%modelName'",
				array('%methodName' => $name, '%modelName' => $this->getModelName()));
		}

		return Core_Event::getLastReturn();
	}

	/**
	 * Get changed columns with values
	 * @return array
	 */
	protected function _getChangedData()
	{
		$data = array();

		foreach ($this->_changedColumns as $column)
		{
			$data[$column] = $this->_modelColumns[$column];
		}

		return $data;
	}

	/**
	 * Convert field value
	 * @param string $property Property name
	 * @param mixed $value Property value
	 * @return mixed converted value
	 */
	protected function _convertPropertyValue($property, $value)
	{
		$aField = $this->_tableColumns[$property];

		// Value is not NULL or value is NULL and default value does not exist
		if (!is_null($value)
			|| is_null($aField['default']) && $aField['null'] != 1 && $aField['extra'] != 'auto_increment'
		)
		{
			switch ($aField['type'])
			{
				case 'int':
					if (!is_numeric($value))
					{
						$value = intval($value);
					}

					if ($value < $aField['min'] || $value > $aField['max'])
					{
						$value = ($value < $aField['min']) ? $aField['min'] : $aField['max'];
					}
				break;
				case 'decimal':
					// Convert "," to "."
					$value = str_replace(',', '.', $value);

					// Remove everything except numbers and dot
					$value = preg_replace('/[^0-9\.\-]/', '', $value);

					$value == '' && $value = 0;
				break;
				case 'float':
					// Convert "," to "."
					$value = str_replace(',', '.', $value);

					// Remove everything except numbers and dot
					$value = preg_replace('/[^0-9\.\-]/', '', $value);

					$value = floatval($value);
				break;
				case 'string':
					$strlen = mb_strlen($value);

					if (!is_null($aField['max_length'])
						&& $aField['datatype'] != 'enum'
						&& $strlen > $aField['max_length'])
					{
						$value = mb_substr($value, 0, $aField['max_length']);
					}
				break;
				default:
					throw new Core_Exception("Unchecked property '%property' type '%type' in the model '%model'",
						array('%property' => $property, '%type' => $aField['type'], '%model' => $this->getModelName()));
			}
		}

		return $value;
	}

	/**
	 * Check model values. If model has incorrect value, one will correct or call exception.
	 *
	 * <code>
	 * $object = Core_ORM::factory('Book', 1);
	 * $object->value = 123;
	 * $object->check(TRUE)->save();
	 * </code>
	 * @param boolean $exception Call exception (TRUE) or correct value (FALSE). Default FALSE.
	 * @return Core_ORM
	 */
	public function check($exception = FALSE)
	{
		//$this->_load();

		// При создании объекта с явным указанием ID нельзя проверять значения, т.к. будет попытка
		// загрузить такой объект из базы, его данных не будут и PK будет сброшен в NULL
		if ($this->loaded())
		{
			foreach ($this->_tableColumns as $property => $aField)
			{
				//$value = $this->_modelColumns[$property];
				$value = $this->$property;

				// Value is not NULL or value is NULL and default value does not exist
				if (!is_null($value) || is_null($aField['default']) && $aField['null'] != 1 && $aField['extra'] != 'auto_increment')
				{
					switch ($aField['type'])
					{
						case 'int':
							if (!is_numeric($value))
							{
								$this->$property = $value = intval($value);
							}

							if ($value < $aField['min'] || $value > $aField['max'])
							{
								if ($exception)
								{
									throw new Core_Exception("The property '%property' has illegal value in the model '%model'",
										array('%property' => $property, '%model' => $this->getModelName()));
								}
								else
								{
									$this->$property = ($value < $aField['min']) ? $aField['min'] : $aField['max'];
								}
							}
						break;
						case 'float':
							// Convert "," to "."
							$value = str_replace(',', '.', $value);

							// Remove everything except numbers and dot
							$value = preg_replace('/[^0-9\.\-]/', '', $value);

							$value = floatval($value);
						break;
						case 'string':
							$strlen = mb_strlen($value);

							if (!is_null($aField['max_length'])
								&& $aField['datatype'] != 'enum'
								&& $strlen > $aField['max_length'])
							{
								if ($exception)
								{
									throw new Core_Exception("The property '%property' has illegal length in the model '%model'",
										array('%property' => $property, '%model' => $this->getModelName()));
								}
								else
								{
									$this->$property = mb_substr($value, 0, $aField['max_length']);
								}
							}
						break;
						default:
							throw new Core_Exception("Unchecked property '%property' type '%type' in the model '%model'",
								array('%property' => $property, '%type' => $aField['type'], '%model' => $this->getModelName()));
					}
				}
			}
		}

		return $this;
	}

	/**
	 * Insert new object data into database
	 * @return Core_ORM
	 * @hostcms-event modelname.onBeforeCreate
	 * @hostcms-event modelname.onAfterCreate
	 */
	public function create()
	{
		if (!$this->_saved)
		{
			$this->_setPreloadValues();

			Core_Event::notify($this->_modelName . '.onBeforeCreate', $this);

			$data = $this->_getChangedData();

			$bPKexists = array_key_exists($this->_primaryKey, $data);

			// Set PK
			if (!$bPKexists)
			{
				$getPrimaryKeyValue = $this->getPrimaryKey();

				$bPKexists = !is_null($getPrimaryKeyValue);

				$bPKexists && $data[$this->_primaryKey] = $getPrimaryKeyValue;
			}

			$oQuery_Builder = Core_QueryBuilder::insert($this->_tableName, $data);

			self::$_databaseDriver != 'default'
				&& $oQuery_Builder->setDataBase($this->getDatabase());

			$oInsert = $oQuery_Builder->execute();

			if (!$bPKexists)
			{
				// Set primary key
				$this->setValues(
					array($this->_primaryKey => $oInsert->getInsertId())
				);
			}

			$this->_saved = TRUE;

			// Clear changed columns
			$this->_changedColumns = array();

			$this->clearRelationCache();

			Core_Event::notify($this->_modelName . '.onAfterCreate', $this);
		}

		return $this;
	}

	/**
	 * Update object data into database
	 * @return Core_ORM
	 * @hostcms-event modelname.onBeforeUpdate
	 * @hostcms-event modelname.onAfterUpdate
	 */
	public function update()
	{
		if (!$this->_saved)
		{
			// Rewrite sets data
			//$this->_setPreloadValues();

			Core_Event::notify($this->_modelName . '.onBeforeUpdate', $this);

			$data = $this->_getChangedData();

			$oQuery_Builder = Core_QueryBuilder::update($this->_tableName);

			self::$_databaseDriver != 'default'
				&& $oQuery_Builder->setDataBase($this->getDatabase());

			$oUpdate = $oQuery_Builder
				->columns($data)
				->where($this->_primaryKey, '=', $this->getPrimaryKey())
				->execute();

			$this->_saved = TRUE;

			// Clear changed columns
			$this->_changedColumns = array();

			$this->clearRelationCache();

			Core_Event::notify($this->_modelName . '.onAfterUpdate', $this);
		}

		return $this;
	}

	/**
	 * Save object. Uses self::update() or self::create()
	 *
	 * @return Core_ORM
	 * @hostcms-event modelname.onBeforeSave
	 * @hostcms-event modelname.onAfterSave
	 */
	public function save()
	{
		Core_Event::notify($this->_modelName . '.onBeforeSave', $this);

		if (!$this->_saved)
		{
			!$this->isEmptyPrimaryKey()/* && !isset($this->_changedColumns[$this->_primaryKey])*/
				? $this->update()
				: $this->create();
		}

		Core_Event::notify($this->_modelName . '.onAfterSave', $this);

		return $this;
	}

	/**
	 * Get $this->_relations
	 * @return array
	 */
	public function getRelations()
	{
		return $this->_relations;
	}

	/**
	 * Set $this->_relations
	 * @param array $relations Array with new relations
	 * @return self
	 */
	public function setRelations(array $relations)
	{
		$this->_relations
			= self::$_relationModelCache[$this->_modelName]
			= $relations;

		// Relation cache
		$bCache = Core::moduleIsActive('cache');

		if ($bCache)
		{
			$cacheName = 'Core_ORM_RelationCache';

			self::$relationCache->set($this->_modelName, $this->_relations, $cacheName);
		}

		return $this;
	}

	/**
	 * Clone entity
	 * @return void
	 */
	public function __clone()
	{
		$this->_load();

		// Set primary key as NULL
		$this->_modelColumns[$this->_primaryKey] = NULL;

		// Mark all properies as changed
		foreach ($this->_modelColumns as $key => $value)
		{
			$this->$key = $value;
		}
	}

	/**
	 * Convert object to string
	 * @return string
	 */
	public function __toString()
	{
		$return = array();

		if (!empty($this->_modelColumns))
		{
			// Model data
			foreach ($this->_modelColumns as $key => $value)
			{
				$return[] = htmlspecialchars($key) . '=' . htmlspecialchars($value);
			}

			// 'dataXXX' values
			foreach ($this->_dataValues as $key => $value)
			{
				$return[] = htmlspecialchars($key) . '=' . htmlspecialchars($value);
			}

			return "Model '" . $this->_modelName . "',\nfields: " . implode(",\n", $return);
		}

		return;
	}

	/**
	 * Convert Object to Array
	 * @return array
	 */
	public function toArray()
	{
		$return = array();

		if (!empty($this->_modelColumns))
		{
			$return = $this->_modelColumns;
		}

		// 'dataXXX' values
		foreach ($this->_dataValues as $key => $value)
		{
			$return[$key] = $value;
		}

		return $return;
	}

	/**
	 * Clear relation cache
	 * @return Core_ORM
	 */
	public function clearRelationCache()
	{
		$this->_relationCache = array();
		return $this;
	}

	/**
	 * Clear object
	 * @return Core_ORM
	 */
	public function clear()
	{
		$this->_relationCache = $this->_changedColumns = $this->_modelColumns = array();
		$this->_saved = TRUE;
		$this->_bSetPreloadValues = $this->_loaded = FALSE;
		$this->queryBuilder()->clear();
		$this->_modelColumns[$this->_primaryKey] = NULL;
		return $this;
	}
}