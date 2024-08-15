<?php

$aTypicalCaches = array(
	'default' => array('expire' => 3600, 'size' => 262144, 'tags' => FALSE),
	'Core_ORM' => array('expire' => 3600, 'size' => 262144, 'tags' => FALSE),
	'Core_ORM_ColumnCache' => array('expire' => 3600, 'size' => 262144, 'tags' => FALSE),
	'Core_ORM_RelationCache' => array('expire' => 3600, 'size' => 262144, 'tags' => FALSE),
	'ipaddresses' => array('expire' => 1800, 'size' => 262144, 'tags' => FALSE),
	'informationsystem_rss' => array('expire' => 14400, 'size' => 262144),
	'informationsystem_show' => array('expire' => 14400, 'size' => 262144, 'compress' => TRUE),
	'informationsystem_tags' => array('expire' => 14400, 'size' => 262144, 'compress' => TRUE),
	'informationsystem_comment_show' => array('expire' => 14400, 'size' => 262144, 'compress' => TRUE),
	'shop_show' => array('expire' => 14400, 'size' => 262144, 'compress' => TRUE),
	'shop_tags' => array('expire' => 14400, 'size' => 262144, 'compress' => TRUE),
	'search' => array('expire' => 14400, 'size' => 262144, 'tags' => FALSE),
	'structure_breadcrumbs' => array('expire' => 14400, 'size' => 262144),
	'structure_show' => array('expire' => 14400, 'size' => 262144, 'compress' => TRUE),
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
	'memcache' => array(
		'name' => 'Memcache',
		'driver' => 'Cache_Memcache',
		'server' => '127.0.0.1',
		'port' => 11211,
		'checksum' => TRUE,
		'caches' => $aTypicalCaches,
	),
	'memcached' => array(
		'name' => 'Memcached',
		'driver' => 'Cache_Memcached',
		'server' => '127.0.0.1',
		'port' => 11211,
		'checksum' => TRUE,
		'caches' => $aTypicalCaches,
	),
	'apc' => array(
		'name' => 'APC/APCU',
		'driver' => 'Cache_APC',
		'checksum' => TRUE,
		'caches' => $aTypicalCaches,
	),
	'phpredis' => array(
		'name' => 'Phpredis',
		'driver' => 'Cache_Phpredis',
		'server' => '127.0.0.1',
		'port' => 6379,
		'auth' => NULL,
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