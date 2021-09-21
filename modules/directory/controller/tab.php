<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Directory_Controller_Tab
 *
 * @package HostCMS
 * @subpackage Directory
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
abstract class Directory_Controller_Tab extends Core_Servant_Properties
{
	protected $_allowedProperties = array(
		'title',
		'relation',
		'showPublicityControlElement',
		'prefix'
	);

	/**
	 * The singleton instances.
	 * @var mixed
	 */
	static public $instance = array();

	/**
	 * Register an existing instance as a singleton.
	 * @param string $name driver's name
	 * @return object
	 */
	static public function instance($name)
	{
		if (!is_string($name))
		{
			throw new Core_Exception('Wrong argument type (expected String)');
		}

		if (!isset(self::$instance[$name]))
		{
			$driver = __CLASS__ . '_' . ucfirst($name);
			self::$instance[$name] = new $driver();
		}

		return self::$instance[$name];
	}

	protected $_aDirectory_Relations = NULL;

	public function execute()
	{
		$oPersonalDataInnerWrapper = Admin_Form_Entity::factory('Div')
			->class('well well-sm margin-bottom-10')
			->add(
				Admin_Form_Entity::factory('Code')
					->html('<p class="semi-bold"><i class="widget-icon ' . $this->_faTitleIcon . ' icon-separator ' . $this->_titleHeaderColor . '"></i>' . $this->title . '</p>')
			);

		$this->_aDirectory_Relations = $this->relation->findAll();

		$this->_execute($oPersonalDataInnerWrapper);

		return $oPersonalDataInnerWrapper;
	}

	protected function _publicityControlElement()
	{
		return Admin_Form_Entity::factory('Checkbox')
			->divAttr(array('class' => 'col-xs-2 no-padding margin-top-23'))
			->caption('<acronym title="" data-original-title="' . Core::_('Core.data_show_title') . '">' . Core::_('Core.show_title') . '</acronym>');
	}

	protected function _buttons($className = '')
	{
		return Admin_Form_Entity::factory('Div') // div с кноками + и -
			->class('add-remove-property margin-top-23 pull-left' . (count($this->_aDirectory_Relations) ? ' btn-group' : '') . ($className ? ' ' . $className : ''))
			->add(
				Admin_Form_Entity::factory('Code')
					->html('<div class="btn btn-palegreen" onclick="$.cloneFormRow(this); event.stopPropagation();"><i class="fa fa-plus-circle close"></i></div><div class="btn btn-darkorange btn-delete' . (count($this->_aDirectory_Relations) ? '' : ' hide') . '" onclick="$.deleteFormRow(this); event.stopPropagation();"><i class="fa fa-minus-circle close"></i></div>')
			);
	}

	protected function _getDirectoryTypes()
	{
		$aDirectory_Types = Core_Entity::factory($this->_directoryTypeName)->findAll();

		$aMasDirectoryTypes = array();

		foreach ($aDirectory_Types as $oDirectory_Type)
		{
			$aMasDirectoryTypes[$oDirectory_Type->id] = $oDirectory_Type->name;
		}

		return $aMasDirectoryTypes;
	}
}