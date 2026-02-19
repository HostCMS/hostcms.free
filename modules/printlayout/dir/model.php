<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Printlayout_Dir_Model
 *
 * @package HostCMS
 * @subpackage Printlayout
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Printlayout_Dir_Model extends Core_Entity
{
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
		'printlayout' => array(),
		'printlayout_dir' => array('foreign_key' => 'parent_id')
	);

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'printlayout_dir' => array('foreign_key' => 'parent_id'),
		'user' => array()
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
	 * Backend callback method
	 * @param Admin_Form_Field_Model $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 */
	public function nameBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		$link = $oAdmin_Form_Field->link;
		$onclick = $oAdmin_Form_Field->onclick;

		$link = $oAdmin_Form_Controller->doReplaces($oAdmin_Form_Field, $this, $link);
		$onclick = $oAdmin_Form_Controller->doReplaces($oAdmin_Form_Field, $this, $onclick);

		$oCore_Html_Entity_Div = Core_Html_Entity::factory('Div');

		$oCore_Html_Entity_Div
			->add(
				Core_Html_Entity::factory('A')
					->href($link)
					->onclick($onclick)
					->value(htmlspecialchars($this->name))
			);

		$count = $this->getChildCount();
		$count && $oCore_Html_Entity_Div
			->add(
				Core_Html_Entity::factory('Span')
					->class('badge badge-hostcms badge-square')
					->value($count)
			);

		$oCore_Html_Entity_Div->execute();
	}

	/**
	 * Get count
	 * @return int
	 */
	public function getChildCount()
	{
		$count = $this->Printlayouts->getCount();

		$aPrintlayout_Dirs = $this->Printlayout_Dirs->findAll(FALSE);

		foreach ($aPrintlayout_Dirs as $oPrintlayout_Dir)
		{
			$count += $oPrintlayout_Dir->getChildCount();
		}

		return $count;
	}

	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return Core_Entity
     * @hostcms-event printlayout_dir.onBeforeRedeclaredDelete
	 */
	public function delete($primaryKey = NULL)
	{
		if (is_null($primaryKey))
		{
			$primaryKey = $this->getPrimaryKey();
		}

		$this->id = $primaryKey;

		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredDelete', $this, array($primaryKey));

		$this->Printlayouts->deleteAll(FALSE);
		$this->Printlayout_Dirs->deleteAll(FALSE);

		return parent::delete($primaryKey);
	}

	/**
	 * Get parent
	 * @return Printlayout_Dir_Model|NULL
	 */
	public function getParent()
	{
		if ($this->parent_id)
		{
			return Core_Entity::factory('Printlayout_Dir_Model', $this->parent_id);
		}
		else
		{
			return NULL;
		}
	}
}