<?php
/**
 * Information systems.
 *
 * @package HostCMS
 * @subpackage Informationsystem
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
return array(
	'model_name' => 'Information items',
	'show_information_groups_title' => 'Information system "%s"',
	'information_system_top_menu_items' => 'Information item',
	'show_information_groups_link2' => 'Add',
	'show_information_groups_link3' => 'Properties',
	'show_all_comments_top_menu' => 'Comments',
	'show_comments_link_show_all_comments' => 'All comments',

	'information_items_add_form_title' => 'Add information item',
	'information_items_edit_form_title' => 'Edit information item',

	'markDeleted' => 'Delete information item',

	'id' => 'Id',
	'informationsystem_id' => 'Informationsystem id',
	'shortcut_id' => "Parent item's id",

	'name' => '<acronym title="Name of information item">Name of information item</acronym>',
	'informationsystem_group_id' => '<acronym title="Group that information item belongs to">Group</acronym>',
	'datetime' => '<acronym title="Date of adding/editing of information item">Date</acronym>',
	'start_datetime' => '<acronym title="Date of publishing of information item">Publishing date</acronym>',
	'end_datetime' => '<acronym title="Completion date of publishing of information item">Completion date of publishing</acronym>',
	'description' => '<acronym title="Description of information item">Description of information item</acronym>',
	'exec_typograph_description' => '<acronym title="Use prepress service to description">Use prepress service to description</acronym>',
	'use_trailing_punctuation' => '<acronym title="Optical text alignment function moves punctuation characters beyond the typing borders">Optical alignment</acronym>',

	'active' => '<acronym title="Status of information item">Active</acronym>',
	'sorting' => '<acronym title="Sorting order of information item">Sorting order</acronym>',
	'ip' => '<acronym title="IP-address of PC of sender of information item, e.g. XXX.XXX.XXX.XXX, with XXX being a number in the range from 0 to 255">IP-address</acronym>',
	'showed' => '<acronym title="Displaying rate of information item">Displaying rate</acronym>',
	'siteuser_id' => '<acronym title="Website user ID that created information item">User code</acronym>',
	'image_large' => '<acronym title="Large picture for information item">Large picture</acronym>',
	'image_small' => '<acronym title="Small picture for information item">Small picture</acronym>',
	'path' => '<acronym title="Name of item in URL">Name of item in URL</acronym>',
	'maillist' => '<acronym title="Information system item can be added as a subscribe issue">Place in subscription</acronym>',
	'maillist_default_value' => '-- Not send --',

	'siteuser_group_id' => '<acronym title="Group having access rights to information item">Access group</acronym>',

	'indexing' => '<acronym title="Checkbox to specify whether information system item should be indexed">Index</acronym>',
	'text' => '<acronym title="Text of information item">Text</acronym>',

	'exec_typograph_for_text' => '<acronym title="Use prepress service to text">Use prepress service to text</acronym>',
	'use_trailing_punctuation_for_text' => '<acronym title="Optical text alignment function moves punctuation characters beyond the typing borders">Optical alignment</acronym>',

	'tab_1' => 'Description',
	'tab_2' => 'SEO',
	'tab_3' => 'Tags',
	'tab_4' => 'Additional properties',

	'seo_title' => '<acronym title="Meta element title">Title</acronym>',
	'seo_description' => '<acronym title="Meta element description">Description</acronym>',
	'seo_keywords' => '<acronym title="Meta element keywords">Keywords</acronym>',
	'tags' => '<acronym title="Labels (tags) of information item divided by comma, e.g. processors, AMD, Athlon64">Labels (tags)</acronym>',
	'type_tag' => 'Type tag ...',

	'error_information_group_URL_item' => 'Group already contains information item with this name in URL!',
	'error_information_group_URL_item_URL' => 'Group contains subgroup with URL coinciding with the item name in URL!',

	'edit_success' => 'Information item modified successfully!',
	'apply_success' => 'Information modified successfully.',
	'copy_success' => 'Information item copied successfully!',

	'changeActive_success' => 'Status changed successfully!',
	'changeIndexation_success' => 'Information item modified successfully.',
	'move_items_groups_title' => 'Transfer of groups and items',
	'move_items_groups_information_groups_id' => '<acronym title="Group into which items and groups will be transferred.">Parent group</acronym>',

	'add_information_item_shortcut_title' => 'Create shortcut',
	'add_item_shortcut_information_groups_id' => '<acronym title="Group where the shortcut of the information item is disposed">Parent group</acronym>',
	'shortcut_success' => 'Shortcut creat successfully.',
	'markDeleted_success' => 'Information item deleted successfully.',
	'markDeleted_error' => 'Information item has not been deleted!',

	'move_success' => 'Information items have been transferred.',

	'show_comments_title' => 'Comments to information item "%s"',
	'shortcut_success' => "Product shortcut added successfully",
	'show_information_propertys_title' => 'Additional properties of information system items "%s"',
	'delete_success' => 'Item deleted successfully!',
	'undelete_success' => 'Item restored successfully!',
	'root' => 'Root dir',
	'shortcut_group_tags' => "<acronym title=\"Another groups with shortcuts\">Additional groups</acronym>",
	'select_group' => 'Select a group',
	'export' => 'Export',
	'export_list_separator' => "<acronym title=\"Columns separation character\">Separation character</acronym>",
	'export_list_separator1' => "Comma",
	'export_list_separator2' => "Semicolon",
	'export_encoding' => "Encoding",
	'input_file_encoding0' => 'Windows-1251',
	'input_file_encoding1' => 'UTF-8',
	'export_parent_group' => "<acronym title=\"Parent group for information system items\">Parent group</acronym>",
	'export_external_properties_allow_items' => "Export additional properties of information system items",
	'export_external_properties_allow_groups' => "Export additional properties of groups",
	'tab_export' => 'Export/Import',
	'guid' => '<acronym title="Item identifier, e.g. ID00029527">GUID</acronym>',
	'import_small_images' => "Small image for ",
	'import' => "Import",
	'import_list_file' => "<acronym title=\"Choose file to upload\">Choose file to upload</acronym>",
	'alternative_file_pointer_form_import' => "<acronym title=\"Set file path on server, e.g., tmp/myfile.csv\">or set file path on server</acronym>",
	'import_list_name_field_f' => "<acronym title=\"Checkbox to determine whether the first line contains field names\">First line contains field names</acronym>",
	'import_separator' => "<acronym title=\"Columns separation character\">Separation character</acronym>",
	'import_separator1' => "Comma",
	'import_separator2' => "Semicolon",
	'import_separator3' => "Tab",
	'import_separator4' => 'Other',
	'import_stop' => "<acronym title=\"Field mark\">Mark</acronym>",
	'import_stop1' => "Quotations",
	'import_stop2' => 'Other',
	'import_encoding' => "Encoding",
	'import_parent_group' => "<acronym title=\"Parent group for information system items\">Parent group</acronym>",
	'import_images_path' => "<acronym title=\"Path for external files, e.g. /upload_images/\">Path for external files</acronym>",
	'import_action_items' => "<acronym title=\"Action for existing items\">Action for existing items</acronym>",
	'import_action_items0' => "Delete existing items in all groups",
	'import_action_items1' => "Update existing items",
	'import_action_items2' => "Nothing",
	'import_action_delete_image' => "<acronym title=\"Activation of this checkbox enables you to delete images for items in case these images are empty or have not been transferred\">Delete images when updating</acronym>",
	'search_event_indexation_import' => "Use event-based indexing groups and items",
	'import_max_time' => "<acronym title=\"Maximum execution time (in seconds)\">Maximum execution time</acronym>",
	'import_max_count' => "<acronym title=\"Maximum items imported per step\">Import per step</acronym>",
	'import_button_load' => "Upload",
	'root_folder' => 'Root folder',
	'count_insert_item' => 'Items uploaded',
	'count_update_item' => 'Items updated',
	'create_catalog' => 'Catalogue sections created',
	'update_catalog' => 'Catalogue sections updated',
	'msg_download_complete' => "Import has finished!",
	'information_items_copy_form_title' => 'Copy item',	
);