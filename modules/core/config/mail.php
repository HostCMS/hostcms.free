<?php

return array (
	'default' => array (
		'driver' => 'sendmail',
	),
	'sendmail' => array (
		'driver' => 'sendmail',
	),
	'smtp' => array (
		'driver' => 'smtp',
		'username' => 'address@domain.com',
		'port' => '25', // для SSL порт 465
		'host' => 'smtp.server.com', // для SSL используйте ssl://smtp.gmail.com
		'password' => '',
		'options' => array(
			'ssl' => array(
				'verify_peer' => FALSE,
				'verify_peer_name' => FALSE,
				'allow_self_signed' => TRUE
			)
		)
	)
);