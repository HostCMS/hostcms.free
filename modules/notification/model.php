<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Notifications.
 *
 * @package HostCMS
 * @subpackage Notification
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Notification_Model extends Core_Entity
{
	/**
	 * Column consist item's name
	 * @var string
	 */
	protected $_nameColumn = 'title';

	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		'notification_user' => array(),
		'user' => array('through' => 'notification_users')
	);

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'module' => array()
	);

	/**
	 * Backend property
	 * @var mixed
	 */
	public $userId = NULL;

	/**
	 * Backend property
	 * @var mixed
	 */
	public $titleDescription = NULL;

	/**
	 * Backend property
	 * @var mixed
	 */
	public $read = NULL;

	/**
	 * Backend callback method
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function imageBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		if ($this->module_id && ($oCore_Module = $this->Module->loadModule()->Core_Module) && !is_null($oCore_Module))
		{
			$aNotificationDecorations = $oCore_Module->getNotificationDesign($this->type, $this->entity_id);

			$aNotification['icon'] = Core_Array::get($aNotificationDecorations, 'icon');

			$sReturn = "<i class=\"notification-ico {$aNotification['icon']['ico']} {$aNotification['icon']['background-color']} {$aNotification['icon']['color']} fa-fw\"></i>";
		}
		else
		{
			$sReturn = '<i class="fa fa-info bg-themeprimary white fa-fw"></i>';
		}

		return $sReturn;
	}

	/**
	 * Backend callback method
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function titleDescriptionBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		$sReturn = htmlspecialchars($this->title);

		if ($this->module_id && ($oCore_Module = $this->Module->loadModule()->Core_Module) && !is_null($oCore_Module))
		{
			$aNotificationDecorations = $oCore_Module->getNotificationDesign($this->type, $this->entity_id);

			$href = Core_Array::get($aNotificationDecorations, 'href');
			$onclick = Core_Array::get($aNotificationDecorations, 'onclick');

			if (strlen($href) || strlen($onclick))
			{
				ob_start();

				Admin_Form_Entity::factory('A')
					->href($href)
					->onclick($onclick)
					->value($sReturn)
					->execute();

				$sReturn = ob_get_clean();
			}
		}

		!empty($this->description)
			&& $sReturn .= '<span class="notification-description">' . htmlspecialchars($this->description) . '</span>';

		return $sReturn;
	}

	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return Core_Entity
	 * @hostcms-event notification.onBeforeRedeclaredDelete
	 */
	public function delete($primaryKey = NULL)
	{
		if (is_null($primaryKey))
		{
			$primaryKey = $this->getPrimaryKey();
		}

		$this->id = $primaryKey;

		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredDelete', $this, array($primaryKey));

		$this->Notification_Users->deleteAll(FALSE);

		return parent::delete($primaryKey);
	}

	/**
	 * Get notification
	 * @param int $module_id
	 * @param int $entity_id
	 * @param int $type
	 * @return Notification_Model|NULL
	 */
	public function getNotification($module_id, $type, $entity_id)
	{
		$this->queryBuilder()
			->where('notifications.module_id', '=', $module_id)
			->where('notifications.type', '=', $type)
			->where('notifications.entity_id', '=', $entity_id)
			;

		return $this->getFirst(FALSE);
	}
}