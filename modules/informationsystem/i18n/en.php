<?php

/**
 * Information systems.
 *
 * @package HostCMS
 * @subpackage Informationsystem
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
 */
return array(
	'model_name' => 'Information systems',
	'information_systems_add_form_link' => 'Information systems',
	'menu' => 'Information systems',
	'show_comments_link3' => 'Information system',
	'show_information_systems_link' => 'Add',
	'add_title' => 'Add information system',
	'edit_title' => 'Edit information system',

	'information_systems_form_tab_2' => 'Sorting',
	'information_systems_form_tab_3' => 'Formats',
	'information_systems_form_tab_4' => 'Image',

	'id' => 'ID',
	'information_systems_dirs_add_form_group' => 'Section',

	'name' => 'Name of information system',
	'description' => 'Description of information system',

	'site_name' => 'Website',

	'information_systems_add_form_order_field' => 'Items sorting field',
	'information_date' => 'Date',
	'show_information_groups_name' => 'Name',
	'show_information_propertys_order' => 'Sorting order',

	'recount_success' => 'Count of items and groups recalculated successfully',
	'copy_success' => 'Information system copied successfully!',

	'information_systems_add_form_order_type' => 'Items sorting direction',
	'sort_to_increase' => 'Ascending',
	'sort_to_decrease' => 'Descending',

	'is_sort_field_group_title' => 'Groups sorting field',
	'is_sort_order_group_type' => 'Groups sorting direction',

	'format_date' => '<acronym title="Date display format, e.g. %d.%m.%Y">Date format</acronym>',
	'format_datetime' => '<acronym title="Date/time display format, e.g. %d.%m.%Y %H:%M:%S">Date/time format</acronym>',

	// Картинки инфосистемы
	'image_large_max_width' => 'Maximum width of large picture',
	'image_large_max_height' => 'Maximum height of large picture',
	'image_small_max_width' => 'Maximum width of small picture',
	'image_small_max_height' => 'Maximum height of small picture',

	'group_image_large_max_width' => 'Maximum width of large picture for group',
	'group_image_large_max_height' => 'Maximum height of large picture for group',
	'group_image_small_max_width' => 'Maximum width of small picture for group',
	'group_image_small_max_height' => 'Maximum height of small picture for group',

	'use_captcha' => 'Use CAPTCHA',
	'typograph_default_items' => 'Use prepress service to items',
	'typograph_default_groups' => 'Use prepress service to groups',

	'siteuser_group_id' => 'Customer Group',
	'information_all' => 'All',

	'structure_name' => 'Structure node',

	/* водяной знак */
	'watermark_file' => 'Watermark picture',
	'watermark_default_use_large_image' => 'Use watermark by default',
	'watermark_default_use_small_image' => 'Use watermark for small pictures by default',
	'watermark_default_position_x' => '<acronym title="Property to specify the dafault watermark position in X-direction, e.g. 200 (in pixels) or 50% (in percentage)">Default position in X-direction</acronym>',
	'watermark_default_position_y' => '<acronym title="Property to specify the default watermark position in Y-direction, e.g. 200 (in pixels) or 50% (in percentage)">Default position in Y-direction</acronym>',
	'preserve_aspect_ratio' => 'Preserve aspect ratio',
	'preserve_aspect_ratio_small' => 'Preserve aspect ratio',
	'preserve_aspect_ratio_group' => 'Preserve aspect ratio for group',
	'preserve_aspect_ratio_group_small' => 'Preserve aspect ratio for group',
	'items_on_page' => 'Number of items on page',
	'apply_tags_automatically' => '<acronym title="Automatic generation of labels (tags) of information item from its name, description and text">Apply labels (tags) automatically</acronym>',
	'change_filename' => '<acronym title="Conversion of all uploaded files for all information system objects: items, groups, additional properties of items and groups">Edit names of uploaded files</acronym>',

	'url_type' => 'Type of generation of URL',
	'url_type_identificater' => 'ID',
	'url_type_transliteration' => 'Transliteration',
	'apply_keywords_automatically' => '<acronym title="Automatic generation of keywords of information item and category from their name, description and text">Generate keywords automatically</acronym>',

	'information_items_add_form_access' => 'Access group',

	'information_items_add_form_show_count' => 'Displaying rate',

	'information_items_users_id' => 'User code',
	'edit_success' => 'Information system saved successfully.',

	'markDeleted_success' => 'Information system deleted successfully!',
	'markDeleted_error' => 'Information system has not been deleted!',

	'comments_title' => 'Comments for information systems',
	'show_comments_system_title' => 'Comments for information system "%s"',

	'comment_mail_subject' => 'Add comment/reply to website',
	'delete_success' => 'Item deleted successfully!',
	'undelete_success' => 'Item restored successfully!',

	'widget_title' => 'Comments',
	'widget_other_comments' => 'Other comments',
	'date' => 'Date',
	'subject' => 'Subject',
	'subject_not_found' =>'Subject not found',
	'tag' => 'Tag: %s',
	'deleteEmptyDirs_success' => 'Empty dirs have been deleted!',

	'structureIsExist' => 'Informationsystem linked with the same structure "%s" has already exist!',

	'schedule-searchIndexItem' => 'Index informationsystem item',
	'schedule-searchIndexGroup' => 'Index informationsystem group',
	'schedule-searchUnindexItem' => 'Unindex informationsystem item',
	'schedule-recountInformationsystem' => 'Recount informationsystem groups and items',

	'tab_seo_templates' => 'SEO templates',
	'seo_group_header' => 'Group templates',
	'seo_item_header' => 'Item templates',
	'seo_root_header' => 'Root page templates',
	'seo_group_title_template' => 'TITLE template',
	'seo_group_description_template' => 'DESCRIPTION template',
	'seo_group_keywords_template' => 'KEYWORDS template',
	'seo_item_title_template' => 'TITLE template',
	'seo_item_description_template' => 'DESCRIPTION template',
	'seo_item_keywords_template' => 'KEYWORDS template',
	'seo_root_title_template' => 'TITLE template',
	'seo_root_description_template' => 'DESCRIPTION template',
	'seo_root_keywords_template' => 'KEYWORDS template',

	'seo_template_informationsystem' => 'Informationsystem',
	'seo_template_informationsystem_name' => 'Informationsystem name',
	'seo_template_group' => 'Group',
	'seo_template_group_name' => 'Group Name',
	'seo_template_group_description' => 'Group Description',
	'seo_template_group_path' => 'Group Path',
	'seo_template_group_page_number' => 'Page Number',
	'seo_template_group_page' => 'page',
	'seo_template_item' => 'Item',
	'seo_template_item_name' => 'Item Name',
	'seo_template_item_description' => 'Item Description',
	'seo_template_item_text' => 'Item Text',
	'seo_template_property_value' => 'Property Value',

	'all_groups_count' => 'Groups: %s',
	'all_items_count' => 'Items: %s',

	'create_small_image' => 'Create small image from large image',

	'option_smallImagePrefix' => 'Prefix for small images',
	'option_itemLargeImage' => 'Item large image pattern',
	'option_itemSmallImage' => 'Item small image pattern',
	'option_groupLargeImage' => 'Group large image pattern',
	'option_groupSmallImage' => 'Group small image pattern',

	'seo_group_h1_template' => 'H1 Template',
	'seo_item_h1_template' => 'H1 Template',
	'seo_root_h1_template' => 'H1 Template',

	'searchIndexItem' => 'Item ID',
	'searchIndexGroup' => 'Group ID',
	'searchUnindexItem' => 'Item ID',
	'recountInformationsystem' => 'Informationsystem ID',

	'all_shortcuts_count' => 'Total shortcuts: %s',

	'url_type_date' => 'Date',
	'path_date_format' => 'Path format of type "Date"',

	'watermark_item_header' => 'Item',
	'watermark_group_header' => 'Group',
);