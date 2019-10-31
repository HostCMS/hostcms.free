<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Admin_Language_Model
 *
 * @package HostCMS
 * @subpackage Admin
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2019 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Admin_Language_Model extends Core_Entity
{
	/**
	 * List of preloaded values
	 * @var array
	 */
	protected $_preloadValues = array(
		'active' => 1,
		'sorting' => 0
	);

	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		'admin_word_value' => array(),
		'antispam_country_language' => array()
	);

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'user' => array()
	);

	/**
	 * Default sorting for models
	 * @var array
	 */
	protected $_sorting = array(
		'admin_languages.sorting' => 'ASC'
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
	 * Get current admin language
	 * @return Admin_Language|NULL
	 */
	public function getCurrent()
	{
		$sCurrentLng = Core_I18n::instance()->getLng();

		$oAdmin_Language = $this->getByShortname($sCurrentLng);

		if ($oAdmin_Language)
		{
			return $oAdmin_Language;
		}

		// Первый язык в списке
		$this->queryBuilder()
			->clear()
			->where('active', '=', 1);

		$aAdmin_Language = $this->findAll();

		return count($aAdmin_Language)
			? $aAdmin_Language[0]
			: NULL;
	}

	/**
	 * Get admin language by short name
	 * @param string $shortname short name
	 * @return Admin_Language|NULL
	 */
	public function getByShortname($shortname)
	{
		$this->queryBuilder()
			->clear()
			->where('shortname', '=', $shortname)
			->where('active', '=', 1)
			->limit(1);

		$aAdmin_Language = $this->findAll();

		return isset($aAdmin_Language[0])
			? $aAdmin_Language[0]
			: NULL;
	}

	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return self
	 * @hostcms-event admin_language.onBeforeRedeclaredDelete
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

		Core::moduleIsActive('antispam')
			&& $this->Antispam_Country_Languages->deleteAll(FALSE);

		return parent::delete($primaryKey);
	}

	/**
	 * Change item status
	 * @return self
	 * @hostcms-event admin_language.onBeforeChangeActive
	 * @hostcms-event admin_language.onAfterChangeActive
	 */
	public function changeActive()
	{
		Core_Event::notify($this->_modelName . '.onBeforeChangeActive', $this);

		$this->active = 1 - $this->active;
		$this->save();

		Core_Event::notify($this->_modelName . '.onAfterChangeActive', $this);

		return $this;
	}
}