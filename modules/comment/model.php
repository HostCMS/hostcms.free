<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Comment_Model
 *
 * @package HostCMS
 * @subpackage Comment
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Comment_Model extends Core_Entity
{
	/**
	 * Backend property
	 * @var string
	 */
	public $view = '';

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
		'comment_shop_item' => array('foreign_key' => 'comment_id'),
		'informationsystem_item' => array('through' => 'comment_informationsystem_item'),
		'shop_item' => array('through' => 'comment_shop_item')
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
		'grade' => 0
	);

	/**
	 * Forbidden tags. If list of tags is empty, all tags will show.
	 * @var array
	 */
	protected $_forbiddenTags = array(
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

		if (is_null($id))
		{
			$oUserCurrent = Core_Entity::factory('User', 0)->getCurrent();

			$this->_preloadValues['user_id'] = is_null($oUserCurrent) ? 0 : $oUserCurrent->id;
			$this->_preloadValues['datetime'] = Core_Date::timestamp2sql(time());
			$this->_preloadValues['ip'] = Core_Array::get($_SERVER, 'REMOTE_ADDR');
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

		$this->Comments->deleteAll(FALSE);

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
	public function short_text($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		ob_start();
		$link = $oAdmin_Form_Controller->doReplaces($oAdmin_Form_Field, $this, $oAdmin_Form_Field->link);
		$onclick = $oAdmin_Form_Controller->doReplaces($oAdmin_Form_Field, $this, $oAdmin_Form_Field->onclick);

		$oCore_Html_Entity_Div = Core::factory('Core_Html_Entity_Div');

		// Subject
		trim($this->subject) != '' && $oCore_Html_Entity_Div
			->add(
				Core::factory('Core_Html_Entity_Strong')
					->value(htmlspecialchars($this->subject))
			);

		$oCore_Html_Entity_Div
			->add(
				Core::factory('Core_Html_Entity_A')
					->href($link)
					->onclick($onclick)
					->value(htmlspecialchars($this->getShortText()))
			);

		$subCommentCount = $this->Comments->getCount();

		$subCommentCount && $oCore_Html_Entity_Div
			->add(
				Core::factory('Core_Html_Entity_Span')
					->class('count')
					->value(htmlspecialchars($subCommentCount))
			);

		if (strlen($this->ip))
		{
			$oCore_Html_Entity_Div
				->add(
					Core::factory('Core_Html_Entity_Span')
						->class('small darkgray')
						->value(htmlspecialchars($this->ip))
				);
		}
			
		$oCore_Html_Entity_Div->execute();
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
	public function author($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		if ($this->siteuser_id && Core::moduleIsActive('siteuser'))
		{
			ob_start();
			$windowId = $oAdmin_Form_Controller->getWindowId();

			Core::factory('Core_Html_Entity_I')
				->class('fa fa-user')
				->execute();

			Core::factory('Core_Html_Entity_A')
				->href($oAdmin_Form_Controller->getAdminActionLoadHref('/admin/siteuser/siteuser/index.php', 'edit', NULL, 0, $this->Siteuser->id))
				->onclick("$.openWindowAddTaskbar({path: '/admin/siteuser/siteuser/index.php', additionalParams: 'document_dir_id=' + $('#{$windowId} #document_dir_id').val() + '&hostcms[checked][0][{$this->Siteuser->id}]=1&hostcms[action]=edit', shortcutImg: '" . '/modules/skin/' . Core_Skin::instance()->getSkinName() . '/images/module/siteuser.png' . "', shortcutTitle: 'undefined', Minimize: true}); return false")
				->value(htmlspecialchars($this->Siteuser->login))
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
	 * Show properties in XML
	 * @var boolean
	 */
	protected $_showXmlProperties = FALSE;

	/**
	 * Show properties in XML
	 * @param boolean $showXmlProperties
	 * @return self
	 */
	public function showXmlProperties($showXmlProperties = TRUE)
	{
		$this->_showXmlProperties = $showXmlProperties;
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

		$this->clearXmlTags()
			->addXmlTag('date', strftime($this->_dateFormat, Core_Date::sql2timestamp($this->datetime)))
			->addXmlTag('datetime', strftime($this->_dateTimeFormat, Core_Date::sql2timestamp($this->datetime)));

		if ($this->siteuser_id && Core::moduleIsActive('siteuser'))
		{
			$this->addEntity($this->Siteuser
				->clearEntities()
				->showXmlProperties($this->_showXmlProperties)
			);
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

		return parent::getXml();
	}
}