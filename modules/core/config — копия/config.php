<?php

return array (
	'skin' => 'bootstrap',
	'dateFormat' => 'd.m.Y',
	'dateTimeFormat' => 'd.m.Y H:i:s',
	//'reverseDateTimeFormat' => '',
	'datePickerFormat' => 'DD.MM.YYYY',
	'dateTimePickerFormat' => 'DD.MM.YYYY HH:mm:ss',
	'timePickerFormat' => 'HH:mm:ss',
	'timezone' => 'Europe/Moscow',
	'translate' => FALSE,
	'chat' => TRUE,
	'switchSelectToAutocomplete' => 3,
	'autocompleteItems' => 10,
	'dirMode' => 0755,
	'fileMode' => 0644,
	'errorDocument503' => 'hostcmsfiles/503.htm',
	'compressionJsDirectory' => 'hostcmsfiles/js/',
	'compressionCssDirectory' => 'hostcmsfiles/css/',
	'sitemapDirectory' => 'hostcmsfiles/sitemap/',
	'backendSessionLifetime' => 14400,
	'availableExtension' => array ('JPG', 'JPEG', 'GIF', 'PNG', 'WEBP', 'AVIF', 'SVG', 'PDF', 'ZIP', 'DOC', 'DOCX', 'XLS', 'XLSX', 'TXT', 'JFIF'),
	'defaultCache' => 'file',
	'session' => array(
		'driver' => 'database',
		'class' => 'Core_Session_Database',
		'subdomain' => FALSE,
		//'driver' => 'phpredis',
		//'class' => 'Core_Session_Phpredis',
		//'server' => '127.0.0.1',
		//'port' => 6379,
		//'auth' => '123456789'
	)
);