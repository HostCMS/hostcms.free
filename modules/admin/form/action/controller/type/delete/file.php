<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Admin forms.
 * Типовой контроллер удаления файла сущности
 *
 * @package HostCMS
 * @subpackage Admin
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2016 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Admin_Form_Action_Controller_Type_Delete_File extends Admin_Form_Action_Controller
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'methodName',
		'divId'
	);

	/**
	 * Constructor.
	 * @param Admin_Form_Action_Model $oAdmin_Form_Action action
	 */
	public function __construct(Admin_Form_Action_Model $oAdmin_Form_Action)
	{
		parent::__construct($oAdmin_Form_Action);
	}

	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 * @return boolean
	 */
	public function execute($operation = NULL)
	{
		if (is_null($this->methodName))
		{
			throw new Core_Exception('methodName is NULL.');
		}

		if (is_null($this->divId))
		{
			throw new Core_Exception('divId is NULL.');
		}

		$methodName = $this->methodName;
		$this->_object->$methodName($operation);

		$windowId = $this->_Admin_Form_Controller->getWindowId();
		
		!is_array($this->divId) && $this->divId = array($this->divId);
		
		ob_start();
		foreach ($this->divId as $sDivId)
		{
			// Удаляем дочерние узлы
			Core::factory('Core_Html_Entity_Script')
				->type("text/javascript")
				->value("$('#{$windowId} #{$sDivId}').remove()")
				->execute();
		}
		$this->addMessage(ob_get_clean());

		return TRUE;
	}
}