<?php
/**
 * Online shop.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
return array(
	'model_name' => 'Payment systems',
	'show_system_of_pay_link' => "Payment systems reference",
	'system_of_pay_menu' => "Payment system",
	'system_of_pay_menu_add' => "Add",
	'name' => "<acronym title=\"Name of payment system\">Name</acronym>",
	'sorting' => "<acronym title=\"Sorting order of payment system\">Sorting order</acronym>",
	'description' => "<acronym title=\"Description of payment system\">Description</acronym>",
	'active' => "<acronym title=\"Activity of payment system\">Activity</acronym>",
	'shop_id' => '<acronym title="Online store">Online store</acronym>',
	'shop_currency_id' => "<acronym title=\"Currency in which calculation in this payment system is performed\">Currency</acronym>",
	'system_of_pay_edit_form_title' => "Edit payment system information",
	'system_of_pay_add_form_title' => "Add payment system information",
	'system_of_pay_add_form_handler' => "<acronym title=\"Handler code of payment system\">Handler code</acronym>",
	'changeStatus_success' => "Activity has been changed",
	'apply_success' => 'Action data updated successfully.',
	'apply_error' => 'Error! Action data has not been modified.',
	'markDeleted_success' => 'Action deleted successfully!',
	'markDeleted_error' => 'Error! Form action has not been deleted!',
	'edit_success' => "Payment system information added successfully!",
	'delete_success' => 'Item deleted successfully!',
	'undelete_success' => 'Item restored successfully!',
	'file_error' => 'File record error %s. Please set the required directory access rights!',
	'attention' => 'Attention! The class name depends on the payment system\s ID, e.g. the payment system 17 should has name<br/><b>class Shop_Payment_System_Handler17 extends Shop_Payment_System_Handler</b>',
	'id' => 'Id',
);