<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Core command controller.
 *
 * @package HostCMS
 * @subpackage Core\Command
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2019 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Template_Less_Command_Controller extends Core_Command_Controller
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
			$iTemplateId = Core_Array::getPost('template');
			if ($iTemplateId)
			{
				$oTemplate = Core_Entity::factory('Template', $iTemplateId);

				if ($oTemplate->less)
				{
					if ($oTemplate->checkUserAccess())
					{
						$less = $oTemplate->loadTemplateLessFile();

						if (strlen($less))
						{
							$variableName = preg_quote(Core_Array::getPost('name'), '/');
							$variableValue = preg_quote(Core_Array::getPost('value'), '/');

							//@color-rgba: rgba(232, 176, 21, 0.8);

							if (trim($variableValue) != '')
							{
								$less = preg_replace("/(@{$variableName})\s*:\s*.*?;/si", '${1}: ' . $variableValue . ';', $less);

								$oTemplate->saveTemplateLessFile($less);

								$oTemplate
									->rebuildCompressionCss()
									->updateTimestamp();

								$result = 'OK';
							}
							else
							{
								$result = 'Error';
							}
						}
					}
					else
					{
						$result = Core::_('Template.error_access_forbidden');
					}
				}
				else
				{
					$result = Core::_('Template.error_less_off');
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