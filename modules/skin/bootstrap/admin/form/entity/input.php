<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Admin forms.
 *
 * @package HostCMS
 * @subpackage Skin
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Skin_Bootstrap_Admin_Form_Entity_Input extends Skin_Default_Admin_Form_Entity_Input
{
	/**
	 * Object has unlimited number of properties
	 * @var boolean
	 */
	protected $_unlimitedProperties = TRUE;
	
	/**
	 * Executes the business logic.
	 */
	public function execute()
	{
		$windowId = $this->_Admin_Form_Controller->getWindowId();

		if (is_null($this->onkeydown))
		{
			$this->onkeydown = $this->onkeyup = $this->onblur = "FieldCheck('{$windowId}', this)";
		}

		$aAttr = $this->getAttrsString();

		$aDivAttr = array();
		if (is_array($this->divAttr))
		{
			foreach ($this->divAttr as $attrName => $attrValue)
			{
				$aDivAttr[] = "{$attrName}=\"" . htmlspecialchars($attrValue) . "\"";
			}
		}

		?>
		<div <?php echo implode(' ', $aDivAttr)?>><?php
		?><span class="caption"><?php echo $this->caption?></span><?php
		if (count($this->_children))
		{
			?><div class="input-group"><?php
		}
		?><input <?php echo implode(' ', $aAttr) ?>/><?php

		$this->_showFormat();

		// Могут быть дочерние элементы элементы
		if (count($this->_children))
		{
			$this->executeChildren();
			?></div><?php
		}

		?></div><?php
	}
}