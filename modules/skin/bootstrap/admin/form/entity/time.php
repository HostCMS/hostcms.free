<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Admin forms.
 *
 * @package HostCMS
 * @subpackage Skin
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2020 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Skin_Bootstrap_Admin_Form_Entity_Time extends Skin_Default_Admin_Form_Entity_Time {

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();

		$this->size('auto')
			->class('form-control hasDatetimepicker');
	}

	/**
	 * Executes the business logic.
	 */
	public function execute()
	{
		$windowId = $this->_Admin_Form_Controller->getWindowId();

		/*$this->value = $this->value == '00:00:00' || $this->value == ''
			? ''
			: $this->value;*/

		$aAttr = $this->getAttrsString();

		$aDivAttr = array();

		// Установим атрибуты div'a.
		if (is_array($this->divAttr))
		{
			foreach ($this->divAttr as $attrName => $attrValue)
			{
				$aDivAttr[] = "{$attrName}=\"" . htmlspecialchars($attrValue) . "\"";
			}
		}

		$sCurrentLng = Core_I18n::instance()->getLng();

		?><div <?php echo implode(' ', $aDivAttr)?>><?php
		?><span class="caption"><?php echo $this->caption?></span><?php
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
			$('#<?php echo Core_Str::escapeJavascriptVariable($windowId)?> #div_<?php echo Core_Str::escapeJavascriptVariable($this->id)?>').datetimepicker({locale: '<?php echo Core_Str::escapeJavascriptVariable($sCurrentLng)?>', format: '<?php echo Core_Str::escapeJavascriptVariable(Core::$mainConfig['timePickerFormat'])?>', showTodayButton: false, showClear: true});
		})(jQuery);
		</script><?php

		?></div><?php
	}
}