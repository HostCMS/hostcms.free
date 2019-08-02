<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Admin forms.
 *
 * @package HostCMS
 * @subpackage Skin
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2019 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Skin_Default_Admin_Form_Entity_Link extends Admin_Form_Entity
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'divAttr', // array
		'a',
		'img',
		'icon',
		'caption',
		'div'
	);

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();

		$this->a = Core::factory('Core_Html_Entity_A')->target('_blank');
		$this->img = Core::factory('Core_Html_Entity_Img');
		$this->icon = Core::factory('Core_Html_Entity_I');
		$this->div = Core::factory('Core_Html_Entity_Div');
	}

	/**
	 * Executes the business logic.
	 */
	public function execute()
	{
		$aDefaultDivAttr = array('class' => 'input-lg item_div item_div_as_is');

		$this->divAttr = Core_Array::union($this->divAttr, $aDefaultDivAttr);

		// Установим атрибуты div'a.
		$aDivAttr = array();
		if (is_array($this->divAttr))
		{
			foreach ($this->divAttr as $attrName => $attrValue)
			{
				$this->div->$attrName = $attrValue;
			}
		}

		!is_null($this->img->src) && $this->div->add($this->img);

		if (count($this->_children))
		{
			?><div class="input-group"><?php
		}
		
		if (strlen($this->caption))
		{
			$this->div->add(
				Core::factory('Core_Html_Entity_Span')
					->class('caption')
					->value(htmlspecialchars($this->caption))
			);
		}
		
		$this->div
			->add($this->a
				->add($this->icon)
				->add(
					Core::factory('Core_Html_Entity_Code')
						->value(htmlspecialchars($this->a->value))
				)
				->value('')
			)
			->execute();
			
		// Могут быть дочерние элементы элементы
		$this->executeChildren();

		if (count($this->_children))
		{
			?></div><?php
		}
	}
}