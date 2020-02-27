<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Показ навигационной цепочки структуры сайта.
 *
 * Доступные методы:
 *
 * - current($parentId) вывод потомков узла структуры $parentId
 * - showProperties(TRUE) выводить значения дополнительных свойств, по умолчанию NULL
 * - cache(TRUE|FALSE) использовать кэширование, по умолчанию TRUE
 *
 * <code>
 * $Structure_Controller_Breadcrumbs = new Structure_Controller_Breadcrumbs(
 * 		Core_Entity::factory('Site', 1)
 * 	);
 *
 * 	$Structure_Controller_Breadcrumbs
 * 		->xsl(
 * 			Core_Entity::factory('Xsl')->getByName('ХлебныеКрошки')
 * 		)
 * 		->show();
 * </code>
 *
 * @package HostCMS
 * @subpackage Structure
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2020 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Structure_Controller_Breadcrumbs extends Core_Controller
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'current',
		'showProperties',
		'showInformationsystem',
		'showShop',
		'showForum',
		'showMessage',
		'showHelpdesk',
		'cache',
		'informationsystem_item_id',
		'informationsystem_group_id',
		'shop_item_id',
		'shop_group_id',
		'forum_category_id',
		'forum_topic_id',
		'forbiddenTags',
		'message_topic_id',
		'helpdesk_ticket_id',
	);

	/**
	 * List of properties
	 * @var array
	 */
	protected $_aProperties = array();

	/**
	 * List of property directories
	 * @var array
	 */
	protected $_aProperty_Dirs = array();

	/**
	 * Constructor.
	 * @param Site_Model $oSite site
	 */
	public function __construct(Site_Model $oSite)
	{
		parent::__construct(
			$oSite
				->showXmlAlias(FALSE)
				->showXmlSiteuserIdentityProviders(FALSE)
				->clearEntities()
		);

		$this->forbiddenTags = array('description', 'text', 'seo_title', 'seo_description', 'seo_keywords');

		$this->current = Core_Page::instance()->structure->id;

		$this->showInformationsystem = $this->showShop = $this->showForum = $this->showMessage = $this->showHelpdesk = TRUE;

		$this->cache = TRUE;
	}

	/**
	 * List of information systems
	 * @var array
	 */
	protected $_Informationsystems = array();

	/**
	 * List of breadcrumbs items
	 * @var array
	 */
	protected $_breadcrumbs = array();

	/**
	 * Add breadcrumb
	 * @param Core_Entity $oObject
	 * @return self
	 */
	public function addBreadcrumb(Core_Entity $oObject)
	{
		$this->_breadcrumbs[] = $oObject;
		return $this;
	}

	/**
	 * Add breadcrumbs
	 * @param array $aObjects
	 * @return self
	 */
	public function addBreadcrumbs(array $aObjects)
	{
		$this->_breadcrumbs = array_merge($this->_breadcrumbs, $aObjects);
		return $this;
	}

	/**
	 * Show built data
	 * @return self
	 * @hostcms-event Structure_Controller_Breadcrumbs.onBeforeRedeclaredShow
	 * @hostcms-event Structure_Controller_Breadcrumbs.onAfterAddInformationsystemItem
	 * @hostcms-event Structure_Controller_Breadcrumbs.onAfterAddInformationsystemGroups
	 * @hostcms-event Structure_Controller_Breadcrumbs.onAfterAddShopItem
	 * @hostcms-event Structure_Controller_Breadcrumbs.onAfterAddShopGroups
	 * @hostcms-event Structure_Controller_Breadcrumbs.onAfterAddStructure
	 */
	public function show()
	{
		Core_Event::notify(get_class($this) . '.onBeforeRedeclaredShow', $this);

		if (is_object(Core_Page::instance()->object))
		{
			if ($this->showInformationsystem && Core_Page::instance()->object instanceof Informationsystem_Controller_Show)
			{
				if (Core_Page::instance()->object->item)
				{
					$this->informationsystem_item_id = Core_Page::instance()->object->item;
				}

				if (Core_Page::instance()->object->group)
				{
					$this->informationsystem_group_id = Core_Page::instance()->object->group;
				}
			}

			if ($this->showShop && Core_Page::instance()->object instanceof Shop_Controller_Show)
			{
				if (Core_Page::instance()->object->item)
				{
					$this->shop_item_id = Core_Page::instance()->object->item;
				}

				if (Core_Page::instance()->object->group)
				{
					$this->shop_group_id = Core_Page::instance()->object->group;
				}
			}

			if ($this->showMessage && Core_Page::instance()->object instanceof Message_Controller_Show)
			{
				$this->message_topic_id = Core_Page::instance()->object->topic;
			}

			if ($this->showHelpdesk && Core_Page::instance()->object instanceof Helpdesk_Controller_Show)
			{
				if (Core_Page::instance()->object->ticket)
				{
					$this->helpdesk_ticket_id = Core_Page::instance()->object->ticket;
				}
			}

			if ($this->showForum && Core_Page::instance()->object instanceof Forum_Controller_Show)
			{
				if (Core_Page::instance()->object->category)
				{
					$this->forum_category_id = Core_Page::instance()->object->category;
				}

				if (Core_Page::instance()->object->topic)
				{
					$this->forum_topic_id = Core_Page::instance()->object->topic;
				}
			}
		}

		if ($this->cache && Core::moduleIsActive('cache'))
		{
			$oCore_Cache = Core_Cache::instance(Core::$mainConfig['defaultCache']);
			$inCache = $oCore_Cache->get($cacheKey = strval($this), $cacheName = 'structure_breadcrumbs');

			if (!is_null($inCache))
			{
				echo $inCache;
				return $this;
			}
		}

		$this->addEntity(
			Core::factory('Core_Xml_Entity')
				->name('current_structure_id')
				->value($this->current)
		);

		$this->_breadcrumbs = array();

		if (is_object(Core_Page::instance()->object))
		{
			if ($this->showInformationsystem && Core_Page::instance()->object instanceof Informationsystem_Controller_Show)
			{
				if ($this->informationsystem_item_id)
				{
					$oInformationsystem_Item = Core_Entity::factory('Informationsystem_Item', $this->informationsystem_item_id);

					Core_Event::notify(get_class($this) . '.onBeforeAddInformationsystemItem', $this, array($oInformationsystem_Item));

					$oInformationsystem_Item
						->clearEntities()
						->addForbiddenTag('url')
						->addEntity(
							Core::factory('Core_Xml_Entity')
								->name('link')
								->value(
									$oInformationsystem_Item->Informationsystem->Structure->getPath() . $oInformationsystem_Item->getPath()
								)
						)->addEntity(
							Core::factory('Core_Xml_Entity')
								->name('show')
								->value($oInformationsystem_Item->active)
						);

					$this->addBreadcrumb($oInformationsystem_Item);

					Core_Event::notify(get_class($this) . '.onAfterAddInformationsystemItem', $this, array($oInformationsystem_Item));
				}

				if ($this->informationsystem_group_id)
				{
					$groupId = $this->informationsystem_group_id;

					Core_Event::notify(get_class($this) . '.onBeforeAddInformationsystemGroups', $this, array($groupId));

					$aInformationsystem_Groups = array();

					while ($groupId)
					{
						$oInformationsystem_Group = Core_Entity::factory('Informationsystem_Group', $groupId);

						$oInformationsystem_Group
							->clearEntities()
							->addForbiddenTag('url')
							->addEntity(
								Core::factory('Core_Xml_Entity')
									->name('link')
									->value(
										$oInformationsystem_Group->Informationsystem->Structure->getPath() . $oInformationsystem_Group->getPath()
									)
							)->addEntity(
								Core::factory('Core_Xml_Entity')
									->name('show')
									->value($oInformationsystem_Group->active)
							);

						$groupId = $oInformationsystem_Group->parent_id;
						$aInformationsystem_Groups[] = $oInformationsystem_Group;
					}

					$this->addBreadcrumbs($aInformationsystem_Groups);

					Core_Event::notify(get_class($this) . '.onAfterAddInformationsystemGroups', $this, array($aInformationsystem_Groups));
				}
			}

			if ($this->showShop && Core_Page::instance()->object instanceof Shop_Controller_Show)
			{
				if ($this->shop_item_id)
				{
					$oShop_Item = Core_Entity::factory('Shop_Item', $this->shop_item_id);

					Core_Event::notify(get_class($this) . '.onBeforeAddShopItem', $this, array($oShop_Item));

					$oShop_Item
						->clearEntities()
						->addForbiddenTag('url')
						->addEntity(
							Core::factory('Core_Xml_Entity')
								->name('link')
								->value(
									$oShop_Item->Shop->Structure->getPath() . $oShop_Item->getPath()
								)
						)->addEntity(
							Core::factory('Core_Xml_Entity')
								->name('show')
								->value($oShop_Item->active)
						);

					// Если модификация, то сначала идет родительский товар, а в нем модификация
					if ($oShop_Item->modification_id)
					{
						$oShop_Item = $oShop_Item->Modification
							->clearEntities()
							->addEntity($oShop_Item);
					}

					$this->addBreadcrumb($oShop_Item);

					Core_Event::notify(get_class($this) . '.onAfterAddShopItem', $this, array($oShop_Item));
				}

				if ($this->shop_group_id)
				{
					$groupId = $this->shop_group_id;

					if (is_array($groupId))
					{
						// Get First Item
						$groupId = Core_Array::first($groupId);
					}

					Core_Event::notify(get_class($this) . '.onBeforeAddShopGroups', $this, array($groupId));

					$aShop_Groups = array();

					while ($groupId)
					{
						$oShop_Group = Core_Entity::factory('Shop_Group', $groupId);

						$oShop_Group
							->clearEntities()
							->addForbiddenTag('url')
							->addEntity(
								Core::factory('Core_Xml_Entity')
									->name('link')
									->value(
										$oShop_Group->Shop->Structure->getPath() . $oShop_Group->getPath()
									)
							)->addEntity(
								Core::factory('Core_Xml_Entity')
									->name('show')
									->value($oShop_Group->active)
							);

						$groupId = $oShop_Group->parent_id;
						$aShop_Groups[] = $oShop_Group;
					}

					$this->addBreadcrumbs($aShop_Groups);

					Core_Event::notify(get_class($this) . '.onAfterAddShopGroups', $this, array($aShop_Groups));
				}
			}

			if ($this->showMessage && Core_Page::instance()->object instanceof Message_Controller_Show)
			{
				if ($this->message_topic_id)
				{
					$oMessage_Topic = Core_Entity::factory('Message_Topic', $this->message_topic_id);

					Core_Event::notify(get_class($this) . '.onBeforeAddMessageTopic', $this, array($oMessage_Topic));

					$sPath = Core_Page::instance()->structure->getPath() . $oMessage_Topic->id . '/';

					$oMessage_Topic
						->clearEntities()
						->addForbiddenTag('url')
						->addEntity(
							Core::factory('Core_Xml_Entity')
								->name('link')
								->value($sPath)
						)->addEntity(
							Core::factory('Core_Xml_Entity')
								->name('show')
								->value(1)
						);

					$this->addBreadcrumb($oMessage_Topic);

					Core_Event::notify(get_class($this) . '.onAfterAddMessageTopic', $this, array($oMessage_Topic));
				}
			}

			if ($this->showHelpdesk && Core_Page::instance()->object instanceof Helpdesk_Controller_Show)
			{
				if ($this->helpdesk_ticket_id)
				{
					$oHelpdesk_Ticket = Core_Entity::factory('Helpdesk_Ticket', $this->helpdesk_ticket_id);

					Core_Event::notify(get_class($this) . '.onBeforeAddHelpdeskTicket', $this, array($oHelpdesk_Ticket));

					$sPath = Core_Page::instance()->structure->getPath() . 'ticket-' . $oHelpdesk_Ticket->id . '/';

					$oHelpdesk_Ticket
						->clearEntities()
						->addForbiddenTag('url')
						->addEntity(
							Core::factory('Core_Xml_Entity')
								->name('link')
								->value($sPath)
						)->addEntity(
							Core::factory('Core_Xml_Entity')
								->name('name')
								->value($oHelpdesk_Ticket->number)
						)->addEntity(
							Core::factory('Core_Xml_Entity')
								->name('show')
								->value(1)
						);

					$this->addBreadcrumb($oHelpdesk_Ticket);

					Core_Event::notify(get_class($this) . '.onAfterAddHelpdeskTicket', $this, array($oHelpdesk_Ticket));
				}
			}

			if ($this->showForum && Core_Page::instance()->object instanceof Forum_Controller_Show)
			{
				if ($this->forum_topic_id)
				{
					$oForum_Topic = Core_Entity::factory('Forum_Topic', $this->forum_topic_id);

					Core_Event::notify(get_class($this) . '.onBeforeAddForumTopic', $this, array($oForum_Topic));

					$oForum_Topic_Post = $oForum_Topic->Forum_Topic_Posts->getFirstPost();

					if (!is_null($oForum_Topic_Post))
					{
						$oForum_Topic
							->clearEntities()
							->addForbiddenTag('url')
							->addEntity(
								Core::factory('Core_Xml_Entity')
									->name('name')
									->value(
										$oForum_Topic_Post->subject
									)
							)
							->addEntity(
								Core::factory('Core_Xml_Entity')
									->name('link')
									->value(
										$oForum_Topic->getPath()
									)
							)->addEntity(
								Core::factory('Core_Xml_Entity')
									->name('show')
									->value(1)
							);

						$this->addBreadcrumb($oForum_Topic);

						Core_Event::notify(get_class($this) . '.onAfterAddForumTopic', $this, array($oForum_Topic));
					}
				}

				if ($this->forum_category_id)
				{
					$oForum_Category = Core_Entity::factory('Forum_Category', $this->forum_category_id);

					Core_Event::notify(get_class($this) . '.onBeforeAddForumCategory', $this, array($oForum_Category));

					$oForum_Category
						->clearEntities()
						->addForbiddenTag('url')
						->addEntity(
							Core::factory('Core_Xml_Entity')
								->name('link')
								->value(
									$oForum_Category->getPath()
								)
						)->addEntity(
							Core::factory('Core_Xml_Entity')
								->name('show')
								->value(1)
						);

					$this->addBreadcrumb($oForum_Category);

					Core_Event::notify(get_class($this) . '.onAfterAddForumCategory', $this, array($oForum_Category));
				}
			}
		}

		$oStructure = Core_Entity::factory('Structure', $this->current)
			->clearEntities();

		do {
			Core_Event::notify(get_class($this) . '.onBeforeAddStructure', $this, array($oStructure));

			$this->addBreadcrumb($oStructure->clearEntities());

			Core_Event::notify(get_class($this) . '.onAfterAddStructure', $this, array($oStructure));
		} while ($oStructure = $oStructure->getParent());

		$this->_breadcrumbs = array_reverse($this->_breadcrumbs);

		$object = $this;
		foreach ($this->_breadcrumbs as $oStructure)
		{
			$this->applyForbiddenTags($oStructure);

			method_exists($oStructure, 'showXmlProperties')
				&& $oStructure->showXmlProperties($this->showProperties);

			$object->addEntity($oStructure);
			$object = $oStructure;
		}

		$oSite = $this->getEntity();

		// Показывать дополнительные свойства
		if ($this->showProperties)
		{
			$oStructure_Property_List = Core_Entity::factory('Structure_Property_List', $oSite->id);

			$aProperties = $oStructure_Property_List->Properties->findAll();
			foreach ($aProperties as $oProperty)
			{
				$this->_aProperties[$oProperty->property_dir_id][] = $oProperty;

				// Load all values for property
				$oProperty->loadAllValues();
			}

			$aProperty_Dirs = $oStructure_Property_List->Property_Dirs->findAll();
			foreach ($aProperty_Dirs as $oProperty_Dir)
			{
				$oProperty_Dir->clearEntities();
				$this->_aProperty_Dirs[$oProperty_Dir->parent_id][] = $oProperty_Dir;
			}

			$this->_addPropertyList(0, $this);
		}

		// Clear
		$this->_aProperty_Dirs = $this->_aProperties = array();

		echo $content = $this->get();
		$this->cache && Core::moduleIsActive('cache') && $oCore_Cache->set($cacheKey, $content, $cacheName);

		return $this;
	}

	/**
	 * Create the tree of property dirs and properties
	 * @param int $parent_id property group ID
	 * @param object $parentObject
	 * @return self
	 */
	protected function _addPropertyList($parent_id, $parentObject)
	{
		if (isset($this->_aProperty_Dirs[$parent_id]))
		{
			foreach ($this->_aProperty_Dirs[$parent_id] as $oProperty_Dir)
			{
				$parentObject->addEntity($oProperty_Dir);
				$this->_addPropertyList($oProperty_Dir->id, $oProperty_Dir);
			}
		}

		if (isset($this->_aProperties[$parent_id]))
		{
			$parentObject->addEntities($this->_aProperties[$parent_id]);
		}

		return $this;
	}

	/**
	 * Apply forbidden tags
	 * @param Structure $oStructure
	 * @return self
	 */
	public function applyForbiddenTags($oStructure)
	{
		if (!is_null($this->forbiddenTags))
		{
			foreach ($this->forbiddenTags as $forbiddenTag)
			{
				$oStructure->addForbiddenTag($forbiddenTag);
			}
		}

		return $this;
	}
}
