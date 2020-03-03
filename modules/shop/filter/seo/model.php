<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Filter_Seo_Model
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2020 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Filter_Seo_Model extends Core_Entity
{
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
	 * Backend callback method
	 * @return string
	 */
	public function nameBackend()
	{
		$oCore_Html_Entity_Div = Core::factory('Core_Html_Entity_Div')->value(
			htmlspecialchars($this->name)
		);

		$sPath = $this->Shop->Structure->getPath() . $this->getUrl();

		if (!$this->active)
		{
			$oCore_Html_Entity_Div->class('inactive');
		}

		$oCore_Html_Entity_Div->add(
			Core::factory('Core_Html_Entity_A')
				->href($sPath)
				->target('_blank')
				->add(
					Core::factory('Core_Html_Entity_I')->class('fa fa-external-link')
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
	 * @return string
	 */
	public function conditionsBackend()
	{
		$aValues = array();

		$aShop_Filter_Seo_Properties = $this->Shop_Filter_Seo_Properties->findAll(FALSE);

		foreach ($aShop_Filter_Seo_Properties as $oShop_Filter_Seo_Property)
		{
			$aValues[$oShop_Filter_Seo_Property->property_id][] = $oShop_Filter_Seo_Property->value;
		}

		$linkedObject = Core_Entity::factory('Shop_Item_Property_List', $this->shop_id);

		// Массив свойств товаров, разрешенных для группы $shop_group_id
		$aProperties = $linkedObject->getPropertiesForGroup($this->shop_group_id);

		$aAvailableProperties = array(0, 11, 1, 7);
		Core::moduleIsActive('list') && $aAvailableProperties[] = 3;
		?>
		<div class="fill-form-text">
			<?php
			foreach ($aProperties as $oProperty)
			{
				if (in_array($oProperty->type, $aAvailableProperties))
				{
					if (isset($aValues[$oProperty->id]))
					{
						?>
						<span class="field-name"><?php echo htmlspecialchars($oProperty->name) . ': '?></span>
						<?php
							$aResult = array();

							foreach ($aValues[$oProperty->id] as $value)
							{
								switch ($oProperty->type)
								{
									case 3:
										if (Core::moduleIsActive('list'))
										{
											$oList_Item = $oProperty->List->List_Items->getById($value, FALSE);

											!is_null($oList_Item)
												&& $aResult[] = $oList_Item->value;
										}
									break;
									default:
										$aResult[] = $value;
								}
							}
						?>
						<span><?php echo htmlspecialchars(implode(', ', $aResult))?></span>
						<?php
					}
				}
			}
			?>
		</div>
		<?php
	}

	public function getUrl()
	{
		$url = '';

		if ($this->shop_group_id)
		{
			$url .= $this->Shop_Group->getPath();
		}

		if ($this->shop_producer_id)
		{
			$url .= rawurlencode($this->Shop_Producer->path) . '/';
		}

		$aValues = array();

		$aShop_Filter_Seo_Properties = $this->Shop_Filter_Seo_Properties->findAll(FALSE);
		foreach ($aShop_Filter_Seo_Properties as $oShop_Filter_Seo_Property)
		{
			$aValues[$oShop_Filter_Seo_Property->property_id][] = $oShop_Filter_Seo_Property->value;
		}

		$linkedObject = Core_Entity::factory('Shop_Item_Property_List', $this->shop_id);

		// Массив свойств товаров, разрешенных для группы $shop_group_id
		$aProperties = $linkedObject->getPropertiesForGroup($this->shop_group_id);

		$aAvailableProperties = array(0, 11, 1, 7);
		Core::moduleIsActive('list') && $aAvailableProperties[] = 3;

		foreach ($aProperties as $oProperty)
		{
			if (in_array($oProperty->type, $aAvailableProperties))
			{
				if (isset($aValues[$oProperty->id]))
				{
					$url .= rawurlencode($oProperty->tag_name) . '/';

					foreach ($aValues[$oProperty->id] as $value)
					{
						switch ($oProperty->type)
						{
							case 3: // List
								if (Core::moduleIsActive('list'))
								{
									$oList_Item = $oProperty->List->List_Items->getById($value, FALSE);

									!is_null($oList_Item)
										&& $url .= rawurlencode($oList_Item->value) . '/';
								}
							break;
							case 7: // Checkbox
								// nothing to do
							break;
							default:
								$url .= rawurlencode($value) . '/';
						}
					}
				}
			}
		}

		return $url;
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
		$oSearch_Page->module_value_type = 4; // search_page_module_value_type
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
		if (Core::moduleIsActive('search') && $this->active)
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
			Search_Controller::deleteSearchPage(3, 4, $this->id);
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
	 */
	public function copy()
	{
		$newObject = parent::copy();

		$aShop_Filter_Seo_Properties = $this->Shop_Filter_Seo_Properties->findAll(FALSE);
		foreach ($aShop_Filter_Seo_Properties as $oShop_Filter_Seo_Property)
		{
			$newObject->add(clone $oShop_Filter_Seo_Property);
		}

		return $newObject;
	}
}