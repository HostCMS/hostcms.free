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
class Skin_Default_Admin_Form_Entity_Date extends Admin_Form_Entity_Input
{
	/**
	 * Constructor.
	 */
	public function __construct()
	{
		$this->_allowedProperties += array(
			'options',
			'dateFormat',
		);

		$this->_skipProperties[] = 'options';
		$this->_skipProperties[] = 'dateFormat';

		parent::__construct();

		$this->size(9)
			->class('calendar_field')
			->options(array(
				'showOtherMonths' => true,
				'selectOtherMonths' => true,
				'changeMonth' => true,
				'changeYear' => true
			));
	}

	/**
	 * Convert $this->velue
	 * @return string
	 */
	protected function _convertDate($value)
	{
		if ($value == '0000-00-00' || $value == '')
		{
			return '';
		}
		elseif ($this->dateFormat != '')
		{
			return date($this->dateFormat, Core_Date::sql2timestamp($value));
		}

		return Core_Date::sql2date($value);
	}

	/**
	 * Executes the business logic.
	 * @hostcms-event Skin_Default_Admin_Form_Entity_Date.onBeforeExecute
	 * @hostcms-event Skin_Default_Admin_Form_Entity_Date.onAfterExecute
	 */
	public function execute()
	{
		Core_Event::notify(get_class($this) . '.onBeforeExecute', $this);

		$this->value = $this->_convertDate($this->value);

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

		?><input <?php echo implode(' ', $aAttr) ?>/>

		<script>
		(function($) {
			$("#<?php echo Core_Str::escapeJavascriptVariable($this->id)?>")
				.datepicker({<?php echo Core_Array::array2jsObject($this->options)?>});
		})(jQuery);
		</script><?php

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
