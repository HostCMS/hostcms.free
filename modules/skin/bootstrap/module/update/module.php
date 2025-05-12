<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Update. Backend's Index Pages and Widget.
 *
 * @package HostCMS
 * @subpackage Skin
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
 */
class Skin_Bootstrap_Module_Update_Module extends Update_Module
{
	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();

		$this->_adminPages = array(
			1 => array('title' => Core::_('Update.menu'))
		);
	}

	public function widget()
	{
		try
		{
			$aUpdates = Update_Controller::instance()->parseUpdates();

			$iUpdateCounts = count($aUpdates['entities']);

			$error = $aUpdates['error'];

			$oModule = Core_Entity::factory('Module')->getByPath('update');

			$aUsers = Core_Entity::factory('User')->getAllBySuperuser(1, FALSE);

			if (!$error && $iUpdateCounts && Core::moduleIsActive('notification'))
			{
				$oUpdate = end($aUpdates['entities']);

				$oNotification = Core_Entity::factory('Notification')->setMarksDeleted(NULL)->getNotification($oModule->id, 0, $oUpdate->id);

				if (is_null($oNotification))
				{
					$oNotification = Core_Entity::factory('Notification')
						->title(Core::_('Update.add_system_notification', $oUpdate->name))
						->description(Core::_('Update.system_notification_description', $oUpdate->name))
						->datetime(Core_Date::timestamp2sql(time()))
						->module_id($oModule->id)
						->type(0) // 0 - Системное обновление
						->entity_id($oUpdate->id)
						->save();

					// Связываем уведомление с сотрудниками
					foreach ($aUsers as $oUser)
					{
						$oUser->add($oNotification);
					}
				}
			}

			$aModuleUpdates = Update_Controller::instance()->parseModules();

			if (count($aModuleUpdates) && Core::moduleIsActive('notification'))
			{
				foreach ($aModuleUpdates as $oModuleUpdate)
				{
					$oNotificationModule = Core_Entity::factory('Notification')->setMarksDeleted(NULL)->getNotification($oModule->id, 1, $oModuleUpdate->id);

					if (is_null($oNotificationModule))
					{
						$oNotificationModule = Core_Entity::factory('Notification')
							->title(Core::_('Update.add_module_notification', $oModuleUpdate->name))
							->description(Core::_('Update.module_notification_description', $oModuleUpdate->number))
							->datetime(Core_Date::timestamp2sql(time()))
							->module_id($oModule->id)
							->type(1) // 1 - Обновление модуля
							->entity_id($oModuleUpdate->id)
							->save();

						// Связываем уведомление с сотрудниками
						foreach ($aUsers as $oUser)
						{
							$oUser->add($oNotificationModule);
						}
					}
				}
			}

			?><!-- Update -->
			<div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
				<div class="databox radius-bordered databox-shadowed hostcms-widget-databox">
					<div class="databox-left bg-themethirdcolor">
						<div class="databox-piechart">
							<a href="<?php echo Admin_Form_Controller::correctBackendPath('/{admin}/update/index.php')?>" onclick="$.adminLoad({path: hostcmsBackend + '/update/index.php'}); return false"><i class="fa-solid fa-rotate fa-3x"></i></a>
						</div>
					</div>
					<div class="databox-right">
						<span class="databox-number themethirdcolor">
							<?php
							if (!$error && $iUpdateCounts)
							{
								echo $iUpdateCounts;
							}
							?>
						</span>
						<div class="databox-text <?php echo $error ? 'databox-small' : ''?>"><?php
							if ($error > 0)
							{
								$sDatetime = !is_null($aUpdates['datetime'])
									? Core_Date::strftime(DATE_TIME_FORMAT, strtotime($aUpdates['datetime']))
									: '';

								echo Core_Str::cutSentences(
									Core::_('Update.server_error_respond_' . $error, $sDatetime), 120
								);
							}
							elseif ($iUpdateCounts == 0)
							{
								echo Core::_('Update.isLastUpdate');
							}
							else
							{
								echo Core_Inflection::getPlural(Core::_('Update.update'), $iUpdateCounts, 'ru');
							}
						?></div>
						<div class="databox-stat themethirdcolor radius-bordered">
							<i class="stat-icon icon-lg fa-solid fa-rotate"></i>
						</div>
					</div>
				</div>
			</div>
			<?php
		}
		catch (Exception $e) {}
	}
}