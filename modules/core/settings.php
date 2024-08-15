<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

return array(
	'config' => array(
		'skin' => 'default',
		'dateFormat' => 'd.m.Y',
		'dateTimeFormat' => 'd.m.Y H:i:s',
		'datePickerFormat' => 'DD.MM.YYYY',
		'dateTimePickerFormat' => 'DD.MM.YYYY HH:mm:ss',
		'timePickerFormat' => 'HH:mm:ss',
		'availableExtension' => array('JPG', 'JPEG', 'GIF', 'PNG', 'WEBP', 'PDF', 'ZIP', 'DOC', 'DOCX', 'XLS', 'XLSX'),
		'availableGetVariables' => array('_openstat', 'utm_source', 'gclid', 'ysclid', 'from'),
		'defaultCache' => 'file',
		'timezone' => 'America/Los_Angeles',
		'translate' => TRUE,
		'chat' => TRUE,
		'switchSelectToAutocomplete' => 100,
		'autocompleteItems' => 10,
		'cms_folders' => NULL,
		'dirMode' => 0755,
		'fileMode' => 0644,
		'errorDocument503' => 'hostcmsfiles/503.htm',
		'compressionJsDirectory' => 'hostcmsfiles/js/',
		'compressionCssDirectory' => 'hostcmsfiles/css/',
		'sitemapDirectory' => 'hostcmsfiles/sitemap/',
		'banAfterFailedAccessAttempts' => 5,
		'csrf_lifetime' => 86400,
		'session' => array(
			'driver' => 'database',
			'class' => 'Core_Session_Database',
			'subdomain' => TRUE,
		),
		'headers' => array(
			'X-Content-Type-Options' => 'nosniff',
			'X-XSS-Protection' => '1;mode=block',
		),
		'backendSessionLifetime' => 14400,
		'backendContentSecurityPolicy' => "default-src 'self' www.hostcms.ru www.youtube.com youtube.com; script-src 'self' 'unsafe-inline' 'unsafe-eval' blob: *.cloudflare.com *.kaspersky-labs.com; img-src 'self' chart.googleapis.com data: blob: www.hostcms.ru; font-src 'self'; connect-src 'self' blob:; style-src 'self' 'unsafe-inline'"
	),
	'captcha' => array(
		'allowedCharacters' => '1234567890', // 23456789abcdeghkmnpqsuvxyz
		'color' => array(0, 0, 0),
		'backgroundColor' => array(255, 255, 255),
		'noise' => 10,
		'width' => 88,
		'height' => 31,
		'minLenght' => 4,
		'maxLenght' => 4,
		'fillBackground' => TRUE,
	),
	'http' => array (
		'type' => array('curl'),
		'data' => array(
			'default' => array (
				'driver' => 'curl',
			),
			'socket' => array (
				'driver' => 'socket',
			),
			'curl' => array (
				'driver' => 'curl',
				// CURLOPT_PROXY - The HTTP proxy to tunnel requests through
				// CURLOPT_PROXYUSERPWD - A username and password formatted as "[username]:[password]" to use for the connection to the proxy
				// CURLOPT_PROXYAUTH - The HTTP authentication method(s) to use for the proxy connection. Use the same bitmasks as described in CURLOPT_HTTPAUTH. For proxy authentication, only CURLAUTH_BASIC and CURLAUTH_NTLM are currently supported.
				// CURLOPT_PROXYPORT - The port number of the proxy to connect to. This port number can also be set in CURLOPT_PROXY.
				// CURLOPT_PROXYTYPE - Either CURLPROXY_HTTP (default) or CURLPROXY_SOCKS5.
				// CURLOPT_HTTPPROXYTUNNEL - TRUE to tunnel through a given HTTP proxy.
				'options' => array(),
			)
		)
	),
	'image' => array(
		'type' => array('gd'),
		'data' => array (
			'default' => array (
				'driver' => 'gd',
			),
			'gd' => array (
				'driver' => 'gd',
			),
			'imagick' => array (
				'driver' => 'imagick',
				'sharpenImage' => array('radius' => 0, 'sigma' => 1),
				'adaptiveSharpenImage' => array('radius' => 0, 'sigma' => 1),
			)
		)
	),
	'mail' => array (
		'default' => array (
			'driver' => 'sendmail',
		),
		'sendmail' => array (
			'driver' => 'sendmail',
			'dkim' => array(
				'private_key' => '',
				'selector' => 'mail'
			)
		),
		'smtp' => array (
			'driver' => 'smtp',
			'username' => 'address@domain.com',
			'port' => '25', // для SSL порт 465
			'host' => 'smtp.server.com', // для SSL используйте ssl://smtp.gmail.com
			'password' => '',
			'log' => FALSE,
			'options' => array(
				'ssl' => array(
					'verify_peer' => FALSE,
					'verify_peer_name' => FALSE,
					'allow_self_signed' => TRUE
				)
			)
		)
	),
	'objectwatcher' => array (
		'maxObjects' => 2048,
	),
	'syntaxhighlighter' => array(
		'mode' => 'ace/mode/php',
		'theme' => 'ace/theme/chrome',
		'showPrintMargin' => false,
		'autoScrollEditorIntoView' => true,
		// 'printMarginColumn' => 80,
		'printMargin' => false,
		'wrap' => '-1', // true/'free' means wrap instead of horizontal scroll, false/'off' means horizontal scroll instead of wrap, and number means number of column before wrap. -1 means wrap at print margin
		'enableBasicAutocompletion' => true,
		'enableSnippets' => true,
		'enableLiveAutocompletion' => true,
	),
	'wysiwyg' => array(
		'theme' => '"silver"',
		'plugins' => '"advlist autolink lists link image charmap preview anchor pagebreak searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking save table directionality emoticons codesample importcss help paste"',
		'toolbar' => '"bold italic underline strikethrough cut copy paste removeformat undo redo blocks fontfamily fontsize | alignleft aligncenter alignright alignjustify bullist numlist link unlink image media table forecolor backcolor hr subscript superscript pagebreak codesample preview code insertShortcode"',
		'toolbar_mode' => '"sliding"',
		'image_advtab' => 'true',
		'image_title' => 'true',
		'menubar' => '"edit insert format view table help"',
		'toolbar_items_size' => '"small"',
		'insertdatetime_dateformat' => '"%d.%m.%Y"',
		'insertdatetime_formats' => '["%d.%m.%Y", "%H:%M:%S"]',
		'insertdatetime_timeformat' => '"%H:%M:%S"',
		'valid_elements' => '"*[*]"',
		'extended_valid_elements' => '"meta[*],i[*],noindex[*]"',
		'file_picker_callback' => 'function (callback, value, meta) { HostCMSFileManager.fileBrowserCallBack(callback, value, meta) }',
		'convert_urls' => 'false',
		'relative_urls' => 'false',
		'remove_script_host' => 'false',
		'forced_root_block' => '"div"',
		'entity_encoding' => '""',
		'verify_html' => 'false',
		'valid_children' => '"+body[style|meta],+footer[meta],+a[div|h1|h2|h3|h4|h5|h6|p|#text]"',
		'browser_spellcheck' => 'true',
		'importcss_append' => 'true',
		'schema' => '"html5"',
		'importcss_selector_filter' => 'function(selector) { return selector.indexOf("body") === -1; }',
		'allow_unsafe_link_target' => 'false',
	)
);