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
class Skin_Bootstrap_Admin_Form_Entity_Buttons extends Skin_Default_Admin_Form_Entity_Buttons {

	/**
	 * Executes the business logic.
	 * @hostcms-event Skin_Bootstrap_Admin_Form_Entity_Buttons.onBeforeExecute
	 * @hostcms-event Skin_Bootstrap_Admin_Form_Entity_Buttons.onAfterExecute
	 */
	public function execute()
	{
		Core_Event::notify(get_class($this) . '.onBeforeExecute', $this);

		parent::execute();

		$windowId = $this->_Admin_Form_Controller->getWindowId();

		?><script>
		$(document).ready(function() {
			var $form = $('#<?php echo Core_Str::escapeJavascriptVariable($windowId)?> form[id ^= "formEdit"]');

			// Указываем таймаут для узлов структуры (подгрузка xsl)
			setTimeout(function() {
				$form.on('keyup change paste', ':input', function(e) { mainFormLocker.lock(e) });
				/*$form.find(':hidden').on('keyup change paste', function(e) { mainFormLocker.lock(e) });*/
				$form.find('input.hasDatetimepicker').parent().on('dp.change', function(e) { mainFormLocker.lock(e) });

				if ($form.data('adminformid') && $form.data('autosave'))
				{
					$form.on('keyup change paste', ':input', function(e) { mainFormAutosave.changed($form, e, '<?php echo $windowId?>') });
					$form.find('input.hasDatetimepicker').parent().on('dp.change', function(e) { mainFormAutosave.changed($form, e, '<?php echo $windowId?>') });
				}

				$form.find('input[type="hidden"]').on('change', function(e) { mainFormLocker.lock(e) });
			}, 5000);

			$('#<?php echo Core_Str::escapeJavascriptVariable($windowId)?> .formButtons :input').on('click', function(e) { mainFormLocker.unlock() });

			$form.on('keyup change paste blur', ':input[data-required]', function(e) { mainFieldChecker.check($(this)) });

			$form.on('keypress paste', 'input:not(.select2-search__field):not(.disable-restore), textarea', function(e) { mainFieldChecker.restoreFieldChange($(this)) });
			$form.on('change', 'select:not(.select2-hidden-accessible):not(.disable-restore):not(.admin-page-selector)', function(e) { mainFieldChecker.restoreFieldChange($(this)) });
			$form.find('input.hasDatetimepicker').parent().on('dp.change', function(e) { mainFieldChecker.restoreFieldChange($(this).find('input.hasDatetimepicker')) });
		});
		</script><?php

		Core_Event::notify(get_class($this) . '.onAfterExecute', $this);
	}
}