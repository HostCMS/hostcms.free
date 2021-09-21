<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * XSL.
 *
 * @package HostCMS
 * @subpackage Xsl
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Xsl_Processor_Observer
{
	/**
	 * Xsl_Processor.onBeforeProcess event
	 * @param Xsl_Model $object
	 * @param array $args list of arguments
	 */
	static public function onBeforeProcess($object, $args)
	{
		Core_Registry::instance()
			->set('Xsl_Processor.onBeforeProcess', Core::getmicrotime());
	}

	/**
	 * Xsl_Processor.onBeforeProcess event
	 * @param Xsl_Model $object
	 * @param array $args list of arguments
	 */
	static public function onAfterProcess($object, $args)
	{
		$oCore_Registry = Core_Registry::instance();

		$iTime = Core::getmicrotime() - $oCore_Registry->get('Xsl_Processor.onBeforeProcess', 0);

		$oCore_Registry->set('Xsl_Processor.process',
			$oCore_Registry->get('Xsl_Processor.process', 0)
				+ $iTime
		);

		if (Core::checkPanel() && Core_Array::getSession('HOSTCMS_SHOW_XML'))
		{
			$oXslPanel = Core::factory('Core_Html_Entity_Div')
				->class('hostcmsPanel')
				->style('display: none');

			$oXslSubPanel = Core::factory('Core_Html_Entity_Div')
				->class('hostcmsSubPanel hostcmsXsl')
				->add(
					Core::factory('Core_Html_Entity_Img')
						->width(3)->height(16)
						->src('/hostcmsfiles/images/drag_bg.gif')
				);

			$oXsl = $object->getXsl();

			$sPath = '/admin/xsl/index.php';
			$sAdditional = "hostcms[action]=edit&xsl_dir_id={$oXsl->xsl_dir_id}&hostcms[checked][1][{$oXsl->id}]=1";

			$sTitle = Core::_('Xsl.panel_edit_xsl', $oXsl->name);

			$oXslSubPanel->add(
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

			$iCount = $oCore_Registry->get('Xsl_Processor.count', 0) + 1;
			$oCore_Registry->set('Xsl_Processor.count', $iCount);

			ob_start();
			Core::factory('Core_Html_Entity_Textarea')
					->readonly('readonly')
					->value($object->formatXml($object->getXml()))
					//->onclick('$(this).select()')
					->execute();
			$form_content = ob_get_clean();

			$sTitle = Core::_('Xsl.panel_edit_xml', $oXsl->name);
			$oXslSubPanel->add(
				Core::factory('Core_Html_Entity_A')
					->onclick("hQuery.showWindow('xmlWindow{$iCount}', '" . Core_Str::escapeJavascriptVariable($form_content) . "', {width: 600, height: 450, title: '{$sTitle}'})")
					->add(
						Core::factory('Core_Html_Entity_Img')
							->src('/hostcmsfiles/images/xml.gif')
							->id('hostcmsShowXml')
							->alt($sTitle)
							->title($sTitle)
							->class('pointer')
					)
			);

			$oXslSubPanel->add(
				Core::factory('Core_Html_Entity_Div')
					->class('hostcmsButton')
					->add(
						Core::factory('Core_Html_Entity_Img')
							->src('/hostcmsfiles/images/time.png')
					)
					->add(
						Core::factory('Core_Html_Entity_Div')
							->value(
								Core::_('Xsl.panel_xsl_time', $iTime)
							)
					)
			)
			->add(
				Core::factory('Core_Html_Entity_Div')
					->class('hostcmsButton')
					->add(
						Core::factory('Core_Html_Entity_Img')
							->src('/hostcmsfiles/images/size.png')
					)
					->add(
						Core::factory('Core_Html_Entity_Div')
							->value(
								Core::_('Xsl.panel_xsl_size', number_format(mb_strlen($object->getXml()), 0, ',', ' '))
							)
					)
			)->add(
				Core::factory('Core_Html_Entity_Div')
					->class('hostcmsButton')
					->add(
						Core::factory('Core_Html_Entity_Div')
							->value("ID {$oXsl->id}")
					)
			);

			$oXslPanel
				->add($oXslSubPanel)
				->execute();
		}
	}
}