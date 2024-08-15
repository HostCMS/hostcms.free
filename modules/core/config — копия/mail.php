<?php

return array(
	'default' => array(
		'driver' => 'sendmail'
	),
	'sendmail' => array(
		'driver' => 'sendmail',
		'dkim' => array(
			'private_key' => CMS_FOLDER . 'champion-tools.ru.private',
			'selector' => 'mail'
		)
	),
	'smtp' => array(
		'driver' => 'smtp',
		'username' => 'address@domain.com',
		'port' => 465,
		'host' => 'ssl://smtp.server.com',
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
);