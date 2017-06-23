<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Admin forms.
 *
 * * postingUnchecked - добавляет скрытый input со значением 0, передается в случае снятия галочки у checkbox
 *
 * @package HostCMS
 * @subpackage Skin
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2016 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Skin_Default_Admin_Form_Entity_Checkbox extends Admin_Form_Entity_Input
{
	/**
	 * Constructor.
	 */
	public function __construct()
	{
		// Add propery
		$this->_allowedProperties[] = 'postingUnchecked';

		parent::__construct();
		$this->type('checkbox');
	}

	/**
	 * Executes the business logic.
	 */
	public function execute()
	{
		if (is_null($this->checked)
			&& $this->value != 0)
		{
			$this->checked = 'checked';
		}

		// Значение, передаваемое при включенном checkbox
		$this->value = 1;

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

		?><div <?php echo implode(' ', $aDivAttr)?>><?php

		/*?><input <?php echo implode(' ', $aAttr) ?>/><?php
		?><span class="caption" style="display: inline"><label for="<?php echo $this->id?>"><?php echo $this->caption?></label></span><?php*/

		if ($this->postingUnchecked)
		{
			?><input type="hidden" name="<?php echo $this->name?>" value="0" /><?php
		}

		?><label><input <?php echo implode(' ', $aAttr) ?>/> <?php
		?><span class="caption" style="display: inline"><?php echo $this->caption?></span></label><?php

		$this->executeChildren();
		?></div><?php
	}
}
