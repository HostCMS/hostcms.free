<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Mail Module.
 *
 * @package HostCMS
 * @subpackage Mail
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Mail_Module extends Core_Module_Abstract
{
	/**
	 * Module version
	 * @var string
	 */
	public $version = '7.0';

	/**
	 * Module date
	 * @var date
	 */
	public $date = '2024-06-06';

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
				'href' => "/admin/mail/index.php",
				'onclick' => "$.adminLoad({path: '/admin/mail/index.php'}); return false"
			)
		);

		return parent::getMenu();
	}

	/**
	 * Notify module on the action on schedule
	 * @param int $action action number
	 * @param int $entityId entity ID
	 * @return array
	 */
	public function callSchedule($action, $entityId)
	{
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