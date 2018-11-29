<?php
/**
 * Online shop.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
return array(
	'model_name' => 'Products',
	'links_items' => 'Product',
	'links_items_add' => 'Add',
	'name' => "Product Name",
	'type' => "<acronym title=\"Select product type (conventional or digital)\">Product type</acronym>",
	'marking' => "Product Marking",
	'vendorcode' => "<acronym title=\"Product code placed in 'vendorCode' element when exporting to Yandex.Market\">Vendor Code</acronym>",
	'description' => "Description",
	'text' => "Text",
	'image_large' => "Product image",
	'image_small' => "Small product image",
	'weight' => "Weight",
	'price_header' => "Prices",
	'price' => "Main price",
	'active' => "Active product",
	'sorting' => 'Sort',
	'path' => "<acronym title=\"Path, e.g. item_30312\">Path</acronym>",
	'seo_title' => 'Title',
	'seo_description' => 'Description',
	'seo_keywords' => 'Keywords',
	'indexing' => "Index product",
	'yandex_market' => 'Export to Yandex.Market',
	'yandex_market_bid' => '<acronym title="Base charge for Yandex.Market system (in cents)">Yandex.Market - base charge</acronym>',
	'yandex_market_cid' => '<acronym title="Charge for model cards of Yandex.Market system (in cents)">Yandex.Market - charge for model cards</acronym>',
	'yandex_market_sales_notes' => 'Difference between product and other products (value of tag &lt;sales_notes&gt;)',
	'datetime' => 'Date',
	'guid' => '<acronym title="Product identifier for CommerceML format, e.g. ID00029527">CommerceML product identifier</acronym>',
	'start_datetime' => 'Publication date',
	'end_datetime' => 'Completion date of publication',
	'showed' => 'Showed',
	'id' => 'ID',
	'tab_description' => 'Description',
	'tab_export' => 'Export/Import',
	'tab_seo' => 'SEO',
	'tab_associated' => 'Associated',
	'tab_prop' => "Additional properties",
	'shop_group_id' => 'Group',
	'item_type_selection_group_buttons_name_simple' => "Conventional",
	'item_type_selection_group_buttons_name_electronic' => "Digital",
	'item_type_selection_group_buttons_name_divisible' => "Divisible",
	'item_type_selection_group_buttons_name_set' => "Product Set",
	'shop_item_catalog_modification_flag' => "Modification for product",
	'shop_seller_id' => "Seller",
	'shop_producer_id' => "Producer",
	'shop_tax_id' => 'Tax',
	'shop_measure_id' => "Measurement unit",
	'property_prefix' => 'Prefix',
	'property_filter' => 'Display mode of property in filter',

	'properties_show_kind_none' => 'Not display',
	'properties_show_kind_text' => 'Input field',
	'properties_show_kind_list' => 'List by lists',
	'properties_show_kind_radio' => 'List by radio buttons',
	'properties_show_kind_checkbox' => 'List by checkboxes',
	'properties_show_kind_checkbox_one' => 'Checkbox',
	'properties_show_kind_from_to' => 'From.. to..',
	'properties_show_kind_listbox' => 'List by list of multiple choice',

	'warehouse_header' => "Quantity of products in stocks",
	'property_header' => "Shop properties",
	'yandex_market_header' => "Yandex.Market export",
	//'warehouse_item_count' => "Quantity of products in stock \"%s\"",
	'siteuser_group_id' => 'Access group',
	'shop_users_group_parrent' => 'Like parent',
	'siteuser_id' => 'Website user',
	'exec_typograph_for_text' => 'Use prepress service to text',
	'use_trailing_punctuation_for_text' => '<acronym title="Optical text alignment function moves punctuation characters beyond the typing borders">Optical alignment</acronym>',
	'shop_id' => 'Shop id',
	'form_edit_add_shop_special_prices_from' => '<acronym title="Minimum products amount that user should buy at a time to activate price">Products amount from</acronym>',
	'form_edit_add_shop_special_prices_to' => '<acronym title="Maximum products amount that user should buy at a time to activate price">Products amount to</acronym>',
	'form_edit_add_shop_special_pricess_price' => '<acronym title="Price for product unit bought in a particular amount">Price</acronym>',
	'form_edit_add_shop_special_pricess_percent' => '<acronym title="Per cent of base price. E.g., for a discount of 15% a per cent of base price amounts to 85">% of price</acronym>',
	'or' => 'or',
	'more' => 'More …',
	'items_catalog_image' => "Product image",
	'items_catalog_image_small' => "Small product image",
	'items_catalog_tags' => "<acronym title=\"Product labels (tags) divided by a comma, e.g. kitchen, domestic machines, fridge, Indesit\">Labels (tags)</acronym>",
	'type_tag' => 'Type tag ...',
	'items_catalog_add_form_title' => "Add product information",
	'items_catalog_edit_form_title' => "Edit product information",
	'changeActive_success' => 'Status changed successfully!',
	'apply_success' => "Information has been successfully changed",
	'adminChangeAssociated_success' => "Information has been successfully changed",
	'adminSetAssociated_success' => "Information has been successfully changed",
	'adminUnsetAssociated_success' => "Information has been successfully changed",
	'markDeleted_success' => "Product information deleted successfully!",
	'shortcut_success' => "Product shortcut added successfully",
	'edit_success' => "Product information added successfully!",
	'copy_success' => "Product copied successfully!",
	'shops_add_form_link_properties' => "Properties",
	'show_list_of_properties_title' => "Properties list of online store product \"%s\"",
	'tab_properties' => "Additional properties",
	'items_catalog_add_form_comment_link' => 'Comments',
	'properties_item_for_groups_link' => 'Properties for group',
	'properties_item_for_groups_root_title' => 'Product properties accessible for the current product group',
	'change_prices_for_shop_group' => 'Price change',
	'import_price_list_link' => "Import",
	'export_shop' => "Export",
	'shops_link_orders' => "Orders",
	'shops_add_form_link_orders' => "Orders",
	'show_delivery_on' => "Delivery",
	'show_type_of_delivery_link' => "Deliveries",
	'show_sds_link' => "References",
	'show_prices_title' => "Prices",
	'system_of_pays' => "Payment systems",
	'show_producers_link' => 'Producers',
	'show_sellers_link' => 'Sellers',
	'main_menu_warehouses_list' => "Warehouses",
	'show_reports_title' => 'Reports',
	'show_sales_order_link' => 'Sales report',
	'show_brands_order_link' => 'Producers report',
	'shop_menu_title' => "Discounts",
	'show_discount_link' => 'Product discounts',
	'order_discount_show_title' => 'Order discounts',
	'coupon_group_link' => 'Coupons',
	'bonus_link' => 'Bonuses',
	'affiliate_menu_title' => 'Affiliate program',
	'add_item_shortcut_shop_groups_id' => "<acronym title=\"Group to which the product shortcut belongs\">Parent group</acronym>",
	'add_shop_item_shortcut_title' => "Shortcut for %s",
	'shortcut_creation_window_caption' => "Create shortcut",
	'show_item_comment_title' => "List of comments to product \"%s\"",
	'show_comments_title' => 'Comments to item "%s"',
	'show_tying_products_title' => "Associated products of product \"%s\"",
	'item_modification_title' => 'Modification of product "%s"',
	'item_modification_add_item' => 'Add',
	'show_groups_modification' => 'Modification',
	'import_price_list_file_type1' => "CSV",
	'import_price_list_file_type1_items' => "CSV items",
	'import_price_list_file_type1_orders' => "CSV orders",
	'import_price_list_file_type2' => "CommerceML",
	'export_file_type' => "Select file type",
	'import_price_list_file' => "Choose file to upload",
	'alternative_file_pointer_form_import' => "<acronym title=\"Set file path on server, e.g., tmp/myfile.csv\">or set file path on server</acronym>",
	'import_price_list_name_field_f' => "<acronym title=\"Checkbox to determine whether the first line contains field names\">First line contains field names</acronym>",
	'import_price_list_separator1' => "Comma",
	'import_price_list_separator2' => "Semicolon",
	'import_price_list_separator3' => "Tab",
	'import_price_list_separator4' => 'Other',
	'import_price_list_separator' => "Separation character",
	'import_price_list_stop' => "Mark",
	'import_price_list_stop1' => "Quotations",
	'import_price_list_stop2' => 'Other',
	'delete_success' => 'Item deleted successfully!',
	'undelete_success' => 'Item restored successfully!',
	'price_list_encoding' => "Encoding",
	'input_file_encoding0' => 'Windows-1251',
	'input_file_encoding1' => 'UTF-8',
	'import_price_list_parent_group' => "Parent group",
	'import_price_list_producer' => "Producer",
	'import_price_list_images_path' => "<acronym title=\"Path for external files, e.g. /upload_images/\">Path for external files</acronym>",
	'import_price_list_action_items' => "<acronym title=\"Action for existing products\">Action for existing products</acronym>",
	'import_price_action_items0' => "Delete existing products in all groups",
	'import_price_action_items1' => "Update existing products",
	'import_price_action_items2' => "Nothing",
	'import_price_list_action_delete_image' => "<acronym title=\"Activation of this checkbox enables you to delete images for product items in case these images are empty or have not been transferred\">Delete products images when updating</acronym>",
	'search_event_indexation_import' => "Use event-based indexing groups and products",
	'import_price_list_max_time' => "<acronym title=\"Maximum execution time (in seconds)\">Maximum execution time</acronym>",
	'import_price_list_max_count' => "<acronym title=\"Maximum products imported per step\">Import per step</acronym>",
	'import_price_list_button_load' => "Upload",
	'move_success' => 'Pruducts transferred successfully',
	'root_folder' => 'Root folder',
	'import_small_images' => "Small image for %s",
	'import_file_description' => "Description for %s",
	'count_insert_item' => 'Products uploaded',
	'count_update_item' => 'Products updated',
	'create_catalog' => 'Catalogue sections created',
	'update_catalog' => 'Catalogue sections updated',
	'msg_download_price' => "The next price-list uploading step will be in 1 second",
	'msg_download_price_complete' => "Import has finished!",
	'export_price_list_file_type2' => "CommerceML v. 1.xx",
	'export_price_list_file_type3_import' => "CommerceML v. 2.0x (import.xml)",
	'export_price_list_file_type3_offers' => "CommerceML v. 2.0x (offers.xml)",
	'multiply_price_to_digit' => 'Multiply price by ',
	'add_price_to_digit' => 'Increase price by ',
	'select_price_form' => '<acronym title="Select change option">Change option: </acronym>',
	'select_discount_type' => '<acronym title="Select a discount to be set for the selected products group">Set discount</acronym>',
	'flag_delete_discount' => '<acronym title="If this checkbox is activated, the discount will be deleted in case it has been granted">Delete selected discount</acronym>',
	'select_bonus_type' => '<acronym title="Select a bonus to be set for the selected products group">Set bonus</acronym>',
	'flag_delete_bonus' => '<acronym title="If this checkbox is activated, the bonus will be deleted in case it has been granted">Delete selected bonus</acronym>',
	'flag_include_modifications' => '<acronym title="If this checkbox is activated, the modifications also be included into itrem list">Include modifications</acronym>',
	'flag_include_spec_prices' => '<acronym title="If this checkbox is activated, special prices will be affected">Apply for special prices</acronym>',
	'select_parent_group' => '<acronym title="Select product group from which prices should start to be changed">Parent group</acronym>',
	'form_sales_order_select_grouping' => '<acronym title="Specifies order grouping period in report">Group:</acronym>',
	'form_sales_order_grouping_monthly' => 'monthly',
	'form_sales_order_grouping_weekly' => 'weekly',
	'form_sales_order_grouping_daily' => 'daily',
	'form_sales_order_show_list_items' => '<acronym title="Displays products list for each order">Display products for order</acronym>',
	'form_sales_order_begin_date' => '<acronym title="Starting date of reporting period">Starting date</acronym>',
	'form_sales_order_end_date' => '<acronym title="Ending date of reporting period">Ending date</acronym>',
	'form_sales_order_show_paid_items' => '<acronym title="Displays a product list of paid orders only">Paid only</acronym>',
	'form_sales_order_sallers' => '<acronym title="Restriction of products ordered according to seller">Seller:</acronym>',
	'form_sales_order_sop' => '<acronym title="Order\'s filter by payment system">Payment system:</acronym>',
	'form_sales_order_status' => '<acronym title="Filter off ordered products by order status">Order status:</acronym>',
	'sales_report_title' => "Sales report of %s for period %s - %s",
	'sales_report_brands_title' => "Producers report of %s for period %s - %s",
	'form_sales_order_count_orders' => 'Orders',
	'form_sales_order_count_items' => 'Products amount',
	'form_sales_order_total_summ' => 'Order amount',
	'form_sales_order_status_of_pay' => 'Paid',
	'form_sales_order_order_status' => 'Order status',
	'form_sales_order_month_january' => 'January',
	'form_sales_order_month_february' => 'February',
	'form_sales_order_month_march' => 'March',
	'form_sales_order_month_april' => 'April',
	'form_sales_order_month_may' => 'May',
	'form_sales_order_month_june' => 'June',
	'form_sales_order_month_july' => 'July',
	'form_sales_order_month_august' => 'August',
	'form_sales_order_month_september' => 'September',
	'form_sales_order_month_october' => 'October',
	'form_sales_order_month_november' => 'November',
	'form_sales_order_month_december' => 'December',
	'form_sales_order_week' => ' week,<br />',
	'form_sales_order_empty_orders' => 'There aren\'t paid orders in the specified period.',
	'form_sales_order_orders_number' => 'Order No. <b>%s</b> of <b>%s</b>',
	'form_sales_order_date_of_paid' => ', paid <b>%s</b>',
	'form_sales_order_status_of_pay_yes' => 'Paid',
	'form_sales_order_status_of_pay_no' => 'Not Paid',
	'export_external_properties_allow_items' => "Export additional properties of products",
	'export_external_properties_allow_groups' => "Export additional properties of groups",
	'export_modifications_allow' => "Export modifications",
	'export_shortcuts_allow' => "Export shortcuts",
	'export_orders_allow' => "Export orders",
	'load_parent_group' => '--- Root ---',
	'accepted_prices' => 'Product prices information updated successfully!',
	'error_URL_shop_item' => 'Group already contains item with the same name in URL!',
	'error_URL_isset_group' => 'Group contains subgroup with the same URL!',
'warehouse_import_field' => "Warehouse \"%s\"",
'userprice_import_field' => "Price \"%s\"",
	'error_property_guid' => "Can not get property GUID!",
	'error_shop_id' => "Shop ID does not specified!",
	'error_parent_directory' => "Parent directory does not specified!",
	'error_save_without_name' => "Can not save item without name!",
	'create_modification' => "Create modifications",
	'create_modification_title' => "Create modifications from product",
	'create_modification_property_enable' => "Use property \"%s\" {P%s}",
	'create_modification_price' => "<acronym title=\"Modifications price\">Price</acronym>",
	'create_modification_mark' => "<acronym title=\"Template to generate modifications articles\">Article, {N} &mdash; order number</acronym>",
	'create_modification_name' => "<acronym title=\"Template to generate names of modifications\">Name, it is possible to add properties values to product name. E.g., \"%s, color {P17}\" will result in \"%s, color: Blue\"</acronym>",
	'create_modification_copy_main_properties' => "<acronym title=\"Copy main images, text, description of product\">Copy main attributes of product</acronym>",
	'create_modification_copy_seo' => "<acronym title=\"Copy values of SEO fields\">Copy values of SEO fields</acronym>",
	'create_modification_copy_export_import' => "<acronym title=\"Copy export/import parameters\">Copy export/import parameters of product</acronym>",
	'create_modification_copy_prices_to_item' => "<acronym title=\"Copy additional prices of product\">Copy additional prices of product</acronym>",
	'create_modification_copy_specials_prices_to_item' => "<acronym title=\"Copy special prices of product\">Copy special prices of product</acronym>",
	'create_modification_copy_tying_products' => "<acronym title=\"Copy associated products\">Copy associated products</acronym>",
	'create_modification_copy_external_property' => "<acronym title=\"Copy values of other additional product properties\">Copy additional product properties</acronym>",
	'create_modification_copy_tags' => "<acronym title=\"Copy product labels (tags)\">Copy product labels (tags)</acronym>",
	'generateModifications_success' => 'Modifications generated successfully!',
	'file_does_not_specified' => 'File does not specified',
	'prices_add_form_recalculate' => "<acronym title=\"If this parameter is selected, the price for all products of online store will be recalculated that it is set for\">Recalculate set prices</acronym>",
	'prices_add_form_apply_for_all' => "<acronym title=\"If this parameter is selected, the added price will be applied to all products of online store\">Set for all products</acronym>",
	'catalog_marking' => "Marking",
	'item_cards' => "Print price-lists",
	'item_cards_print' => "Print price-lists",
	'item_cards_print_parent_group' => "Parent group",
	'item_cards_print_fio' => "Full name of the responsible person",
	'item_cards_print_date' => "Date",
	'item_cards_print_height' => "Height, mm.",
	'item_cards_print_width' => "Width, mm.",
	'item_cards_print_font' => "Font size",
	'item_cards_desription' => "Description",
	'item_cards_price' => "Price",
	'item_cards_sign' => "Signature of responsible person",
	'manufacturer_warranty' => '<acronym title="Checks if product has just a manufacturer warranty">Manufacturer warranty</acronym>',
	'country_of_origin' => 'Country of origin',
	'apply_price_for_modification' => "Apply price for modifications",
	'item_length'=>'Length',
	'item_width'=>'Width',
	'item_height'=>'Height',
	'apply_purchase_discount' => 'Use for purchase discount',
	'delivery' => 'Delivery',
	'pickup' => 'Pickup',
	'store' => 'Offline Store',
	'adult' => 'Adult',
	'cpa' => 'Allow to order Yandex.Market',
	'show_in_group'=>'Show property in group',
	'show_in_item'=>'Show property in item',
	'add_value'=>'Add the default property values ​​for the items with unset values',
	'start_order_date'=>'Start date',
	'stop_order_date' => 'End date',
	'empty_shop' => 'Are you sure you want to permanently delete all of the items in the store?',
	'root' => 'Root dir',
	'shortcut_group_tags' => "<acronym title=\"Another groups with shortcuts\">Additional groups</acronym>",
	'select_group' => 'Select a group',
	'apply_discount_items_title' => 'Add discounts and bonuses',
	'discount_select_caption' => 'Discounts',
	'bonus_select_caption' => 'Bonuses',
	'apply_discount_success' => 'Information has been successfully changed!',
	'shop_item_associated_unset' => 'Information has been successfully changed',
	'print_forms' => 'Print forms',
	'show_all_warehouses' => 'Show all warehouses',
	'sales_order_show_producers_limit' => 'Producers chart limit',
	'reset' => 'Reset',
	'legend' => 'Legend',
	'special_price_header' => 'Special Prices',
	'quantity' => 'Quantity',
	'associated_item_price' => 'Price',
	'set_item_header' => 'Product Set',
	'apply_recount_set' => 'Recount Set',
	'shop_item_set_not_currency' => 'There is no currency for "%s"',
	'import_price_list_delay' => 'Delay (sec.)',
	'create_modification_copy_warehouse_count' => 'Copy the same warehouse rest',
	'markDeleted' => "Delete item",
	'items_catalog_copy_form_title' => 'Copy item',
	'item_warehouse' => 'Warehouse balances',
	'item_warehouse_title' => 'Warehouse balances in the store "%s"',
	'shop_item_not_currency' => 'There is no currency!',
	'min_quantity' => '<acronym title="Minimum order quantity">Min qty</acronym>',
	'max_quantity' => '<acronym title="Maximum order quantity">Max qty</acronym>',
	'quantity_step' => '<acronym title="Quantity step">Step</acronym>',
	'modifications_root' => '...',
	'disountcard_link' => 'Discount cards',
	'items_catalog_barcodes' => 'Barcodes',
	'type_barcode' => 'Input barcode',	
);