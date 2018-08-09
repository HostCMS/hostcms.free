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
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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

		return $this->_entity->getXml();
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
	 * assign variables/objects to the TPL-template
	 * @return self
	 */
	public function assign($varname, $var)
	{
		$this->_vars[$varname] = $var;

		return $this;
	}

	/**
	 * append an element to an assigned array
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

		if (!is_null($this->_xsl))
		{
			$sXml = $this->getXml();

			$return = Xsl_Processor::instance()
				->xml($sXml)
				->xsl($this->_xsl)
				->process();

			Core_Event::notify(get_class($this) . '.onAfterShow', $this, array($sXml));

			$this->clearEntities();
		}
		elseif (!is_null($this->_tpl))
		{
			$oTpl_Processor = Tpl_Processor::instance();

			$oTpl_Processor->vars($this->_vars);
			$this->clearVars();

			$oTpl_Processor->entities($this->_entities);
			$this->clearEntities();

			$return = $oTpl_Processor
				->tpl($this->_tpl)
				->process();

			Core_Event::notify(get_class($this) . '.onAfterShow', $this);
		}
		else
		{
			throw new Core_Exception('Xsl or Tpl does not exist.');
		}

		/*echo "<br />Build HTML from XML and XSL '{$this->_xsl->name}'",
			"<pre>" . htmlspecialchars(
				Xsl_Processor::instance()->formatXml($sXml)
			) . "</pre>";*/

		return $return;
	}

	/**
	 * List of cache signatures
	 * @var array
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
	 * Convert object to string
	 * @return string
	 */
	public function __toString()
	{
		$str = parent::__toString();
		!is_null($this->_xsl) && $str .= ',xsl=' . $this->_xsl->name;
		count($this->_cacheSignatures) && $str .= ',cacheSignatures=' . implode(',', $this->_cacheSignatures);

		return $str;
	}
}
