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
class Skin_Default_Admin_Form_Entity_Time extends Admin_Form_Entity_Input
{
	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();

		$this->size(9)
			->class('calendar_field');
	}

	/**
	 * Executes the business logic.
	 * @hostcms-event Skin_Default_Admin_Form_Entity_Time.onBeforeExecute
	 * @hostcms-event Skin_Default_Admin_Form_Entity_Time.onAfterExecute
	 */
	public function execute()
	{
		Core_Event::notify(get_class($this) . '.onBeforeExecute', $this);

		$this->value = $this->value == '00:00:00' || $this->value == ''
			? ''
			: $this->value;

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

		//$this->_showFormat();

		$this->executeChildren();

		if (count($this->_children))
		{
			?></div><?php
		}

		?></div><?php

		Core_Event::notify(get_class($this) . '.onAfterExecute', $this);
	}
}
