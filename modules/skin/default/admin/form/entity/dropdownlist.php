<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Admin forms. Dropdown list.
 *
 * - options(array()) Массив значений
 * - value(string) Значение
 *
 * <code>
 * $oController->options(
 * 	array(
 * 		0 => array('value' => 'Default', 'icon' => 'fa fa-user', 'color' => '#eee'),
 * 		1 => array('value' => 'Second', 'icon' => 'fa fa-phone', 'color' => '#aaa'),
 * 		2 => 'Third',
 * )
 * );
 * </code>
 *
 * @package HostCMS
 * @subpackage Skin
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Skin_Default_Admin_Form_Entity_Dropdownlist extends Admin_Form_Entity
{
	/**
	 * Skip properties
	 * @var array
	 */
	protected $_skipProperties = array(
		'divAttr', // array
		'options', // array
		'caption',
		'value', // идет в selected
		'format' // array, массив условий форматирования
	);

	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'name',
		'disabled'
	);

	/**
	 * Counter of Admin_Form_Entity_Select used in the form
	 * @var object
	 */
	//static $iFilterCount = 0;

	protected $_oCore_Html_Entity_Dropdownlist = NULL;

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		// Combine
		$this->_skipProperties = array_combine($this->_skipProperties, $this->_skipProperties);

		$this->_oCore_Html_Entity_Dropdownlist = new Core_Html_Entity_Dropdownlist();
		$this->_allowedProperties += $this->_oCore_Html_Entity_Dropdownlist->getAllowedProperties();

		// Свойства, исключаемые для dropdownlist, добавляем в список разрешенных объекта
		$this->_allowedProperties += $this->_skipProperties;

		parent::__construct();

		$oCore_Registry = Core_Registry::instance();
		$iAdmin_Form_Count = $oCore_Registry->get('Admin_Form_Count', 0);
		$oCore_Registry->set('Admin_Form_Count', $iAdmin_Form_Count + 2);

		$this->id = $this->name = 'field_id_' . $iAdmin_Form_Count;

		//$this->class .= ' form-control';
		$this->divAttr = array('class' => 'form-group col-md-6 col-xs-12');
	}

	/**
	 * Executes the business logic.
	 * @hostcms-event Skin_Default_Admin_Form_Entity_Dropdownlist.onBeforeExecute
	 * @hostcms-event Skin_Default_Admin_Form_Entity_Dropdownlist.onAfterExecute
	 */
	public function execute()
	{
		Core_Event::notify(get_class($this) . '.onBeforeExecute', $this);

		// is_null($this->size) && is_null($this->style) && $this->style('width: 100%');

		$aDefaultDivAttr = array('class' => 'item_div');
		$this->divAttr = Core_Array::union($this->divAttr, $aDefaultDivAttr);

		// $aAttr = $this->getAttrsString();

		// Установим атрибуты div'a.
		$aDivAttr = array();
		if (is_array($this->divAttr))
		{
			foreach ($this->divAttr as $attrName => $attrValue)
			{
				$aDivAttr[] = "{$attrName}=\"" . htmlspecialchars((string) $attrValue) . "\"";
			}
		}

		?><div <?php echo implode(' ', $aDivAttr)?>><?php

		if ($this->caption != '')
		{
			?><span class="caption"><?php echo $this->caption?></span><?php
		}

		if (count($this->_children))
		{
			?><div class="input-group"><?php
		}

		$this->_oCore_Html_Entity_Dropdownlist->_data = $this->_data;

		$this->_oCore_Html_Entity_Dropdownlist
			->value($this->value)
			->options($this->options)
			->name($this->name)
			->disabled($this->disabled)
			->onchange($this->onchange)
			->execute();

		$this->executeChildren();

		if (count($this->_children))
		{
			?></div><?php
		}

		?></div><?php

		Core_Event::notify(get_class($this) . '.onAfterExecute', $this);
	}
}