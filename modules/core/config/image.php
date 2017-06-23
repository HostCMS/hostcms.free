<?php

return array (
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
	),
);