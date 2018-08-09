<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Revision_Model
 *
 * @package HostCMS
 * @subpackage Revision
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Revision_Model extends Core_Entity
{
	/**
	 * Backend property
	 * @var mixed
	 */
	public $name = NULL;

	/**
	 * Column consist item's name
	 * @var string
	 */
	protected $_nameColumn = 'entity_id';

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

		if (is_null($id))
		{
			$oUserCurrent = Core_Entity::factory('User', 0)->getCurrent();
			$this->_preloadValues['user_id'] = is_null($oUserCurrent) ? 0 : $oUserCurrent->id;
		}
	}

	/**
	 * Rollback Revision
	 * @return self
	 */
	public function rollback()
	{
		$oModel = Core_Entity::factory($this->model, $this->entity_id);
		$oModel->rollbackRevision($this->id);
		return $this;
	}

	/**
	 * Backend callback method
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function user($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		$oUser = $this->User;

		Core::factory('Core_Html_Entity_Span')
			->class('badge badge-hostcms badge-square')
			->value(
				htmlspecialchars(
					!is_null($oUser->id) ? $oUser->login : 'Unknown user'
				)
			)
			->execute();
	}

	/**
	 * Get entity description
	 * @return string
	 */
	public function getTrashDescription()
	{
		return htmlspecialchars(
			Core_Str::cut($this->model, 255)
		);
	}
}