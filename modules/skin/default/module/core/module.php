<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Core. Backend's Index Pages and Widget.
 *
 * @package HostCMS
 * @subpackage Skin
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Skin_Default_Module_Core_Module extends Core_Module
{
	/**
	 * Name of the skin
	 * @var string
	 */
	protected $_skinName = 'default';

	/**
	 * Name of the module
	 * @var string
	 */
	protected $_moduleName = 'core';

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();

		$this->_adminPages = array(
			1 => array('title' => Core::_('Admin.index_systems_events')),
			2 => array('title' => Core::_('Admin.index_systems_characteristics'))
		);
	}

	/**
	 * Show admin widget
	 * @param int $type
	 * @param boolean $ajax
	 * @return self
	 */
	public function adminPage($type = 0, $ajax = FALSE)
	{
		// Modal windows
		$oCore_Log = Core_Log::instance();
		$file_name = $oCore_Log->getLogName(date('Y-m-d'));

		$type = intval($type);

		switch ($type)
		{
			// Журнал событий
			case 1:
			$windowId = 'modalEvents';
			break;
			default:
			$windowId = 'modalCharacteristics';
			break;
		}

		if (!$ajax)
		{
			$oModalWindow = Core::factory('Core_Html_Entity_Div')
				->id($windowId)
				->class('widget')
				->title(
					Core_Array::get(
						Core_Array::get($this->_adminPages, $type, array('title')), 'title'
				))
				->add($oModalWindowSub = Core::factory('Core_Html_Entity_Div')
					->class('sub')
				);
		}
		else
		{
			$oModalWindowSub = Core::factory('Core_Html_Entity_Div');
		}

		switch ($type)
		{
			// Журнал событий
			case 1:
				if (is_file($file_name))
				{
					if ($fp = @fopen($file_name, 'r'))
					{
						$aLines = array();
						$iSize = @filesize($file_name);
						$iSlice = 10240;

						$iSize > $iSlice && fseek($fp, $iSize - $iSlice);

						// [0]-дата/время, [1]-имя пользователя, [2]-события, [3]-статус события, [4]-сайт, [5]-страница
						while (!feof($fp))
						{
							$event = fgetcsv($fp, $iSlice);
							if (empty($event[0]) || count($event) < 3)
							{
								continue;
							}
							$aLines[] = $event;
						}

						count($aLines) > 3 && $aLines = array_slice($aLines, -3);
						$aLines = array_reverse($aLines);

						foreach ($aLines as $aLine)
						{
							if (count($aLine) > 3)
							{
								$oModalWindowSub
									->add(
										Core::factory('Core_Html_Entity_Div')
											->class('event event' . intval($aLine[3] == -1 ? 0 : $aLine[3]))
											->add(
												Core::factory('Core_Html_Entity_Div')
													->class('corner')
											)
											->add(
												Core::factory('Core_Html_Entity_Span')
													->value(htmlspecialchars(Core_Str::cut(strip_tags($aLine[2]), 70)))
											)
											->add(
												Core::factory('Core_Html_Entity_Div')
													->class('clear')
											)
											->add(
												Core::factory('Core_Html_Entity_Div')
													->class('login')
													->value(htmlspecialchars($aLine[1]))
											)
											->add(
												Core::factory('Core_Html_Entity_Div')
													->class('date')
													->value(htmlspecialchars(Core_Date::sql2datetime($aLine[0])))
											)
									);
							}
						}
						unset($aLines);

						if (Core::moduleIsActive('eventlog'))
						{
							$sEventlogHref = '/admin/eventlog/index.php';
							$oModalWindowSub->add(
								Core::factory('Core_Html_Entity_Div')
									->class('widgetDescription')
									->add(
										Core::factory('Core_Html_Entity_Img')
											->src('/modules/skin/' . $this->_skinName . '/images/widget/event.png')
									)->add(
										Core::factory('Core_Html_Entity_Div')
											->add(
												Core::factory('Core_Html_Entity_A')
													->id('widgetEventlogOther')
													->href($sEventlogHref)
													->value(Core::_('Admin.index_events_journal_link'))
											)
											->add(
												Core::factory('Core_Html_Entity_Script')
													->type('text/javascript')
													->value("$('#widgetEventlogOther').linkShortcut({path: '{$sEventlogHref}', shortcutImg: '/modules/skin/{$this->_skinName}/images/module/eventlog.png', shortcutTitle: '" . Core::_('Admin.index_systems_events') . "', Minimize: true, Closable: true});")
											)
									)
							);
						}
					}
					else
					{
						$oModalWindowSub->value(
							Core_Message::get(Core::_('Admin.index_error_open_log') . $file_name, 'error')
						);
					}
				}
			break;
			// Системные характеристики
			default:
				$dbVersion = Core_DataBase::instance()->getVersion();
				$gdVersion = Core_Image::instance('gd')->getVersion();
				$pcreVersion = Core::getPcreVersion();
				$memoryLimit = ini_get('memory_limit')
					? ini_get('memory_limit')
					: 'undefined';

				$maxExecutionTime = intval(ini_get('max_execution_time'));

				$oModalWindowSub
					->add(
						Core::factory('Core_Html_Entity_Div')
							->class('characteristics characteristicOk')
							->add(
								Core::factory('Core_Html_Entity_Span')
									->value(Core::_('Admin.index_tech_date_hostcms') . ' ' . Core::_('Core.redaction' . Core_Array::get(Core::$config->get('core_hostcms'), 'integration', 0)) . ' ' . CURRENT_VERSION)
							)
					)->add(
						Core::factory('Core_Html_Entity_Div')
							->class('characteristics ' . (version_compare(phpversion(), '5.3.0', ">=") ? 'characteristicOk' : 'characteristicFail'))
							->add(
								Core::factory('Core_Html_Entity_Span')
									->value(Core::_('Admin.index_tech_date_php') . ' ' . phpversion())
							)
					)->add(
						Core::factory('Core_Html_Entity_Div')
							->class('characteristics ' . (version_compare($dbVersion, '5.0.0', ">=") ? 'characteristicOk' : 'characteristicFail'))
							->add(
								Core::factory('Core_Html_Entity_Span')
									->value(Core::_('Admin.index_tech_date_sql') . ' ' . $dbVersion)
							)
					)->add(
						Core::factory('Core_Html_Entity_Div')
							->class('characteristics ' . (version_compare($gdVersion, '2.0', ">=") ? 'characteristicOk' : 'characteristicFail'))
							->add(
								Core::factory('Core_Html_Entity_Span')
									->value(Core::_('Admin.index_tech_date_gd') . ' ' . $gdVersion)
							)
					)->add(
						Core::factory('Core_Html_Entity_Div')
							->class('characteristics ' . (version_compare($pcreVersion, '7.0', ">=") ? 'characteristicOk' : 'characteristicFail'))
							->add(
								Core::factory('Core_Html_Entity_Span')
									->value(Core::_('Admin.index_tech_date_pcre') . ' ' . $pcreVersion)
							)
					)->add(
						Core::factory('Core_Html_Entity_Div')
							->class('characteristics ' . (!$maxExecutionTime || $maxExecutionTime >= 30 ? 'characteristicOk' : 'characteristicFail'))
							->add(
								Core::factory('Core_Html_Entity_Span')
									->value(Core::_('Admin.index_tech_date_max_time') . ' ' . $maxExecutionTime)
							)
					)->add(
						Core::factory('Core_Html_Entity_Div')
							->class('characteristics ' . (Core_Str::convertSizeToBytes($memoryLimit) >= Core_Str::convertSizeToBytes('16M') ? 'characteristicOk' : 'characteristicFail'))
							->add(
								Core::factory('Core_Html_Entity_Span')
									->value(Core::_('Admin.index_memory_limit') . ' ' . $memoryLimit)
							)
					)->add(
						Core::factory('Core_Html_Entity_Div')
							->class('characteristics ' . (function_exists('mb_internal_encoding') ? 'characteristicOk' : 'characteristicFail'))
							->add(
								Core::factory('Core_Html_Entity_Span')
									->value(Core::_('Admin.index_tech_date_mb') . ' ' . (function_exists('mb_internal_encoding') ? Core::_('Admin.index_on') : Core::_('Admin.index_off')))
							)
					)->add(
						Core::factory('Core_Html_Entity_Div')
							->class('characteristics ' . (function_exists('json_encode') ? 'characteristicOk' : 'characteristicFail'))
							->add(
								Core::factory('Core_Html_Entity_Span')
									->value(Core::_('Admin.index_tech_date_json') . ' ' . (function_exists('json_encode') ? Core::_('Admin.index_on') : Core::_('Admin.index_off')))
							)
					)->add(
						Core::factory('Core_Html_Entity_Div')
							->class('characteristics ' . (function_exists('simplexml_load_string') ? 'characteristicOk' : 'characteristicFail'))
							->add(
								Core::factory('Core_Html_Entity_Span')
									->value(Core::_('Admin.index_tech_date_simplexml') . ' ' . (function_exists('simplexml_load_string') ? Core::_('Admin.index_on') : Core::_('Admin.index_off')))
							)
					)->add(
						Core::factory('Core_Html_Entity_Div')
							->class('characteristics ' . (function_exists('iconv') ? 'characteristicOk' : 'characteristicFail'))
							->add(
								Core::factory('Core_Html_Entity_Span')
									->value(Core::_('Admin.index_tech_date_iconv') . ' ' . (function_exists('iconv') ? Core::_('Admin.index_on') : Core::_('Admin.index_off')))
							)
					)
					;
			break;
		}

		if (!$ajax)
		{
			$oUser = Core_Entity::factory('User')->getCurrent();
			$module_id = 0;
			$oUser_Setting = $oUser->User_Settings->getByModuleIdAndTypeAndEntityId($module_id, $type, 0);

			if (is_null($oUser_Setting))
			{
				$oUser_Setting = Core_Entity::factory('User_Setting');
				$oUser_Setting->position_x = 700;
				$oUser_Setting->position_y = 385;
				$oUser_Setting->width = 250;
				$oUser_Setting->height = 220;
			}

			$oModalWindow
				->add(
					Core::factory('Core_Html_Entity_Script')
						->type('text/javascript')
						->value("$(function(){
							$('#{$windowId}').widgetWindow({
								position: [{$oUser_Setting->position_x}, {$oUser_Setting->position_y}],
								width: {$oUser_Setting->width},
								height: {$oUser_Setting->height},
								moduleId: '{$module_id}',
								type: '{$type}',
								path: '/admin/index.php?ajaxWidgetLoad&moduleId={$module_id}&type={$type}'
							});
						});")
				)
				->execute();
		}
		else
		{
			$oModalWindowSub->execute();
		}

		return $this;
	}
}