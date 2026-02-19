<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Admin forms.
 *
 * @package HostCMS
 * @subpackage Skin
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Skin_Bootstrap_Admin_Form_Entity_Menu extends Admin_Form_Entity
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'divAttr',
		'name',
		'img',
		'href',
		'onclick',
		'target',
		'icon',
		'position',
	);

	public function __construct() {

		parent::__construct();

		$this->divAttr = array('class' => 'btn-group');
	}

	/**
	 * Show menu item
	 */
	protected function _showMenuItem($bTop)
	{
		$aFirstColors = array(
			'btn-success',
			'btn-info',
			'btn-danger',
			'btn-warning',
			'btn-maroon',
		);

		$aSecondColors = array(
			'btn-palegreen',
			'btn-azure',
			'btn-darkorange',
			'btn-yellow',
			'btn-magenta',
		);

		$aDropdownColors = array(
			'dropdown-success',
			'dropdown-info',
			'dropdown-danger',
			'dropdown-warning',
			'dropdown-maroon',
		);

		$count = count($aSecondColors);

		$index = $this->position % $count;

		$bHasSubmenu = !empty($this->_children);
		$bHasName = strlen((string) $this->name) > 0;

		$oCore_Html_Entity_A = Core_Html_Entity::factory('A');

		strlen((string) $this->href) && $oCore_Html_Entity_A->href($this->href);
		strlen((string) $this->onclick) && $oCore_Html_Entity_A->onclick($this->onclick);
		!is_null($this->target) && $oCore_Html_Entity_A->target($this->target);
		strlen((string) $this->icon) && $oCore_Html_Entity_A->add(
			Core_Html_Entity::factory('I')->class($this->icon . ($bHasName ? ' icon-separator' : ' fa-fw no-margin'))
		);

		$bTop && $oCore_Html_Entity_A->class(
			!is_null($this->class) ? $this->class : "btn {$aFirstColors[$index]}"
		);

		$bHasSubmenu && $oCore_Html_Entity_A
			->set('data-toggle', 'dropdown');

		$bHasName && $oCore_Html_Entity_A->add(
			Core_Html_Entity::factory('Code')->value(htmlspecialchars((string) $this->name))
		);

		foreach ($this->_data as $name => $value)
		{
			$oCore_Html_Entity_A->data($name, $value);
		}

		$oCore_Html_Entity_A->execute();

		if ($bHasSubmenu)
		{
			if (!$this->href && !$this->onclick)
			{
				?><a class="btn <?php echo htmlspecialchars($aSecondColors[$index])?> dropdown-toggle" data-toggle="dropdown"><i class="fa fa-angle-down"></i></a><?php
			}

			?><ul class="dropdown-menu <?php echo $aDropdownColors[$index]?>"><?php

			// Вывод подменю
			foreach ($this->_children as $subMenu)
			{
				?><li><?php $subMenu->_showMenuItem(FALSE)?></li><?php
			}
			?></ul><?php
		}
	}

	/**
	 * Executes the business logic.
	 * @hostcms-event Skin_Bootstrap_Admin_Form_Entity_Menu.onBeforeExecute
	 * @hostcms-event Skin_Bootstrap_Admin_Form_Entity_Menu.onAfterExecute
	 */
	public function execute()
	{
		Core_Event::notify(get_class($this) . '.onBeforeExecute', $this);

		$aDivAttr = array();
		if (is_array($this->divAttr))
		{
			foreach ($this->divAttr as $attrName => $attrValue)
			{
				$aDivAttr[] = "{$attrName}=\"" . htmlspecialchars((string) $attrValue) . "\"";
			}
		}

		?><div <?php echo implode(' ', $aDivAttr)?>><?php $this->_showMenuItem(TRUE)?></div><?php

		Core_Event::notify(get_class($this) . '.onAfterExecute', $this);
	}
}