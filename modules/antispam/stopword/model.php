<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Antispam_Stopword_Model
 *
 * @package HostCMS
 * @subpackage Antispam
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
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
			$this->_preloadValues['type'] = 0;
			$this->_preloadValues['case_sensitive'] = 0;
		}
	}

	/**
	 * Backend
	 */
	public function typeBackend()
	{
		Core_Html_Entity::factory('Span')
			->class('badge badge-round badge-max-width blue')
			->value(Core::_('Antispam_Stopword.type' . $this->type))
			->title(Core::_('Antispam_Stopword.type' . $this->type))
			->execute();
	}

	/**
	 * Change active
	 * @return self
	 * @hostcms-event antispam_stopword.onBeforeChangeCaseSesitive
	 * @hostcms-event antispam_stopword.onAfterChangeCaseSesitive
	 */
	public function changeCaseSesitive()
	{
		Core_Event::notify($this->_modelName . '.onBeforeChangeCaseSesitive', $this);

		$this->case_sensitive = 1 - $this->case_sensitive;
		$this->save();

		Core_Event::notify($this->_modelName . '.onAfterChangeCaseSesitive', $this);

		return $this;
	}

	/**
	 * Change active
	 * @return self
	 * @hostcms-event antispam_stopword.onBeforeChangeActive
	 * @hostcms-event antispam_stopword.onAfterChangeActive
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