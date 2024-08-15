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
class Skin_Bootstrap_Admin_Form_Entity_DateTime extends Skin_Default_Admin_Form_Entity_DateTime {

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
				'format' => Core::$mainConfig['dateTimePickerFormat'],
				'showTodayButton' => true,
				'showClear' => true,
				//'viewMode' => 'years',
				//'format' => 'MM.YYYY',
			));
	}

	/**
	 * Executes the business logic.
	 * @hostcms-event Skin_Bootstrap_Admin_Form_Entity_DateTime.onBeforeExecute
	 * @hostcms-event Skin_Bootstrap_Admin_Form_Entity_DateTime.onAfterExecute
	 */
	public function execute()
	{
		Core_Event::notify(get_class($this) . '.onBeforeExecute', $this);

		$windowId = $this->_Admin_Form_Controller->getWindowId();

		$this->value = $this->_convertDatetime($this->value);

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

		?><div id="div_<?php echo htmlspecialchars((string) $this->id)?>" class="input-group">
			<input <?php echo implode(' ', $aAttr) ?>/>
			<span class="input-group-addon<?php echo $this->disabled == 'disabled' ? ' disabled'  : ''; ?>">
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

		Core_Event::notify(get_class($this) . '.onAfterExecute', $this);
	}
}