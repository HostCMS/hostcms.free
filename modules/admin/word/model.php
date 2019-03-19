<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Admin_Word_Model
 *
 * @package HostCMS
 * @subpackage Admin
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2019 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Admin_Word_Model extends Core_Entity
{
	/**
	 * Column consist item's name
	 * @var string
	 */
	protected $_nameColumn = 'id';

	/**
	 * Disable markDeleted()
	 * @var mixed
	 */
	protected $_marksDeleted = NULL;

	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		'admin_word_value' => array()
	);

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
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
			$oUserCurrent = Core_Entity::factory('User', 0)->getCurrent();
			$this->_preloadValues['user_id'] = is_null($oUserCurrent) ? 0 : $oUserCurrent->id;
		}
	}

	/**
	 * Get admin word by language
	 * @param int $admin_language_id language id
	 * @return Admin_Word_Value|NULL
	 */
	public function getWordByLanguage($admin_language_id = CURRENT_LANGUAGE_ID)
	{
		$Admin_Word_Values = $this->Admin_Word_Values;

		$Admin_Word_Values
			->queryBuilder()
			->where('admin_language_id', '=', $admin_language_id);

		$aResult = $Admin_Word_Values->findAll();

		$this->Admin_Word_Values
			->queryBuilder()
			->clear();

		if (isset($aResult[0]))
		{
			return $aResult[0];
		}

		return NULL;
	}

	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return self
	 * @hostcms-event admin_word.onBeforeRedeclaredDelete
	 */
	public function delete($primaryKey = NULL)
	{
		if (is_null($primaryKey))
		{
			$primaryKey = $this->getPrimaryKey();
		}

		$this->id = $primaryKey;

		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredDelete', $this, array($primaryKey));
		
		$this->Admin_Word_Values->deleteAll(FALSE);

		return parent::delete($primaryKey);
	}

	/**
	 * Copy object
	 * @return Core_Entity
	 */
	public function copy()
	{
		$newObject = parent::copy();

		$aAdmin_Word_Values = $this->Admin_Word_Values->findAll();
		foreach ($aAdmin_Word_Values as $oAdmin_Word_Value)
		{
			$newObject->add(clone $oAdmin_Word_Value);
		}

		return $newObject;
	}
}