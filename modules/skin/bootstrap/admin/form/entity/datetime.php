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
class Skin_Bootstrap_Admin_Form_Entity_DateTime extends Skin_Default_Admin_Form_Entity_DateTime {

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

		$this->value = $this->value == '0000-00-00 00:00:00' || $this->value == ''
			? ''
			: Core_Date::sql2datetime($this->value);

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
			$('#<?php echo $windowId?> #div_<?php echo $this->id?>').datetimepicker({locale: '<?php echo $sCurrentLng?>', format: '<?php echo Core::$mainConfig['dateTimePickerFormat']?>', showTodayButton: true, showClear: true});
		})(jQuery);
		</script><?php

		?></div><?php
	}
}