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
class Skin_Bootstrap_Admin_Form_Entity_Checkbox extends Skin_Default_Admin_Form_Entity_Checkbox {

	/**
	 * Executes the business logic.
	 * @hostcms-event Skin_Bootstrap_Admin_Form_Entity_Checkbox.onBeforeExecute
	 * @hostcms-event Skin_Bootstrap_Admin_Form_Entity_Checkbox.onAfterExecute
	 */
	public function execute()
	{
		Core_Event::notify(get_class($this) . '.onBeforeExecute', $this);

		/*if (is_null($this->checked) && $this->value != 0)
		{
			$this->checked = 'checked';
		}*/

		// Skip check field
		$this->format = NULL;

		// Значение, передаваемое при включенном checkbox
		$this->value === '' && $this->value = 1;

		$aAttr = $this->getAttrsString();

		if (is_null($this->checked) && $this->value != 0 || $this->checked)
		{
			$aAttr[] = 'checked="checked"';
		}

		$aDivAttr = array();
		if (is_array($this->divAttr))
		{
			foreach ($this->divAttr as $attrName => $attrValue)
			{
				$aDivAttr[] = "{$attrName}=\"" . htmlspecialchars((string) $attrValue) . "\"";
			}
		}

		?><div <?php echo implode(' ', $aDivAttr)?>><?php

		if ($this->postingUnchecked)
		{
			?><input type="hidden" name="<?php echo htmlspecialchars((string) $this->name)?>" value="0" /><?php
		}

		if (count($this->_children))
		{
			?><div class="input-group"><?php
		}

		?><label class="checkbox-inline"><input <?php echo implode(' ', $aAttr)?>/><span class="text"><?php echo $this->caption?></span></label>

		<?php
		if (count($this->_children))
		{
			// Могут быть дочерние элементы элементы
			$this->executeChildren();
			?></div><?php
		}
		?>
		</div><?php

		Core_Event::notify(get_class($this) . '.onAfterExecute', $this);
	}
}