<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Core command controller.
 *
 * @package HostCMS
 * @subpackage Core\Command
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Core_Command_Controller_Check_Bots extends Core_Command_Controller
{
	/**
	 * Default controller action
	 * @return Core_Response
	 * @hostcms-event Core_Command_Controller_Check_Bots.onBeforeShowAction
	 * @hostcms-event Core_Command_Controller_Check_Bots.onAfterShowAction
	 */
	public function showAction()
	{
		Core_Event::notify(get_class($this) . '.onBeforeShowAction', $this);

		$oCore_Response = new Core_Response();

		Core_Page::instance()
			->response($oCore_Response);

		$oCore_Response
			->header('Content-Type', "text/html; charset=UTF-8")
			->header('Last-Modified', gmdate('D, d M Y H:i:s', time()) . ' GMT')
			->header('X-Powered-By', 'HostCMS');

		$oSite = Core_Entity::factory('Site', CURRENT_SITE);

		if ($oSite->error_bot)
		{
			$oStructure = Core_Entity::factory('Structure')->find($oSite->error_bot);

			if (!is_null($oStructure->id))
			{
				// Если доступ к узлу структуры только по HTTPS, а используется HTTP,
				// то делаем 301 редирект
				if ($oStructure->https == 1 && !Core::httpsUses())
				{
					$url = Core::$url['host'] . $this->_uri;
					isset(Core::$url['query']) && $url .= '?' . Core::$url['query'];

					$oCore_Response
						->status(301)
						->header('Location', 'https://' . Core_Http::sanitizeHeader($url));

					return $oCore_Response;
				}
		
				define('CURRENT_STRUCTURE_ID', $oStructure->id);

				$oCore_Response->status(503);

				$oCore_Page = Core_Page::instance()/*->deleteChild()*/;

				$oStructure->setCorePageSeo($oCore_Page);

				if ($oStructure->type == 0)
				{
					$oTemplate = $oStructure->Document->Template;
				}
				// Если динамическая страница или типовая дин. страница
				elseif ($oStructure->type == 1 || $oStructure->type == 2)
				{
					$oTemplate = $oStructure->Template;
				}
				else
				{
					$oTemplate = NULL;
				}

				ob_start();

				if ($oStructure->type == 1)
				{
					$StructureConfig = $oStructure->getStructureConfigFilePath();

					if (Core_File::isFile($StructureConfig) && is_readable($StructureConfig))
					{
						include $StructureConfig;
					}
				}
				elseif ($oStructure->type == 2)
				{
					$oCore_Page->libParams
						= $oStructure->Lib->getDat($oStructure->id);

					$LibConfig = $oStructure->Lib->getLibConfigFilePath();
					if (Core_File::isFile($LibConfig) && is_readable($LibConfig))
					{
						include $LibConfig;
					}
				}

				$oCore_Page
					->addChild($oStructure->getRelatedObjectByType())
					->template($oTemplate)
					->addTemplates($oTemplate)
					->structure($oStructure)
					->execute();

				$oCore_Response->body(ob_get_clean());

				return $oCore_Response;
			}
		}

		$oCore_Response->status(503);

		$oCore_Response->body('Captcha is not configured, contact the administrator');

		Core_Event::notify(get_class($this) . '.onAfterShowAction', $this, array($oCore_Response));

		return $oCore_Response;
	}
}