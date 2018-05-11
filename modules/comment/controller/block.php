<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Comment_Controller_Block
 *
 * @package HostCMS
 * @subpackage Comment
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Comment_Controller_Block extends Admin_Form_Action_Controller
{
	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 * @return self
	 */
	public function execute($operation = NULL)
	{
		if (Core::moduleIsActive('ipaddress'))
		{
			$oComment = $this->_object;

			$bBlocked = $oComment->ip != '127.0.0.1'
				&& Ipaddress_Controller::instance()->isBlocked($oComment->ip);

			if (!$bBlocked)
			{
				$oIpaddress = Core_Entity::factory('Ipaddress');
				$oIpaddress->ip = $oComment->ip;
				$oIpaddress->deny_access = 1;
				$oIpaddress->comment = Core::_('Comment.ban_comment', $oComment->subject);
				$oIpaddress->save();
			}
		}

		return TRUE;
	}
}