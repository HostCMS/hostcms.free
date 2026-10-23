<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Constant_Model
 *
 * @package HostCMS
 * @subpackage Constant
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Constant_Model extends Core_Entity
{
	/**
	 * Backend property
	 * @var int
	 */
	public $img = 1;

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'constant_dir' => array(),
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
	 * Define a constant
	 */
	public function define()
	{
		if (!is_null($this->name) && !defined($this->name))
		{
			$lowerValue = strtoupper(trim($this->value));

			if ($lowerValue == 'FALSE')
			{
				$value = FALSE;
			}
			elseif ($lowerValue == 'TRUE')
			{
				$value = TRUE;
			}
			else
			{
				$value = $this->value;
			}

			define($this->name, $value);
		}
	}

	/**
	 * Change constant status
	 * @return self
	 * @hostcms-event constant.onBeforeChangeStatus
	 * @hostcms-event constant.onAfterChangeStatus
	 */
	public function changeStatus()
	{
		Core_Event::notify($this->_modelName . '.onBeforeChangeStatus', $this);

		$this->active = 1 - $this->active;
		$this->save();

		Core_Event::notify($this->_modelName . '.onAfterChangeStatus', $this);

		return $this;
	}
}