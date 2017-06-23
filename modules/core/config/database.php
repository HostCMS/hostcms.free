<?php

return array (
	'default' => array (
		'driver' => 'mysql',
		'host' => defined('DB_HOST') ? DB_HOST : NULL,
		'username' => defined('DB_USER_NAME') ? DB_USER_NAME : NULL,
		'password' => defined('DB_PASSWORD') ? DB_PASSWORD : NULL,
		'database' => defined('DB_NAME') ? DB_NAME : NULL
	)
);