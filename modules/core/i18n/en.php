<?php

return array(
	'error_file_write' => 'Error while opening file to write %s; check your access rights to folder.',
	'error_resize' => 'Error while minimizing small image to maximum permissible size. Image size you specified is probably less than 0.',
	'error_upload' => 'File not uploaded!',

	'error_log_message_stack' => "File: %s, line %s",
	'error_log_message_short' => "<strong>%s:</strong> %s in file %s (line %s)",
	'error_log_message' => "<strong>%s:</strong> %s in file %s (line %s)\nRequests stack:\n%s",
	'error_log_add_message' => "<strong>Error!</strong> Message error add into log",

	'info_cms' => 'Content management system',
	'info_cms_site_support' => 'Support service website: ',
	'info_cms_site' => 'Official website: ',
	'info_cms_support' => 'Support service: ',
	'info_cms_sales' => 'Sales department: ',

	'purchase_commercial_version' => 'Purchase commercial version',

	'administration_center' => 'Administration center',
	'debug_information' => 'Debug information',
	'sql_queries' => 'SQL queries',
	'sql_statistics' => 'Time: <strong>%.3f</strong> s. <a onclick="hQuery(\'#sql_h%d\').toggle()" class="sqlShowStack">Backtrace</a>',
	'sql_debug_backtrace' => '%s, line %d<br/>',
	'show_xml' => 'Show XML/XSL',
	'hide_xml' => 'Hide XML/XSL',
	'lock' => 'Lock panel',
	'logout' => 'Logout',

	'total_time' => 'Execution time: <strong>%.3f</strong> sec, including',
	'time_load_modules' => "load modules: <strong>%.3f</strong> sec",
	'time_template' => "templates and content: <strong>%.3f</strong> sec",
	'time_page' => "page: <strong>%.3f</strong> sec",
	'time_page_config' => "page config: <strong>%.3f</strong> sec",
	'time_database_connection' => "database connection: <strong>%.3f</strong> sec",
	'time_database_select' => "database select: <strong>%.3f</strong> sec",
	'time_sql_execution' => "SQL execution: <strong>%.3f</strong> sec",
	'time_xml_execution' => "XML execution: <strong>%.3f</strong> sec",
	'time_tpl_execution' => "TPL execution: <strong>%.3f</strong> sec",
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
	'error_log_access_was_denied' => "Access denied to module '%s'",
	'error_log_module_disabled' => "Module %s is disabled",
	'error_log_module_access_allowed' => "Access allowed to module \"%s\"",
	'error_log_action_access_allowed' => "Action \"%s\" of form \"%s\"",
	'error_log_logged' => "User was logged on",
	'error_log_authorization_error' => "Incorrect authentication data, login '%s'",
	'error_log_exit' => 'Logout',
	'session_destroy_error' => 'Error session closing',
	'session_change_ip' => 'Trying to use session %s with IP %s',

	'error_message' => "Hello!\n\n"
	. "This event was occurred on site:\n"
	. "Date: %s\n"
	. "Event: %s\n"
	. "Level: %s\n"
	. "User login: %s\n"
	. "Site: %s\n"
	. "Page: %s\n"
	. "User Agent: %s\n"
	. "IP: %s\n\n"
	. "Content management system HostCMS,\n"
	. "http://www.hostcms.org",

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
	'E_DEPRECATED' => "Deprecated",

	'default_form_name' => 'Main',
	'default_event_name' => 'Browsing',

	'widgets' => 'Widgets',
	'addNote' => 'Add note',
	'deleteNote' => 'Delete note',

	'key_not_found' => 'License Key Not Found!',
	'getting_key' => '<div style="width: 100%; margin-top: 20px; overflow: auto; z-index: 9999; background-color: rgba(255, 255, 255, .8); padding: 0 20px; text-shadow: 1px 1px 0 rgba(255, 255, 255, .4)">
	<h2>Getting key</h2>

	<p>After installing you should register new user on our site or use exist username and password to access your «<a href="https://www.hostcms.ru/users/" target="_blank">Personal account</a>»</p>
	<p>When you have logged in, you will see a list of licenses at menu "My licenses":</p>

	<p align="center">
	<img src="//www.hostcms.ru/images/documentation/guide/site/licenses-list.png" class="img-responsive" />
	</p>

	<p>You can enter given license number and PIN in the <a href="/admin/" target="_blank">administration center</a> HostCMS in the section "Websites" — "Settings" — "Registration data".</p>
	<p>Afterwards you can get license key. In section "Websites" — "Domains" click on "key" in the column "License key":</p>

	<p align="center">
	<img src="//www.hostcms.ru/images/documentation/introduction/install/step-by-step/key/key-2.png" class="img-responsive" />
	</p>

	<p>Then click on key:</p>

	<p align="center">
	<img src="//www.hostcms.ru/images/documentation/introduction/install/step-by-step/key/key-3.png" class="img-responsive" />
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
	'timePeriodHours' => '%shours later',
	'timePeriodDays' => '%sdays later',
	'timePeriodYesterday' => 'yesterday',
	'timePeriodMonths' => '%smon later',
	'timePeriodYears' => '%syears later',
	'timePeriodYearMonths' => '%sy %smon later',

	'now' => 'Now',
	'ago' => '%1$s %2$s ago',
	'today' => 'Today',
	'yesterday' => 'Yesterday',
	'tomorrow' => 'Tomorrow',
	'estimate_date' => '%1$d %2$s',
	'estimate_date_year' => '%1$d %2$s %3$d',

	'time_postfix' => ' at %s',

	'month1' => 'January',
	'month2' => 'February',
	'month3' => 'March',
	'month4' => 'April',
	'month5' => 'May',
	'month6' => 'June',
	'month7' => 'July',
	'month8' => 'August',
	'month9' => 'September',
	'month10' => 'October',
	'month11' => 'November',
	'month12' => 'December',

	'capitalMonth1' => 'January',
	'capitalMonth2' => 'February',
	'capitalMonth3' => 'March',
	'capitalMonth4' => 'April',
	'capitalMonth5' => 'May',
	'capitalMonth6' => 'June',
	'capitalMonth7' => 'July',
	'capitalMonth8' => 'August',
	'capitalMonth9' => 'September',
	'capitalMonth10' => 'October',
	'capitalMonth11' => 'November',
	'capitalMonth12' => 'December',

	'hour_nominative' => 'hour',
	'hour_genitive_singular' => 'hour',
	'hour_genitive_plural' => 'hours',
	'minute_nominative' => 'minute',
	'minute_genitive_singular' => 'minutes',
	'minute_genitive_plural' => 'minutes',

	'shortTitleSeconds' => 's.',
	'shortTitleSeconds_1' => 'sec.',
	'shortTitleMinutes' => 'm.',
	'shortTitleHours' => 'h.',
	'shortTitleDays' => 'd.',
	'shortTitleYears' => 'y.',

	'day' => 'Day',
	'month' => 'Month',
	'year' => 'Year',
	'quarter' => 'Quarter',

	'random' => 'Random',
	'generateChars' => 'Symbols',

	'title_no_access_to_page' => 'You are not allowed to access this page!',
	'message_more_info' => 'For more information, contact the site administrator.',
	'title_domain_must_be_added' => 'You need to add domain %s to the list of the site domains!',
	'message_domain_must_be_added' => 'Domain <b>%s</b> must be added to the list of domains supported by the <b>HostCMS</b>!',
	'add_domain_instruction1' => 'To add a domain, go to the <b><a href="/admin/site/index.php" target="_blank">"Backend" → "System" → "Sites"</a></b>.',
	'add_domain_instruction2' => 'Select the pictogram <b>"Domains"</b> for the required site, on the opened page click the menu <b>"Add"</b>.',
	'home_page_not_found' => 'Home page not found',
	'message_home_page_must_be_added' => 'You need to add main page to <b>"Backend" → "Structure"</b>. <br /><b>"Path"</b> for the main page should be "<b>/</b>".',
	'site_disabled_by_administrator' => 'Site %s has been disabled by the administrator and unavailable!',
	'site_activation_instruction' => 'To enable the site, go to the "Sites" section and set the "Activity" value of the required site.',
	'title_limit_available_sites_exceeded' => 'The limit of available sites has been exceeded!',
	'message_limit_available_sites_exceeded' => 'The limit of available sites in the HostCMS has been exceeded!',
	'message_remove_unnecessary_sites' => 'Remove unnecessary sites from the system (<b>"Backend" → "System" → "Sites"</b>) or purchase the version without the limit of multisite.',
	'missing_template_for_page' => 'Missing template for page!',
	'change template instruction' => 'You need to change main page in <b>"Backend" → "Structure" → "Structure"</b>',
	'hosting_mismatch_system_requirements' => 'Hosting mismatch with the system requirements!',
	'requires_php5' => 'You need PHP 5 or PHP 7/8 with installed <a href="https://www.hostcms.ru/documentation/server/ibxslt/" target="_blank">Libxslt</a> for work.',
	'list_tested_hosting' => 'Our website contains <a href="https://www.hostcms.ru/hosting/" target="_blank">list of tested hosting</a> suitable for HostCMS.',

	'show_title' => 'Show',
	'data_show_title' => 'Show on site',

	'unpack_wrong_crc' => 'Error calculating checksum %s: %d calculated, %d actually specified',
	'unpack_file_already_exists_and_directory' => 'File %s already exists and is a directory',
	'unpack_dir_already_exists_and_file' => 'Directory %s already exists and is a file',
	'unpack_file_already_exists_and_protected' => 'File %s already exists and is write protected! Set file permissions according to the installation manual.',
	'unpack_error_creating_dir' => 'Error creating directory for %s',
	'unpack_error_opening_binary_mode' => 'Error opening file %s in binary mode',
	'unpack_file_incorrect_size' => 'Extracted file %s has incorrect size %d, expected %d. The archive may be corrupted.',

	'error_log_backend_blocked_ip' => "Access to the admin center is blocked, IP '%s'",
	'error_log_backend_blocked_useragent' => "Access to the admin center is blocked, IP '%s', User Agent '%s'",
	'csrf_wrong_token' => 'Invalid CSRF token, please update the form!',
	'csrf_token_timeout' => 'CSRF token has expired, please refresh the form!',
);