<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * TPL.
 *
 * @package HostCMS
 * @subpackage Tpl
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Tpl_Processor_Observer
{
	/**
	 * Tpl_Processor.onBeforeProcess event
	 * @param Tpl_Model $object
	 * @param array $args list of arguments
	 */
	static public function onBeforeProcess($object, $args)
	{
		Core_Registry::instance()
			->set('Tpl_Processor.onBeforeProcess', Core::getmicrotime());
	}

	/**
	 * Tpl_Processor.onBeforeProcess event
	 * @param Tpl_Model $object
	 * @param array $args list of arguments
	 */
	static public function onAfterProcess($object, $args)
	{
		$oCore_Registry = Core_Registry::instance();

		$iTime = Core::getmicrotime() - $oCore_Registry->get('Tpl_Processor.onBeforeProcess', 0);

		$oCore_Registry->set('Tpl_Processor.process',
			$oCore_Registry->get('Tpl_Processor.process', 0)
				+ $iTime
		);

		if (Core::checkPanel() && Core_Array::getSession('HOSTCMS_SHOW_XML'))
		{
			$oTplPanel = Core::factory('Core_Html_Entity_Div')
				->class('hostcmsPanel')
				->style('display: none');

			$oTplSubPanel = Core::factory('Core_Html_Entity_Div')
				->class('hostcmsSubPanel hostcmsXsl')
				->add(
					Core::factory('Core_Html_Entity_Img')
						->width(3)->height(16)
						->src('/hostcmsfiles/images/drag_bg.gif')
				);

			$oTpl = $object->getTpl();

			$sPath = '/admin/tpl/index.php';
			$sAdditional = "hostcms[action]=edit&tpl_dir_id={$oTpl->tpl_dir_id}&hostcms[checked][1][{$oTpl->id}]=1";

			$sTitle = Core::_('Tpl.panel_edit_tpl', $oTpl->name);

			$oTplSubPanel->add(
				Core::factory('Core_Html_Entity_A')
					->href("{$sPath}?{$sAdditional}")
					->onclick("hQuery.openWindow({path: '{$sPath}', additionalParams: '{$sAdditional}', title: '" . Core_Str::escapeJavascriptVariable($sTitle) . "'}); return false")
					->add(
						Core::factory('Core_Html_Entity_Img')
							->width(16)->height(16)
							->src('/hostcmsfiles/images/xsl_edit.gif')
							->id('hostcmsEditXsl')
							->alt($sTitle)
							->title($sTitle)
					)
			);

			$iCount = $oCore_Registry->get('Tpl_Processor.count', 0) + 1;
			$oCore_Registry->set('Tpl_Processor.count', $iCount);

			ob_start();
			$object->format();
			$content = ob_get_clean();

			$sTitle = Core::_('Tpl.panel_show_vars', $oTpl->name);
			$oTplSubPanel->add(
				Core::factory('Core_Html_Entity_A')
					->onclick("hQuery.showWindow('xmlWindow{$iCount}', '" . Core_Str::escapeJavascriptVariable($content) . "', {width: 600, height: 350, title: '{$sTitle}'})")
					->add(
						Core::factory('Core_Html_Entity_Img')
							->src('/hostcmsfiles/images/xml.gif')
							->id('hostcmsShowXml')
							->alt($sTitle)
							->title($sTitle)
							->class('pointer')
					)
			);

			$oTplSubPanel->add(
				Core::factory('Core_Html_Entity_Div')
					->class('hostcmsButton')
					->add(
						Core::factory('Core_Html_Entity_Img')
							->src('/hostcmsfiles/images/time.png')
					)
					->add(
						Core::factory('Core_Html_Entity_Div')
							->value(
								Core::_('Tpl.panel_tpl_time', $iTime)
							)
					)
			)->add(
				Core::factory('Core_Html_Entity_Div')
					->class('hostcmsButton')
					->add(
						Core::factory('Core_Html_Entity_Div')
							->value("ID {$oTpl->id}")
					)
			);

			$oTplPanel
				->add($oTplSubPanel)
				->execute();
		}
	}
}