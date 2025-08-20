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
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
 */
class Core_Entity extends Core_ORM
{
	/**
	 * Name of the tag in XML
	 * @var string
	 * @ignore
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
	 * Get tag name
	 * @return string
	 */
	public function getXmlTagName()
	{
		return $this->_tagName;
	}

	/**
	 * List of Shortcodes tags
	 * @var array
	 */
	protected $_shortcodeTags = array();

	/**
	 * Allowed tags. If list of tags is empty, all tags will show.
	 *
	 * @var array
	 */
	protected $_allowedTags = array();

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
	 * Add tags to allowed tags list
	 * @param array $aTags array of tags
	 * @return self
	 */
	public function addAllowedTags(array $aTags)
	{
		$this->_allowedTags = array_merge($this->_allowedTags, array_combine($aTags, $aTags));
		return $this;
	}

	/**
	 * Remove tag from allowed tags list
	 * @param string $tag tag
	 * @return self
	 */
	public function removeAllowedTag($tag)
	{
		if (isset($this->_allowedTags[$tag]))
		{
			unset($this->_allowedTags[$tag]);
		}

		return $this;
	}

	/**
	 * Get allowed tags list
	 * @return array
	 */
	public function getAllowedTags()
	{
		return $this->_allowedTags;
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
	protected $_forbiddenTags = array('deleted', 'user_id');

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
		$this->_forbiddenTags = array_merge($this->_forbiddenTags, array_combine($aTags, $aTags));
		return $this;
	}

	/**
	 * Remove tag from forbidden tags list
	 * @param string $tag tag
	 * @return self
	 */
	public function removeForbiddenTag($tag)
	{
		if (isset($this->_forbiddenTags[$tag]))
		{
			unset($this->_forbiddenTags[$tag]);
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
	 * @ignore
	 */
	protected $_xmlTags = array();

	/**
	 * Add external tag for entity
	 * @param string $tagName tag name
	 * @param string $tagValue tag value
	 * @param array $attributes attributes
	 * @return self
	 */
	public function addXmlTag($tagName, $tagValue, array $attributes = array())
	{
		//if (!isset($this->_forbiddenTags[$tagName]))
		//{
		$this->_xmlTags[] = array($tagName, $tagValue, $attributes);
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
	 * @ignore
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
		$this->_childrenEntities = $this->_allowedTags = $this->_attributes = array();
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
	 * Has revisions
	 * @param boolean
	 */
	protected $_hasRevisions = FALSE;

	/**
	 * Get column name for marks deleted
	 * @return string
	 */
	public function getMarksDeleted()
	{
		return $this->_marksDeleted;
	}

	/**
	 * Set column name for marks deleted
	 * @param mixed $marksDeleted
	 * @return self
	 */
	public function setMarksDeleted($marksDeleted = 'deleted')
	{
		$this->_marksDeleted = $marksDeleted;
		return $this;
	}

	/**
	 * Cache for $this->_allowedTags
	 * @var array
	 * @ignore
	 */
	static protected $_cacheAllowedTags = array();

	/**
	 * Cache for $this->_forbiddenTags
	 * @var array
	 * @ignore
	 */
	static protected $_cacheForbiddenTags = array();

	/**
	 * Cache for $this->_shortcodeTags
	 * @var array
	 * @ignore
	 */
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
		if ($primaryKey && is_scalar($primaryKey))
		{
			Core_ObjectWatcher::instance()->add($this);
		}
		*/
	}

	/**
	 * Create and return an object of model
	 * @param string $modelName Entity name
	 * @param mixed $primaryKey Primary key
	 * @retrun self
	 */
	static public function factory($modelName, $primaryKey = NULL)
	{
		// May be NULL or 0
		if ($primaryKey && is_scalar($primaryKey))
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
		$primaryKey && is_scalar($primaryKey)
			&& $Core_ObjectWatcher->add($object);

		return $object;
	}

	/**
	 * Get table columns. Fix wrong method name
	 * @return array
	 */
	public function getTableColums()
	{
		return $this->getTableColumns();
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
	 * @return self
	 * @hostcms-event modelname.onBeforeMarkDeleted
	 * @hostcms-event modelname.onAfterMarkDeleted
	 */
	public function markDeleted()
	{
		if (!is_null($this->_marksDeleted))
		{
			Core_Event::notify($this->_modelName . '.onBeforeMarkDeleted', $this);

			if ($this->getPrimaryKey())
			{
				// Delete from ObjectWatcher
				Core_ObjectWatcher::instance()->delete($this);

				$marksDeletedFieldName = $this->_marksDeleted;
				$this->$marksDeletedFieldName = 1;
				$this->save();

				if (Core::moduleIsActive('webhook'))
				{
					$webhookName = 'on' . implode('', array_map('ucfirst', explode('_', $this->getModelName())));
					Webhook_Controller::notify($webhookName . 'MarkDeleted', $this);
				}
			}

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
	 * @hostcms-event modelname.onBeforeUndelete
	 * @hostcms-event modelname.onAfterUndelete
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
			throw new Core_Exception("The model '%model' cannot be undeleted",
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
	 * @return self
	 */
	public function delete($primaryKey = NULL)
	{
		// Delete from ObjectWatcher
		Core_ObjectWatcher::instance()->delete($this);

		// Delete Revisions
		if ($this->_hasRevisions && Core::moduleIsActive('revision'))
		{
			is_null($primaryKey)
				&& $primaryKey = $this->getPrimaryKey();

			Core_QueryBuilder::delete('revisions')
				->where('model', '=', $this->getModelName())
				->where('entity_id', '=', $primaryKey)
				->execute();
		}

		// Delete Fields
		if (Core::moduleIsActive('field'))
		{
			$aFields = Field_Controller::getFields($this->getModelName());

			if (count($aFields))
			{
				$aFieldsIds = array();
				foreach ($aFields as $oField)
				{
					$aFieldsIds[] = $oField->id;
				}

				$fieldDir = CMS_FOLDER . Field_Controller::getPath($this);

				is_null($primaryKey)
					&& $primaryKey = $this->getPrimaryKey();

				$aField_Values = Field_Controller_Value::getFieldsValues($aFieldsIds, $primaryKey, FALSE);
				foreach ($aField_Values as $oField_Value)
				{
					$oField_Value->Field->type == 2 && $oField_Value->setDir($fieldDir);
					$oField_Value->delete();
				}

				if (Core_File::isDir($fieldDir))
				{
					try {
						Core_File::deleteDir($fieldDir);
					}
					catch (Exception $e) {}
				}
			}
		}

		if (Core::moduleIsActive('webhook'))
		{
			$webhookName = 'on' . implode('', array_map('ucfirst', explode('_', $this->getModelName())));
			Webhook_Controller::notify($webhookName . 'Deleted', $this);
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
	 * @return self
	 */
	public function find($primaryKey = NULL, $bCache = TRUE)
	{
		// May be NULL or 0
		if ($bCache && $primaryKey && is_scalar($primaryKey))
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
				->from($this->_tableName)
				->where($this->_tableName . '.' . $this->_primaryKey, '=', $primaryKey);
		}

		$this->applyMarksDeleted();

		//$object = parent::find($primaryKey, $bCache);
		$object = parent::find(NULL, $bCache);

		// Add into ObjectWatcher
		$bCache && $primaryKey && is_scalar($primaryKey)
			&& $Core_ObjectWatcher->add($object);

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
	 * @param string $fieldName default '*'
	 * @param boolean $distinct default FALSE
	 * @return int
	 */
	public function getCount($bCache = TRUE, $fieldName = '*', $distinct = FALSE)
	{
		$this->applyMarksDeleted();
		return parent::getCount($bCache, $fieldName, $distinct);
	}

	/**
	 * Add a children entity
	 *
	 * @param Core_Entity $oChildrenEntity
	 * @return self
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
	 * @return self
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
	 * Entity Attributes
	 * @var array
	 */
	protected $_attributes = array();

	/**
	 * Add entity attribute
	 *
	 * @param string $name
	 * @param string $value
	 * @return self
	 */
	public function addAttribute($name, $value)
	{
		$this->_attributes[$name] = $value;
		return $this;
	}

	/**
	 * Clear all entities after XML generation
	 * @var boolean
	 * @ignore
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
	 * Cache of fields set by the model
	 * @var array
	 * @ignore
	 */
	static protected $_cacheFieldIDs = array();

	/**
	 * Get visible field's IDs
	 * @return array
	 */
	public function getFieldIDs()
	{
		if (!isset(self::$_cacheFieldIDs[$this->_modelName]))
		{
			$aFields = Field_Controller::getFields($this->_modelName);

			self::$_cacheFieldIDs[$this->_modelName] = array();
			foreach ($aFields as $oField)
			{
				$oField->visible
					&& self::$_cacheFieldIDs[$this->_modelName][] = $oField->id;
			}
		}

		return self::$_cacheFieldIDs[$this->_modelName];
	}

	/**
	 * Get field's values
	 * @return array
	 */
	public function getFields()
	{
		return Core::moduleIsActive('field')
			? Field_Controller_Value::getFieldsValues($this->getFieldIDs(), $this->getPrimaryKey())
			: array();
	}

	/**
	 * Get XML for entity and children entities
	 * @return string
	 * @hostcms-event modelname.onBeforeGetXml
	 * @hostcms-event modelname.onAfterGetXml
	 */
	public function getXml()
	{
		$this->_load();

		Core_Event::notify($this->_modelName . '.onBeforeGetXml', $this);

		$xml = "<" . $this->_tagName;

		// Primary key as tag property
		if (array_key_exists($this->_primaryKey, $this->_modelColumns))
		{
			$xml .= " {$this->_primaryKey}=\"" . Core_Str::xml($this->getPrimaryKey()) . "\"";
		}

		foreach ($this->_attributes as $attributeName => $attributeValue)
		{
			$xml .= ' ' . $attributeName . '="' . Core_Str::xml($attributeValue) . '"';
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
			// Allowed Tags
			if ($field_name != $this->_primaryKey
				&& ($bAllowedTagsIsEmpty || isset($this->_allowedTags[$field_name]))
			)
			{
				// Forbidden Tags
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
			$xml .= "<{$aTag[0]}";

			if (isset($aTag[2]))
			{
				foreach ($aTag[2] as $tagName => $tagValue)
				{
					$xml .= " {$tagName}=\"" . Core_Str::xml($tagValue) . "\"";
				}
			}

			$xml .= ">" . Core_Str::xml($aTag[1]) . "</{$aTag[0]}>\n";
		}

		// Children entities
		foreach ($this->_childrenEntities as $oChildEntity)
		{
			$xml .= $oChildEntity->getXml();
		}

		// data-values, e.g. dataMyValue
		foreach ($this->_dataValues as $field_name => $field_value)
		{
			// Allowed Tags
			if ($bAllowedTagsIsEmpty || isset($this->_allowedTags[$field_name]))
			{
				// Forbidden Tags
				if ($bForbiddenTagsIsEmpty || !isset($this->_forbiddenTags[$field_name]))
				{
					$xml .= "<{$field_name}>" . Core_Str::xml($field_value) . "</{$field_name}>\n";
				}
			}
		}

		// User fields
		$aField_Values = $this->getFields();
		foreach ($aField_Values as $oField_Value)
		{
			$oField_Value->Field->type == 2 && $oField_Value->setDir(CMS_FOLDER . ($sPath = Field_Controller::getPath($this)))->setHref('/' . $sPath);
			
			$xml .= $oField_Value->getXml();
		}

		$xml .= "</" . $this->_tagName . ">\n";

		$this->_clearEntitiesAfterGetXml && $this->clearEntities();

		Core_Event::notify($this->_modelName . '.onAfterGetXml', $this);

		return $xml;
	}

	/**
	 * Is $tagName Available
	 * @param $tagName Tag Name
	 * @return bool
	 */
	protected function _isTagAvailable($tagName)
	{
		return (count($this->_allowedTags) == 0 || isset($this->_allowedTags[$tagName]))
			&& !isset($this->_forbiddenTags[$tagName]);
	}

	/**
	 * Get stdObject for entity and children entities
	 * @return stdObject
	 * @hostcms-event modelname.onBeforeGetArray
	 * @hostcms-event modelname.onAfterGetArray
	 */
	public function getStdObject($attributePrefix = '_')
	{
		$this->_load();

		Core_Event::notify($this->_modelName . '.onBeforeGetArray', $this);

		$oRetrun = new stdClass();

		// Primary key as tag property
		if (array_key_exists($this->_primaryKey, $this->_modelColumns))
		{
			$properttName = $attributePrefix . $this->_primaryKey;
			$oRetrun->$properttName = $this->getPrimaryKey();
		}

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
			// Allowed Tags
			if ($field_name != $this->_primaryKey
				&& ($bAllowedTagsIsEmpty || isset($this->_allowedTags[$field_name]))
			)
			{
				// Forbidden Tags
				if ($bForbiddenTagsIsEmpty || !isset($this->_forbiddenTags[$field_name]))
				{
					if ($bShortcodeTags && $iCountShortcodes && isset($this->_shortcodeTags[$field_name]))
					{
						$field_value = $oShortcode_Controller->applyShortcodes($field_value);
					}
					$oRetrun->$field_name = $field_value;
				}
			}
		}

		// External tags
		foreach ($this->_xmlTags as $aTag)
		{
			$sTmp = $aTag[0];
			if (empty($aTag[2]))
			{
				$oRetrun->$sTmp = $aTag[1];
			}
			else
			{
				$stdClass = new stdClass();
				$stdClass->value = $aTag[1];

				foreach ($aTag[2] as $tagName => $tagValue)
				{
					$properttName = $attributePrefix . $tagName;
					$stdClass->$properttName = $tagValue;
				}

				$oRetrun->$sTmp = $stdClass;
			}
		}

		// Children entities
		foreach ($this->_childrenEntities as $oChildEntity)
		{
			$bModel = $oChildEntity instanceof Core_ORM;
			
			$childName = $bModel
				? $oChildEntity->getXmlTagName()
				: $oChildEntity->name;

			$childArray = $oChildEntity->getStdObject($attributePrefix);

			if (!isset($oRetrun->$childName))
			{
				$oRetrun->$childName = $bModel && $this->_checkEntityIsHasManyRelation($oChildEntity)
					? array($childArray)
					: $childArray;
			}
			else
			{
				// Convert to array
				!is_array($oRetrun->$childName)
					&& $oRetrun->$childName = array($oRetrun->$childName);

				$oRetrun->{$childName}[] = $childArray;
			}
		}

		// data-values, e.g. dataMyValue
		foreach ($this->_dataValues as $field_name => $field_value)
		{
			// Allowed Tags
			if ($bAllowedTagsIsEmpty || isset($this->_allowedTags[$field_name]))
			{
				// Forbidden Tags
				if ($bForbiddenTagsIsEmpty || !isset($this->_forbiddenTags[$field_name]))
				{
					$oRetrun->$field_name = $field_value;
				}
			}
		}

		$this->_clearEntitiesAfterGetXml && $this->clearEntities();

		Core_Event::notify($this->_modelName . '.onAfterGetArray', $this);

		return $oRetrun;
	}

	/**
	 * Check model of $oEntity is hasMany relation
	 * @param Core_Entity $oEntity
	 * @return boolean
	 */
	protected function _checkEntityIsHasManyRelation($oEntity)
	{
		$modelName = $oEntity->getModelName();
		
		return isset($this->_hasMany[$modelName]) || isset($this->_hasMany[Core_Inflection::getPlural($modelName)]);
	}

	/**
	 * Convert Object to Array
	 * @return array
	 */
	public function toArray()
	{
		$aReturn = parent::toArray();

		// Children entities
		foreach ($this->_childrenEntities as $oChildEntity)
		{
			$childName = $oChildEntity instanceof Core_ORM
				? $oChildEntity->getXmlTagName()
				: $oChildEntity->name;

			$childArray = $oChildEntity->toArray();

			if (!isset($aReturn[$childName]))
			{
				$aReturn[$childName] = $childArray;
			}
			else
			{
				// Convert to array
				!is_array($aReturn[$childName])
					&& $aReturn[$childName] = array($aReturn[$childName]);

				$aReturn[$childName][] = $childArray;
			}
		}

		return $aReturn;
	}

	/**
	 * Check model values. If model has incorrect value, one will correct or call exception.
	 * @var boolean
	 * @ignore
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
	 * @return self
	 */
	public function create()
	{
		$this->_check && $this->check(FALSE);
		return parent::create();
	}

	/**
	 * Update object data into database
	 * @return self
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
	 * @hostcms-event modelname.onBeforeGetName
	 */
	public function getName()
	{
		Core_Event::notify($this->_modelName . '.onBeforeGetName', $this);

		$eventResult = Core_Event::getLastReturn();

		if (!is_null($eventResult))
		{
			return $eventResult;
		}

		$nameColumn = $this->_nameColumn;

		return htmlspecialchars((string) $this->$nameColumn);
	}

	/**
	 * Get entity description
	 * @return string
	 * @hostcms-event modelname.onBeforeGetTrashDescription
	 */
	public function getTrashDescription()
	{
		Core_Event::notify($this->_modelName . '.onBeforeGetTrashDescription', $this);

		$eventResult = Core_Event::getLastReturn();

		if (!is_null($eventResult))
		{
			return $eventResult;
		}

		$text = isset($this->description) && $this->description != ''
			? $this->description
			: (
				isset($this->text) && $this->text != ''
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
			? Core::_('Admin.copy', $prefix . $this->$nameColumn, date('d.m.Y H:i:s'), FALSE)
			: $this->$nameColumn;
	}

	/**
	 * Copy object
	 * @return new copied object
	 * @hostcms-event modelname.onBeforeCopy
	 * @hostcms-event modelname.onAfterCopy
	 */
	public function copy()
	{
		$newObject = clone $this;

		Core_Event::notify($this->_modelName . '.onBeforeCopy', $newObject, array($this));

		$nameColumn = $this->_nameColumn;
		$nameColumn != 'id' && $newObject->$nameColumn = $this->_getCopiedName();
		$newObject->save();

		if (Core::moduleIsActive('field'))
		{
			$aFields = Field_Controller::getFields($this->getModelName());

			if (count($aFields))
			{
				$aFieldsIds = array();
				foreach ($aFields as $oField)
				{
					$aFieldsIds[] = $oField->id;
				}

				$aField_Values = Field_Controller_Value::getFieldsValues($aFieldsIds, $this->getPrimaryKey(), FALSE);
				foreach ($aField_Values as $oField_Value)
				{
					$newFieldValue = clone $oField_Value;

					$newFieldValue->entity_id = $newObject->id;
					$newFieldValue->save();

					if ($oField_Value->Field->type == 2)
					{
						$fieldDir = CMS_FOLDER . Field_Controller::getPath($this);
						$oField_Value->setDir($fieldDir);

						$newFieldDir = CMS_FOLDER . Field_Controller::getPath($newObject);
						$newFieldValue->setDir($newFieldDir);

						try {
							if ($oField_Value->file != '')
							{
								Core_File::copy($oField_Value->getLargeFilePath(), $newFieldValue->getLargeFilePath());
							}
						} catch (Exception $e) {}

						try {
							if ($oField_Value->file_small != '')
							{
								Core_File::copy($oField_Value->getSmallFilePath(), $newFieldValue->getSmallFilePath());
							}
						} catch (Exception $e) {}
					}
				}
			}
		}

		Core_Event::notify($this->_modelName . '.onAfterCopy', $newObject, array($this));

		return $newObject;
	}

	/**
	 * Get Related Site
	 * @return Site_Model|NULL
	 * @hostcms-event modelname.onBeforeGetRelatedSite
	 * @hostcms-event modelname.onAfterGetRelatedSite
	 */
	public function getRelatedSite()
	{
		Core_Event::notify($this->_modelName . '.onBeforeGetRelatedSite', $this);

		$oSite = isset($this->site_id) && isset($this->_relations['site'])
			? $this->Site
			: NULL;

		Core_Event::notify($this->_modelName . '.onAfterGetRelatedSite', $this, array($oSite));

		return $oSite;
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