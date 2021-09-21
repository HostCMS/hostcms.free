<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Calendar_Caldav_Command_Controller
 *
 * @package HostCMS
 * @subpackage Calendar
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Calendar_Caldav_Command_Controller extends Core_Command_Controller
{
	/**
	 * Default controller action
	 * @return Core_Response
	 * @hostcms-event Cloud_Command_Controller.onBeforeShowAction
	 * @hostcms-event Cloud_Command_Controller.onAfterShowAction
	 */
	public function showAction()
	{
		$oSite = Core_Entity::factory('Site')->getByAlias(Core::$url['host']);

		Core_Event::notify(get_class($this) . '.onBeforeShowAction', $this);

		$oCore_Response = new Core_Response();
		$oCore_Response->header('X-Powered-By', 'HostCMS');

		$sCode = Core_Array::getRequest('code');

		if (!is_null($sCode) && $oSite)
		{
			$oCore_Response
				->status(200)
				->header('Content-Type', "text/html; charset={$oSite->coding}")
				->header('Last-Modified', gmdate('D, d M Y H:i:s', time()) . ' GMT');

			ob_start();
			?>

			<html>
				<head>
					<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
					<title><?php echo Core::_('Calendar.calendar_auth_code')?></title>

					<style>
					*{
						margin: 0px;
						padding: 0px;
					}
					body{
						text-align: center;
					}
					body, html{
						height: 100%;
						width: 100%;
					}
					.outer_div {
						align-items: center;
						display: flex;
						height: 100%;
						justify-content: center;
						margin: 0 auto;
						overflow: auto;
						width: 100%;
					}
					.inner_div {
						overflow: auto;
						padding: 5px;
					}
					.info p
					{
						margin-bottom: 50px;
						font-size: 16pt;
						color: #777;
						font-family: sans;
					}
					.code
					{
						display: inline-block;
						padding: 5px;
						border: 2px dotted #ccc;
						font-size: 30;
						color: #777;
						font-family: sans-serif;
					}
					</style>
				</head>
				<body>
					<div class="outer_div">
						<div class="inner_div">
							<div class="info">
								<p><?php echo Core::_('Calendar.calendar_auth_code_text')?></p>
							</div>
							<div class="code"><?php echo htmlspecialchars($sCode)?></div>
						</div>
					</div>
				</body>
			</html>

			<?php
			$oCore_Response->body(ob_get_clean());

			$aConfig = Core_Config::instance()->get('calendar_caldav_google_config', array());

			if (isset($aConfig['code']))
			{
				$aConfig['code'] = $sCode;

				Core_Config::instance()->set('calendar_caldav_google_config', $aConfig);
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