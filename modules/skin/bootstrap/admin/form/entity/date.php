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
class Skin_Bootstrap_Admin_Form_Entity_Date extends Skin_Default_Admin_Form_Entity_Date {

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();

		$this->size('auto')
			->class('form-control hasDatetimepicker')
			->options(array(
				'locale' => Core_I18n::instance()->getLng(),
				'format' => Core::$mainConfig['datePickerFormat'],
				'showTodayButton' => true,
				'showClear' => true
			));
	}

	/**
	 * Executes the business logic.
	 */
	public function execute()
	{
		$windowId = $this->_Admin_Form_Controller->getWindowId();

		$this->value = $this->_convertDate($this->value);

		$aAttr = $this->getAttrsString();

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
		if (strlen($this->caption))
		{
			?><span class="caption"><?php echo $this->caption?></span><?php
		}
		?><div id="div_<?php echo $this->id?>" class="input-group">
			<input <?php echo implode(' ', $aAttr) ?>/>
			<span class="input-group-addon">
				<span class="fa fa-calendar"></span>
			</span>
			<?php
			$this->executeChildren();
			?>
		</div>

		<script>
		(function($) {
			$('#<?php echo Core_Str::escapeJavascriptVariable($windowId)?> #div_<?php echo Core_Str::escapeJavascriptVariable($this->id)?>')
				.datetimepicker({<?php echo Core_Array::array2jsObject($this->options)?>});
		})(jQuery);
		</script><?php

		?></div><?php
	}
}