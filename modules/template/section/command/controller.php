<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Core command controller.
 *
 * @package HostCMS
 * @subpackage Template
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Template_Section_Command_Controller extends Core_Command_Controller
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
			$iTemplateSectionId = Core_Array::getGet('template_section_id');
			if (!is_null($iTemplateSectionId))
			{
				$iTemplateSectionId = intval($iTemplateSectionId);
				$oTemplate_Section = Core_Entity::factory('Template_Section', $iTemplateSectionId);

				$oTemplate = $oTemplate_Section->Template;

				$bUserAccess = $oTemplate->checkUserAccess();

				if ($bUserAccess)
				{
					ob_start();
					$oTemplate->showSection($oTemplate_Section->alias);
					$result = ob_get_clean();
				}
				else
				{
					$result = 'Error: Access Forbidden';
				}
			}
			else
			{
				// Массив идентификаторов
				$aHostcmsSectionWidget = Core_Array::getPost('hostcmsSectionWidget');

				if (count($aHostcmsSectionWidget))
				{
					$sorting = 1;
					$result = "OK";

					foreach ($aHostcmsSectionWidget as $iTemplateSectionLibId)
					{
						$oTemplate_Section_Lib = Core_Entity::factory('Template_Section_Lib', $iTemplateSectionLibId);

						$bUserAccess = $oTemplate_Section_Lib->Template_Section->Template->checkUserAccess();

						if ($bUserAccess)
						{
							$oTemplate_Section_Lib
								->sorting($sorting * 10)
								->save();
						}
						else
						{
							$result = 'Error: Access Forbidden';
							break;
						}

						$sorting++;
					}
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