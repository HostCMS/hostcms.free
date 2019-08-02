<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Constant_Model
 *
 * @package HostCMS
 * @subpackage Constant
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2019 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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
			$oUserCurrent = Core_Entity::factory('User', 0)->getCurrent();
			$this->_preloadValues['user_id'] = is_null($oUserCurrent) ? 0 : $oUserCurrent->id;
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
	 * return self
	 */
	public function changeStatus()
	{
		$this->active = 1 - $this->active;
		return $this->save();
	}
}