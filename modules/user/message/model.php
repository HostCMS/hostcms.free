<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * User_Message_Model
 *
 * @package HostCMS
 * @subpackage User
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2016 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class User_Message_Model extends Core_Entity
{
	/**
	 * Disable markDeleted()
	 * @var mixed
	 */
	protected $_marksDeleted = NULL;

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'user' => array(),
	);

	/**
	 * Return number of seconds since last message
	 * @return int
	 */
	public function getLastMessage(User_Model $oUser)
	{
		$this->queryBuilder()
			->where('user_messages.recipient_user_id', '=', $oUser->id)
			->clearOrderBy()
			->orderBy('user_messages.id', 'DESC')
			->limit(1);

		$aUsers = $this->findAll(FALSE);

		return isset($aUsers[0])
			? time() - Core_Date::sql2timestamp($aUsers[0]->datetime)
			: NULL;
	}
}