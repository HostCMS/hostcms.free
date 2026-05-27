<?php

return array (
	'adminMenu' => array(
		'timeline' => array(
			'ico' => 'fa-solid fa-bars-staggered',
			'caption' => Core::_('Skin_Bootstrap.admin_menu_timeline'),
			'modules' => array('timeline'),
		),
		'content' => array(
			'ico' => 'fa-regular fa-newspaper',
			'caption' => Core::_('Skin_Bootstrap.admin_menu_content'),
			'modules' => array('informationsystem', 'shop', 'document', 'media', 'tag', 'printlayout', 'revision'),
		),
		'structure' => array(
			'ico' => 'fa-solid fa-sitemap',
			'caption' => Core::_('Skin_Bootstrap.admin_menu_structure'),
			'modules' => array('structure', 'template', 'lib', 'xsl', 'tpl', 'shortcode'),
		),
		'services' => array(
			'ico' => 'fa-solid fa-cubes',
			'caption' => Core::_('Skin_Bootstrap.admin_menu_services'),
			'modules' => array('helpdesk', 'production',  'dms', 'form', 'list', 'forum', 'ai', 'search', 'maillist', 'poll', 'message'),
		),
		'crm' => array(
			'ico' => 'fa-solid fa-users',
			'caption' => Core::_('Skin_Bootstrap.admin_menu_crm'),
			'modules' => array('lead', 'siteuser', 'event', 'deal', 'crm_project', 'user', 'calendar', 'telephony', 'messenger', 'company'),
		),
		'finance' => array(
			'ico' => 'fa-solid fa-coins',
			'caption' => Core::_('Skin_Bootstrap.admin_menu_finance'),
			'modules' => array('report', 'chartaccount')
		),
		'tools' => array(
			'ico' => 'fa-solid fa-briefcase',
			'caption' => Core::_('Skin_Bootstrap.admin_menu_tools'),
			'modules' => array('filemanager', 'typograph', 'shortlink', 'antispam', 'schedule'),
		),
		'seo' => array(
			'ico' => 'fa-solid fa-rocket',
			'caption' => Core::_('Skin_Bootstrap.admin_menu_seo'),
			'modules' => array('counter', 'advertisement', 'seo', 'oneps', 'roistat'),
		),
		'clouds' => array(
			'ico' => 'fa-solid fa-cloud',
			'caption' => Core::_('Skin_Bootstrap.admin_menu_clouds'),
			'modules' => array('cloud', 'cdn'),
		),
		'market' => array(
			'ico' => 'fa-solid fa-puzzle-piece',
			'caption' => Core::_('Skin_Bootstrap.admin_menu_market'),
			'modules' => array('market'),
		),
		'system' => array(
			'ico' => 'fa-solid fa-gear',
			'caption' => Core::_('Skin_Bootstrap.admin_menu_system'),
			'modules' => array('site', 'eventlog', 'notification', 'field', 'ipaddress', 'constant', 'restapi', 'webhook', 'certificate', 'benchmark', 'admin_form', 'module', 'mail', 'bot', 'wysiwyg', 'syntaxhighlighter'),
		),
		'cache' => array(
			'ico' => 'fa-solid fa-archive',
			'caption' => Core::_('Skin_Bootstrap.admin_menu_cache'),
			'modules' => array('cache'),
		),
		'administration' => array(
			'ico' => 'fa-solid fa-wrench',
			'caption' => Core::_('Skin_Bootstrap.admin_menu_administration'),
			'modules' => array('update', 'backup', 'sql', 'support'),
		),
		'trash' => array(
			'ico' => 'fa-regular fa-trash-can',
			'caption' => Core::_('Skin_Bootstrap.admin_menu_trash'),
			'modules' => array('trash'),
		),
	)
);