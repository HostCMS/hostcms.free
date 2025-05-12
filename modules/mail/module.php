<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Mail Module.
 *
 * @package HostCMS
 * @subpackage Mail
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
 */
class Mail_Module extends Core_Module_Abstract
{
	/**
	 * Module version
	 * @var string
	 */
	public $version = '7.1';

	/**
	 * Module date
	 * @var date
	 */
	public $date = '2025-04-04';

	/**
	 * Module name
	 * @var string
	 */
	protected $_moduleName = 'mail';

	/**
	 * Get List of Schedule Actions
	 * @return array
	 */
	public function getScheduleActions()
	{
		return array(
			0 => array(
				'name' => 'receive',
				'entityCaption' => Core::_('Mail.entityCaption')
			)
		);
	}

	/**
	 * Get Module's Menu
	 * @return array
	 */
	public function getMenu()
	{
		$this->menu = array(
			array(
				'sorting' => 260,
				'block' => 3,
				'ico' => 'fa-solid fa-envelope-o',
				'name' => Core::_('Mail.menu'),
				'href' => Admin_Form_Controller::correctBackendPath("/{admin}/mail/index.php"),
				'onclick' => Admin_Form_Controller::correctBackendPath("$.adminLoad({path: '/{admin}/mail/index.php'}); return false")
			)
		);

		return parent::getMenu();
	}

	/**
	 * Notify module on the action on schedule
	 * @param Schedule_Model $oSchedule
	 */
	public function callSchedule($oSchedule)
	{
		$action = $oSchedule->action;
		$entityId = $oSchedule->entity_id;

		if ($entityId)
		{
			switch ($action)
			{
				// Recieve mail
				case 0:
					$aMails = Core_Entity::factory('Mail')->getAllBySite_id($entityId, FALSE);

					foreach ($aMails as $oMail)
					{
						if ($oMail->create_leads && Core::moduleIsActive('lead') && $oMail->lead_days)
						{
							$date = date("d M Y", strtotime("-{$oMail->lead_days} days"));

							$oMail
								->deleteMessages(FALSE)
								->search('SINCE ' . $date . ' UNSEEN')
								->receive();
						}
					}
				break;
			}
		}
	}
}