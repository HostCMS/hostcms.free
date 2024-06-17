<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * User. Backend's Index Pages and Widget.
 *
 * @package HostCMS
 * @subpackage Skin
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Skin_Bootstrap_Module_User_Module extends User_Module
{
	/**
	 * Name of the skin
	 * @var string
	 */
	protected $_skinName = 'bootstrap';

	/**
	 * Name of the module
	 * @var string
	 */
	protected $_moduleName = 'user';

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();

		$this->_adminPages = array(
			0 => array('title' => Core::_('User.title'))
		);
	}

	/**
	 * Show admin widget
	 * @param int $type
	 * @param boolean $ajax
	 * @return self
	 */
	public function adminPage($type = 0, $ajax = FALSE)
	{
		$type = intval($type);

		$oModule = Core_Entity::factory('Module')->getByPath($this->_moduleName);

		switch ($type)
		{
			// Список пользователей
			case 77:
				$oUsers = Core_Entity::factory('User');

				$oCurrentUser = $oUsers->getCurrent();

				Core_Session::close();

				$aJson = array();

				if (!is_null($oCurrentUser))
				{
					$oUsers->queryBuilder()
						->where('users.active', '=', 1)
						->where('users.id', '!=', $oCurrentUser->id)
						->leftJoin('user_messages', 'users.id', '=', 'user_messages.user_id',
							array(
								array('AND' => array('user_messages.recipient_user_id', '=', $oCurrentUser->id)),
								array('OR' => array('users.id', '=', Core_QueryBuilder::expression('`user_messages`.`recipient_user_id`'))),
								array('AND' => array('user_messages.user_id', '=', $oCurrentUser->id)),
							)
						)
						->groupBy('users.id')
						->clearOrderBy()
						->orderBy('user_messages.datetime', 'DESC')
						->orderBy('users.id', 'DESC');

					$aUsers = $oUsers->findAll();

					foreach ($aUsers as $oUser)
					{
						$iLastMessageTime = $oCurrentUser->User_Messages->getLastMessage($oUser);

						$aJson[] = array(
							'id' => $oUser->id,
							'login' => $oUser->login,
							'firstName' => $oUser->name,
							'lastName' => $oUser->surname,
							'avatar' => $oUser->getAvatar(),
							'lastChatTime' => Core_Date::time2string($iLastMessageTime),
							'lastActivity' => Core_Date::time2string($oUser->getLastActivity()),
							'online' => intval($oUser->isOnline()),
							// Количество непрочитанных сообщений
							'count_unread' => $oCurrentUser->getUnreadCount($oUser),
						);
					}
				}

				Core::showJson($aJson);
			break;
			// Список сообщений
			case 78:
				$oUsers = Core_Entity::factory('User');
				$oCurrentUser = $oUsers->getCurrent();

				Core_Session::close();

				$iRecipientUserId = Core_Array::getPost('user-id', 0);

				$aJson = array();

				$iLimit = 10;

				if (!is_null($oCurrentUser) && $iRecipientUserId)
				{
					$aJson['user-info'] = array(
						'id' => $oCurrentUser->id,
						'login' => $oCurrentUser->login,
						'firstName' => strval($oCurrentUser->name),
						'lastName' => strval($oCurrentUser->surname),
					);

					$oRecipient_User = Core_Entity::factory('User')->getById($iRecipientUserId);

					if (!is_null($oRecipient_User))
					{
						$iLastMessageTime = $oCurrentUser->User_Messages->getLastMessage($oRecipient_User);

						$aJson['recipient-user-info'] = array(
							'id' => $oRecipient_User->id,
							'login' => $oRecipient_User->login,
							'firstName' => strval($oRecipient_User->name),
							'lastName' => strval($oRecipient_User->surname),
							'avatar' => $oRecipient_User->getAvatar(),
							'lastChatTime' => Core_Date::time2string($iLastMessageTime),
							'lastActivity' => Core_Date::time2string($oRecipient_User->getLastActivity()),
							'online' => intval($oRecipient_User->isOnline()),
						);
					}

					// Load model columns BEFORE FOUND_ROWS()
					Core_Entity::factory('User_Message')->getTableColumns();

					$oUser_Messages = Core_Entity::factory('User_Message');

					$iFirstMessageId = Core_Array::getGet('first_message_id', 0);
					$iFirstMessageId && $oUser_Messages->queryBuilder()
						->where('user_messages.id', '<', $iFirstMessageId);

					$oUser_Messages->queryBuilder()
						->sqlCalcFoundRows()
						->open()
							->where('user_messages.user_id', '=', $oCurrentUser->id)
							->where('user_messages.recipient_user_id', '=', $iRecipientUserId)
							->setOr()
							->where('user_messages.user_id', '=', $iRecipientUserId)
							->where('user_messages.recipient_user_id', '=', $oCurrentUser->id)
						->close()
						->clearOrderBy()
						->orderBy('user_messages.datetime', 'DESC')
						->orderBy('user_messages.id', 'DESC')
						->limit($iLimit);

					$aUser_Messages = $oUser_Messages->findAll(FALSE);

					$aJson['total_messages'] = Core_QueryBuilder::select()->getFoundRows();

					foreach ($aUser_Messages as $oUser_Message)
					{
						$aJson['messages'][] = array(
							'id' => $oUser_Message->id,
							'user_id' => $oUser_Message->user_id,
							'recipient_user_id' => $oUser_Message->recipient_user_id,
							'datetime' => Core_Date::sql2datetime($oUser_Message->datetime),
							'text' => nl2br(htmlspecialchars($oUser_Message->text)),
							'read' => intval($oUser_Message->read),
						);

						/* if ($oUser_Message->recipient_user_id == $oCurrentUser->id && !$oUser_Message->read)
						{
							$oUser_Message->read = 1;
							$oUser_Message->alert = 0;
							$oUser_Message->save();
						} */
					}

					// Количество непрочитанных пользователем сообщений
					$aJson['count_unread'] = $oCurrentUser->getUnreadCount($oRecipient_User);

					// Сообщение для непрочитанных
					$aJson['count_unread_message'] = Core::_('User.chat_count_unread_message', $aJson['count_unread']);

					Core::showJson($aJson);
				}
			break;

			// Добавление сообщения
			case 79:
				$sMessageText = trim(strval(Core_Array::getPost('message')));

				if (strlen($sMessageText) && Core_Array::getPost('recipient-user-id'))
				{
					$aJson = array();

					$oCurrentUser = Core_Auth::getCurrentUser();
					Core_Session::close();

					if (!is_null($oCurrentUser))
					{
						$iRecipientUserId = intval(Core_Array::getPost('recipient-user-id'));

						/* $dateTime = date("Y-m-d H:i:s");						
						var_dump($dateTime); */

						$oUser_Message = Core_Entity::factory('User_Message');
						$oUser_Message->user_id = $oCurrentUser->id;
						$oUser_Message->recipient_user_id = $iRecipientUserId;
						$oUser_Message->datetime = date("Y-m-d H:i:s");
						$oUser_Message->text = $sMessageText;
						$oUser_Message->read = 0;
						$oUser_Message->alert = 1;
						$oUser_Message->save();

						$aJson['answer'] = array('OK');

						$aJson['user-info'] = array(
							'id' => $oCurrentUser->id,
							'login' => $oCurrentUser->login,
							'firstName' => strval($oCurrentUser->name),
							'lastName' => strval($oCurrentUser->surname),
						);

						$aJson['message'] = array(
							'id'=> $oUser_Message->id,
							'datetime' => Core_Date::sql2datetime($oUser_Message->datetime),
						);

						$oCurrentUser->updateLastActivity();
					}
					Core::showJson($aJson);
				}
			break;
			// Уведомления
			case 80:
				$oCurrentUser = Core_Auth::getCurrentUser();

				Core_Session::close();

				if (!is_null($oCurrentUser))
				{
					$aJson = array();

					$oUser_Messages = Core_Entity::factory('User_Message');
					$oUser_Messages->queryBuilder()
						->where('user_messages.recipient_user_id', '=', $oCurrentUser->id)
						->where('user_messages.read', '=', 0)
						->where('user_messages.alert', '=', 1)
						->limit(1)
						->clearOrderBy()
						->orderBy('user_messages.id', 'DESC');

					$aUser_Messages = $oUser_Messages->findAll(FALSE);

					if (count($aUser_Messages))
					{
						$oUser_Message = $aUser_Messages[0];

						$aJson['info'] = array(
							'user_id' => $oUser_Message->User->id,
							'avatar' => $oUser_Message->User->getAvatar(),
							'text' => Core::_('User.new_message_from', $oUser_Message->User->name, $oUser_Message->User->surname, FALSE),
							'sound' => intval($oCurrentUser->sound),
						);

						if (Core_Array::getPost('alert') == 1)
						{
							$oUser_Message->alert = 0;
							$oUser_Message->save();

							$aJson['alert'] = 0;
						}
					}

					// Общее количество непрочитанных пользователем сообщений, идет в верхнюю панель
					$oCore_QueryBuilder_Select = Core_QueryBuilder::select()
						->select(array(Core_QueryBuilder::expression('COUNT(*)'), 'count'))
						->from('user_messages')
						->where('user_messages.read', '=', 0)
						->where('user_messages.recipient_user_id', '=', $oCurrentUser->id);

					$row = $oCore_QueryBuilder_Select->execute()->asAssoc()->current();

					$aJson['count'] = intval($row['count']);
				}

				Core::showJson($aJson);
			break;
			// Обновление списка сообщений
			case 81:
				$oCurrentUser = Core_Auth::getCurrentUser();

				$iLastMessageId = intval(Core_Array::getPost('last-message-id', 0));
				$iRecipientUserId = Core_Array::getPost('recipient-user-id', 0);

				Core_Session::close();

				$aJson = array();

				if (!is_null($oCurrentUser) && $iRecipientUserId)
				{
					$aJson['user-info'] = array(
						'id' => $oCurrentUser->id,
						'login' => $oCurrentUser->login,
						'firstName' => $oCurrentUser->name,
						'lastName' => $oCurrentUser->surname,
					);

					$oRecipient_User = Core_Entity::factory('User')->getById($iRecipientUserId);

					if (!is_null($oRecipient_User))
					{
						$aJson['recipient-user-info'] = array(
							'id' => $oRecipient_User->id,
							'login' => $oRecipient_User->login,
							'firstName' => $oRecipient_User->name,
							'lastName' => $oRecipient_User->surname,
							'avatar' => $oRecipient_User->getAvatar(),
							'online' => intval($oRecipient_User->isOnline()),
						);
					}

					$oUser_Messages = Core_Entity::factory('User_Message');
					$oUser_Messages->queryBuilder()
						->open()
							->where('user_messages.user_id', '=', $oCurrentUser->id)
							->where('user_messages.recipient_user_id', '=', $iRecipientUserId)
							->setOr()
							->where('user_messages.user_id', '=', $iRecipientUserId)
							->where('user_messages.recipient_user_id', '=', $oCurrentUser->id)
						->close()
						->where('user_messages.id', '>', $iLastMessageId)
						//->where('user_messages.user_id', '=', $iRecipientUserId)
						//->where('user_messages.recipient_user_id', '=', $oCurrentUser->id)
						->clearOrderBy()
						->orderBy('user_messages.id', 'ASC');

					$aUser_Messages = $oUser_Messages->findAll(FALSE);

					foreach ($aUser_Messages as $oUser_Message)
					{
						$aJson['messages'][] = array(
							'id' => $oUser_Message->id,
							'user_id' => $oUser_Message->user_id,
							'recipient_user_id' => $oUser_Message->recipient_user_id,
							'datetime' => Core_Date::sql2datetime($oUser_Message->datetime),
							'text' => nl2br(htmlspecialchars($oUser_Message->text)),
							'read' => intval($oUser_Message->read),
						);
					}
				}

				Core::showJson($aJson);
			break;
			// Обновление статусов
			case 82:
				$oCurrentUser = Core_Auth::getCurrentUser();

				Core_Session::close();

				$aJson = array();

				$aUsers = Core_Entity::factory('User')->getAllByActive(1);
				foreach ($aUsers as $oUser)
				{
					$aJson[$oUser->id] = array(
						'status' => intval($oUser->isOnline()),
						'lastActivity' => Core_Date::time2string($oUser->getLastActivity()),
						'count_unread' => $oCurrentUser->getUnreadCount($oUser)
					);
				}

				Core::showJson($aJson);
			break;

			// "Делаем" сообщение прочитанным
			case 83:

				//$iMessageId = intval(Core_Array::getPost('message-id', 0));

				$aJson = array();

				$aMessagesId = Core_Array::getPost('messagesId', 0);

				if (is_array($aMessagesId))
				{
					foreach ($aMessagesId as $iMessageId)
					{
						$oUser_Message = Core_Entity::factory('User_Message')->find($iMessageId);

						if (!is_null($oUser_Message))
						{
							$oUser_Message->read = 1;
							$oUser_Message->alert = 0;
							$oUser_Message->save();

							//$aJson['answer'] = array($oUser_Message->id);
							$aJson['answer'][] = $oUser_Message->id;
						}
					}
				}

				Core::showJson($aJson);
			break;
			// Включение/отключение звука
			case 84:
				$oCurrentUser = Core_Auth::getCurrentUser();

				$iSoundSwitchStatus = intval(Core_Array::getPost('sound_switch_status', 0));

				Core_Session::close();

				$aJson = array();

				if ($oCurrentUser && $iSoundSwitchStatus)
				{
					$oCurrentUser
						->sound(1 - $oCurrentUser->sound)
						->save();

					$aJson['answer'] = array($oCurrentUser->sound);
				}

				Core::showJson($aJson);
			break;
			// Получение закладок
			case 85:
				$oCurrentUser = Core_Auth::getCurrentUser();

				$aJson = array();

				if (!is_null($oCurrentUser))
				{
					$aJson['userId'] = $oCurrentUser->id;
					$aJson['Bookmarks'] = array();

					$oUser_Bookmarks = $oCurrentUser->User_Bookmarks;
					$oUser_Bookmarks->queryBuilder()
						->clearOrderBy()
						->orderBy('user_bookmarks.id', 'ASC');

					$aUser_Bookmarks = $oUser_Bookmarks->findAll(FALSE);

					foreach ($aUser_Bookmarks as $oUser_Bookmark)
					{
						$oModule = Core_Entity::factory('Module')->getById($oUser_Bookmark->module_id);

						if($oModule)
						{
							$oCore_Module = Core_Module_Abstract::factory($oModule->path);

							if ($oModule->active && $oCore_Module)
							{
								$aMenu = $oCore_Module->getMenu();

								$ico = is_array($aMenu) && isset($aMenu[0])
									? strval(Core_Array::get($aMenu[0], 'ico'))
									: 'fa fa-bookmark';

								$aBookmark = array(
									'id' => $oUser_Bookmark->id,
									'name' => $oUser_Bookmark->name,
									'href' => $oUser_Bookmark->path,
									'ico' => $ico,
									'onclick' => "$(this).parents('li.open').click(); $.adminLoad({path: '"
										. Core_Str::escapeJavascriptVariable($oUser_Bookmark->path)
										. "'}); return false",
									'remove-title' => Core::_("User_Bookmark.remove_message"),
									'remove-submit' => Core::_("User_Bookmark.remove_submit"),
									'remove-cancel' => Core::_("User_Bookmark.cancel")
								);

								$aJson['Bookmarks'][] = $aBookmark;
							}
						}
					}
				}

				Core::showJson($aJson);
			break;
		}
	}
}