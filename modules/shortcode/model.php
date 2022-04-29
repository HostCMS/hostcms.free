<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shortcode_Model
 *
 * @package HostCMS
 * @subpackage Shortcode
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shortcode_Model extends Core_Entity
{
	/**
	 * Model name
	 * @var mixed
	 */
	protected $_modelName = 'shortcode';

	/**
	 * Callback property_id
	 * @var int
	 */
	public $img = 1;

	/**
	 * Backend property
	 * @var mixed
	 */
	public $rollback = 0;

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'shortcode_dir' => array(),
		'user' => array()
	);

	/**
	 * Default sorting for models
	 * @var array
	 */
	protected $_sorting = array(
		'shortcodes.sorting' => 'ASC'
	);

	/**
	 * List of preloaded values
	 * @var array
	 */
	protected $_preloadValues = array(
		'sorting' => 0,
		'active' => 1,
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
	 * Has revisions
	 *
	 * @param boolean
	 */
	protected $_hasRevisions = TRUE;

	/**
	 * Change item status
	 * @return self
	 * @hostcms-event shortcode.onBeforeChangeActive
	 * @hostcms-event shortcode.onAfterChangeActive
	 */
	public function changeActive()
	{
		Core_Event::notify($this->_modelName . '.onBeforeChangeActive', $this);

		$this->active = 1 - $this->active;
		$this->save();

		// Rebuild shortcodes list
		Shortcode_Controller::instance()->rebuild();

		Core_Event::notify($this->_modelName . '.onAfterChangeActive', $this);

		return $this;
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function nameBackend()
	{
		$oCore_Html_Entity_Div = Core_Html_Entity::factory('Div')->value(
			htmlspecialchars($this->name)
		);

		// Зачеркнут в зависимости от статуса
		!$this->active && $oCore_Html_Entity_Div->class('inactive');

		$oCore_Html_Entity_Div->execute();
	}

	/**
	 * Backend badge
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function nameBadge($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		Core_Html_Entity::factory('Span')
			->class('badge badge-hostcms badge-square')
			->value(htmlspecialchars($this->example))
			->execute();
	}

	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return Core_Entity
	 * @hostcms-event shortcode.onBeforeRedeclaredDelete
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

		// Rebuild shortcodes list
		Shortcode_Controller::instance()->rebuild();

		return parent::delete($primaryKey);
	}

	/**
	 * Copy object
	 * @return Core_Entity
	 * @hostcms-event shortcode.onAfterRedeclaredCopy
	 */
	public function copy()
	{
		$newObject = parent::copy();
		$newObject->shortcode = 'copy_' . $this->shortcode;
		$newObject->save();

		// Rebuild shortcodes list
		Shortcode_Controller::instance()->rebuild();

		Core_Event::notify($this->_modelName . '.onAfterRedeclaredCopy', $newObject, array($this));

		return $newObject;
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
				'name' => $this->name,
				'shortcode' => $this->shortcode,
				'php' => $this->php,
				'shortcode_dir_id' => $this->shortcode_dir_id,
				'sorting' => $this->sorting,
				'active' => $this->active
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
				$this->shortcode = Core_Array::get($aBackup, 'shortcode');
				$this->php = Core_Array::get($aBackup, 'php');
				$this->shortcode_dir_id = Core_Array::get($aBackup, 'shortcode_dir_id');
				$this->sorting = Core_Array::get($aBackup, 'sorting');
				$this->active = Core_Array::get($aBackup, 'active');
				$this->save();
			}
		}

		return $this;
	}
}