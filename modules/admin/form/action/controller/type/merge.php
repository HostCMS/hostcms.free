<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Admin forms.
 * Типовой контроллер объединения в списке сущностей
 * Объект должен иметь метод Core_Entity::merge(Core_Entity $object), в который передайте объект, с которым происходит объединение
 *
 * @package HostCMS
 * @subpackage Admin
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Admin_Form_Action_Controller_Type_Merge extends Admin_Form_Action_Controller
{
	/**
	 * Global properties support
	 * @var Core_Registry
	 */
	protected $_Core_Registry = NULL;

	/**
	 * Key name for saving in Core_Registry
	 * @var string
	 */
	protected $_keyName = NULL;

	/**
	 * Constructor.
	 * @param Admin_Form_Action_Model $oAdmin_Form_Action action
	 */
	public function __construct(Admin_Form_Action_Model $oAdmin_Form_Action)
	{
		parent::__construct($oAdmin_Form_Action);

		$this->_Core_Registry = Core_Registry::instance();
		$this->_keyName = 'merge_' . $this->_Admin_Form_Action->id;

		// Skip prev value
		$this->_Core_Registry->set($this->_keyName, NULL);
	}

	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 * @return self
	 */
	public function execute($operation = NULL)
	{
		$keyValue = $this->_Core_Registry->get($this->_keyName);

		if (is_null($keyValue))
		{
			$this->_Core_Registry->set($this->_keyName, $this->_object->getPrimaryKey());
			return NULL;
		}
		else
		{
			// Предыдущий объект
			$className = get_class($this->_object);
			$prevObject = new $className($keyValue);

			$prevObject->merge($this->_object);

			return $this;
		}
	}
}