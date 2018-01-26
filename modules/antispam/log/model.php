<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Antispam_Log_Model
 *
 * @package HostCMS
 * @subpackage Antispam
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Antispam_Log_Model extends Core_Entity
{
	/**
	 * Callback property
	 * @var string
	 */
	public $country_name = NULL;

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'antispam_country' => array(),
	);

	/**
	 * Model name
	 * @var mixed
	 */
	protected $_modelName = 'antispam_log';

	/**
	 * Default sorting for models
	 * @var array
	 */
	protected $_sorting = array(
		'antispam_logs.datetime' => 'ASC',
	);

	/**
	 * Constructor.
	 * @param int $id entity ID
	 */
	public function __construct($id = NULL)
	{
		parent::__construct($id);

		if (is_null($id))
		{
			$this->_preloadValues['datetime'] = Core_Date::timestamp2sql(time());
		}
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
			$oAntispam_Country = Core_Entity::factory('Antispam_Country')->find($this->antispam_country_id);

			if (!is_null($oAntispam_Country->id))
			{
				$oAntispam_Country_Language = $oAntispam_Country->Antispam_Country_Languages->getByAdmin_language_id($oAdmin_Language->id);

				if (!is_null($oAntispam_Country_Language))
				{
					echo "<img alt='" . htmlspecialchars($oAntispam_Country_Language->name) . "' title='" . htmlspecialchars($oAntispam_Country_Language->name) . "' class='antispam-flag' src='/modules/skin/bootstrap/images/flags/" . htmlspecialchars($oAntispam_Country->code) . ".png' />";
				}
			}
			else
			{
				echo "<img alt='Unknown' title='Unknown' class='antispam-flag' src='/modules/skin/bootstrap/images/flags/unknown.png' />";
			}
		}
	}
}