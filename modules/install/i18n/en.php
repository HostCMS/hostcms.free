<?php

/**
 * Install.
 *
 * @package HostCMS
 * @subpackage Install
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
return array(
	'changeLanguage' => 'Select your language:',
	'constant_on' => 'Enabled',
	'constant_off' => 'Disabled',
	'undefined' => 'Undefined',
	'not_installed' => 'Not installed',

	'supported' => 'Supported',
	'unsupported' => 'Unsupported',

	'success' => 'Success',
	'error' => 'Error',

	'start' => 'Start',
	'back' => 'Back',
	'next' => 'Next',

	'yes' => 'Yes',
	'seconds' => 'sec',
	'megabytes' => 'M',

	'title' => 'Content Management System Installation',
	'menu_0' => 'Select your language',
	'menu_1' => 'License Agreement',
	'menu_2' => 'Server Settings',
	'menu_3' => 'Settings',
	'menu_4' => 'Configuration',
	'menu_5' => 'License',
	'menu_6' => 'Choose template',
	'menu_7' => 'Template Settings',
	'menu_8' => 'Installation Complete',
	
	'license-caption' => 'Registration Data',
	'login' => 'Login at hostcms.org',
	'login-placeholder' => 'Login at hostcms.org',
	'license' => 'License Number',
	'pin' => 'PIN сode',
	
	'step_0' => 'HostCMS installation',
	'step_1' => 'Step 1: License Agreement',
	'step_2' => 'Step 2: Server Settings',
	'step_3' => 'Step 3: Settings',
	'step_4' => 'Step 4: Configuration',
	'step_5' => 'Step 5: License',
	'step_6' => 'Step 6: Choose Template',
	'step_7' => 'Step 7: Template Settings',
	'step_8' => 'Step 8: Installation Complete',

	'step_5_warning1' => 'License number and PIN-code can be found in your <a href="http://www.hostcms.ru/users/" target="_blank">account</a> on our website in the <a href="http://www.hostcms.ru/users/licence/" target="_blank">Licenses</a>.',
	'step_5_warning2' => 'You can not fill the license data at this step.',
	'step_5_warning3' => 'New user has an empty list of licenses, you can <a href="http://www.hostcms.ru/shop/" target="_blank">buy</a> <i class="fa fa-external-link"></i> or <a href="http://www.hostcms.ru/users/licence/add-free/" target="_blank">create your own license</a> <i class="fa fa-external-link"></i> for the edition HostCMS.Free. Just press the button +HostCMS.Free.',
	
	'write_error' => 'Error while writing into file %s.',
	'template_data_information' => 'Template settings.',
	'allowed_extension' => 'Allowed file extensions: %s',
	'max_file_size' => 'Maximum file size: %s x %s',
	'empty_settings' => 'Template hasn\'t settings',
	'file_not_found' => 'File %s not found.',
	'template_install_success' => 'Template installed successfully.',
	'template_files_copy_error' => 'Error! Template files has not been copied!',
	'file_copy_error' => 'Error! File has not been copied in %s!',
	'file_disabled_extension' => 'File for %s has forbidden extension!',

	'templates_dont_exist' => 'List of templates is unavailable. CMS will be installed with the default template.',

	'license_agreement_error' => 'Please accept the License Agreement terms to continue the installation process!',
	'license_agreement' => 'I accept the terms in the license agreement.',

	'table_field_param' => 'Option',
	'table_field_need' => 'Required',
	'table_field_thereis' => 'Available',
	'table_field_value' => 'Value',

	'table_field_php_version' => 'PHP version:',
	'table_field_mbstring' => 'Multibyte String:',
	'table_field_json' => 'JSON:',
	'table_field_simplexml' => 'SimpleXML:',
	'table_field_iconv' => 'Iconv:',
	'table_field_gd_version' => 'GD version:',
	'table_field_pcre_version' => 'PCRE version:',
	'table_field_mysql' => 'MySQL:',
	'table_field_maximum_upload_data_size' => 'Maximum uploaded data size:',
	'table_field_maximum_execution_time' => 'Maximum execution time:',
	'table_field_disc_space' => 'Space available:',
	'table_field_ram_space' => 'Memory available:',
	'table_field_safe_mode' => 'PHP safe mode:',
	'table_field_register_globals' => 'Global variables:',
	'table_field_magic_quotes_gpc' => 'Magic quotes:',
	'table_field_xslt_support' => 'XSLT:',

	'parameter_corresponds' => 'OK',
	'parameter_not_corresponds_but_it_is_safe' => 'Performance-independent mismatch.',
	'parameter_not_corresponds' => 'The parameter doesn\'t conform to.',

	'access_parameter' => 'Access Options',
	'file_access' => 'File Mode',
	'directory_access' => 'Directory Mode',
	'example' => '(for example, %s)',
	'database_params' => 'Database Parameters',
	'mysql_server' => 'MySQL Server',
	'database_login' => 'Database Login',
	'database_pass' => 'Database Password',
	'database_mysql' => 'Database Name',
	'database_storage_engine' => 'Storage Engine',
	'database_driver' => 'MySQL Driver',
	'create_database' => 'Create Database',
	'create_database_flag' => 'If your database was created do not select this checkbox.',
	'clear_database' => 'Clear Database',
	'clear_database_caution' => 'All data will be removed after clearing your database!',

	'action' => 'Action',
	'result' => 'Result',
	'comment' => 'Comment',

	'empty_color_scheme' => 'The template does not have a color scheme. Please press <strong>"Next"</strong>.',
	
	'store_database_params' => 'Database parameters record',
	'not_enough_rights_error' => 'File write error <b>%s</b>. Please set the required directory access rights.',
	'database_connection' => 'Database connection',
	'database_connection_check_params' => 'Please verify your data to be connected to your database.',
	'database_creation' => 'Database creation',
	'attention_message' => 'The database user to be connected through should have enough rights to create a database. The database users of most hosting do not possess such rights. If the case, it is recommended to create your database using the hosting control panel. Do not mark with a flag "Create database".',

	'attention_message2' => '<p>If reinstalling, it is recommended to install in the new database otherwise all your database data shall be lost.</p><p>To reinstall HostCMS press <strong>"Next"</strong>, and the button <strong>"Start"</strong> to start your work.</p><p>if the installer cannot be removed delete manually the directory <b>/install/</b> from the website.</p>',
	'attention_message3' => '<p>To continue the installation process please connect to the FTP server and <strong>delete the file install.php</strong>, from the website roots.</p>',

	'database_selection' => 'Database selection',
	'database_selection_access_denied' => 'The user access to the selected database is denied or this database does not exist.',
	'database_clearing' => 'Database clearing',
	'sql_dump_loading' => 'SQL dump loading and execution',
	'sql_dump_loading_error' => 'Error. MySQL version %s',
	'domen_installing' => 'Domain installation',
	'lng_installing' => 'Language installation',
	'sql_error' => 'Error %s',

	'error_system_already_install' => 'The HostCMS has been installed!',
	'delete_install_file' => 'Delete file install.php',
	'attention_complete_install' => '<p>To finish the installation and delete the installation system please push the button <strong>"Start"</strong>.</p><p>Back-end available on <a href="/admin/" target="_blank">http://[your_site]/admin/</a> in the browser address line change [your_site] to the website address.</p><p>To login into the back-end: <br />Login: <strong>admin</strong> <br />Password: <strong>admin</strong></p><p>Thank you for choosing HostCMS content management system!</p>',
);