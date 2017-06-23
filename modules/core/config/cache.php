<?php

$aTypicalCaches = array(
	'default' => array('expire' => 3600, 'size' => 262144, 'tags' => FALSE),
	'Core_ORM' => array('expire' => 3600, 'size' => 262144, 'tags' => FALSE),
	'Core_ORM_ColumnCache' => array('expire' => 3600, 'size' => 262144, 'tags' => FALSE),
	'informationsystem_rss' => array('expire' => 14400, 'size' => 262144),
	'informationsystem_show' => array('expire' => 14400, 'size' => 262144),
	'informationsystem_tags' => array('expire' => 14400, 'size' => 262144),
	'shop_show' => array('expire' => 14400, 'size' => 262144),
	'shop_tags' => array('expire' => 14400, 'size' => 262144),
	'search' => array('expire' => 14400, 'size' => 262144, 'tags' => FALSE),
	'structure_breadcrumbs' => array('expire' => 14400, 'size' => 262144),
	'structure_show' => array('expire' => 14400, 'size' => 262144),
	'counter_allSession' => array('expire' => 1800, 'size' => 1024, 'tags' => FALSE),
);

return array (
	'memory' => array(
		'name' => 'Memory',
		'driver' => 'Core_Cache_Memory',
		'caches' => array(
			'default' => array()
		),
	),
	'file' => array(
		'name' => 'File',
		'driver' => 'Cache_File',
		'checksum' => FALSE,
		'caches' => $aTypicalCaches,
	),
	'eaccelerator' => array(
		'name' => 'eAccelerator',
		'driver' => 'Cache_Eaccelerator',
		'checksum' => TRUE,
		'caches' => $aTypicalCaches,
	),
	'apc' => array(
		'name' => 'APC',
		'driver' => 'Cache_APC',
		'checksum' => TRUE,
		'caches' => $aTypicalCaches,
	),
	'xcache' => array(
		'name' => 'XCache',
		'driver' => 'Cache_XCache',
		'checksum' => TRUE,
		'caches' => $aTypicalCaches,
	),
	'static' => array(
		'name' => 'Static',
		'driver' => 'Cache_Static',
		'caches' => array(
			'default' => array('expire' => 3600, 'size' => NULL),
		),
	),
);