<?php
/**
 * Market.
 *
 * @package HostCMS
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2019 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
require_once('../../bootstrap.php');

Core_Auth::authorization($sModule = 'market');

// Код формы
$iAdmin_Form_Id = 210;
$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

$sAdminFormAction = '/admin/market/index.php';

$category_id = intval(Core_Array::getRequest('category_id'));

$sQuery = trim(Core_Str::stripTags(strval(Core_Array::getRequest('search_query'))));

$additionalParam = '';

$additionalParam .= $category_id ? 'category_id=' . $category_id : '';
$additionalParam .= $sQuery ? '&search_query=' . $sQuery . '&hostcms[action]=sendSearchQuery' : '';

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title(Core::_('Market.title'))
	->setAdditionalParam($additionalParam);

ob_start();

$oMarket_Controller = Market_Controller::instance();
$oMarket_Controller
	->controller($oAdmin_Form_Controller)
	->setMarketOptions()
	->category_id($category_id)
	->page($oAdmin_Form_Controller->getCurrent());

if ($oAdmin_Form_Controller->getAction() == 'sendSearchQuery'
	&& !is_null(Core_Array::getRequest('search_query')))
{
	$oMarket_Controller->search($sQuery);
}

$oAdmin_View = Admin_View::create()->module(Core_Module::factory($sModule));

$category_id
	&& $oMarket_Controller->order('price');

// Установка модуля
if (Core_Array::getRequest('install'))
{
	// Текущий пользователь
	$oUser = Core_Auth::getCurrentUser();

	if (defined('READ_ONLY') && READ_ONLY || $oUser->read_only && !$oUser->superuser)
	{
		Core_Message::show(Core::_('User.demo_mode'), 'error');
	}
	else
	{
		$aActions = array();

		$aAdmin_Form_Actions = $oAdmin_Form->Admin_Form_Actions->getAllowedActionsForUser($oUser);

		// Проверка на право доступа к действию
		foreach ($aAdmin_Form_Actions as $oAdmin_Form_Action)
		{
			$aActions[] = $oAdmin_Form_Action->name;
		}

		$oAction = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
			->Admin_Form_Actions
			->getByName('installModule');

		if ($oAction && in_array('installModule', $aActions))
		{
			try
			{
				$oModule = $oMarket_Controller->getModule(intval(Core_Array::getRequest('install')));

				if (is_object($oModule))
				{
					$oAdminModule = Core_Entity::factory('Module')->getByPath($oModule->path, FALSE);

					if (is_null($oAdminModule))
					{
						// Устанавливать сейчас
						$bInstall = FALSE;

						// Читаем modules.xml
						$oModuleXml = $oMarket_Controller->parseModuleXml();

						if (is_object($oModuleXml))
						{
							$aXmlFields = $oModuleXml->xpath("fields/field");

							if (count($aXmlFields))
							{
								// Вывод списка опций
								if ($oAdmin_Form_Controller->getAction() != 'sendOptions')
								{
									$oMarket_Controller->showModuleOptions();

									$sTitle = Core::_('Market.module-options', $oModule->name);
									$oAdmin_View->pageTitle($sTitle);
									$oMarket_Controller->controller->title($sTitle);
								}
								// Применение списка преданных опций
								else
								{
									// было применение опций
									$oMarket_Controller->applyModuleOptions();

									$bInstall = TRUE;
								}
							}
							else
							{
								// Устанавливаем сразу без опций модуля
								$bInstall = TRUE;
							}
						}
						else
						{
							// Устанавливаем сразу без опций модуля
							$bInstall = TRUE;
						}

						if ($bInstall)
						{
							$oMarket_Controller->install();

							$oAdmin_View->addMessage(
								Core_Message::get(Core::_('Market.install_success', $oModule->name))
							);

							// Вывод списка
							$oMarket_Controller
								->getMarket()
								->showItemsList();
						}
					}
					else
					{
						$oAdmin_View->addMessage(
							Core_Message::get(Core::_('Market.module-was-installed', $oModule->name), 'error')
						);

						// Вывод списка
						$oMarket_Controller
							->getMarket()
							->showItemsList();
					}

					// Reload list of sites
					$oAdmin_View->addMessage('<script type="text/javascript">$.loadSiteList()</script>');
				}
				else
				{
					$oAdmin_View->addMessage(
						Core_Message::get(Core::_('Market.server_error_respond_12'), 'error')
					);
				}
			}
			catch (Exception $e)
			{
				$oAdmin_View->addMessage(
					Core_Message::get($e->getMessage(), 'error')
				);

				// Вывод списка
				$oMarket_Controller
					->getMarket()
					->showItemsList();
			}
		}
		else
		{
			Core_Message::show(Core::_('Admin_Form.msg_error_access'), 'error');
		}
	}
}
else
{
	try
	{
		$oAdmin_View->pageTitle(Core::_('Market.title'));

		// Вывод списка
		$oMarket_Controller
			->getMarket()
			->showItemsList();
	}
	catch (Exception $e)
	{
		$oAdmin_View->addMessage(
			Core_Message::get($e->getMessage(), 'error')
		);
	}
}

$oAdmin_View->content(ob_get_clean());

ob_start();
$oAdmin_View->show();

Core_Skin::instance()
	->answer()
	->ajax(Core_Array::getRequest('_', FALSE))
	->content(ob_get_clean())
	->message($oAdmin_View->message)
	->title(Core::_('Market.title'))
	->module($sModule)
	->execute();