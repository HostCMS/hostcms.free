<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Document_Model
 *
 * @package HostCMS
 * @subpackage Document
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Document_Model extends Core_Entity
{
	/**
	 * Backend property
	 * @var mixed
	 */
	public $img = 1;

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'document_dir' => array(),
		'document_status' => array(),
		'template' => array(),
		'user' => array(),
		'site' => array()
	);

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
			$this->_preloadValues['site_id'] = defined('CURRENT_SITE') ? CURRENT_SITE : 0;
			$this->_preloadValues['datetime'] = Core_Date::timestamp2sql(time());
		}
	}

	/**
	 * Get document by site id
	 * @param int $site_id site id
	 * @return array
	 */
	public function getBySiteId($site_id)
	{
		$this->queryBuilder()
			//->clear()
			->where('site_id', '=', $site_id)
			->orderBy('name');

		return $this->findAll();
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function adminTemplate()
	{
		return htmlspecialchars($this->Template->name);
	}

	/**
	 * Edit-in-Place callback
	 * @param string $text Text of document
	 * @return self
	 */
	public function editInPlace()
	{
		$args = func_get_args();

		if (!isset($args[0]))
		{
			return $this->text;
		}

		$this->text = $args[0];
		$this->save();

		return $this;
	}

	/**
	 * Add message into search index
	 * @return self
	 */
	public function index()
	{
		if (Core::moduleIsActive('search'))
		{
			Search_Controller::indexingSearchPages(array($this->indexing()));
		}

		return $this;
	}

	/**
	 * Remove message from search index
	 * @return self
	 */
	public function unindex()
	{
		if (Core::moduleIsActive('search'))
		{
			Search_Controller::deleteSearchPage(6, 0, $this->id);
		}

		return $this;
	}

	/**
	 * Search indexation
	 * @return Search_Page
	 * @hostcms-event document.onBeforeIndexing
	 * @hostcms-event document.onAfterIndexing
	 */
	public function indexing()
	{
		$oSearch_Page = new stdClass();

		Core_Event::notify($this->_modelName . '.onBeforeIndexing', $this, array($oSearch_Page));

		$oSearch_Page->text = htmlspecialchars($this->name) . ' ' . $this->text;
		$oSearch_Page->size = mb_strlen($oSearch_Page->text);
		$oSearch_Page->datetime = $this->datetime;

		$oSearch_Page->title = $this->name;
		$oSearch_Page->site_id = $this->site_id;
		$oSearch_Page->module = 6;
		$oSearch_Page->module_id = $this->id;
		$oSearch_Page->inner = 1;
		$oSearch_Page->module_value_type = 0; // search_page_module_value_type
		$oSearch_Page->module_value_id = $this->id; // search_page_module_value_id
		$oSearch_Page->url = 'document-' . $this->id; // Уникальный номер

		$oSearch_Page->siteuser_groups = array(0);

		Core_Event::notify($this->_modelName . '.onAfterIndexing', $this, array($oSearch_Page));

		return $oSearch_Page;
	}

	/**
	 * Backend callback method
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function nameBadge($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		$oStructures = Core_Entity::factory('Structure');
		$oStructures->queryBuilder()
			->where('structures.site_id', '=', CURRENT_SITE)
			->where('structures.document_id', '=', $this->id);

		$aStructures = $oStructures->findAll(FALSE);

		if (count($aStructures))
		{
			$sListStructures = '';

			foreach ($aStructures as $oStructure)
			{
				$sListStructures .= '<i class="fa fa-folder-open-o" style="margin-right: 5px"></i><a onclick="'
				. ("$.adminCheckObject({objectId: 'check_0_" . $oStructure->id . "', windowId: 'id_content'}); $.adminLoad({path: '/admin/structure/index.php', action: 'edit', additionalParams: '', windowId: 'id_content'}); return false")
				. '">' . htmlspecialchars($oStructure->name) . "</a><br />";
			}

			Admin_Form_Entity::factory('Code')
				->html('<a id="document_' . $this->id . '" type="button" class="structure_list_link" data-toggle="popover" data-placement="right" data-content="' . htmlspecialchars($sListStructures) . '" data-title="' . Core::_('Document.structures') . '" data-titleclass="bordered-darkorange" data-container="#document_' . $this->id . '" title="' . Core::_('Document.structures') . '"><i class="fa fa-link gray"></i></a>
				')
				->execute();
		}
	}

	/**
	 * Document content
	 */
	protected $_content = NULL;

	/**
	 * Get $this->_content
	 * @return string
	 */
	public function getContent()
	{
		return $this->_content;
	}

	/**
	 * Set $this->_content
	 * @param string $content
	 * @return self
	 */
	public function setContent($content)
	{
		$this->_content = $content;
		return $this;
	}

	/**
	 * Show document version.
	 *
	 * @hostcms-event document.onBeforeExecute
	 * @hostcms-event document.onAfterExecute
	 * <code>
	 * Core_Entity::factory('Document', 123)->execute();
	 * </code>
	 */
	public function execute()
	{
		$this->setContent($this->text);

		Core_Event::notify($this->_modelName . '.onBeforeExecute', $this);

		$checkPanel = Core::checkPanel();
		if ($checkPanel)
		{
			?><div hostcms:id="<?php echo intval($this->id)?>" hostcms:field="editInPlace" hostcms:entity="document" hostcms:type="wysiwyg"><?php
		}

		$bShortcodeTags = Core::moduleIsActive('shortcode');

		if ($bShortcodeTags)
		{
			$oShortcode_Controller = Shortcode_Controller::instance();
			$iCountShortcodes = $oShortcode_Controller->getCount();

			if ($iCountShortcodes)
			{
				$this->_content = $oShortcode_Controller->applyShortcodes($this->_content);
			}
		}

		// Show content of document
		echo $this->getContent();

		if ($checkPanel)
		{
			?></div><?php
		}

		Core_Event::notify($this->_modelName . '.onAfterExecute', $this);

		return $this;
	}

	/**
	 * Backup revision
	 * @return self
	 */
	public function backupRevision()
	{
		if (Core::moduleIsActive('revision'))
		{
			$aBackup = array(
				'document_dir_id' => $this->document_dir_id,
				'document_status_id' => $this->document_status_id,
				'template_id' => $this->template_id,
				'name' => $this->name,
				'text' => $this->text,
				'site_id' => $this->site_id,
				'user_id' => $this->user_id
			);

			Revision_Controller::backup($this, $aBackup);
		}

		return $this;
	}

	/**
	 * Rollback Revision
	 * @param int $revision_id Revision ID
	 * @return self
	 */
	public function rollbackRevision($revision_id)
	{
		if (Core::moduleIsActive('revision'))
		{
			$oRevision = Core_Entity::factory('Revision', $revision_id);

			$aBackup = json_decode($oRevision->value, TRUE);

			if (is_array($aBackup))
			{
				$this->name = Core_Array::get($aBackup, 'name');
				$this->text = Core_Array::get($aBackup, 'text');
				$this->save();
			}
		}

		return $this;
	}

	/**
	 * Utilized for reading data from inaccessible properties
	 * @param string $property property name
	 * @return mixed
	 */
	public function __get($property)
	{
		if ($property == 'Document_Versions')
		{
			$oDocument_Version_Std = new Document_Version_Std();
			$oDocument_Version_Std->document_id = $this->id;
			$oDocument_Version_Std->Template = $this->template;
			return $oDocument_Version_Std;
		}

		return parent::__get($property);
	}

	/**
	 * Add related object. If main object does not save, it will save.
	 * @param Core_ORM $model
	 * @param string $relation
	 * @return Core_ORM
	 */
	public function add(Core_ORM $model, $relation = NULL)
	{
		if (is_null($relation))
		{
			$modelName = $model->getModelName();

			if ($modelName == 'document_version')
			{
				$this->template_id = $model->template_id;
				$model->document_id = $this->id;

				return $this->save();
			}
		}

		return parent::add($model, $relation);
	}

	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return self
	 * @hostcms-event document.onBeforeRedeclaredDelete
	 */
	public function delete($primaryKey = NULL)
	{
		if (is_null($primaryKey))
		{
			$primaryKey = $this->getPrimaryKey();
		}

		$this->id = $primaryKey;

		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredDelete', $this, array($primaryKey));

		if (Core::moduleIsActive('revision'))
		{
			Revision_Controller::delete($this->getModelName(), $this->id);
		}

		return parent::delete($primaryKey);
	}
}