<?php

/**
 * Core.
 *
 * @package HostCMS
 * @subpackage Core
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
return array(
	'error_file_write' => 'Error while opening file to write %s; check your access rights to folder.',
	'error_resize' => 'Error while minimizing small image to maximum permissible size. Image size you specified is probably less than 0.',
	'error_upload' => 'File not uploaded!',

	'error_log_message_stack' => "File: %s, line %s",
	'error_log_message_short' => "<strong>%s:</strong> %s in file %s (line %s)",
	'error_log_message' => "<strong>%s:</strong> %s in file %s (line %s)\nRequests stack:\n%s",

	'info_cms' => 'Content management system',
	'info_cms_site_support' => 'Support service website: ',
	'info_cms_site' => 'Official website: ',
	'info_cms_support' => 'Support service: ',
	'info_cms_sales' => 'Sales department: ',

	'purchase_commercial_version' => 'Purchase commercial version',

	'administration_center' => 'Administration center',
	'debug_information' => 'Debug information',
	'sql_queries' => 'SQL queries',
	'sql_statistics' => 'Time: <strong>%.3f</strong> s. <a onclick="hQuery(\'#sql_h%d\').toggle()" class="pointer">Backtrace</a>',
	'sql_debug_backtrace' => '%s, line %d<br/>',
	'show_xml' => 'Show XML/XSL',
	'hide_xml' => 'Hide XML/XSL',
	'logout' => 'Logout',

	'total_time' => 'Execution time: <strong>%.3f</strong> sec, including',
	'time_load_modules' => "load modules: <strong>%.3f</strong> sec",
	'time_page' => "templates and content: <strong>%.3f</strong> sec",
	'time_page_config' => "page config: <strong>%.3f</strong> sec",
	'time_database_connection' => "database connection: <strong>%.3f</strong> sec",
	'time_database_select' => "database select: <strong>%.3f</strong> sec",
	'time_sql_execution' => "SQL execution: <strong>%.3f</strong> sec",
	'time_xml_execution' => "XML execution: <strong>%.3f</strong> sec",
	'memory_usage' => "Memory usage: <strong>%.2f</strong> M.",
	'number_of_queries' => "Number of queries: <strong>%d</strong>.",
	'compression' => 'Compression: <strong>%s</strong>.',
	'cache' => 'Cache: <strong>%s</strong>.',
	
	'cache_insert_time' => 'inserts into cache time: <strong>%.3f</strong> sec',
	'cache_read_time' => 'reads from cache time: <strong>%.3f</strong> sec',

	'cache_write_requests' => 'number of write requests: <strong>%d</strong>',
	'cache_read_requests' => 'number of read requests: <strong>%d</strong>',

	'error_create_log' => "Unable to create log-file <b>%s</b><br /><b>Check this directory and set the necessary permissions for it.</b>",
	'error_log_level_0' => "Info",
	'error_log_level_1' => "Successful",
	'error_log_level_2' => "Notice",
	'error_log_level_3' => "Warning",
	'error_log_level_4' => "Critical",

	'error_log_attempt_to_access' => "Attempt to access %s by unregistered user was denied",
	'error_log_several_modules' => "Error! Several identical modules were founded!",
	'error_log_access_was_denied' => "Access denied to %s",
	'error_log_module_disabled' => "Module %s is disabled",
	'error_log_module_access_allowed' => "Access allowed to module \"%s\"",
	'error_log_action_access_allowed' => "Action \"%s\" of form \"%s\"",
	'error_log_logged' => "User was logged on",
	'error_log_authorization_error' => 'Incorrect authentication data',
	'error_log_exit' => 'Logout',
	'session_destroy_error' => 'Error session closing',

	'error_message' => "Hello!\n"
	. "This event was occurred on site:\n"
	. "Date: %s\n"
	. "Event: %s\n"
	. "Level: %s\n"
	. "User login: %s\n"
	. "Site: %s\n"
	. "Page: %s\n"
	. "IP-address: %s\n"
	. "Content management system %s,\n"
	. "http://%s/\n",

	'E_ERROR' => "Error",
	'E_WARNING' => "Warning",
	'E_PARSE' => "Parse Error",
	'E_NOTICE' => "Notice",
	'E_CORE_ERROR' => "Core Error",
	'E_CORE_WARNING' => "Core Warning",
	'E_COMPILE_ERROR' => "Compile Error",
	'E_COMPILE_WARNING' => "Compile Warning",
	'E_USER_ERROR' => "User Error",
	'E_USER_WARNING' => "User Warning",
	'E_USER_NOTICE' => "User Notice",
	'E_STRICT' => "Strict",

	'default_form_name' => 'Main',
	'default_event_name' => 'Browsing',

	'widgets' => 'Widgets',
	'addNote' => 'Add note',
	'deleteNote' => 'Delete note',

	'key_not_found' => 'License Key Not Found!',
	'getting_key' => '<div style="overflow: auto; height: 500px; z-index: 9999; background-color: rgba(255, 255, 255, .4); padding: 0 20px; text-shadow: 1px 1px 0 rgba(255, 255, 255, 0.4)">
	<h2>Getting key</h2>

	<p>After installing you should register new user on our site or use exist username and password to access your «<a href="http://www.hostcms.ru/users/" target="_blank">Personal account</a>»</p>
	<p>When you have logged in, you will see a list of licenses at menu "My licenses":</p>

	<p align="center">
	<img src="//www.hostcms.ru/images/documentation/guide/site/licenses-list.png" class="screen" />
	</p>

	<p>You can enter given license number and PIN in the <a href="/admin/" target="_blank">administration center</a> HostCMS in the section "Websites" — "Settings" — "Registration data".</p>
	<p>Afterwards you can get license key. In section "Websites" — "Domains" click on "key" in the column "License key":</p>

	<p align="center">
	<img src="//www.hostcms.ru/images/documentation/introduction/install/step-by-step/key/key-2.png" class="screen" />
	</p>

	<p>Then click on key:</p>

	<p align="center">
	<img src="//www.hostcms.ru/images/documentation/introduction/install/step-by-step/key/key-3.png" class="screen" />
	</p>

	<h2>Administration Center</h2>
	<p>Use login <b>admin</b> and password <b>admin</b> to enter into <a href="/admin/" target="_blank">administration center</a>.</p>
	</div>',

	'access_forbidden_title' => 'Access Forbidden',
	'access_forbidden' => 'You do not have permission to access. Please contact the site\'s administrator.',

	'extension_does_not_allow' => 'File extension "%s" does not allow.',
	'delete_success' => 'Item deleted successfully!',
	'undelete_success' => 'Item restored successfully!',
	'redaction0'=>'Free',
	'redaction1'=>'My site',
	'redaction3'=>'Small business',
	'redaction5'=>'Business',
	'redaction7'=>'Corporation',
	
	'byte' => 'Byte',
	'kbyte' => 'KB',
	'mbyte' => 'MB',
	'gbyte' => 'GB',
	
	'timePeriodSeconds' => '%ssec later',
	'timePeriodMinutes' => '%smin later',
	'timePeriodHours' => '%sh later',
	'timePeriodDays' => '%sd later',
	'timePeriodYesterday' => 'yesterday',
	'timePeriodMonths' => '%smon назад',
	'timePeriodYears' => '%sy назад',
	'timePeriodYearMonths' => '%sy %smon назад',
);