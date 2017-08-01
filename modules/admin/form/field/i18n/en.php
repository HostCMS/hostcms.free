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
	'model_name' => 'Administration center form fields',
	'form_forms_field_lng_name' => '<acronym title="Administration center form field name">Field name</acronym>',
	'form_forms_field_lng_description' => '<acronym title="Administration center form field description">Field 	description</acronym>',

	// Форма редактирования полей формы центра администрирования.
	'form_add_forms_field_title' => 'Add administration center form field',
	'form_edit_forms_field_title' => 'Edit administration center form field "%s"',

	'show_form_fields_menu_add_new_top' => 'Form field',

	'show_form_fields_menu_add_new' => 'Add',


	'admin_form_tab_0' => 'Name',
	'admin_form_tab_3' => 'View',
	'name' => '<acronym title="Key field">Key field</acronym>',
	'sorting' => '<acronym title="Field sort order">Sort order</acronym>',
	'type' => '<acronym title="Field type (input field, drop-down list, checkbox etc.)">Field type</acronym>',
	'view' => 'View',
	'format' => '<acronym title="Data display format line. Format line consists of commands: general characters (except for %) that are copied to the resulting line and transformation descriptors each of which is substituted by one of the parameters">Display format</acronym>',
	'allow_sorting' => '<acronym title="Enable alphabetical sorting">Enable sorting</acronym>',
	'allow_filter' => '<acronym title="Enable field values filter">Enable filter</acronym>',
	'editable' => '<acronym title="Allow edit field in place">Allow edit in place</acronym>',
	'width' => '<acronym title="Field width in pixels, per cents etc. e.g. 45px/">Field width</acronym>',
	'ico' => '<acronym title="Field\'s ico, e.g. &quot;fa fa-comment&quot;">Icon class</acronym>',
	'class' => '<acronym title="CSS class">CSS class</acronym>',
	'attributes' => '<acronym title="Field attributes list">Attributes</acronym>',
	'image' => '<acronym title="Correspondence of pictures and field values that is set as <Field value>=<Path to picture>">Correspondence of pictures and field values</acronym>',
	'link' => '<acronym title="Link with substitution">Link</acronym>',
	'onclick' => '<acronym title="Actions performed in event of onclick">Onclick</acronym>',
	'list' => '<acronym title="Correspondence of values and list elements that is set as <Field value>=<List element>">Correspondence of values and list elements</acronym>',

	'admin_form_id' => 'Admin form Id',
	'id' => 'Id',

	'edit_success' => 'Form field information added successfully!',
	'edit_error' => 'Error! Form field information has not been added!',

	'form_fields_menu_admin_form_fields' => 'Form fields list "%s"',

	// Тип поля.
	'field_type_text' => 'Text',
	'field_type_text_as_is' => 'Text "AS IS"',
	'field_type_input' => 'Input field',
	'field_type_checkbox' => 'Checkbox',
	'field_type_link' => 'Link',
	'field_type_date_time' => 'Date-time',
	'field_type_date' => 'Date',
	'field_type_image_link' => 'Picture-link',
	'field_type_image_list' => 'List',
	'field_type_image_callback_function' => 'Calculated field (Function callback is used)',
	
	// Отображение	
	'field_view_column' => 'Column',
	'field_view_filter_element' => 'Filter',

	// Список полей.
	'show_form_fields_title' => 'Administration center form fields "%s"',
	'apply_success' => 'Field data updated successfully.',
	'apply_error' => 'Error! Field data not modified!',

	'markDeleted_success' => 'Form field deleted successfully!',
	'markDeleted_error' => 'Error! Form field has not been deleted!',

	'copy_success' => 'Form field copied successfully!',
	'copy_error' => 'Error! Form field has not been copied!',

	'filter_type' => 'Kind of filter',
	'filter_where' => 'WHERE',
	'filter_having' => 'HAVING',
	'delete_success' => 'Item deleted successfully!',
	'undelete_success' => 'Item restored successfully!',
);