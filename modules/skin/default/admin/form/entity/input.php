<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Admin forms.
 *
 * @package HostCMS
 * @subpackage Skin
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Skin_Default_Admin_Form_Entity_Input extends Admin_Form_Entity
{
	/**
	 * Skip properties
	 * @var array
	 */
	protected $_skipProperties = array(
		'divAttr', // array
		'caption',
		'format' // array, массив условий форматирования
	);

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		$this->_allowedProperties += array(
			'colorpicker',
		);

		// Combine
		$this->_skipProperties = array_combine($this->_skipProperties, $this->_skipProperties);

		$oCore_Html_Entity_Input = new Core_Html_Entity_Input();
		$this->_allowedProperties += $oCore_Html_Entity_Input->getAllowedProperties();

		// Свойства, исключаемые для <input>, добавляем в список разрешенных объекта
		$this->_allowedProperties += $this->_skipProperties;

		parent::__construct();

		$oCore_Registry = Core_Registry::instance();
		$iAdmin_Form_Count = $oCore_Registry->get('Admin_Form_Count', 0);
		$oCore_Registry->set('Admin_Form_Count', $iAdmin_Form_Count + 1);

		$this->id = $this->name = 'field_id_' . $iAdmin_Form_Count;
		$this->type('text');

		$this->class .= ' form-control';

		$this->divAttr = array('class' => 'form-group col-xs-12');
	}

	/**
	 * Executes the business logic.
	 */
	public function execute()
	{
		is_null($this->size) && is_null($this->style) && $this->style('width: 100%');

		$aAttr = $this->getAttrsString();

		$aDefaultDivAttr = array('class' => 'item_div');
		$this->divAttr = Core_Array::union($this->divAttr, $aDefaultDivAttr);

		$aDivAttr = array();
		// Установим атрибуты div'a.
		if (is_array($this->divAttr))
		{
			foreach ($this->divAttr as $attrName => $attrValue)
			{
				$aDivAttr[] = "{$attrName}=\"" . htmlspecialchars((string) $attrValue) . "\"";
			}
		}

		?><div <?php echo implode(' ', $aDivAttr)?>><?php
		?><span class="caption"><?php echo $this->caption?></span><?php

		if (count($this->_children))
		{
			?><div class="input-group"><?php
		}

		?><input <?php echo implode(' ', $aAttr) ?>/><?php

		// Могут быть дочерние элементы элементы
		$this->executeChildren();

		$this->_showFormat();

		if (count($this->_children))
		{
			?></div><?php
		}

		?></div><?php
	}
}