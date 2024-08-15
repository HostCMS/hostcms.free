<?php
/**
 * Wysiwyg File Upload.
 *
 * @package HostCMS
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
require_once('../../bootstrap.php');

Core_Auth::authorization($sModule = 'wysiwyg');

$sAdminFormAction = '/admin/wysiwyg/upload.php';

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create();
$oAdmin_Form_Controller
	->module(Core_Module_Abstract::factory($sModule))
	->setUp()
	->path($sAdminFormAction);

$aJSON = array(
	'status' => 'error',
	'location' => ''
);

$modelName = Core_Array::getPost('entity_type', '', 'trim');
$id = Core_Array::getPost('entity_id', 0, 'int');

if ($modelName != '')
{
	if (class_exists($modelName . '_Model'))
	{
		$href = Wysiwyg_Controller::getTmpDirHref();
		$path = CMS_FOLDER . ltrim($href, DIRECTORY_SEPARATOR);

		if ($id)
		{
			$oEntity = Core_Entity::factory($modelName)->find($id);

			if (!is_null($oEntity->id))
			{
				$oUser = Core_Auth::getCurrentUser();

				// Get Module Name
				list($moduleName) = explode('_', $modelName);

				$oSite = Core_Entity::factory('Site', CURRENT_SITE);

				if ($oUser
					&& $oUser->checkModuleAccess(array($moduleName), $oSite)
					&& $oUser->checkObjectAccess($oEntity)
				)
				{
					$aTmp = Wysiwyg_Controller::getPathAndHref($oEntity);

					if (is_array($aTmp))
					{
						$href = $aTmp['href'];
						$path = $aTmp['path'];
					}
				}
			}
		}

		if (!is_null($href) && !is_null($path))
		{
			$filename = Core_Array::getPost('filename', '', 'trim');
			$aFile = Core_Array::getFiles('blob', '');

			if ($filename != '' && isset($aFile['tmp_name']))
			{
				if (Core_File::isValidExtension($filename, Core::$mainConfig['availableExtension']))
				{
					$ext = Core_File::getExtension($filename);
					$name = uniqid() . '.' . $ext;

					Core_File::upload($aFile['tmp_name'], $path . $name);

					$aJSON = array(
						'status' => 'success',
						'location' => '/' . ltrim($href, '/') . $name
					);
				}
			}
		}
	}
}

Core::showJson($aJSON);