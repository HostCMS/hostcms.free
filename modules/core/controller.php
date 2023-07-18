<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Default entity controller.
 *
 * Set XSL:
 * <code>
 * ->xsl(
 * 	Core_Entity::factory('Xsl')->getByName('myXslName')
 * )
 * </code>
 *
 * Add external entity:
 * <code>
 * ->addEntity(
 * 	Core::factory('Core_Xml_Entity')->name('my_tag')->value(123)
 * )
 * </code>
 *
 * Add additional cache signature:
 * <code>
 * ->addCacheSignature('option=' . $value)
 * </code>
 *
 * @package HostCMS
 * @subpackage Core
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Core_Controller extends Core_Servant_Properties
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array();

	/**
	 * Entity
	 * @var Core_Entity
	 */
	protected $_entity = NULL;

	/**
	 * XSL
	 * @var Xsl_Model
	 */
	protected $_xsl = NULL;

	/**
	 * TPL
	 * @var Tpl_Model
	 */
	protected $_tpl = NULL;

	/**
	 * Controller's mode
	 * @var string
	 */
	protected $_mode = 'json';

	/**
	 * Attribute's prefix
	 * @var string
	 */
	protected $_attributePrefix = '_';

	/**
	 * Cache tags
	 * @var array
	 */
	protected $_cacheTags = array();

	/**
	 * Constructor.
	 * @param Core_Entity $oEntity entity
	 */
	public function __construct(Core_Entity $oEntity)
	{
		$this->setEntity($oEntity);
		parent::__construct();
		$this->addCacheSignature('entityId=' . $oEntity->getPrimaryKey());
	}

	/**
	 * List of children entities
	 * @var array
	 */
	protected $_entities = array();

	/**
	 * Add a children entity
	 *
	 * @param Core_Entity $oChildrenEntity
	 * @return self
	 */
	public function addEntity($oChildrenEntity)
	{
		$this->_entities[] = $oChildrenEntity;
		return $this;
	}

	/**
	 * Set entity
	 * @param Core_Entity $entity entity
	 * @return self
	 */
	public function setEntity(Core_Entity $entity)
	{
		$this->_entity = $entity;
		return $this;
	}

	/**
	 * Get entity
	 * @return object
	 */
	public function getEntity()
	{
		return $this->_entity;
	}

	/**
	 * Add enities
	 * @param array $aChildrenEntities entities
	 * @return self
	 */
	public function addEntities(array $aChildrenEntities)
	{
		foreach ($aChildrenEntities as $oEntity)
		{
			$this->addEntity($oEntity);
		}

		return $this;
	}

	/**
	 * Get enities
	 * @return array
	 */
	public function getEntities()
	{
		return $this->_entities;
	}

	/**
	 * Clear enities
	 * @return self
	 */
	public function clearEntities()
	{
		$this->_entities = array();
		return $this;
	}

	/**
	 * Set XSL
	 * @param Xsl_Model|string $xsl
	 * @return self
	 */
	public function xsl($xsl)
	{
		if (is_string($xsl))
		{
			$oXsl = Core_Entity::factory('Xsl')->getByName($xsl);
			if (is_null($oXsl))
			{
				throw new Core_Exception('Xsl %name does not exist.', array('%name' => $xsl));
			}
		}
		else
		{
			$oXsl = $xsl;
		}

		if (!($oXsl instanceof Xsl_Model))
		{
			throw new Core_Exception('Wrong Xsl object "%type".', array('%type' => get_class($oXsl)));
		}

		$this->_xsl = $oXsl;
		$this->_mode = 'xsl';

		return $this;
	}

	/**
	 * Get XSL
	 * @return Xsl_Model
	 */
	public function getXsl()
	{
		return $this->_xsl;
	}

	/**
	 * Get XML for entity and children entities
	 * @return string
	 */
	public function getXml()
	{
		$this->_entity
			->clearEntities()
			->addEntities($this->_entities);

		// Apply Forbidden-Allowed tags for root entity
		$this->applyForbiddenAllowedTags('/', $this->_entity);

		return $this->_entity->getXml();
	}

	/**
	 * Get ARRAY for entity and children entities
	 * @return array
	 */
	public function getStdObject()
	{
		$this->_entity
			->clearEntities()
			->addEntities($this->_entities);

		$oReturn = new stdClass();

		$propertyName = $this->_entity->getXmlTagName();

		$oReturn->$propertyName = $this->_entity->getStdObject($this->_attributePrefix);

		return $oReturn;
	}

	/**
	 * Set mode
	 * @param string $mode
	 * @return self
	 */
	public function mode($mode)
	{
		$this->_mode = $mode;
		return $this;
	}

	/**
	 * Set TPL
	 * @param Tpl_Model|string $tpl
	 * @return self
	 */
	public function tpl($tpl)
	{
		if (is_string($tpl))
		{
			$oTpl = Core_Entity::factory('Tpl')->getByName($tpl);
			if (is_null($oTpl))
			{
				throw new Core_Exception('Tpl %name does not exist.', array('%name' => $tpl));
			}
		}
		else
		{
			$oTpl = $tpl;
		}

		if (!($oTpl instanceof Tpl_Model))
		{
			throw new Core_Exception('Wrong Tpl object "%type".', array('%type' => get_class($oTpl)));
		}

		$this->_tpl = $oTpl;
		$this->_mode = 'tpl';

		return $this;
	}

	/**
	 * Get TPL
	 * @return Tpl_Model
	 */
	public function getTpl()
	{
		return $this->_tpl;
	}

	/**
	 * Variables/objects to the TPL-template
	 * @var array
	 */
	protected $_vars = array();

	/**
	 * Assign variables/objects to the TPL-template
	 * @return self
	 */
	public function assign($varname, $var)
	{
		$this->_vars[$varname] = $var;

		return $this;
	}

	/**
	 * Append an element to an assigned array
	 * @return self
	 */
	public function append($varname, $var)
	{
		$this->_vars[$varname][] = $var;

		return $this;
	}

	/**
	 * Clear vars
	 * @return self
	 */
	public function clearVars()
	{
		$this->_vars = array();
		return $this;
	}

	/**
	 * Show built data
	 * @see get()
	 * @return self
	 * @hostcms-event Core_Controller.onBeforeShow
	 * @hostcms-event Core_Controller.onAfterShow
	 */
	public function show()
	{
		echo $this->get();

		return $this;
	}

	/**
	 * Get HTML based by entities tree, use XSL $this->_xsl
	 * @see getXml()
	 * @return string
	 * @hostcms-event Core_Controller.onBeforeShow
	 * @hostcms-event Core_Controller.onAfterShow
	 */
	public function get()
	{
		Core_Event::notify(get_class($this) . '.onBeforeShow', $this);

		//if (!is_null($this->_xsl))
		//{
		switch ($this->_mode)
		{
			case 'xsl':
				$sXml = $this->getXml();

				$return = Xsl_Processor::instance()
					->xml($sXml)
					->xsl($this->_xsl)
					->process();

				Core_Event::notify(get_class($this) . '.onAfterShow', $this, array($sXml));

				$this->clearEntities();
			break;
			case 'tpl':
				$oTpl_Processor = Tpl_Processor::instance();

				$oTpl_Processor->vars($this->_vars);
				$this->clearVars();

				$oTpl_Processor->entities($this->_entities);
				$this->clearEntities();

				$return = $oTpl_Processor
					->tpl($this->_tpl)
					->process();

				Core_Event::notify(get_class($this) . '.onAfterShow', $this);
			break;
			case 'json':
				$return = json_encode($this->getStdObject());
			break;
			default:
				throw new Core_Exception('Core_Controller::get(), wrong mode: %mode.', array('%mode' => $this->_mode));
		}

		$this->_cacheApplyForbiddenAllowedTags = array();

		/*echo "<br />Build HTML from XML and XSL '{$this->_xsl->name}'",
			"<pre>" . htmlspecialchars(
				Xsl_Processor::instance()->formatXml($sXml)
			) . "</pre>";*/

		return $return;
	}

	/**
	 * List of cache signatures
	 * @var array
	 * @ignore
	 */
	protected $_cacheSignatures = array();

	/**
	 * Add additional signature for cache name
	 * @param string $name name
	 * @return self
	 */
	public function addCacheSignature($name)
	{
		$this->_cacheSignatures[] = $name;
		return $this;
	}

	/**
	 * Change attributePrefix
	 * @param string $attributePrefix
	 * @return self
	 */
	public function setAttributePrefix($attributePrefix)
	{
		$this->_attributePrefix = $attributePrefix;
		return $this;
	}

	/**
	 * Shown IDs
	 * @var array
	 */
	protected $_shownIDs = array();

	/**
	 * Get shown IDs
	 * @return array
	 */
	public function getShownIDs()
	{
		return $this->_shownIDs;
	}

	/**
	 * Add Cache Tag
	 * @param string $tagName
	 * @return self
	 */
	public function addCacheTag($tagName)
	{
		$this->_cacheTags[] = $tagName;
		return $this;
	}

	/**
	 * Get Cache Tags
	 * @return array
	 */
	public function getCacheTags()
	{
		return $this->_cacheTags;
	}

	/**
	 * Clear Cache Tag
	 * @return self
	 */
	public function clearCacheTag()
	{
		$this->_cacheTags = array();
		return $this;
	}

	/**
	 * Allowed tags for specified elements
	 *
	 * @var array
	 */
	protected $_allowedTags = array();

	/**
	 * Add tag to allowed tags list
	 * @param string $path Path to item, e.g. /shop/shop_item
	 * @param string $tag tag
	 * @return self
	 */
	public function addAllowedTag($path, $tag)
	{
		$this->_allowedTags[$path][$tag] = $tag;
		return $this;
	}

	/**
	 * Add tags to allowed tags list
	 * @param string $path Path to item, e.g. /shop/shop_item
	 * @param array $aTags array of tags
	 * @return self
	 */
	public function addAllowedTags($path, array $aTags)
	{
		$this->_allowedTags[$path] = isset($this->_allowedTags[$path])
			? array_merge($this->_allowedTags[$path], array_combine($aTags, $aTags))
			: array_combine($aTags, $aTags);
		return $this;
	}

	/**
	 * Remove tag from allowed tags list
	 * @param string $path Path to item, e.g. /shop/shop_item
	 * @param string $tag tag
	 * @return self
	 */
	public function removeAllowedTag($path, $tag)
	{
		if (isset($this->_allowedTags[$path][$tag]))
		{
			unset($this->_allowedTags[$path][$tag]);
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
	 * Forbidden tags for specified elements
	 *
	 * @var array
	 */
	protected $_forbiddenTags = array();

	/**
	 * Add tag to forbidden tags list
	 * @param string $path Path to item, e.g. /shop/shop_item
	 * @param string $tag tag
	 * @return self
	 */
	public function addForbiddenTag($path, $tag)
	{
		$this->_forbiddenTags[$path][$tag] = $tag;
		return $this;
	}

	/**
	 * Add tags to forbidden tags list
	 * @param string $path Path to item, e.g. /shop/shop_item
	 * @param array $aTags array of tags
	 * @return self
	 */
	public function addForbiddenTags($path, array $aTags)
	{
		$this->_forbiddenTags[$path] = isset($this->_forbiddenTags[$path])
			? array_merge($this->_forbiddenTags[$path], array_combine($aTags, $aTags))
			: array_combine($aTags, $aTags);
		return $this;
	}

	/**
	 * Remove tag from forbidden tags list
	 * @param string $path Path to item, e.g. /shop/shop_item
	 * @param string $tag tag
	 * @return self
	 */
	public function removeForbiddenTag($path, $tag)
	{
		if (isset($this->_forbiddenTags[$path][$tag]))
		{
			unset($this->_forbiddenTags[$path][$tag]);
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
	 * Cache for applyForbiddenAllowedTags
	 * @var array
	 */
	protected $_cacheApplyForbiddenAllowedTags = array();

	/**
	 * Apply Forbidden-Allowed Tags for Entity
	 * @param string $path Path to item, e.g. /shop/shop_item
	 * @param Core_Entity|array $mEntity
	 * @return self
	 */
	public function applyForbiddenAllowedTags($path, $mEntity)
	{
		if (!isset($this->_cacheApplyForbiddenAllowedTags[$path]))
		{
			$this->_cacheApplyForbiddenAllowedTags[$path] = array();

			$aPath = explode('|', $path);

			foreach ($aPath as $sTmpPath)
			{
				if (isset($this->_allowedTags[$sTmpPath]) || isset($this->_forbiddenTags[$sTmpPath]))
				{
					isset($this->_allowedTags[$sTmpPath])
						&& $this->_cacheApplyForbiddenAllowedTags[$path]['allowed'] = $this->_allowedTags[$sTmpPath];

					isset($this->_forbiddenTags[$sTmpPath])
						&& $this->_cacheApplyForbiddenAllowedTags[$path]['forbidden'] = $this->_forbiddenTags[$sTmpPath];

					break;
				}
			}
		}

		if (isset($this->_cacheApplyForbiddenAllowedTags[$path]['allowed']))
		{
			if (is_array($mEntity))
			{
				foreach ($mEntity as $oEntity)
				{
					$oEntity->addAllowedTags($this->_cacheApplyForbiddenAllowedTags[$path]['allowed']);
				}
			}
			else
			{
				$mEntity->addAllowedTags($this->_cacheApplyForbiddenAllowedTags[$path]['allowed']);
			}
		}

		if (isset($this->_cacheApplyForbiddenAllowedTags[$path]['forbidden']))
		{
			if (is_array($mEntity))
			{
				foreach ($mEntity as $oEntity)
				{
					$oEntity->addForbiddenTags($this->_cacheApplyForbiddenAllowedTags[$path]['forbidden']);
				}
			}
			else
			{
				$mEntity->addForbiddenTags($this->_cacheApplyForbiddenAllowedTags[$path]['forbidden']);
			}
		}

		return $this;
	}

	/**
	 * Convert object to string
	 * @return string
	 * @ignore
	 */
	public function __toString()
	{
		$str = parent::__toString();
		!is_null($this->_xsl) && $str .= ',xsl=' . $this->_xsl->name;
		count($this->_cacheSignatures) && $str .= ',cacheSignatures=' . implode(',', $this->_cacheSignatures);

		return $str;
	}
}