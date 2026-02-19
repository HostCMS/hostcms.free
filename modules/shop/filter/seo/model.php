<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Filter_Seo_Model
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Shop_Filter_Seo_Model extends Core_Entity
{
	/**
	 * Backend property
	 * @var int
	 */
	public $img = 1;

	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		'shop_filter_seo_property' => array(),
	);

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'shop' => array(),
		'shop_group' => array(),
		'shop_producer' => array(),
		'shop_filter_seo_dir' => array(),
		'user' => array()
	);

	/**
	 * Forbidden tags. If list of tags is empty, all tags will be shown.
	 * @var array
	 */
	protected $_forbiddenTags = array(
		'deleted',
		'dataConditionsCount'
	);

	/**
	 * List of preloaded values
	 * @var array
	 */
	protected $_preloadValues = array(
		'active' => 1
	);

	/**
	 * List of Shortcodes tags
	 * @var array
	 */
	protected $_shortcodeTags = array(
		'text'
	);

	/**
	 * Constructor.
	 * @param int $id entity ID
	 */
	public function __construct($id = NULL)
	{
		parent::__construct($id);

		if (is_null($id) && !$this->loaded())
		{
			$oUser = Core_Auth::getCurrentUser();
			$this->_preloadValues['user_id'] = is_null($oUser) ? 0 : $oUser->id;
		}
	}

	/**
	 * Change item status
	 * @return self
	 * @hostcms-event shop_filter_seo.onBeforeChangeActive
	 * @hostcms-event shop_filter_seo.onAfterChangeActive
	 */
	public function changeActive()
	{
		Core_Event::notify($this->_modelName . '.onBeforeChangeActive', $this);

		$this->active = 1 - $this->active;
		$this->save();

		$this->active
			? $this->index()
			: $this->unindex();

		Core_Event::notify($this->_modelName . '.onAfterChangeActive', $this);

		return $this;
	}

	/**
	 * Switch indexing mode
	 * @return self
	 */
	public function changeIndexing()
	{
		$this->indexing = 1 - $this->indexing;
		$this->save();

		if ($this->indexing && Core::moduleIsActive('search'))
		{
			Search_Controller::indexingSearchPages(array(
				$this->indexing()
			));
		}

		return $this;
	}

	/**
	 * Backend callback method
	 */
	public function nameBackend()
	{
		$oCore_Html_Entity_Div = Core_Html_Entity::factory('Div')->value(
			htmlspecialchars($this->name)
		);

		$sPath = $this->Shop->Structure->getPath() . $this->getUrl();

		if (!$this->active)
		{
			$oCore_Html_Entity_Div->class('inactive');
		}

		$oCore_Html_Entity_Div->add(
			Core_Html_Entity::factory('A')
				->href($sPath)
				->target('_blank')
				->add(
					Core_Html_Entity::factory('I')->class('fa fa-external-link')
				)
		);

		$oCore_Html_Entity_Div->execute();
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function shop_group_idBackend()
	{
		return $this->shop_group_id
			? htmlspecialchars($this->Shop_Group->name)
			: '';
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function shop_producer_idBackend()
	{
		return $this->shop_producer_id
			? htmlspecialchars($this->Shop_Producer->name)
			: '';
	}

	/**
	 * Backend callback method
	 */
	public function conditionsBackend()
	{
		$aValues = $aValuesTo = array();

		$aProperties = array();

		$aShop_Filter_Seo_Properties = $this->Shop_Filter_Seo_Properties->findAll(FALSE);

		foreach ($aShop_Filter_Seo_Properties as $oShop_Filter_Seo_Property)
		{
			$aValues[$oShop_Filter_Seo_Property->property_id][] = $oShop_Filter_Seo_Property->value;
			$aValuesTo[$oShop_Filter_Seo_Property->property_id][] = $oShop_Filter_Seo_Property->value_to;

			$oProperty = $oShop_Filter_Seo_Property->Property;

			$aProperties[$oProperty->id] = $oProperty;
		}

		$linkedObject = Core_Entity::factory('Shop_Item_Property_List', $this->shop_id);

		// Массив свойств товаров, разрешенных для группы $shop_group_id
		$aPropertiesForGroup = $linkedObject->getPropertiesForGroup($this->shop_group_id);

		$aAvailableProperyIDs = array();
		foreach ($aPropertiesForGroup as $oProperty)
		{
			$aAvailableProperyIDs[] = $oProperty->id;
		}

		$aAvailableProperties = array(0, 11, 1, 7, 8, 9);
		Core::moduleIsActive('list') && $aAvailableProperties[] = 3;
		?>
		<div class="fill-form-text">
			<div>
			<?php

			if ($this->price_to > 0)
			{
				echo Core::_('Shop_Filter_Seo.prices', floatval($this->price_from), floatval($this->price_to));
			}
			?></div><?php

			// Условия
			foreach ($aProperties as $oProperty)
			{
				if (in_array($oProperty->type, $aAvailableProperties))
				{
					if (isset($aValues[$oProperty->id]))
					{
						?>
						<div class="<?php echo !in_array($oProperty->id, $aAvailableProperyIDs) ? ' line-through' : ''?>"><span class="field-name"><?php echo htmlspecialchars($oProperty->name) . ': '?></span>
						<?php
							$aResult = array();

							foreach ($aValues[$oProperty->id] as $key => $value)
							{

								$aResult[] = $this->_printValue($value, $oProperty) . (
									$oProperty->Shop_Item_Property->filter == 6 && isset($aValuesTo[$oProperty->id][$key])
										? ' — ' . $this->_printValue($aValuesTo[$oProperty->id][$key], $oProperty)
										: ''
								);
							}
						?>
						<span><?php echo htmlspecialchars(implode(', ', $aResult))?></span></div>
						<?php
					}
				}
			}
			?>
		</div>
		<?php
	}

	/**
	 * Get url
	 * @return string
	 */
	public function getUrl()
	{
		$url = '';

		if ($this->shop_group_id)
		{
			$url .= $this->Shop_Group->getPath();
		}

		if ($this->shop_producer_id)
		{
			$url .= rawurlencode($this->Shop->filter_mode == 0
					? $this->Shop_Producer->name
					: $this->Shop_Producer->path
				) . '/';
		}

		if ($this->price_to > 0)
		{
			$url .= 'price-' . floatval($this->price_from) . '-' . floatval($this->price_to) . '/';
		}

		$aValues = array();

		$aShop_Filter_Seo_Properties = $this->Shop_Filter_Seo_Properties->findAll(FALSE);
		foreach ($aShop_Filter_Seo_Properties as $oShop_Filter_Seo_Property)
		{
			$aValues[$oShop_Filter_Seo_Property->property_id][] = is_null($oShop_Filter_Seo_Property->value_to) || $oShop_Filter_Seo_Property->value_to == ''
				? $this->_correctValue($oShop_Filter_Seo_Property->value, $oShop_Filter_Seo_Property->Property)
				: array(
					$this->_correctValue($oShop_Filter_Seo_Property->value, $oShop_Filter_Seo_Property->Property),
					$this->_correctValue($oShop_Filter_Seo_Property->value_to, $oShop_Filter_Seo_Property->Property)
				);
		}

		$linkedObject = Core_Entity::factory('Shop_Item_Property_List', $this->shop_id);

		// Массив свойств товаров, разрешенных для группы $shop_group_id
		$aProperties = $linkedObject->getPropertiesForGroup($this->shop_group_id);

		$aAvailableProperties = array(0, 11, 1, 7, 8, 9);
		Core::moduleIsActive('list') && $aAvailableProperties[] = 3;

		foreach ($aProperties as $oProperty)
		{
			if (in_array($oProperty->type, $aAvailableProperties))
			{
				if (isset($aValues[$oProperty->id]))
				{
					$url .= rawurlencode($oProperty->tag_name);

					$bType6 = $oProperty->type != 3 && $oProperty->type != 7 && $oProperty->type != 1
						&& count($aValues[$oProperty->id]) == 1
						&& is_array($aValues[$oProperty->id][0]);

					$url .= $bType6 ? '-' : '/';

					foreach ($aValues[$oProperty->id] as $mValue)
					{
						switch ($oProperty->type)
						{
							case 3: // List
								if (Core::moduleIsActive('list') && !is_array($mValue))
								{
									$oList_Item = $oProperty->List->List_Items->getById($mValue, FALSE);

									!is_null($oList_Item)
										&& $url .= rawurlencode($this->Shop->filter_mode == 1 && $oList_Item->path != ''
											? $oList_Item->path
											: $oList_Item->value
										) . '/';
								}
							break;
							case 7: // Checkbox
								// nothing to do
							break;
							default:
								if (!is_array($mValue))
								{
									$url .= rawurlencode($mValue);
								}
								else
								{
									$url .= rawurlencode($mValue[0]) . '-' . rawurlencode($mValue[1]);
								}

								$url .= '/';
						}
					}
				}
			}
		}

		return $url;
	}

	/**
	 * Get printable value for backend
	 * @param string $value
	 * @param Property_Model $oProperty
	 * @return string
	 */
	protected function _printValue($value, $oProperty)
	{
		switch ($oProperty->type)
		{
			case 3:
				if (Core::moduleIsActive('list'))
				{
					$oList_Item = $oProperty->List->List_Items->getById($value, FALSE);

					!is_null($oList_Item)
						&& $value = $oList_Item->value;
				}
			break;
			case 8: // Date
				$value = $value == '0000-00-00 00:00:00'
					? ''
					: Core_Date::sql2date($value);
			break;
			case 9: // Datetime
				$value = $value == '0000-00-00 00:00:00'
					? ''
					: Core_Date::sql2datetime($value);
			break;
		}

		return $value;
	}

	/**
	 * Correct value for getUrl()
	 * @param string $value
	 * @param Property_Model $oProperty
	 * @return string
	 */
	protected function _correctValue($value, $oProperty)
	{
		switch ($oProperty->type)
		{
			case 8: // Date
				$value = $value == '0000-00-00 00:00:00'
					? ''
					: Core_Date::sql2date($value);
			break;
			case 9: // Datetime
				$value = $value == '0000-00-00 00:00:00'
					? ''
					: Core_Date::sql2datetime($value);
			break;
		}

		return $value;
	}

	/**
	 * Get XML for entity and children entities
	 * @return string
	 * @hostcms-event shop_filter_seo.onBeforeRedeclaredGetXml
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
	 * @hostcms-event shop_filter_seo.onBeforeRedeclaredGetStdObject
	 */
	public function getStdObject($attributePrefix = '_')
	{
		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredGetStdObject', $this);

		$this->_prepareData();

		return parent::getStdObject($attributePrefix);
	}

	/**
	 * Prepare entity and children entities
	 * @return self
	 */
	protected function _prepareData()
	{
		$this->clearXmlTags();

		$this->_isTagAvailable('url')
			&& $this->addXmlTag('url', $this->Shop->Structure->getPath() . $this->getUrl());

		if ($this->_isTagAvailable('shop_filter_seo_property'))
		{
			$aShop_Filter_Seo_Properties = $this->Shop_Filter_Seo_Properties->findAll();
			foreach ($aShop_Filter_Seo_Properties as $oShop_Filter_Seo_Property)
			{
				$this->addEntity($oShop_Filter_Seo_Property->clearXmlTags());
			}
		}

		return $this;
	}

	/**
	 * Search indexation
	 * @return Search_Page
	 * @hostcms-event shop_filter_seo.onBeforeIndexing
	 * @hostcms-event shop_filter_seo.onAfterIndexing
	 */
	public function indexing()
	{
		$oSearch_Page = new stdClass();

		Core_Event::notify($this->_modelName . '.onBeforeIndexing', $this, array($oSearch_Page));

		$eventResult = Core_Event::getLastReturn();

		if (!is_null($eventResult))
		{
			return $eventResult;
		}

		$oSearch_Page->text = htmlspecialchars($this->seo_title) . ' ' . htmlspecialchars($this->seo_description) . ' ' . htmlspecialchars($this->seo_keywords) . ' ' . $this->text;

		$oSearch_Page->title = $this->h1;

		if (Core::moduleIsActive('field'))
		{
			$aField_Values = Field_Controller_Value::getFieldsValues($this->getFieldIDs(), $this->id);
			foreach ($aField_Values as $oField_Value)
			{
				// List
				if ($oField_Value->Field->type == 3 && Core::moduleIsActive('list'))
				{
					if ($oField_Value->value != 0)
					{
						$oList_Item = $oField_Value->List_Item;
						$oList_Item->id && $oSearch_Page->text .= htmlspecialchars($oList_Item->value) . ' ' . htmlspecialchars($oList_Item->description) . ' ';
					}
				}
				// Informationsystem
				elseif ($oField_Value->Field->type == 5 && Core::moduleIsActive('informationsystem'))
				{
					if ($oField_Value->value != 0)
					{
						$oInformationsystem_Item = $oField_Value->Informationsystem_Item;
						if ($oInformationsystem_Item->id)
						{
							$oSearch_Page->text .= htmlspecialchars($oInformationsystem_Item->name) . ' ' . $oInformationsystem_Item->description . ' ' . $oInformationsystem_Item->text . ' ';
						}
					}
				}
				// Shop
				elseif ($oField_Value->Field->type == 12 && Core::moduleIsActive('shop'))
				{
					if ($oField_Value->value != 0)
					{
						$oShop_Item = $oField_Value->Shop_Item;
						if ($oShop_Item->id)
						{
							$oSearch_Page->text .= htmlspecialchars($oShop_Item->name) . ' ' . $oShop_Item->description . ' ' . $oShop_Item->text . ' ';
						}
					}
				}
				// Wysiwyg
				elseif ($oField_Value->Field->type == 6)
				{
					$oSearch_Page->text .= htmlspecialchars(strip_tags($oField_Value->value)) . ' ';
				}
				// Other type
				elseif ($oField_Value->Field->type != 2)
				{
					$oSearch_Page->text .= htmlspecialchars($oField_Value->value) . ' ';
				}
			}
		}

		$oSiteAlias = $this->Shop->Site->getCurrentAlias();
		if ($oSiteAlias)
		{
			$oSearch_Page->url = ($this->Shop->Structure->https ? 'https://' : 'http://')
				. $oSiteAlias->name
				. $this->Shop->Structure->getPath()
				. $this->getUrl();
		}
		else
		{
			return NULL;
		}

		$oSearch_Page->size = mb_strlen($oSearch_Page->text);
		$oSearch_Page->site_id = $this->Shop->site_id;
		$oSearch_Page->datetime = date('Y-m-d H:i:s');
		$oSearch_Page->module = 3;
		$oSearch_Page->module_id = $this->shop_id;
		$oSearch_Page->inner = 0;
		$oSearch_Page->module_value_type = 5; // search_page_module_value_type
		$oSearch_Page->module_value_id = $this->id; // search_page_module_value_id

		$oSearch_Page->siteuser_groups = array(intval($this->Shop->siteuser_group_id));

		Core_Event::notify($this->_modelName . '.onAfterIndexing', $this, array($oSearch_Page));

		return $oSearch_Page;
	}

	/**
	 * Add item into search index
	 * @return self
	 */
	public function index()
	{
		if (Core::moduleIsActive('search')
			&& $this->indexing && $this->active
		)
		{
			Search_Controller::indexingSearchPages(array($this->indexing()));
		}

		return $this;
	}

	/**
	 * Remove item from search index
	 * @return self
	 */
	public function unindex()
	{
		if (Core::moduleIsActive('search'))
		{
			Search_Controller::deleteSearchPage($this->Shop->site_id, 3, 4, $this->id);
		}

		return $this;
	}

	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return Core_Entity
	 * @hostcms-event shop_filter_seo.onBeforeRedeclaredDelete
	 */
	public function delete($primaryKey = NULL)
	{
		if (is_null($primaryKey))
		{
			$primaryKey = $this->getPrimaryKey();
		}

		$this->id = $primaryKey;

		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredDelete', $this, array($primaryKey));

		$this->Shop_Filter_Seo_Properties->deleteAll(FALSE);

		// Remove from search index
		$this->unindex();

		return parent::delete($primaryKey);
	}

	/**
	 * Copy object
	 * @return Core_Entity
	 * @hostcms-event shop_filter_seo.onAfterRedeclaredCopy
	 */
	public function copy()
	{
		$newObject = parent::copy();

		$aShop_Filter_Seo_Properties = $this->Shop_Filter_Seo_Properties->findAll(FALSE);
		foreach ($aShop_Filter_Seo_Properties as $oShop_Filter_Seo_Property)
		{
			$newObject->add(clone $oShop_Filter_Seo_Property);
		}

		Core_Event::notify($this->_modelName . '.onAfterRedeclaredCopy', $newObject, array($this));

		return $newObject;
	}

	/**
	 * Move seo to another dir
	 * @param int $iFilterSeoDirId target dir id
	 * @return Core_Entity
	 * @hostcms-event shop_filter_seo.onBeforeMove
	 * @hostcms-event shop_filter_seo.onAfterMove
	 */
	public function move($iFilterSeoDirId)
	{
		Core_Event::notify($this->_modelName . '.onBeforeMove', $this, array($iFilterSeoDirId));

		$this->shop_filter_seo_dir_id = $iFilterSeoDirId;
		$this->save();

		Core_Event::notify($this->_modelName . '.onAfterMove', $this);

		return $this;
	}

	/**
	 * Get Related Site
	 * @return Site_Model|NULL
	 * @hostcms-event shop_filter_seo.onBeforeGetRelatedSite
	 * @hostcms-event shop_filter_seo.onAfterGetRelatedSite
	 */
	public function getRelatedSite()
	{
		Core_Event::notify($this->_modelName . '.onBeforeGetRelatedSite', $this);

		$oSite = $this->Shop->Site;

		Core_Event::notify($this->_modelName . '.onAfterGetRelatedSite', $this, array($oSite));

		return $oSite;
	}
}