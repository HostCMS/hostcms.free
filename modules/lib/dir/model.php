<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Lib_Dir_Model
 *
 * @package HostCMS
 * @subpackage Lib
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Lib_Dir_Model extends Core_Entity
{
	/**
	 * Backend property
	 * @var int
	 */
	public $img = 0;

	/**
	 * Backend property
	 * @var string
	 */
	public $description = '';

	/**
	 * Backend property
	 * @var int
	 */
	public $properties = 0;

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'lib_dir' => array('foreign_key' => 'parent_id'),
		'user' => array()
	);

	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		'lib' => array(),
		'lib_dir' => array('foreign_key' => 'parent_id')
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
	 * @return self
	 * @hostcms-event lib_dir.onBeforeRedeclaredDelete
	 */
	public function delete($primaryKey = NULL)
	{
		if (is_null($primaryKey))
		{
			$primaryKey = $this->getPrimaryKey();
		}

		$this->id = $primaryKey;

		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredDelete', $this, array($primaryKey));

		$this->libs->deleteAll(FALSE);
		$this->Lib_Dirs->deleteAll(FALSE);

		return parent::delete($primaryKey);
	}

	/**
	 * Get parent comment
	 * @return Lib_Dir_Model|NULL
	 */
	public function getParent()
	{
		if ($this->parent_id)
		{
			return Core_Entity::factory('Lib_Dir', $this->parent_id);
		}
		else
		{
			return NULL;
		}
	}
}
