<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Field_Dir_Model
 *
 * @package HostCMS
 * @subpackage Field
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Field_Dir_Model extends Core_Entity
{
	/**
	 * Backend property
	 * @var mixed
	 */
	public $img = 0;

	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		'field' => array(),
		'field_dir' => array('foreign_key' => 'parent_id')
	);

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'field_dir' => array('foreign_key' => 'parent_id')
	);

	/**
	 * List of preloaded values
	 * @var array
	 */
	protected $_preloadValues = array(
		'parent_id' => 0
	);

	/**
	 * Get parent comment
	 * @return Field_Dir_Model|NULL
	 */
	public function getParent()
	{
		if ($this->parent_id)
		{
			return Core_Entity::factory('Field_Dir', $this->parent_id);
		}
		else
		{
			return NULL;
		}
	}

	/**
	 * Backend badge
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function nameBadge($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{

		$countField_Dirs = $this->Field_Dirs->getCount();
		$countField_Dirs && Core_Html_Entity::factory('Span')
			->class('badge badge-hostcms badge-square')
			->value('<i class="fa-regular fa-folder-open"></i> ' . $countField_Dirs)
			->title(Core::_('Informationsystem.all_groups_count', $countField_Dirs))
			->execute();

		$countFields = $this->Fields->getCount();
		$countFields && Core_Html_Entity::factory('Span')
			->class('badge badge-hostcms badge-square')
			->value('<i class="fa fa-file-o"></i> ' . $countFields)
			->title(Core::_('Informationsystem.all_items_count', $countFields))
			->execute();
	}

	/**
	 * Move group to another group
	 * @param int $iFieldDirId field dir id
	 * @return self
	 * @hostcms-event field_dir.onBeforeMove
	 * @hostcms-event field_dir.onAfterMove
	 */
	public function move($iFieldDirId)
	{
		Core_Event::notify($this->_modelName . '.onBeforeMove', $this, array($iFieldDirId));

		$this->parent_id = $iFieldDirId;
		$this->save();

		Core_Event::notify($this->_modelName . '.onAfterMove', $this);

		return $this;
	}

	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return self
	 * @hostcms-event field_dir.onBeforeRedeclaredDelete
	 */
	public function delete($primaryKey = NULL)
	{
		if (is_null($primaryKey))
		{
			$primaryKey = $this->getPrimaryKey();
		}

		$this->id = $primaryKey;

		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredDelete', $this, array($primaryKey));

		$this->Fields->deleteAll(FALSE);
		$this->Field_Dirs->deleteAll(FALSE);

		return parent::delete($primaryKey);
	}
}