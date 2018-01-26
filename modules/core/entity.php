<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Content management system entity
 *
 * Implement methods like getByXXX($value, $cache = TRUE) where XXX is the field name
 * <code>
 * $object = Core_Entity::factory('Book')->getByName('The Catcher in the Rye');
 * </code>
 *
 * Implement methods like getAllByXXX($value, $cache = TRUE) where XXX is the field name
 * <code>
 * $aObject = Core_Entity::factory('Book')->getAllByName('The Catcher in the Rye');
 * </code>
 *
 * @see Core_ORM
 *
 * @package HostCMS
 * @subpackage Core
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Core_Entity extends Core_ORM
{
	/**
	 * Name of the tag in XML
	 * @var string
	 */
	protected $_tagName = NULL;

	/**
	 * Set name of XML node
	 * @param string $tagName new tag name for node
	 * @return self
	 */
	public function setXmlTagName($tagName)
	{
		$this->_tagName = strval($tagName);
		return $this;
	}

	/**
	 * Allowed tags. If list of tags is empty, all tags will show.
	 *
	 * @var array
	 */
	protected $_allowedTags = array();

	/**
	 * List of Shortcodes tags
	 * @var array
	 */
	protected $_shortcodeTags = array();

	/**
	 * Add tag to allowed tags list
	 * @param string $tag tag
	 * @return self
	 */
	public function addAllowedTag($tag)
	{
		$this->_allowedTags[$tag] = $tag;
		return $this;
	}

	/**
	 * Typical forbidden tags.
	 *
	 * @var array
	 */
	protected $_typicalForbiddenTags = array();

	/**
	 * Forbidden tags. If list of tags is empty, all tags will be shown.
	 *
	 * @var array
	 */
	protected $_forbiddenTags = array('deleted');

	/**
	 * Add tag to forbidden tags list
	 * @param string $tag tag
	 * @return self
	 */
	public function addForbiddenTag($tag)
	{
		$this->_forbiddenTags[$tag] = $tag;
		return $this;
	}

	/**
	 * Add tags to forbidden tags list
	 * @param array $aTags array of tags
	 * @return self
	 */
	public function addForbiddenTags(array $aTags)
	{
		foreach ($aTags as $tag)
		{
			$this->_forbiddenTags[$tag] = $tag;
		}
		return $this;
	}

	/**
	 * Get forbidden tags list
	 * @return array
	 */
	public function getForbiddenTags()
	{
		return $this->_forbiddenTags;
	}

	/**
	 * External XML tags for entity.
	 *
	 * @var array
	 */
	protected $_xmlTags = array();

	/**
	 * Add external tag for entity
	 * @param string $tagName tag name
	 * @param string $tagValue tag value
	 * @return self
	 */
	public function addXmlTag($tagName, $tagValue)
	{
		//if (!isset($this->_forbiddenTags[$tagName]))
		//{
		$this->_xmlTags[] = array($tagName, $tagValue);
		//}
		return $this;
	}

	/**
	 * Clear external XML tags for entity.
	 */
	public function clearXmlTags()
	{
		$this->_xmlTags = array();
		return $this;
	}

	/**
	 * Get external XML tags for entity.
	 * @return array
	 */
	public function getXmlTags()
	{
		return $this->_xmlTags;
	}

	/**
	 * Children entities
	 *
	 * @var array
	 */
	protected $_childrenEntities = array();

	/**
	 * Get children entities
	 * @return array
	 */
	public function getEntities()
	{
		return $this->_childrenEntities;
	}

	/**
	 * Clear enities
	 * @return self
	 */
	public function clearEntities()
	{
		$this->_childrenEntities = $this->_allowedTags = array();
		$this->_forbiddenTags = $this->_typicalForbiddenTags;

		$this->clearXmlTags();
		return $this;
	}

	/**
	 * Marks deleted entity
	 *
	 * @param mixed Name of field with 'deleted' status. If NULL, marks deleted will not be allowed.
	 */
	protected $_marksDeleted = 'deleted';

	/**
	 * Get column name for marks deleted
	 */
	public function getMarksDeleted()
	{
		return $this->_marksDeleted;
	}

	/**
	 * Set column name for marks deleted
	 * @param mixed $marksDeleted
	 */
	public function setMarksDeleted($marksDeleted = 'deleted')
	{
		$this->_marksDeleted = $marksDeleted;
		return $this;
	}

	static protected $_cacheAllowedTags = array();

	static protected $_cacheForbiddenTags = array();

	static protected $_cacheShortcodeTags = array();

	/**
	 * Constructor.
	 * @param string $primaryKey
	 */
	public function __construct($primaryKey = NULL)
	{
		$className = get_class($this);

		if (!empty($this->_allowedTags))
		{
			!isset(self::$_cacheAllowedTags[$className])
				&& self::$_cacheAllowedTags[$className] = array_combine($this->_allowedTags, $this->_allowedTags);

			$this->_allowedTags = self::$_cacheAllowedTags[$className];
		}

		if (!empty($this->_forbiddenTags))
		{
			!isset(self::$_cacheForbiddenTags[$className])
				&& self::$_cacheForbiddenTags[$className] = array_combine($this->_forbiddenTags, $this->_forbiddenTags);

			$this->_forbiddenTags = self::$_cacheForbiddenTags[$className];
		}

		if (!empty($this->_shortcodeTags))
		{
			!isset(self::$_cacheShortcodeTags[$className])
				&& self::$_cacheShortcodeTags[$className] = array_combine($this->_shortcodeTags, $this->_shortcodeTags);

			$this->_shortcodeTags = self::$_cacheShortcodeTags[$className];
		}

		if (!is_null($this->_marksDeleted) && !isset($this->_preloadValues[$this->_marksDeleted]))
		{
			$this->_preloadValues[$this->_marksDeleted] = 0;
		}

		$this->_typicalForbiddenTags = $this->_forbiddenTags;

		parent::__construct($primaryKey);

		if (is_null($this->_tagName))
		{
			$this->_tagName = $this->_modelName;
		}

		/*$primaryKey = $this->getPrimaryKey();
		// Заменяет уже существующий объект при mysql_fetch_object()
		if ($primaryKey)
		{
			Core_ObjectWatcher::instance()->add($this);
		}
		*/
	}

	/**
	 * Create and return an object of model
	 * @param string $modelName Entity name
	 * @param mixed $primaryKey Primary key
	 */
	static public function factory($modelName, $primaryKey = NULL)
	{
		// May be NULL or 0
		if ($primaryKey)
		{
			$Core_ObjectWatcher = Core_ObjectWatcher::instance();
			$realModelName = ucfirst($modelName) . '_Model';
			$object = $Core_ObjectWatcher->exists($realModelName, $primaryKey);

			if (!is_null($object))
			{
				return $object;
			}
		}

		$object = parent::factory($modelName, $primaryKey);

		// Add into ObjectWatcher
		$primaryKey && $Core_ObjectWatcher->add($object);

		return $object;
	}

	/**
	 * Get table columns
	 * @return array
	 */
	public function getTableColums()
	{
		return $this->_loadColumns()->_tableColumns;
	}

	/**
	 * Get all relations of entity
	 * @return array
	 */
	public function getRelations()
	{
		return $this->_relations;
	}

	/*protected function _setDefaultValues()
	{
		// Unregister object from Core_ObjectWatcher becouse it hadn't found (PK is NULL)
		$Core_ObjectWatcher = Core_ObjectWatcher::instance();
		$Core_ObjectWatcher->delete($this);
		return parent::_setDefaultValues();
	}*/

	/**
	 * Mark entity as deleted
	 * @return Core_Entity
	 */
	public function markDeleted()
	{
		if (!is_null($this->_marksDeleted))
		{
			Core_Event::notify($this->_modelName . '.onBeforeMarkDeleted', $this);

			// Delete from ObjectWatcher
			Core_ObjectWatcher::instance()->delete($this);

			$marksDeletedFieldName = $this->_marksDeleted;
			$this->$marksDeletedFieldName = 1;
			$this->save();

			Core_Event::notify($this->_modelName . '.onAfterMarkDeleted', $this);
		}
		else
		{
			throw new Core_Exception("The model '%model' cannot be marked deleted",
				array('%model' => $this->getModelName()));
		}

		return $this;
	}

	/**
	 * Turn off deleted status
	 * @return self
	 */
	public function undelete()
	{
		if (!is_null($this->_marksDeleted) && !is_null($this->id))
		{
			Core_Event::notify($this->_modelName . '.onBeforeUndelete', $this);

			$marksDeletedFieldName = $this->_marksDeleted;
			$this->$marksDeletedFieldName = 0;
			$this->save();

			Core_Event::notify($this->_modelName . '.onAfterUndelete', $this);
		}
		else
		{
			throw new Core_Exception("The model '%model' cannot be undelete",
				array('%model' => $this->getModelName()));
		}

		return $this;
	}

	/**
	 * Clear object
	 * @return self
	 */
	public function clear()
	{
		// Delete from ObjectWatcher
		Core_ObjectWatcher::instance()->delete($this);

		return parent::clear();
	}

	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return Core_Entity
	 */
	public function delete($primaryKey = NULL)
	{
		// Delete from ObjectWatcher
		Core_ObjectWatcher::instance()->delete($this);

		// Delete Revisions
		if (Core::moduleIsActive('revision'))
		{
			if (is_null($primaryKey))
			{
				$primaryKey = $this->getPrimaryKey();
			}

			$oRevisions = Core_Entity::factory('Revision');
			$oRevisions->queryBuilder()
				->where('model', '=', $this->getModelName())
				->where('entity_id', '=', $primaryKey);

			$aRevisions = $oRevisions->findAll(FALSE);
			foreach ($aRevisions as $oRevision)
			{
				$oRevision->delete();
			}
		}

		return parent::delete($primaryKey);
	}

	/**
	 * Apply markDeleted flag if is set
	 * @return self
	 */
	public function applyMarksDeleted()
	{
		if (!is_null($this->_marksDeleted))
		{
			$this->queryBuilder()
				->where($this->_tableName . '.' . $this->_marksDeleted, '=', 0);
		}
		return $this;
	}

	/**
	 * Find object in database and load one
	 * @param mixed $primaryKey default NULL
	 * @param boolean $bCache use cache
	 * @return Core_ORM
	 */
	public function find($primaryKey = NULL, $bCache = TRUE)
	{
		// May be NULL or 0
		if ($bCache && $primaryKey)
		{
			$Core_ObjectWatcher = Core_ObjectWatcher::instance();
			$object = $Core_ObjectWatcher->exists(get_class($this), $primaryKey);

			if (!is_null($object) && $object->loaded())
			{
				return $object;
			}
		}

		// Apply applyMarksDeleted AFTER clear()!
		if (!is_null($primaryKey))
		{
			// Clear object
			$this->clear();

			$this->queryBuilder()
				// in $this->clear() // ->clear() // clear if find by PK
				->where($this->_tableName . '.' . $this->_primaryKey, '=', $primaryKey);
		}

		$this->applyMarksDeleted();

		//$object = parent::find($primaryKey, $bCache);
		$object = parent::find(NULL, $bCache);

		// Add into ObjectWatcher
		$bCache && $primaryKey && $Core_ObjectWatcher->add($object);

		return $object;
	}

	/**
	 * Find all objects
	 * @param boolean $bCache use cache, default TRUE
	 * @return array
	 */
	public function findAll($bCache = TRUE)
	{
		$this->applyMarksDeleted();
		return parent::findAll($bCache);
	}

	/**
	 * Get count object
	 * @param boolean $bCache use cache, default TRUE
	 * @return int
	 */
	public function getCount($bCache = TRUE)
	{
		$this->applyMarksDeleted();
		return parent::getCount($bCache);
	}

	/**
	 * Add a children entity
	 *
	 * @param Core_Entity $oChildrenEntity
	 */
	public function addEntity($oChildrenEntity)
	{
		$this->_childrenEntities[] = $oChildrenEntity;
		return $this;
	}

	/**
	 * Add children entities
	 *
	 * @param array $aChildrenEntities
	 */
	public function addEntities(array $aChildrenEntities)
	{
		foreach ($aChildrenEntities AS $oChildrenEntity)
		{
			$this->addEntity($oChildrenEntity);
		}
		return $this;
	}

	/**
	 * Clear all entities after XML generation
	 * @var boolean
	 */
	protected $_clearEntitiesAfterGetXml = TRUE;

	/**
	 * Clear all entities after XML generation
	 * @param boolean $clear mode
	 * @return self
	 */
	public function clearEntitiesAfterGetXml($clear = TRUE)
	{
		$this->_clearEntitiesAfterGetXml = $clear;
		return $this;
	}

	/**
	 * Get XML for entity and children entities
	 * @return string
	 * @hostcms-event Core_Entity.onBeforeGetXml
	 * @hostcms-event Core_Entity.onAfterGetXml
	 */
	public function getXml()
	{
		$this->_load();

		Core_Event::notify($this->_modelName . '.onBeforeGetXml', $this);

		//$object_values = $this->_modelColumns;

		$xml = "<" . $this->_tagName;

		// Primary key as tag property
		if (array_key_exists($this->_primaryKey, $this->_modelColumns))
		{
			$xml .= " {$this->_primaryKey}=\"" . Core_Str::xml($this->_modelColumns[$this->_primaryKey]) . "\"";
			//unset($object_values[$this->_primaryKey]);
		}

		$xml .= ">\n";

		$bAllowedTagsIsEmpty = count($this->_allowedTags) == 0;
		$bForbiddenTagsIsEmpty = count($this->_forbiddenTags) == 0;
		$bShortcodeTags = Core::moduleIsActive('shortcode') && count($this->_shortcodeTags) > 0;

		if ($bShortcodeTags)
		{
			$oShortcode_Controller = Shortcode_Controller::instance();
			$iCountShortcodes = $oShortcode_Controller->getCount();
		}

		foreach ($this->_modelColumns as $field_name => $field_value)
		{
			// Разрешенные теги
			if ($field_name != $this->_primaryKey
				&& ($bAllowedTagsIsEmpty || isset($this->_allowedTags[$field_name]))
			)
			{
				// Запрещенные теги
				if ($bForbiddenTagsIsEmpty || !isset($this->_forbiddenTags[$field_name]))
				{
					if ($bShortcodeTags && $iCountShortcodes && isset($this->_shortcodeTags[$field_name]))
					{
						$field_value = $oShortcode_Controller->applyShortcodes($field_value);
					}
					$xml .= "<{$field_name}>" . Core_Str::xml($field_value) . "</{$field_name}>\n";
				}
			}
		}

		// External tags
		foreach ($this->_xmlTags as $aTag)
		{
			$xml .= "<{$aTag[0]}>" . Core_Str::xml($aTag[1]) . "</{$aTag[0]}>\n";
		}

		// Children entities
		foreach ($this->_childrenEntities as $oChildrenEntity)
		{
			$xml .= $oChildrenEntity->getXml();
		}

		$xml .= "</" . $this->_tagName . ">\n";

		$this->_clearEntitiesAfterGetXml && $this->clearEntities();

		Core_Event::notify($this->_modelName . '.onAfterGetXml', $this);

		return $xml;
	}

	/**
	 * Check model values. If model has incorrect value, one will correct or call exception.
	 * @var boolean
	 */
	protected $_check = FALSE;

	/**
	 * Set _check flag
	 * @param boolean $check mode
	 * @return self
	 */
	public function setCheck($check)
	{
		$this->_check = $check;
		return $this;
	}

	/**
	 * Insert new object data into database
	 * @return Core_ORM
	 */
	public function create()
	{
		$this->_check && $this->check(FALSE);
		return parent::create();
	}

	/**
	 * Update object data into database
	 * @return Core_ORM
	 */
	public function update()
	{
		$this->_check && $this->check(FALSE);

		// Delete from ObjectWatcher
		Core_ObjectWatcher::instance()->delete($this);

		return parent::update();
	}

	/**
	 * Column consist item's name
	 * @var string
	 */
	protected $_nameColumn = 'name';

	/**
	 * Get Name Column
	 * @return string
	 */
	public function getNameColumn()
	{
		return $this->_nameColumn;
	}

	/**
	 * Get entity name
	 * @return string
	 */
	public function getName()
	{
		$nameColumn = $this->_nameColumn;
		return htmlspecialchars($this->$nameColumn);
	}

	/**
	 * Get entity description
	 * @return string
	 */
	public function getTrashDescription()
	{
		$text = isset($this->description) && strlen($this->description)
			? $this->description
			: (
				isset($this->text) && strlen($this->text)
					? $this->text
					: NULL
			);

		return !is_null($text)
			? htmlspecialchars(
				Core_Str::cut($text, 255)
			)
			: $text;
	}

	/**
	 * Change copied name is necessary, default FALSE
	 * @var boolean
	 */
	protected $_changeCopiedName = FALSE;

	/**
	 * Set if change copied name is necessary
	 * @param boolean $changeCopiedName mode
	 * @return self
	 */
	public function changeCopiedName($changeCopiedName)
	{
		$this->_changeCopiedName = $changeCopiedName;
		return $this;
	}

	/**
	 * Get the name of a new copied object
	 */
	protected function _getCopiedName()
	{
		$nameColumn = $this->_nameColumn;

		$prefix = defined('XSL_PREFIX')
			? XSL_PREFIX
			: '';

		return $this->_changeCopiedName
			? Core::_('Admin.copy', $prefix . $this->$nameColumn, date('d.m.Y H:i:s'))
			: $this->$nameColumn;
	}

	/**
	 * Copy object
	 * @return Core_Entity new copied object
	 */
	public function copy()
	{
		$newObject = clone $this;

		Core_Event::notify($this->_modelName . '.onBeforeCopy', $newObject, array($this));

		$nameColumn = $this->_nameColumn;
		$nameColumn != 'id' && $newObject->$nameColumn = $this->_getCopiedName();
		$newObject->save();

		Core_Event::notify($this->_modelName . '.onAfterCopy', $newObject, array($this));

		return $newObject;
	}

	/**
	 * Triggered when invoking inaccessible methods in an object context
	 * @param string $name method name
	 * @param array $arguments arguments
	 * @return mixed
	 */
	public function __call($name, $arguments)
	{
		$this->_loadColumns();

		// Implement a getByXXX($value, $bCache = TRUE, $compare = '=') methods
		if (count($arguments) > 0)
		{
			if (strpos($name, 'getBy') === 0)
			{
				$field_name = strtolower(substr($name, 5));

				$this->queryBuilder()
					->where($this->_tableName . '.' . $field_name, isset($arguments[2]) ? $arguments[2] : '=', $arguments[0])
					->limit(1);

				$aObjects = $this->findAll(isset($arguments[1]) ? $arguments[1] : TRUE);
				return isset($aObjects[0]) ? $aObjects[0] : NULL;
			}
			// Implement a getAllByXXX($value, $bCache = TRUE, $compare = '=') methods
			elseif (strpos($name, 'getAllBy') === 0)
			{
				$field_name = strtolower(substr($name, 8));

				$this->queryBuilder()
					->where($this->_tableName . '.' . $field_name, isset($arguments[2]) ? $arguments[2] : '=', $arguments[0]);

				return $this->findAll(isset($arguments[1]) ? $arguments[1] : TRUE);
			}
			// Implement a getCountByXXX($value, $bCache = TRUE, $compare = '=') methods
			elseif (strpos($name, 'getCountBy') === 0)
			{
				$field_name = strtolower(substr($name, 10));

				$this->queryBuilder()
					->where($this->_tableName . '.' . $field_name, isset($arguments[2]) ? $arguments[2] : '=', $arguments[0]);

				return $this->getCount(isset($arguments[1]) ? $arguments[1] : TRUE);
			}
		}

		return parent::__call($name, $arguments);
	}
}