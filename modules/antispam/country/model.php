<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Antispam_Country_Model
 *
 * @package HostCMS
 * @subpackage Antispam
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Antispam_Country_Model extends Core_Entity
{
	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		'antispam_country_language' => array(),
	);

	/**
	 * Callback property
	 * @var string
	 */
	public $country_name = NULL;

	/**
	 * Model name
	 * @var mixed
	 */
	protected $_modelName = 'antispam_country';

	/**
	 * Change allow
	 * @return self
	 * @hostcms-event shortcode.onBeforeChangeActive
	 * @hostcms-event shortcode.onAfterChangeActive
	 */
	public function changeAllow()
	{
		Core_Event::notify($this->_modelName . '.onBeforeChangeAllow', $this);

		$this->allow = 1 - $this->allow;
		$this->save();

		Core_Event::notify($this->_modelName . '.onAfterChangeAllow', $this);

		return $this;
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function country_flag()
	{
		$oAdmin_Language = Core_Entity::factory('Admin_Language')->getByShortname(Core_Array::getSession('current_lng'));

		if (!is_null($oAdmin_Language))
		{
			$oAntispam_Country_Language = $this->Antispam_Country_Languages->getByAdmin_language_id($oAdmin_Language->id);

			if (!is_null($oAntispam_Country_Language))
			{
				echo "<img alt='" . htmlspecialchars($oAntispam_Country_Language->name) . "' title='" . htmlspecialchars($oAntispam_Country_Language->name) . "' class='antispam-flag' src='/modules/skin/bootstrap/images/flags/" . htmlspecialchars($this->code) . ".png' />";
			}
		}
	}

	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return self
	 * @hostcms-event antispam_country.onBeforeRedeclaredDelete
	 */
	public function delete($primaryKey = NULL)
	{
		if (is_null($primaryKey))
		{
			$primaryKey = $this->getPrimaryKey();
		}

		$this->id = $primaryKey;

		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredDelete', $this, array($primaryKey));

		$this->Antispam_Country_Languages->deleteAll(FALSE);

		return parent::delete($primaryKey);
	}

	/**
	 * Allow countries
	 * @return self
	 */
	public function allowAccess()
	{
		$this->allow = 1;
		return $this->save();
	}

	/**
	 * Deny countries
	 * @return self
	 */
	public function denyAccess()
	{
		$this->allow = 0;
		return $this->save();
	}
}