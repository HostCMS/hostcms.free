<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Property_Value_Bigint_Model
 *
 * @package HostCMS
 * @subpackage Property
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
 */
class Property_Value_Bigint_Model extends Core_Entity
{
	/**
	 * Model name
	 * @var mixed
	 */
	protected $_modelName = 'property_value_bigint';

	/**
	 * Disable markDeleted()
	 * @var mixed
	 */
	protected $_marksDeleted = NULL;

	/**
	 * Column consist item's name
	 * @var string
	 */
	protected $_nameColumn = 'id';

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'property' => array(),
	);

	/**
	 * Forbidden tags. If list of tags is empty, all tags will show.
	 * @var array
	 */
	protected $_forbiddenTags = array(
		'entity_id'
	);

	/**
	 * Default sorting for models
	 * @var array
	 */
	/*protected $_sorting = array(
		'property_value_bigints.id' => 'ASC'
	);*/

	/**
	 * Set property value
	 * @param int $value value
	 * @return self
	 */
	public function setValue($value)
	{
		$this->value = intval($value);
		return $this;
	}

	/**
	 * Name of the tag in XML
	 * @var string
	 */
	protected $_tagName = 'property_value';

	/**
	 * Module config
	 */
	static public $aConfig = NULL;

	/**
	 * Constructor.
	 * @param string $primaryKey
	 */
	public function __construct($primaryKey = NULL)
	{
		parent::__construct($primaryKey);

		if (is_null(self::$aConfig))
		{
			self::$aConfig = Core_Config::instance()->get('property_config', array()) + array(
				'recursive_properties' => TRUE,
			);
		}
	}

	/**
	 * Get XML for entity and children entities
	 * @return string
	 * @hostcms-event property_value_bigint.onBeforeRedeclaredGetXml
	 */
	public function getXml()
	{
		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredGetXml', $this);

		$this->_prepareData();

		return parent::getXml();
	}

	/**
	 * Get stdObject for entity and children entities
	 * @return stdObject
	 * @hostcms-event property_value_bigint.onBeforeRedeclaredGetStdObject
	 */
	public function getStdObject($attributePrefix = '_')
	{
		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredGetStdObject', $this);

		$this->_prepareData();

		return parent::getStdObject($attributePrefix);
	}

	/**
	 * Show media in XML
	 * @var boolean
	 */
	protected $_showXmlMedia = FALSE;

	/**
	 * Show properties in XML
	 * @param mixed $showXmlProperties array of allowed properties ID or boolean
	 * @return self
	 */
	public function showXmlMedia($showXmlMedia = TRUE)
	{
		$this->_showXmlMedia = $showXmlMedia;

		return $this;
	}

	/**
	 * Prepare entity and children entities
	 * @return self
	 */
	protected function _prepareData()
	{
		$oProperty = $this->Property;

		// ---------------------------
		/*$this->_tagName = $oProperty->tag_name;
		return $this;*/
		// ---------------------------

		$this->clearXmlTags()
			->addXmlTag('property_dir_id', $oProperty->property_dir_id)
			->addXmlTag('tag_name', $oProperty->tag_name);

		!$oProperty->multiple && $this->addForbiddenTag('sorting');

		return $this;
	}

	/**
	 * Convert Object to Array
	 * @return array
	 * @hostcms-event modelname.onAfterToArray
	 */
	public function toArray()
	{
		$return = parent::toArray();

		// List
		if ($this->Property->type == 3 && $this->value != 0 && Core::moduleIsActive('list'))
		{
			$oList_Item = $this->List_Item;

			if ($oList_Item->id)
			{
				$return['list_item'] = $oList_Item->toArray();
			}
		}

		$return['__model_name'] = $this->_modelName;

		return $return;
	}
}