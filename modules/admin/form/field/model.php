<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Admin_Form_Field_Model
 *
 * @package HostCMS
 * @subpackage Admin
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Admin_Form_Field_Model extends Core_Entity
{
	/**
	 * Backend property
	 * @var string
	 */
	public $word_name = NULL;

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'admin_word' => array(),
		'admin_form' => array(),
		'user' => array()
	);

	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		'admin_form_field_setting' => array()
	);

	/**
	 * Default sorting for models
	 * @var array
	 */
	protected $_sorting = array(
		'admin_form_fields.sorting' => 'ASC'
	);

	/**
	 * List of preloaded values
	 * @var array
	 */
	protected $_preloadValues = array(
		'sorting' => 0
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
			$this->_preloadValues['type'] = 1;

			$oUser = Core_Auth::getCurrentUser();
			$this->_preloadValues['user_id'] = is_null($oUser) ? 0 : $oUser->id;
		}
	}

	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return Core_Entity
     * @hostcms-event admin_form_field.onBeforeRedeclaredDelete
	 */
	public function delete($primaryKey = NULL)
	{
		if (is_null($primaryKey))
		{
			$primaryKey = $this->getPrimaryKey();
		}

		$this->id = $primaryKey;

		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredDelete', $this, array($primaryKey));

		$this->Admin_Word->delete();

		$this->Admin_Form_Field_Settings->deleteAll(FALSE);

		return parent::delete($primaryKey);
	}

	/**
	 * Copy object
	 * @return Core_Entity
	 * @hostcms-event admin_form_field.onAfterRedeclaredCopy
	 */
	public function copy()
	{
		$newObject = parent::copy();
		$newObject->add($this->admin_word->copy());

		Core_Event::notify($this->_modelName . '.onAfterRedeclaredCopy', $newObject, array($this));

		return $newObject;
	}

	/**
	 * Get caption of the field
	 * @return string|NULL
	 */
	public function getCaption($admin_language_id)
	{
		$oAdmin_Word = $this->Admin_Word->getWordByLanguage($admin_language_id);

		return !is_null($oAdmin_Word)
			? $oAdmin_Word->name
			: NULL;
	}

	/**
	 * Backend badge
	 */
	public function nameBadge()
	{
		if ($this->width != '')
		{
			Core_Html_Entity::factory('Span')
				->class('badge badge-round badge-max-width badge-palegreen')
				->value(htmlspecialchars($this->width))
				->execute();
		}

		if ($this->class != '')
		{
			Core_Html_Entity::factory('Span')
				->class('badge badge-round badge-max-width badge-sky')
				->value(htmlspecialchars($this->class))
				->execute();
		}

		switch ($this->view)
		{
			case 0:
			default:
				$badge = '<i class="fa fa-bars fa-fw"></i>';
			break;
			case 1:
				$badge = '<i class="fa fa-filter fa-fw"></i>';
			break;
			case 2:
				$badge = '<i class="fa fa-minus fa-fw"></i>';
			break;
		}

		Core_Html_Entity::factory('Span')
			->class('badge badge-hostcms badge-square darkgray pull-right')
			->title(Core::_('Admin_Form_Field.field_view' . $this->view))
			->value($badge)
			->execute();

		if (!$this->show_by_default)
		{
			Core_Html_Entity::factory('Span')
				->class('badge badge-hostcms badge-square darkgray pull-right margin-right-5')
				->title(Core::_('Admin_Form_Field.not_show_by_default'))
				->value('<i class="fa-solid fa-eye-slash fa-fw"></i>')
				->execute();
		}
	}
}
