<?php

/**
 * Admin forms.
 *
 * @package HostCMS
 * @subpackage Admin
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
return array(
	'save' => 'Save',
	'apply' => 'Apply',
	'noSubject' => 'No subject',
	'actions' => 'Actions',

	'add' => 'Add',

	'yes' => 'Yes',
	'no' => 'No',
	'enabled' => 'Enabled',
	'disabled' => 'Disabled',
	'enable' => 'Enable',
	'disable' => 'Disable',
	'asc' => 'Ascending',
	'desc' => 'Descending',
	'not-installed' => 'Not installed',
	'buy' => 'Buy',
	'filter' => 'Filter',

	'model_name' => 'Administration center forms',
	'menu' => 'Administration center forms',
	'form_forms_tab_1' => 'Main',
	'form_forms_tab_2' => 'Parameters',

	'show_form_fields_menu_admin_forms' => 'Forms',

	'show_forms_title' => 'Administration center forms',
	'confirm_dialog' => 'Are you sure you would like to %s?',

	'button_execute' => 'Execute',
	'button_to_filter' => 'Filter',
	'button_to_clear' => 'Clear filter',

	// Форма редактирования форм центра администрирования.
	'form_add_forms_title' => 'Add an administration center form',
	'form_edit_forms_title' => 'Edit the administration center form "%s"',

	'admin_form_tab_0' => 'Name',
	'form_forms_lng_name' => '<acronym title="Administration center form name">Form name</acronym>',
	'form_forms_lng_description' => '<acronym title="Administration center form description">Form description</acronym>',
	'on_page' => '<acronym title="Number of elements per page">Number of elements per page</acronym>',
	'key_field' => '<acronym title="Key field name">Key field name</acronym>',
	'show_operations' => '<acronym title="Display buttons for operation performance">Display actions</acronym>',
	'show_group_operations' => '<acronym title="Display the list of group operations">Group operations</acronym>',
	'default_order_field' => '<acronym title="Default sorting field name">Sorting field name</acronym>',
	'default_order_direction' => '<acronym title="Sorting direction">Sorting direction</acronym>',
	'id' => 'Id',
	'guid' => 'GUID',

	'apply_success' => 'Form information modified successfully!',
	'apply_error' => 'Error! Form information not modified!',

	'markDeleted_success' => 'Form deleted successfully!',
	'markDeleted_error' => 'Error! Form has not been deleted!',

	// -----------------------------------------

	'edit_success' => 'Form information updated successfully!',
	'edit_error' => 'Form information has not been updated!',

	'show_form_menu_admin_forms_top1' => 'Form',
	'show_form_menu_admin_forms_sub_add' => 'Add',

	'show_form_menu_admin_forms_top2' => 'Language',
	'show_form_menu_admin_forms_top2_sub_add' => 'Languages',
	'show_form_menu_admin_forms_add' => 'Add',

	// Подписи к размерам загружаемых изображений.
	'window_large_image' => 'Large picture',
	'window_small_image' => 'Small picture',

	'large_image_max_width' => '<acronym title="Maximum picture width after its narrowing">Maximum picture width</acronym>',
	'large_image_max_height' => '<acronym title="Maximum picture height after its narrowing">Maximum picture height</acronym>',
	'small_image_max_width' => '<acronym title="Maximum width of a small picture after narrowing the large one">Maximum width of small picture</acronym>',
	'small_image_max_height' => '<acronym title="Maximum height of a small picture after narrowing the large one">Maximum height of small picture</acronym>',

	'small_image' => "<acronym title=\"Small picture.\">Small picture</acronym>",

	'information_items_add_form_image_watermark_is_use' => '<acronym title="Watermark specified for the information system is laid on the picture to identify the uploaded picture with your website">Lay the watermark on the picture</acronym>',

	'image_preserve_aspect_ratio' => '<acronym title="Preserve aspect ratio">Preserve aspect ratio</acronym>',
	'create_thumbnail' => '<acronym title="Create small thumbnail versions of images">Create small thumbnail versions of images</acronym>',

	'msg_file_view' => 'View',
	'msg_file_settings' => 'File settings',

	'msg_information_delete' => 'Are you sure you would like to delete?',
	'msg_information_alt_delete' => 'Delete',

	'watermark_position_x' => '<acronym title="Property of watermark position in X-direction (in pixels or per cents)">X-direction</acronym>',

	'watermark_position_y' => '<acronym title="Property of watermark position in Y-direction (in pixels or per cents)">Y-direction</acronym>',

	'msg_error_access' => 'You have not enough rights to perform this action or action does not exist.',

	'input_clear_filter' => 'Clear',

	'input_case_sensitive' => 'Case Sensitive',

	'filter_selected_all' => 'All',
	'filter_selected' => 'Selected',
	'filter_not_selected' => 'Not selected',

	'export_csv' => 'Export into CSV',
	'file_description' => 'File description',

	'delete_success' => 'Item deleted successfully!',
	'undelete_success' => 'Item restored successfully!',
	
	'note' => 'Note',

	'note-license' => 'License number <a href="/admin/site/index.php?hostcms[action]=accountInfo&hostcms[operation]=&hostcms[current]=1&hostcms[checked][0][0]=1" onclick="$.adminLoad( { path: \'/admin/site/index.php\',action: \'accountInfo\', current: \'1\', additionalParams: \'hostcms[checked][0][0]=1\' } ); return false">is not entered</a>, Content Management System works in a restricted mode.',
	'note-bad-password' => 'User with standard login and password exists in the system, please change the password!',
);