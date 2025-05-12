<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Comment_Model
 *
 * @package HostCMS
 * @subpackage Comment
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
 */
class Comment_Model extends Core_Entity
{
	/**
	 * Backend property
	 * @var string
	 */
	public $view = '';

	/**
	 * Backend property
	 * @var string
	 */
	public $fulltext = '';

	/**
	 * Column consist item's name
	 * @var string
	 */
	protected $_nameColumn = 'datetime';

	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		'comment' => array('foreign_key' => 'parent_id'),
		'vote' => array('through' => 'vote_comment')
	);

	/**
	 * One-to-one relations
	 * @var array
	 */
	protected $_hasOne = array(
		'comment_informationsystem_item' => array('foreign_key' => 'comment_id'),
		'informationsystem_item' => array('through' => 'comment_informationsystem_item'),
		'comment_shop_item' => array('foreign_key' => 'comment_id'),
		'shop_item' => array('through' => 'comment_shop_item'),
		'comment_shop_order' => array('foreign_key' => 'comment_id'),
		'shop_order' => array('through' => 'comment_shop_order')
	);

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'comment' => array('foreign_key' => 'parent_id'),
		'user' => array(),
		'siteuser' => array(),
	);

	/**
	 * List of preloaded values
	 * @var array
	 */
	protected $_preloadValues = array(
		'text' => '',
		'siteuser_id' => 0,
		'parent_id' => 0,
		'grade' => 0,
		'active' => 1
	);

	/**
	 * Forbidden tags. If list of tags is empty, all tags will show.
	 * @var array
	 */
	protected $_forbiddenTags = array(
		'deleted',
		'user_id',
		'datetime'
	);

	/**
	 * Date format.
	 * @var string
	 */
	protected $_dateFormat = NULL;

	/**
	 * Set date format
	 * @param string $dateFormat
	 * @return self
	 */
	public function dateFormat($dateFormat)
	{
		$this->_dateFormat = $dateFormat;
		return $this;
	}

	/**
	 * DateTime format.
	 * @var string
	 */
	protected $_dateTimeFormat = NULL;

	/**
	 * Set DateTime format
	 * @param string $dateTimeFormat
	 * @return self
	 */
	public function dateTimeFormat($dateTimeFormat)
	{
		$this->_dateTimeFormat = $dateTimeFormat;
		return $this;
	}

	/**
	 * Show votes in XML
	 * @var boolean
	 */
	protected $_showXmlVotes = FALSE;

	/**
	 * Add votes XML to item
	 * @param boolean $showXmlSiteuser mode
	 * @return self
	 */
	public function showXmlVotes($showXmlVotes = TRUE)
	{
		$this->_showXmlVotes = $showXmlVotes;
		return $this;
	}

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
			$this->_preloadValues['datetime'] = Core_Date::timestamp2sql(time());
			$this->_preloadValues['ip'] = Core::getClientIp();
		}

		//!is_null($this->id) && $this->_setShortText();
	}

	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return Core_Entity
	 */
	public function delete($primaryKey = NULL)
	{
		if (is_null($primaryKey))
		{
			$primaryKey = $this->getPrimaryKey();
		}

		$this->id = $primaryKey;

		$this->Comment_Informationsystem_Item->delete();
		$this->Comment_Shop_Item->delete();
		$this->Comment_Shop_Order->delete();

		$this->Comments->deleteAll(FALSE);

		$this->deleteDir();

		return parent::delete($primaryKey);
	}

	/**
	 * Get parent comment
	 * @return Comment_Model|NULL
	 */
	public function getParent()
	{
		return $this->parent_id
			? Core_Entity::factory('Comment', $this->parent_id)
			: NULL;
	}

	/**
	 * Change comment status
	 * @return Comment_Model
	 * @hostcms-event comment.onBeforeChangeActive
	 * @hostcms-event comment.onAfterChangeActive
	 */
	public function changeActive()
	{
		Core_Event::notify($this->_modelName . '.onBeforeChangeActive', $this);

		$this->active = 1 - $this->active;
		$this->save();

		// Clear tagged cache
		if ($this->Comment_Informationsystem_Item->id)
		{
			$this->Comment_Informationsystem_Item->Informationsystem_Item->clearCache();
		}
		elseif ($this->Comment_Shop_Item->id)
		{
			$this->Comment_Shop_Item->Shop_Item->clearCache();
		}

		Core_Event::notify($this->_modelName . '.onAfterChangeActive', $this);

		return $this;
	}

	/**
	 * Backend callback method
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function fulltextBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		$aConfig = Comment_Controller::getConfig();

		ob_start();
		$link = $oAdmin_Form_Controller->doReplaces($oAdmin_Form_Field, $this, $oAdmin_Form_Field->link);
		$onclick = $oAdmin_Form_Controller->doReplaces($oAdmin_Form_Field, $this, $oAdmin_Form_Field->onclick);

		// Subject
		$this->subject != '' && Core_Html_Entity::factory('Strong')
			->value(htmlspecialchars($this->subject))
			->execute();

		Core_Html_Entity::factory('A')
			->href($link)
			->onclick($onclick)
			->value(htmlspecialchars($this->getShortText()))
			->execute();

		$subCommentCount = $this->Comments->getCount(FALSE);

		$subCommentCount && Core_Html_Entity::factory('Span')
			->class('count')
			->value($subCommentCount)
			->execute();

		if (strlen($this->ip))
		{
			Core_Html_Entity::factory('Span')
				->class('small darkgray')
				->value(htmlspecialchars($this->ip))
				->execute();
		}

		if ($this->grade && $this->grade <= $aConfig['gradeLimit'])
		{
			Core_Html_Entity::factory('Span')
				->class('small green')
				->value(str_repeat('★', $this->grade) . str_repeat('☆', $aConfig['gradeLimit'] - $this->grade))
				->execute();
		}

		return ob_get_clean();
	}

	/**
	 * Get short text (max length is 70 chars)
	 * @return string
	 */
	public function getShortText()
	{
		return mb_substr(strip_tags(
			html_entity_decode($this->text, ENT_COMPAT, 'UTF-8')
		), 0, 70) . '…';
	}

	/**
	 * Backend callback method
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function authorBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		if ($this->siteuser_id && Core::moduleIsActive('siteuser'))
		{
			ob_start();
			$windowId = $oAdmin_Form_Controller->getWindowId();

			Core_Html_Entity::factory('I')
				->class('fa fa-user')
				->execute();

			Core_Html_Entity::factory('A')
				->href($oAdmin_Form_Controller->getAdminActionLoadHref('/{admin}/siteuser/index.php', 'edit', NULL, 0, intval($this->Siteuser->id)))
				->onclick("$.openWindowAddTaskbar({path: hostcmsBackend + '/siteuser/index.php', additionalParams: '&hostcms[checked][0][{$this->Siteuser->id}]=1&hostcms[action]=edit', shortcutImg: '" . '/modules/skin/' . Core_Skin::instance()->getSkinName() . '/images/module/siteuser.png' . "', shortcutTitle: 'undefined', Minimize: true}); return false")
				->value(htmlspecialchars((string) $this->Siteuser->login))
				->execute();

			return ob_get_clean();
		}

		return htmlspecialchars($this->author);
	}

	/**
	 * Get last comment by ip
	 * @param string $ip IP
	 * @return Comment_Model|NULL
	 */
	public function getLastCommentByIp($ip)
	{
		$this->queryBuilder()
			->where('ip', '=', $ip)
			->orderBy('datetime', 'DESC')
			->limit(1);
		$aComments = $this->findAll();

		return isset($aComments[0]) ? $aComments[0] : NULL;
	}

	/**
	 * Get item href without trailing slash
	 * @return string
	 */
	protected function _getHref()
	{
		$oEntity = $this->getRelatedEntity();

		$uploaddir = $oEntity ? $oEntity->Site->uploaddir : 'upload/';
		$nestingLevel = $oEntity ? $oEntity->Site->nesting_level : 3;

		return $uploaddir . 'comments/' . Core_File::getNestingDirPath($this->id, $nestingLevel) . '/comment_' . $this->id . '/';
	}

	/**
	 * Get item path
	 * @return string
	 */
	public function getPath()
	{
		return CMS_FOLDER . $this->_getHref();
	}

	/**
	 * Get item href
	 * @return string
	 */
	public function getHref()
	{
		return '/' . $this->_getHref();
	}

	/**
	 * Create files directory
	 * @return self
	 */
	public function createDir()
	{
		clearstatcache();

		if (!Core_File::isDir($this->getPath()))
		{
			try
			{
				Core_File::mkdir($this->getPath(), CHMOD, TRUE);
			} catch (Exception $e) {}
		}

		return $this;
	}

	/**
	 * Delete item's directory for files
	 * @return self
	 */
	public function deleteDir()
	{
		if (Core_File::isDir($this->getPath()))
		{
			try
			{
				Core_File::deleteDir($this->getPath());
			} catch (Exception $e) {}
		}

		return $this;
	}

	/**
	 * Show properties in XML
	 * @var boolean
	 */
	protected $_showXmlProperties = FALSE;

	/**
	 * Sort properties values in XML
	 * @var mixed
	 */
	protected $_xmlSortPropertiesValues = TRUE;

	/**
	 * Show properties in XML
	 * @param boolean $showXmlProperties
	 * @return self
	 */
	public function showXmlProperties($showXmlProperties = TRUE, $xmlSortPropertiesValues = TRUE)
	{
		$this->_showXmlProperties = $showXmlProperties;

		$this->_xmlSortPropertiesValues = $xmlSortPropertiesValues;

		return $this;
	}

	/**
	 * Show Siteuser properties in XML
	 * @var boolean
	 */
	protected $_showXmlSiteuserProperties = FALSE;

	/**
	 * Show Siteuser properties in XML
	 * @param boolean $showXmlSiteuserProperties
	 * @return self
	 */
	public function showXmlSiteuserProperties($showXmlSiteuserProperties = TRUE)
	{
		$this->_showXmlSiteuserProperties = $showXmlSiteuserProperties;
		return $this;
	}

	/**
	 * Get XML for entity and children entities
	 * @return string
	 * @hostcms-event comment.onBeforeRedeclaredGetXml
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
	 * @hostcms-event comment.onBeforeRedeclaredGetStdObject
	 */
	public function getStdObject($attributePrefix = '_')
	{
		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredGetStdObject', $this);

		$this->_prepareData();

		return parent::getStdObject($attributePrefix);
	}

	/**
	 * Values of all properties of item
	 * @var array
	 */
	protected $_propertyValues = NULL;

	/**
	 * Values of all properties of item
	 * Значения всех свойств товара
	 * @param boolean $bCache cache mode status
	 * @param array $aPropertiesId array of properties' IDs
	 * @param boolean $bSorting sort results, default FALSE
	 * @return array Property_Value
	 */
	public function getPropertyValues($bCache = TRUE, $aPropertiesId = array(), $bSorting = FALSE)
	{
		if ($bCache && !is_null($this->_propertyValues))
		{
			return $this->_propertyValues;
		}

		if (!is_array($aPropertiesId) || !count($aPropertiesId))
		{
			if ($this->Comment_Informationsystem_Item->id)
			{
				$entityModel = 'Informationsystem_Comment_Property_List';
				$entityId = $this->Comment_Informationsystem_Item->Informationsystem_Item->informationsystem_id;
			}
			elseif ($this->Comment_Shop_Item->id)
			{
				$entityModel = 'Shop_Comment_Property_List';
				$entityId = $this->Comment_Shop_Item->Shop_Item->shop_id;
			}
			elseif ($this->Comment_Shop_Order->id)
			{
				$entityModel = 'Shop_Order_Comment_Property_List';
				$entityId = $this->Comment_Shop_Order->Shop_Order->shop_id;
			}

			$aProperties = Core_Entity::factory($entityModel, $entityId)
				->Properties
				->findAll();

			$aPropertiesId = array();
			foreach ($aProperties as $oProperty)
			{
				$aPropertiesId[] = $oProperty->id;
			}
		}

		$aReturn = Property_Controller_Value::getPropertiesValues($aPropertiesId, $this->id, $bCache, $bSorting);

		// setHref()
		foreach ($aReturn as $oProperty_Value)
		{
			$this->_preparePropertyValue($oProperty_Value);
		}

		$bCache && $this->_propertyValues = $aReturn;

		return $aReturn;
	}

	/**
	 * Get Related Entuty
	 */
	public function getRelatedEntity()
	{
		if ($this->Comment_Informationsystem_Item->id)
		{
			$oEntity = $this->Informationsystem_Item->Informationsystem;
		}
		elseif ($this->Comment_Shop_Item->id)
		{
			$oEntity = $this->Shop_Item->Shop;
		}
		elseif ($this->Comment_Shop_Order->id)
		{
			$oEntity = $this->Shop_Order->Shop;
		}
		else
		{
			$oEntity = NULL;
		}

		return $oEntity;
	}

	/**
	 * Prepare Property Value
	 * @param Property_Value_Model $oProperty_Value
	 */
	protected function _preparePropertyValue($oProperty_Value)
	{
		$oEntity = $this->getRelatedEntity();

		if ($oEntity)
		{
			switch ($oProperty_Value->Property->type)
			{
				case 2:
					$oProperty_Value
						->setHref($this->getHref())
						->setDir($this->getPath());
				break;
				case 8:
					$oProperty_Value->dateFormat($oEntity->format_date);
				break;
				case 9:
					$oProperty_Value->dateTimeFormat($oEntity->format_datetime);
				break;
			}
		}
	}

	/**
	 * Prepare entity and children entities
	 * @return self
	 * @hostcms-event comment.onBeforeAddPropertyValues
	 */
	protected function _prepareData()
	{
		$this->clearXmlTags()
			->addXmlTag('date', Core_Date::strftime($this->_dateFormat, Core_Date::sql2timestamp($this->datetime)))
			->addXmlTag('datetime', Core_Date::strftime($this->_dateTimeFormat, Core_Date::sql2timestamp($this->datetime)));

		$this->_isTagAvailable('dir')
			&& $this->addXmlTag('dir', $this->getHref());

		if ($this->siteuser_id && Core::moduleIsActive('siteuser'))
		{
			$this->addEntity($this->Siteuser
				->clearEntities()
				->showXmlProperties($this->_showXmlSiteuserProperties, $this->_xmlSortPropertiesValues)
			);
		}

		if ($this->_showXmlProperties)
		{
			if (is_array($this->_showXmlProperties))
			{
				$aProperty_Values = Property_Controller_Value::getPropertiesValues($this->_showXmlProperties, $this->id, FALSE, $this->_xmlSortPropertiesValues);
				foreach ($aProperty_Values as $oProperty_Value)
				{
					$this->_preparePropertyValue($oProperty_Value);
				}
			}
			else
			{
				$aProperty_Values = $this->getPropertyValues(TRUE, array(), $this->_xmlSortPropertiesValues);
			}

			Core_Event::notify($this->_modelName . '.onBeforeAddPropertyValues', $this, array($aProperty_Values));

			$aListIDs = array();

			foreach ($aProperty_Values as $oProperty_Value)
			{
				// List_Items
				if ($oProperty_Value->Property->type == 3)
				{
					$aListIDs[] = $oProperty_Value->value;
				}

				$this->addEntity($oProperty_Value);
			}

			if (Core::moduleIsActive('list'))
			{
				// Cache necessary List_Items
				if (count($aListIDs))
				{
					$oList_Items = Core_Entity::factory('List_Item');
					$oList_Items->queryBuilder()
						->where('id', 'IN', $aListIDs)
						->clearOrderBy();

					$oList_Items->findAll();
				}
			}
		}

		if ($this->_showXmlVotes && Core::moduleIsActive('siteuser'))
		{
			$aRate = Vote_Controller::instance()->getRateByObject($this);

			$this->addEntity(
				Core::factory('Core_Xml_Entity')
					->name('rate')
					->value($aRate['rate'])
					->addAttribute('likes', $aRate['likes'])
					->addAttribute('dislikes', $aRate['dislikes'])
			);

			if (!is_null($oCurrentSiteuser = Core_Entity::factory('Siteuser')->getCurrent()))
			{
				$oVote = $this->Votes->getBySiteuser_Id($oCurrentSiteuser->id);
				!is_null($oVote) && $this->addEntity($oVote);
			}
		}

		return $this;
	}
}