<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Admin forms.
 *
 * @package HostCMS
 * @subpackage Skin
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
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

		$this->a = Core_Html_Entity::factory('A')->target('_blank');
		$this->img = Core_Html_Entity::factory('Img');
		$this->icon = Core_Html_Entity::factory('I');
		$this->div = Core_Html_Entity::factory('Div');
	}

	/**
	 * Executes the business logic.
	 * @hostcms-event Skin_Default_Admin_Form_Entity_Link.onBeforeExecute
	 * @hostcms-event Skin_Default_Admin_Form_Entity_Link.onAfterExecute
	 */
	public function execute()
	{
		Core_Event::notify(get_class($this) . '.onBeforeExecute', $this);

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

		if ($this->caption != '')
		{
			$this->div->add(
				Core_Html_Entity::factory('Span')
					->class('caption')
					->value(htmlspecialchars((string) $this->caption))
			);
		}

		$this->div
			->add($this->a
				->add($this->icon)
				->add(
					Core_Html_Entity::factory('Code')
						->value(htmlspecialchars((string) $this->a->value))
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

		Core_Event::notify(get_class($this) . '.onAfterExecute', $this);
	}
}