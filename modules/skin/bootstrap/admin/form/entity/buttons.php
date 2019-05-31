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
class Skin_Bootstrap_Admin_Form_Entity_Buttons extends Skin_Default_Admin_Form_Entity_Buttons {
	/**
	 * Executes the business logic.
	 */
	public function execute()
	{
		parent::execute();
		
		?><script>
			formLocked = false;

			function lockForm(e) {
				if (!formLocked)
				{
					$('body').on('beforeAdminLoad beforeAjaxCallback', function(e) {
						if (!confirm('<?php echo Core::_('Admin_Form.lock_message')?>'))
						{
							return 'break';
						}

						formLocked = false;

						unbindEvents();
					});

					$('h5.row-title').append('<i class="fa fa-lock edit-lock"></i>');

					formLocked = true;
				}
			}

			function unbindEvents()
			{
				$('body')
					.unbind('beforeAdminLoad')
					.unbind('beforeAjaxCallback');

				$('h5.row-title > i.edit-lock').remove();
			}

			$(document).ready(function() {
				// Указываем таймаут для узлов структуры (подгрузка xsl)
				setTimeout(function() {
					$('body').on('afterTinyMceInit', function(event, editor) {
						editor.on('change', lockForm);
					});

					$('#id_content form[id ^= "formEdit"]').on('keyup change paste', ':input', lockForm);
					$('#id_content form[id ^= "formEdit"] input.hasDatetimepicker').parent().on('dp.change', lockForm);

					$('div#ControlElements input').on('click', function(){
						formLocked = false;

						unbindEvents();
					});
				}, 5000);
			});

			$(function (){
				// Sticky actions
				$('#ControlElements').addClass('sticky-actions');

				$(document).on("scroll", function () {
					// to bottom
					if ($(window).scrollTop() + $(window).height() == $(document).height()) {
						$('#ControlElements').removeClass('sticky-actions');
					}

					// to top
					if ($(window).scrollTop() + $(window).height() < $(document).height()) {
						$('#ControlElements').addClass('sticky-actions');
					}
				});
			});
		</script><?php
	}
}