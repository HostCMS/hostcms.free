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
class Skin_Bootstrap_Admin_Form_Entity_Buttons extends Skin_Default_Admin_Form_Entity_Buttons {
	/**
	 * Executes the business logic.
	 */
	public function execute()
	{
		parent::execute();

		$windowId = $this->_Admin_Form_Controller->getWindowId();

		?><script>
		$(document).ready(function() {
			var jForm = $('#<?php echo $windowId?> form[id ^= "formEdit"]');

			// Указываем таймаут для узлов структуры (подгрузка xsl)
			setTimeout(function() {
				jForm.on('keyup change paste', ':input', function(e) { mainFormLocker.lock(e) });
				jForm.find('input.hasDatetimepicker').parent().on('dp.change', function(e) { mainFormLocker.lock(e) });
			}, 5000);

			$('#<?php echo $windowId?> .formButtons :input').on('click', function(e) { mainFormLocker.unlock() });

			jForm.on('keyup change paste blur', ':input[data-required]', backendFieldCheck);
		});
		</script><?php
	}
}