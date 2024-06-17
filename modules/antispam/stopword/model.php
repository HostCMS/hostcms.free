<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Antispam_Stopword_Model
 *
 * @package HostCMS
 * @subpackage Antispam
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Antispam_Stopword_Model extends Core_Entity
{
	/**
	 * Model name
	 * @var mixed
	 */
	protected $_modelName = 'antispam_stopword';

	/**
	 * Column consist item's name
	 * @var string
	 */
	protected $_nameColumn = 'value';

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
			$oUser = Core_Auth::getCurrentUser();
			$this->_preloadValues['user_id'] = is_null($oUser) ? 0 : $oUser->id;
		}
	}
}