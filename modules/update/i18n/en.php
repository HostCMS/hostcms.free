<?php
/**
 * Updates.
 *
 * @package HostCMS
 * @subpackage Update
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
return array(
	'menu' => 'Updates',
	'main_menu' => 'Updates',
	'submenu' => 'Check for updates',
	'loadUpdates_success' => 'Updates list was loaded',
	'install_success' => 'Update "%s" installed successfully.',
	'constant_check_error' => 'Necessary constants have not been found. Please fill in the fields of form Websites &#8594; Settings &#8594; Registration data',
	'isLastUpdate' => 'You have the latest system version installed.',
	'server_error_xml' => 'Error in structure of XML response of server! Please try to request update once more.',
	'server_error_respond_0' => 'Unknown error! Up to %s',
	'server_error_respond_1' => 'Server configuration has been changed; unable to update. Please contact the support. Up to %s',
	'server_error_respond_2' => 'User not found. Please ensure that you specified a correct login in your registration data in section Websites. Up to %s',
	'server_error_respond_3' => 'Order not found. This order may belong to another user. Please ensure that you specified a correct login in your registration data in section Websites. Up to %s',
	'server_error_respond_4' => 'HostCMS system not found. Please try to request update once more. Please pay attention &mdash; that the management system should be available on the main domain of the current website. Specify a main domain in the domains list. Up to %s',
	'server_error_respond_5' => 'Period of technical support has expired! You can extend your technical support in private office.',
	'server_error_respond_6' => 'You can only update from a younger to a major version.',
	'server_error_respond_7' => 'Update unavailable.',
	'server_error_respond_8' => 'Does not fit the management system edition.',
	'server_error_respond_9' => 'License has several installations.',
	'server_error_respond_10' => 'Error 10. Please contact the support. Up to %s',
	'error_open_updatefile' => 'Update file not found.',
	'error_write_file_update' => 'Error while writing data into file "%s".',
	'update_constant_error' => 'Constant HOSTCMS_UPDATE_SERVER not found.',
	'update_files_error' => 'Error while extracting tar.gz file.',
	'server_return_empty_answer' => 'Server returned an empty response. Outgoing connections using fsockopen() may be forbidden on the hosting server. Please contact your hosting provider or server administrator.',
	'support_available' => 'Support available until %s.',
	'support_has_expired' => 'Support has expired at %s. <a href="http://%s/users/" target="_blank">Extend technical support</a>. Up to %s',
	'msg_update_required' => 'Update %s required',
	'msg_installing_package' => 'Installing package %s',
	'msg_unpack_package' => 'Unpacking file %s',
);