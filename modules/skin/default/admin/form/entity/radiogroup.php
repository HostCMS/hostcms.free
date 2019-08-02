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
class Skin_Default_Admin_Form_Entity_Radiogroup extends Admin_Form_Entity_Input
{
	/**
	 * Constructor.
	 */
	public function __construct()
	{
		// Add label propery
		$this->_allowedProperties[] = 'radio';
		$this->_allowedProperties[] = 'labelAttr';
		$this->_allowedProperties[] = 'separator';
		$this->_allowedProperties[] = 'ico';
		$this->_allowedProperties[] = 'buttonset';

		$this->_skipProperies[] = 'id';
		$this->_skipProperies[] = 'value';
		$this->_skipProperies[] = 'radio';
		$this->_skipProperies[] = 'labelAttr';
		$this->_skipProperies[] = 'separator';
		$this->_skipProperies[] = 'ico';
		$this->_skipProperies[] = 'buttonset';

		parent::__construct();

		$this->type('radio');
	}

	/**
	 * Executes the business logic.
	 */
	public function execute()
	{
		/*if (is_null($this->checked)
			&& $this->value != 0)
		{
			$this->checked = 'checked';
		}

		// Значение, передаваемое при включенном checkbox
		$this->value = 1;*/

		$aAttr = $this->getAttrsString();

		$aDefaultDivAttr = array('class' => 'item_div');
		$this->divAttr = Core_Array::union($this->divAttr, $aDefaultDivAttr);

		$aDivAttr = array();
		// Установим атрибуты div'a.
		if (is_array($this->divAttr))
		{
			foreach ($this->divAttr as $attrName => $attrValue)
			{
				$aDivAttr[] = "{$attrName}=\"" . htmlspecialchars($attrValue) . "\"";
			}
		}

		$aLabelAttr = array();
		// Установим атрибуты div'a.
		if (is_array($this->labelAttr))
		{
			foreach ($this->labelAttr as $attrName => $attrValue)
			{
				$aLabelAttr[] = "{$attrName}=\"" . htmlspecialchars($attrValue) . "\"";
			}
		}

		?><div <?php echo implode(' ', $aDivAttr)?>><?php

		if ($this->buttonset)
		{
			$sButtonsetId = 'buttonset_' . Core_Array::get($aDivAttr, 'id');
			?><div id="<?php echo $sButtonsetId?>"><?php
		}
		
		?><span class="caption"><?php echo $this->caption?></span><?php

		foreach ($this->radio as $key => $value)
		{
			$tmpAttr = $aAttr;

			if ($key == $this->value)
			{
				$tmpAttr[] = 'checked="checked"';
			}
			$tmpAttr[] = 'id="' . htmlspecialchars($this->id) . $key . '"';
			$tmpAttr[] = 'value="' . htmlspecialchars($key) . '"';

			?><input <?php echo implode(' ', $tmpAttr) ?>/><?php
			?><span class="caption" style="display: inline"><label for="<?php echo $this->id, $key?>"<?php echo implode(' ', $aLabelAttr)?>><?php echo $value?></label></span><?php

			echo $this->separator;
		}

		if ($this->buttonset)
		{
			$windowId = $this->_Admin_Form_Controller->getWindowId();
			?></div><script>$(function() {
				$('#<?php echo $windowId?> #<?php echo $sButtonsetId?>').buttonset();
			});</script><?php
		}
		
		?></div><?php
	}
}
