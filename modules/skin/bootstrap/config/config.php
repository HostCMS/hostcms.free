<?php

return array (
	'adminMenu' => array(
		'content' => array(
			'ico' => 'fa fa-newspaper-o',
			'caption' => Core::_('Skin_Bootstrap.admin_menu_content'),
			'modules' => array('informationsystem', 'shop', 'document', 'tag', 'printlayout', 'revision'),
		),
		'structure' => array(
			'ico' => 'fa fa-sitemap',
			'caption' => Core::_('Skin_Bootstrap.admin_menu_structure'),
			'modules' => array('structure', 'template', 'lib', 'xsl', 'tpl', 'shortcode'),
		),
		'services' => array(
			'ico' => 'fa fa-cubes',
			'caption' => Core::_('Skin_Bootstrap.admin_menu_services'),
			'modules' => array('helpdesk', 'form', 'list', 'forum', 'maillist', 'poll', 'search', 'message'),
		),
		'crm' => array(
			'ico' => 'fa fa-users',
			'caption' => Core::_('Skin_Bootstrap.admin_menu_crm'),
			'modules' => array('siteuser', 'deal', 'event', 'user', 'calendar', 'kanban', 'company'),
		),		
		'tools' => array(
			'ico' => 'fa fa-briefcase',
			'caption' => Core::_('Skin_Bootstrap.admin_menu_tools'),
			'modules' => array('filemanager', 'typograph', 'antispam', 'schedule'),
		),
		'seo' => array(
			'ico' => 'fa fa-rocket',
			'caption' => Core::_('Skin_Bootstrap.admin_menu_seo'),
			'modules' => array('counter', 'advertisement', 'seo', 'oneps', 'roistat'),
		),
		'clouds' => array(
			'ico' => 'fa fa-cloud',
			'caption' => Core::_('Skin_Bootstrap.admin_menu_clouds'),
			'modules' => array('cloud', 'cdn'),
		),
		'market' => array(
			'ico' => 'fa fa-cogs',
			'caption' => Core::_('Skin_Bootstrap.admin_menu_market'),
			'modules' => array('market'),
		),
		'system' => array(
			'ico' => 'fa fa-gear',
			'caption' => Core::_('Skin_Bootstrap.admin_menu_system'),
			'modules' => array('site', 'eventlog', 'notification', 'benchmark', 'admin_form', 'module', 'constant', 'restapi', 'ipaddress'),
		),
		'cache' => array(
			'ico' => 'fa fa-archive',
			'caption' => Core::_('Skin_Bootstrap.admin_menu_cache'),
			'modules' => array('cache'),
		),
		'administration' => array(
			'ico' => 'fa fa-shield',
			'caption' => Core::_('Skin_Bootstrap.admin_menu_administration'),
			'modules' => array('update', 'backup', 'sql', 'support'),
		),
		'trash' => array(
			'ico' => 'fa fa-trash-o',
			'caption' => Core::_('Skin_Bootstrap.admin_menu_trash'),
			'modules' => array('trash'),
		),
	)
);