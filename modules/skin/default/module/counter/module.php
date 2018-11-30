<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Counter. Backend's Index Pages and Widget.
 *
 * @package HostCMS
 * @subpackage Skin
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Skin_Default_Module_Counter_Module extends Counter_Module
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
	protected $_moduleName = 'counter';

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();

		$this->_adminPages = array(
			77 => array('title' => Core::_('Counter.menu'))
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
		$windowId = 'modalCounter';

		if (!$ajax)
		{
			$oModalWindow = Core::factory('Core_Html_Entity_Div')
				->id($windowId)
				->class('widget')
				->title(Core::_('Counter.menu'))
				->add($oModalWindowSub = Core::factory('Core_Html_Entity_Div')
					->class('sub')
				);
		}
		else
		{
			$oModalWindowSub = Core::factory('Core_Html_Entity_Div');
		}

		$oCounters = Core_Entity::factory('Site', CURRENT_SITE)->Counters;

		$oCounters->queryBuilder()
			->where('date', '<=', Core_Date::timestamp2sql(time()))
			->where('date', '>=', Core_Date::timestamp2sql(strtotime('-144 day')));

		$aObjects = $oCounters->findAll();

		if (count($aObjects))
		{
			//$aObjects = array_reverse($aObjects);

			$aColors = array(
				'A7BD34',
				'25A5FF',
				'FF851D',
				'FE1662',
				'48909D'
			);

			$oXml = simplexml_load_string('<?xml version="1.0" encoding="UTF-8"?>
				<graph hoverCapBorder="A7BD34"
						hoverCapBgColor="A7BD34"
						formatNumberScale="0"
						decimalPrecision="0"
						showvalues="0"
						numdivlines="3"
						numVdivlines="10"
						showShadow="0"
						lineThickness="1"
						animation="1"
						showLegend="1"
						canvasBorderColor="cccccc"
						canvasBorderThickness="1"
						anchorSides="10"
						anchorRadius="1"
						baseFontColor="ffffff"
						outCnvBaseFontColor="000000"
						shownames="0"
						chartBottomMargin="0">
					<categories></categories>
				</graph>');

			$aChars = array(
				'hits',
				'hosts',
				'bots',
				'sessions',
				'new_users'
			);

			foreach ($aChars as $key => $value)
			{
				$aDatasets[$value] = $oXml->addChild('dataset');
				$aDatasets[$value]->addAttribute('seriesName', Core::_("Counter.graph_{$value}"));
				$aDatasets[$value]->addAttribute('color', $aColors[$key]);
				$aDatasets[$value]->addAttribute('anchorBgColor', $aColors[$key]);
			}

			$iCountObjects = count($aObjects);
			$iShowCount = 7;
			$iEach = round($iCountObjects / $iShowCount);
			$iEach == 0 && $iEach = 1;
			foreach ($aObjects as $iObjectKey => $oObject)
			{
				$oCategory = $oXml->categories->addChild('category');
				$oCategory->addAttribute('name', Core_Date::sql2date($oObject->date));

				$iObjectKey % $iEach != 0 && $oCategory->addAttribute('showName', 0) && $oCategory->addAttribute('showAnchors', 0);

				foreach ($aDatasets as $key => $oDataset)
				{
					$oDataset->addChild('set')->addAttribute('value', $oObject->$key);
				}
			}

			$sScript = "(function($){
				var chart = new FusionCharts('/admin/js/fusionchart/FCF_MSLine.swf', 'ChartId', '250', '130');
				chart.setDataXML('" . Core_Str::escapeJavascriptVariable($oXml->asXml()) . "');
				chart.render('widget');
			})(jQuery);";

			$oModalWindowSub->add(Core::factory('Core_Html_Entity_Div')
				->add(Core::factory('Core_Html_Entity_Div')->id('widget'))
				->add(Core::factory('Core_Html_Entity_Script')->value($sScript)));
		}

		$sCounterHref = '/admin/counter/index.php';
		$oModalWindowSub->add(
			Core::factory('Core_Html_Entity_Div')
				->class('widgetDescription')
				->add(
					Core::factory('Core_Html_Entity_Img')
						->src('/modules/skin/' . $this->_skinName . '/images/widget/counter.png')
				)->add(
					Core::factory('Core_Html_Entity_Div')
						->add(
							Core::factory('Core_Html_Entity_A')
								->id('widgetCounterOther')
								->href($sCounterHref)
								->value(Core::_('Counter.widget_detailed_statistics'))
						)
						->add(
							Core::factory('Core_Html_Entity_Script')
								->value("$('#widgetCounterOther').linkShortcut({path: '{$sCounterHref}', shortcutImg: '/modules/skin/{$this->_skinName}/images/module/{$this->_moduleName}.png', shortcutTitle: '" . Core::_('Counter.menu') . "', Minimize: true, Closable: true});")
						)
				)
		);

		if (!$ajax)
		{
			$oUser = Core_Entity::factory('User')->getCurrent();

			$oModule = Core_Entity::factory('Module')->getByPath($this->_moduleName);
			$module_id = $oModule->id;
			$oUser_Setting = $oUser->User_Settings->getByModuleIdAndTypeAndEntityId($module_id, 77, 0);

			if (is_null($oUser_Setting))
			{
				$oUser_Setting = Core_Entity::factory('User_Setting');
				$oUser_Setting->position_x = "'right'";
				$oUser_Setting->position_y = 400;
				$oUser_Setting->width = 250;
				$oUser_Setting->height = 160;
			}

			$oModalWindow
				->add(
					Core::factory('Core_Html_Entity_Script')
						->value("$(function(){
							$('#{$windowId}').widgetWindow({
								position: [{$oUser_Setting->position_x}, {$oUser_Setting->position_y}],
								width: {$oUser_Setting->width},
								height: {$oUser_Setting->height},
								moduleId: '{$module_id}',
								path: '/admin/index.php?ajaxWidgetLoad&moduleId={$module_id}&type=0'
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