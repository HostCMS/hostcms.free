<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Ipaddress_Filter_Dir_Model
 *
 * @package HostCMS
 * @subpackage Ipaddress
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Ipaddress_Filter_Dir_Model extends Core_Entity
{
	/**
	 * Model name
	 * @var mixed
	 */
	protected $_modelName = 'ipaddress_filter_dir';

	/**
	 * Backend property
	 * @var int
	 */
	public $img = 0;

	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		'ipaddress_filter_dir' => array('foreign_key' => 'parent_id'),
		'ipaddress_filter' => array(),
	);

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'ipaddress_filter_dir' => array('foreign_key' => 'parent_id'),
		'user' => array()
	);

	/**
	 * Default sorting for models
	 * @var array
	 */
	protected $_sorting = array(
		'ipaddress_filter_dirs.sorting' => 'ASC'
	);

	/**
	 * List of preloaded values
	 * @var array
	 */
	protected $_preloadValues = array(
		'sorting' => 0,
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
	 * Get parent
	 * @return Ipaddress_Filter_Dir_Model|NULL
	 */
	public function getParent()
	{
		return $this->parent_id
			? Core_Entity::factory('Ipaddress_Filter_Dir', $this->parent_id)
			: NULL;
	}

	/**
	 * Get count of items all levels
	 * @return int
	 */
	public function getChildCount()
	{
		$count = $this->Ipaddress_Filters->getCount();

		$aIpaddress_Filter_Dirs = $this->Ipaddress_Filter_Dirs->findAll(FALSE);
		foreach ($aIpaddress_Filter_Dirs as $oIpaddress_Filter_Dir)
		{
			$count += $oIpaddress_Filter_Dir->getChildCount();
		}

		return $count;
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

		$iCount = $this->getChildCount();

		$iCount > 0 && $oCore_Html_Entity_Div
			->add(
				Core_Html_Entity::factory('Span')
					->class('badge badge-hostcms badge-square')
					->value($iCount)
			);

		$oCore_Html_Entity_Div->execute();
	}

	/**
	 * Move group to another
	 * @param int $parent_id group id
	 * @return self
	 * @hostcms-event ipaddress_filter_dir.onBeforeMove
	 * @hostcms-event ipaddress_filter_dir.onAfterMove
	 */
	public function move($parent_id)
	{
		Core_Event::notify($this->_modelName . '.onBeforeMove', $this, array($parent_id));

		$this->parent_id = $parent_id;
		$this->save();

		Core_Event::notify($this->_modelName . '.onAfterMove', $this);

		return $this;
	}

	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return Core_Entity
	 * @hostcms-event ipaddress_filter_dir.onBeforeRedeclaredDelete
	 */
	public function delete($primaryKey = NULL)
	{
		if (is_null($primaryKey))
		{
			$primaryKey = $this->getPrimaryKey();
		}

		$this->id = $primaryKey;

		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredDelete', $this, array($primaryKey));

		$this->Ipaddress_Filter_Dirs->deleteAll(FALSE);
		$this->Ipaddress_Filters->deleteAll(FALSE);

		return parent::delete($primaryKey);
	}
}