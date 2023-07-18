<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * XSL.
 *
 * @package HostCMS
 * @subpackage Xsl
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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
// var_dump('=======', count(Xsl_Stream_Import::getImported()));

			$oXslPanel = Core_Html_Entity::factory('Div')
				->class('hostcmsPanel')
				->style('margin-top: 40px; display: none');

			$oXslSubPanel = Core_Html_Entity::factory('Div')
				->class('hostcmsSubPanel hostcmsXsl');

			$oXsl = $object->getXsl();

			$sPath = '/admin/xsl/index.php';
			$sAdditional = "hostcms[action]=edit&xsl_dir_id={$oXsl->xsl_dir_id}&hostcms[checked][1][{$oXsl->id}]=1";

			$sTitle = Core::_('Xsl.panel_edit_xsl', $oXsl->name);

			$oXslSubPanel->add(
				Core_Html_Entity::factory('A')
					->href("{$sPath}?{$sAdditional}")
					->onclick("hQuery.openWindow({path: '{$sPath}', additionalParams: '{$sAdditional}', title: '" . Core_Str::escapeJavascriptVariable($sTitle) . "'}); return false")
					->add(
						Core_Html_Entity::factory('I')
							->id('hostcmsEditXsl')
							->class('fa-regular fa-file-code fa-fw')
							->title($sTitle)
					)
			);

			$iCount = $oCore_Registry->get('Xsl_Processor.count', 0) + 1;
			$oCore_Registry->set('Xsl_Processor.count', $iCount);

			ob_start();
			Core_Html_Entity::factory('Textarea')
					->readonly('readonly')
					->value($object->formatXml($object->getXml()))
					//->onclick('$(this).select()')
					->execute();
			$form_content = ob_get_clean();

			$sTitle = Core::_('Xsl.panel_edit_xml', $oXsl->name);
			$oXslSubPanel->add(
				Core_Html_Entity::factory('A')
					->onclick("hQuery.showWindow('xmlWindow{$iCount}', '" . Core_Str::escapeJavascriptVariable($form_content) . "', {width: 600, height: 450, title: '{$sTitle}'})")
					->add(
						Core_Html_Entity::factory('I')
							->id('hostcmsShowXml')
							->title($sTitle)
							->class('fa-solid fa-code fa-fw pointer')
					)
			);

			$oXslSubPanel->add(
				Core_Html_Entity::factory('Div')
					->class('hostcmsButton')
					->add(
						Core_Html_Entity::factory('Div')
							->value("{$oXsl->id}")
							->title('ID')
					)
			)->add(
				Core_Html_Entity::factory('Div')
					->class('hostcmsButton')
					->add(
						Core_Html_Entity::factory('Div')
							->value(
								Core::_('Xsl.panel_xsl_time', $iTime)
							)
					)
			)
			->add(
				Core_Html_Entity::factory('Div')
					->class('hostcmsButton')
					->add(
						Core_Html_Entity::factory('Div')
							->value(
								Core::_('Xsl.panel_xsl_size', number_format(mb_strlen($object->getXml()), 0, ',', ' '))
							)
					)
			);

			$aImported = Xsl_Stream_Import::getImported();

			foreach ($aImported as $oXsl)
			{
				$sAdditional = "hostcms[action]=edit&xsl_dir_id={$oXsl->xsl_dir_id}&hostcms[checked][1][{$oXsl->id}]=1";

				$sTitle = Core::_('Xsl.panel_edit_xsl', $oXsl->name);

				$oXslSubPanel->add(
					Core_Html_Entity::factory('Div')
						->class('hostcmsButton hostcmsButtonImported')
						->add(
							Core_Html_Entity::factory('A')
								->href("{$sPath}?{$sAdditional}")
								->onclick("hQuery.openWindow({path: '{$sPath}', additionalParams: '{$sAdditional}', title: '" . Core_Str::escapeJavascriptVariable($sTitle) . "'}); return false")
								->value($oXsl->id)
								->title($sTitle)
						)
				);
			}

			$oXslPanel
				->add($oXslSubPanel)
				->execute();
		}
	}
}