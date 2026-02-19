<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Admin_Form_Action_Dir_Model
 *
 * @package HostCMS
 * @subpackage Admin_Form
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Admin_Form_Action_Dir_Model extends Core_Entity
{
	/**
	 * Name of the table
	 * @var string
	 */
	protected $_tableName = 'admin_form_action_dirs';

	/**
	 * Name of the model
	 * @var string
	 */
	protected $_modelName = 'admin_form_action_dir';

	/**
	 * Word name in back-end form
	 */
	public $word_name = NULL;

	/**
	 * Backend property
	 * @var string
	 */
	public $img = 0;

	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		'admin_form_action' => array()
	);

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'admin_form' => array(),
		'admin_word' => array(),
		'user' => array()
	);

	/**
	 * Default sorting for models
	 * @var array
	 */
	protected $_sorting = array(
		'admin_form_action_dirs.sorting' => 'ASC'
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
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return Core_Entity
     * @hostcms-event admin_form_action_dir.onBeforeRedeclaredDelete
	 */
	public function delete($primaryKey = NULL)
	{
		if (is_null($primaryKey))
		{
			$primaryKey = $this->getPrimaryKey();
		}

		$this->id = $primaryKey;

		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredDelete', $this, array($primaryKey));

		$this->Admin_Form_Actions->deleteAll(FALSE);

		return parent::delete($primaryKey);
	}

	/**
	 * Get count of items
	 * @return int
	 */
	public function getChildCount()
	{
		return $this->Admin_Form_Actions->getCount();
	}

	/**
	 * Get word name
	 * @return string
	 */
	public function getWordName()
	{
		$oCore_QueryBuilder_Select = Core_QueryBuilder::select()
			->select('admin_form_action_dirs.*', array('admin_word_values.name', 'word_name'))
			->from('admin_form_action_dirs')
			->leftJoin('admin_words', 'admin_form_action_dirs.admin_word_id', '=', 'admin_words.id')
			->leftJoin('admin_word_values', 'admin_words.id', '=', 'admin_word_values.admin_word_id')
			->open()
				->where('admin_word_values.admin_language_id', '=', CURRENT_LANGUAGE_ID)
				->setOr()
				->where('admin_form_action_dirs.admin_word_id', '=', 0)
			->close()
			->where('admin_form_action_dirs.id', '=', $this->id)
			->where('admin_form_action_dirs.admin_form_id', '=', $this->admin_form_id);

		$row = $oCore_QueryBuilder_Select->execute()->asAssoc()->current();

		return $row['word_name'];
	}

	/**
	 * Get name
	 * @return string
	 */
	public function getName()
	{
		return $this->word_name != ''
			? htmlspecialchars($this->word_name)
			: Core::_('Admin.no_title');
	}

	/**
	 * Backend callback method
	 * @param Admin_Form_Field_Model $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 */
	public function word_nameBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		$link = $oAdmin_Form_Field->link;
		$onclick = $oAdmin_Form_Field->onclick;

		$link = $oAdmin_Form_Controller->doReplaces($oAdmin_Form_Field, $this, $link);
		$onclick = $oAdmin_Form_Controller->doReplaces($oAdmin_Form_Field, $this, $onclick);

		$oCore_Html_Entity_Div = Core_Html_Entity::factory('Div');

		$name = $this->getName();

		$oCore_Html_Entity_Div
			->add(
				Core_Html_Entity::factory('A')
					->href($link)
					->onclick($onclick)
					->value($name)
			);

		$iCount = $this->getChildCount();

		$iCount > 0 && $oCore_Html_Entity_Div
			->add(
				Core_Html_Entity::factory('Span')
					->class('badge badge-hostcms badge-square')
					->value($iCount)
			);

		$oCore_Html_Entity_Div->execute();
	}
}