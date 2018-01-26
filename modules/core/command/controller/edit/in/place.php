<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Core command controller.
 *
 * @package HostCMS
 * @subpackage Core\Command
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Core_Command_Controller_Edit_In_Place extends Core_Command_Controller
{
	/**
	 * Default controller action
	 * @return Core_Response
	 */
	public function showAction()
	{
		Core_Event::notify(get_class($this) . '.onBeforeShowAction', $this);

		$oCore_Response = new Core_Response();

		Core_Page::instance()
			->response($oCore_Response);
		
		$result = 'Error';

		if (Core::checkPanel())
		{
			$modelName = strval(Core_Array::getPost('entity'));
			$fieldName = strval(Core_Array::getPost('field'));
			$id = intval(Core_Array::getPost('id'));
			$value = Core_Array::getPost('value');

			if (class_exists($modelName . '_Model'))
			{
				$oEntity = Core_Entity::factory($modelName)->find($id);

				if (!is_null($oEntity->id))
				{
					$oUser = Core_Entity::factory('User')->getCurrent();

					if ($oUser && $oUser->checkObjectAccess($oEntity))
					{
						if (!is_null(Core_Array::getPost('loadValue')))
						{
							if (isset($oEntity->$fieldName))
							{
								$result = $oEntity->$fieldName;
							}
							elseif (method_exists($oEntity, $fieldName))
							{
								$result = $oEntity->$fieldName();
							}
						}
						else
						{
							if (!is_null($value))
							{
								$value = strval($value);

								// Backup revision
								if (Core::moduleIsActive('revision') && method_exists($oEntity, 'backupRevision'))
								{
									$oEntity->backupRevision();
								}

								if (isset($oEntity->$fieldName))
								{
									$oEntity->$fieldName = $value;
									$oEntity->save();
									$result = 'OK';
								}
								elseif (method_exists($oEntity, $fieldName))
								{
									$oEntity->$fieldName($value);
									$result = 'OK';
								}
							}
						}
					}
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

		Core_Event::notify(get_class($this) . '.onAfterShowAction', $this, array($oCore_Response));

		return $oCore_Response;
	}
}