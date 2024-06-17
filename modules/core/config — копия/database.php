<?php

return array(
	'default' => array(
		'driver' => 'pdo',
		'class' => 'Core_DataBase_Pdo',
		'host' => 'localhost',
		'username' => 'root',
		'password' => '65535',
		'database' => 'hostcms6',
		'storageEngine' => 'MyISAM',
		'charset' => 'utf8'
	),
	'sphinx' => array(
		'driver' => 'mysql',
		'host' => '127.0.0.1:9312',
		'database' => NULL
	),
	'external' => array(
		'driver' => 'pdo',
		'class' => 'Core_DataBase_Pdo',
		'host' => 'localhost',
		'username' => 'root',
		'password' => '65535',
		'database' => 'barcode',
		'storageEngine' => 'MyISAM'
	)
);