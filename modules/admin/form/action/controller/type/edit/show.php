<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Admin forms.
 *
 * @package HostCMS
 * @subpackage Admin
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
abstract class Admin_Form_Action_Controller_Type_Edit_Show extends Core_Servant_Properties
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'title',
		'message',
		'children',
		'Admin_Form_Controller',
		'tabs',
		'buttons',
	);

	/**
	 * Admin form entity form
	 * @var Admin_Form_Entity_Form_Model
	 */
	protected $_Admin_Form_Entity_Form = NULL;

	/**
	 * Constructor
	 * @param Admin_Form_Entity_Form_Model $oAdmin_Form_Entity_Form
	 */
	public function __construct($oAdmin_Form_Entity_Form)
	{
		parent::__construct();

		$this->children = array();

		$this->_Admin_Form_Entity_Form = $oAdmin_Form_Entity_Form;
	}

    /**
     * Create new form controller
     * @param object $oAdmin_Form_Entity_Form
     * @return object
     * @throws Core_Exception
     */
	static public function create($oAdmin_Form_Entity_Form)
	{
		$className = 'Skin_' . ucfirst(Core_Skin::instance()->getSkinName()) . '_' . __CLASS__;

		if (!class_exists($className))
		{
			throw new Core_Exception("Class '%className' does not exist",
				array('%className' => $className));
		}

		return new $className($oAdmin_Form_Entity_Form);
	}

	/**
	 * Show edit form
	 * @return boolean
	 */
	abstract public function showEditForm();
}