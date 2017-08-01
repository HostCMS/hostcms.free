<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Core command controller.
 *
 * @package HostCMS
 * @subpackage Core\Command
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Template_Section_Lib_Command_Controller extends Core_Command_Controller
{
	/**
	 * Default controller action
	 * @return Core_Response
	 */
	public function showAction()
	{
		Core_Event::notify(get_class($this) . '.onBeforeShowAction', $this);

		$oCore_Response = new Core_Response();

		$result = 'Error';

		if (Core::checkPanel())
		{
			$iTemplateSectionLibId = intval(Core_Array::getGet('template_section_lib_id'));
			$oTemplate_Section_Lib = Core_Entity::factory('Template_Section_Lib', $iTemplateSectionLibId);

			$bUserAccess = $oTemplate_Section_Lib->Template_Section->Template->checkUserAccess();

			if ($bUserAccess)
			{
				$active = Core_Array::getGet('active');
				$delete = Core_Array::getGet('delete');

				if (!is_null($active))
				{
					$oTemplate_Section_Lib
						->active($active)
						->save();

					ob_start();
					$oTemplate_Section_Lib->execute();
					$result = ob_get_clean();
				}
				elseif(!is_null($delete))
				{
					$oTemplate_Section = $oTemplate_Section_Lib->Template_Section;

					// Delete
					$oTemplate_Section_Lib->markDeleted();

					// New section content
					ob_start();
					$oTemplate_Section->Template->showSection($oTemplate_Section->alias);
					$result = ob_get_clean();
				}
			}

			$oCore_Response
				->status(200)
				->header('Pragma', 'no-cache')
				->header('Cache-Control', 'private, no-cache')
				->header('Vary', 'Accept')
				->header('Last-Modified', gmdate('D, d M Y H:i:s', time()) . ' GMT')
				->header('X-Powered-By', 'HostCMS')
				->body(json_encode($result));

			if (strpos(Core_Array::get($_SERVER, 'HTTP_ACCEPT', ''), 'application/json') !== FALSE)
			{
				$oCore_Response->header('Content-type', 'application/json; charset=utf-8');
			}
			else
			{
				$oCore_Response
					->header('X-Content-Type-Options', 'nosniff')
					->header('Content-type', 'text/plain; charset=utf-8');
			}
		}
		else
		{
			$oCore_Response->status(404);
		}

		Core_Event::notify(get_class($this) . '.onAfterShowAction', $this, array($oCore_Response));

		return $oCore_Response;
	}
}