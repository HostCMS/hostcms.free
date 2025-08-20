<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * User_Model
 *
 * @package HostCMS
 * @subpackage User
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
 */
class User_Model extends Core_Entity
{
	/**
	 * Column consist item's name
	 * @var string
	 */
	protected $_nameColumn = 'login';

	/**
	 * Column consist full item's name
	 * @var string
	 */
	public $fullname = NULL;

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'user' => array(),
		//'company' => array('through' => 'company_department_post_user'),
		'company_department' => array('through' => 'company_department_post_user'),
	);

	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		'admin_form_setting' => array(),
		'admin_form_autosave' => array(),
		'admin_form_field_setting' => array(),
		'company_post' => array('through' => 'company_department_post_user'),
		'company_department' => array('through' => 'company_department_post_user'),
		'company_department_post_user' => array(),
		'crm_note' => array(),
		'event' => array('through' => 'event_user'),
		'event_user' => array(),
		'forum' => array(),
		'forum_group' => array(),
		'forum_category_siteuser_group' => array(),
		'forum_category' => array(),
		'forum_topic' => array(),
		'helpdesk' => array(),
		'helpdesk_category' => array(),
		'helpdesk_attachment' => array(),
		'helpdesk_message' => array(),
		'helpdesk_account' => array(),
		'helpdesk_holiday' => array(),
		'helpdesk_ticket' => array(),
		'helpdesk_ticket_flag' => array(),
		'helpdesk_status' => array(),
		'helpdesk_criticality_level' => array(),
		'helpdesk_responsible_user' => array(),
		'helpdesk_user_letter_template' => array(),
		'helpdesk_responsible_user_second' => array(
			'foreign_key' => 'responsible_user_id',
			'model' => 'Helpdesk_Responsible_User'
		),
		'informationsystem_dir' => array(),
		'informationsystem' => array(),

		'user_directory_email' => array(),
		'directory_email' => array('through' => 'user_directory_email'),
		'user_directory_phone' => array(),
		'directory_phone' => array('through' => 'user_directory_phone'),
		'user_directory_messenger' => array(),
		'directory_messenger' => array('through' => 'user_directory_messenger'),
		'user_directory_social' => array(),
		'directory_social' => array('through' => 'user_directory_social'),
		'user_directory_website' => array(),
		'directory_website' => array('through' => 'user_directory_website'),
		'notification_user' => array(),
		'notification_subscriber' => array(),
		'notification' => array('through' => 'notification_user'),
		'deal' => array(),
		'deal_template_step_access_user' => array(),
		'deal_attachment' => array(),
		'deal_step_user' => array(),
		'user_note' => array(),
		'user_setting' => array(),
		'user_session' => array(),
		'user_message' => array(),
		'user_webauthn' => array(),
		'siteuser_user' => array(),
		'calendar_caldav_user' => array(),
		'dealdeal_step_user' => array(),
		'user_bookmark' => array(),
		'restapi_token' => array(),
		'user_worktime' => array(),
		'user_workday' => array(),
		'user_absence' => array('foreign_key' => 'employee_id', 'model' => 'user_absence'),
		'lead_step' => array(),
		'sql_user_tab' => array(),
		'chartaccount_cashflow' => array(),
		'siteuser_representative_contract' => array(),
		'shop_warehouse_purchaseorder' => array(),
		'shop_warehouse_invoice' => array(),
		'shop_warehouse_supply' => array(),
		'shop_warehouse_purchasereturn' => array(),
		'telephony' => array(),
		'telephony_line' => array(),
		'production_process_stage' => array(),
	);

	/**
	 * List of preloaded values
	 * @var array
	 */
	protected $_preloadValues = array(
		'superuser' => 1,
		'only_access_my_own' => 0,
		'read_only' => 0,
		'settings' => 0,
		'root_dir' => '/'
	);

	/**
	 * Forbidden tags. If list of tags is empty, all tags will show.
	 * @var array
	 */
	protected $_forbiddenTags = array(
		'deleted',
		'user_id',
		'password',
		'settings',
		'dismissed',
		'freelance',
		'guid',
		'only_access_my_own',
		'read_only',
		'sound',
		'root_dir',
		'birthday',
		'last_activity'
	);

	/**
	 * Constructor.
	 * @param int $id entity ID
	 */
	public function __construct($id = NULL)
	{
		parent::__construct($id);

		if (is_null($id) && !$this->loaded())
		{
			$oUser = Core_Auth::getCurrentUser();
			$this->_preloadValues['user_id'] = is_null($oUser) ? 0 : $oUser->id;
			$this->_preloadValues['guid'] = Core_Guid::get();
		}
	}

	/**
	 * Get active user by login and password
	 * @param string $login login
	 * @param string $password password
	 * @return User_Model|NULL
	 */
	public function getByLoginAndPassword($login, $password)
	{
		$this->queryBuilder()
			->clear()
			->where('users.login', '=', $login)
			->where('users.password', '=', Core_Hash::instance()->hash($password))
			->where('users.active', '=', 1)
			->where('users.dismissed', '=', 0)
			->limit(1);

		$aUsers = $this->findAll(FALSE);

		return isset($aUsers[0]) ? $aUsers[0] : NULL;
	}

	/**
	 * Get current user
	 * @return User_Model|NULL
	 */
	public function getCurrent()
	{
		Core_Session::hasSessionId() && Core_Session::start();

		if (isset($_SESSION['current_users_id']) && $_SESSION['current_users_id'] > 0
			&& isset($_SESSION['valid_user']) && isset($_SESSION['date_user']) && isset($_SESSION['is_superuser'])
		)
		{
			$oUser = $this->find(intval($_SESSION['current_users_id']));

			if (!is_null($oUser->id) && $oUser->login === $_SESSION['valid_user'])
			{
				return $oUser;
			}
		}

		return NULL;
	}

	/**
	 * Check if user has access to site based by company_departments and company_department_post_users
	 * @param Site_Model $oSite site
	 * @return boolean
	 */
	public function checkSiteAccess(Site_Model $oSite)
	{
		$oCompany_Department_Module = Core_Entity::factory('Company_Department_Module');
		$oCompany_Department_Module->queryBuilder()
			->join('company_departments', 'company_department_modules.company_department_id', '=', 'company_departments.id')
			->join('company_department_post_users', 'company_department_post_users.company_department_id', '=', 'company_department_modules.company_department_id')
			->where('site_id', '=', $oSite->id)
			->where('company_department_post_users.user_id', '=', $this->id)
			->where('company_departments.deleted', '=', 0)
			->limit(1);

		$aCompany_Department_Modules = $oCompany_Department_Module->findAll();

		return count($aCompany_Department_Modules) == 1;
	}

	/**
	 * Check if user has access to module
	 * @param array $aModuleNames module name
	 * @param Site_Model $oSite
	 * @return boolean
	 */
	public function checkModuleAccess(array $aModuleNames, Site_Model $oSite)
	{
		foreach ($aModuleNames as $sModuleName)
		{
			$oModule = Core_Entity::factory('Module')->getByPath($sModuleName);

			if (is_null($oModule))
			{
				throw new Core_Exception("Module '%s' does not exsit",
					array('%s' => $sModuleName), 0, $bShowDebugTrace = FALSE
				);
			}

			if ($oModule->active != 1)
			{
				/*throw new Core_Exception(Core::_('Core.error_log_module_disabled'),
					array('%s' => $sModuleName), 0, $bShowDebugTrace = FALSE
				);*/
				return FALSE;
			}

			// Вынесено после проверки прав доступа, т.к. идет отдельная проверка на активность модуля
			if ($this->superuser == 1)
			{
				// SU разрешен доступ ко всем модулям
				return TRUE;
			}

			$aCompany_Departments = $this->Company_Departments->findAll();
			foreach ($aCompany_Departments as $oCompany_Department)
			{
				$access = $oCompany_Department->issetModuleAccess(
					$oModule, $oSite
				);

				// Если доступ разрешен
				if ($access)
				{
					// Прерываем проверку, т.к. доступ хотя бы к одному из указанных модулей уже есть
					return TRUE;
				}
			}
		}

		return FALSE;
	}

	/**
	 * Check user access to the object
	 * @param Core_Entity $oObject object
	 * @return boolean
	 */
	public function checkObjectAccess(Core_Entity $oObject)
	{
		if ($this->read_only || $this->dismissed)
		{
			return FALSE;
		}

		if ($this->superuser /*|| !$this->only_access_my_own*/)
		{
			return TRUE;
		}

		// Exists object
		if ($oObject->getPrimaryKey() != 0)
		{
			// Доступ только к своим
			if ($this->only_access_my_own)
			{
				$aTableColumns = $oObject->getTableColumns();

				// Объект имеет поле user_id
				if (isset($aTableColumns['user_id']))
				{
					return ($oObject->user_id == 0 || $oObject->user_id == $this->id);
				}
			}
			// Проверка на право доступа пользователя к сайту, которому принадлежит элемент
			else
			{
				$oRelatedSite = $oObject->getRelatedSite();

				if ($oRelatedSite)
				{
					$aSites = $this->getSites();

					foreach ($aSites as $oSites)
					{
						if ($oRelatedSite->id == $oSites->id)
						{
							return TRUE;
						}
					}

					return FALSE;
				}
				else
				{
					return TRUE;
				}
			}
		}
		else
		{
			// New object
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Cache getSites()
	 * @var array|NULL
	 */
	protected $_cacheGetSites = NULL;

	/**
	 * Get allowed sites for User
	 * @return array
	 */
	public function getSites()
	{
		if (is_null($this->_cacheGetSites))
		{
			$oSite = Core_Entity::factory('Site');

			if (!$this->superuser)
			{
				$oSite->queryBuilder()
					->select('sites.*')
					->join('company_department_modules', 'company_department_modules.site_id', '=', 'sites.id')
					->join('company_departments', 'company_department_modules.company_department_id', '=', 'company_departments.id')
					->join('company_department_post_users', 'company_department_post_users.company_department_id', '=', 'company_department_modules.company_department_id')
					->where('company_department_post_users.user_id', '=', $this->id)
					->where('company_departments.deleted', '=', 0)
					->groupBy('sites.id');
			}

			$this->_cacheGetSites = $oSite->findAll();
		}

		return $this->_cacheGetSites;
	}

	/**
	 * Get user href
	 * @return string
	 */
	public function getHref()
	{
		// Используем upload вместо настроек сайта поскольку пользователь ЦА инвариантен относительно сайта
		return 'upload/user/' . intval($this->id) . '/';
	}

	/**
	 * Get user path
	 * @return string
	 */
	public function getPath()
	{
		return CMS_FOLDER . $this->getHref();
	}

	/**
	 * Get image file path
	 * @return string|NULL
	 */
	public function getImageFilePath()
	{
		return $this->image != ''
			? $this->getPath() . $this->image
			: NULL;
	}

	/**
	 * Get image href or default user icon
	 * @return string
	 */
	public function getImageHref()
	{
		return $this->image
			? $this->getImageFileHref()
			: '/modules/skin/bootstrap/img/default_user.png';
	}

	/**
	 * Get image href
	 * @return string
	 */
	public function getImageFileHref()
	{
		return '/' . $this->getHref() . rawurlencode($this->image);
	}

	/**
	 * Create files directory
	 * @return self
	 */
	public function createDir()
	{
		clearstatcache();

		if (!Core_File::isDir($this->getPath()))
		{
			try
			{
				Core_File::mkdir($this->getPath(), CHMOD, TRUE);
			} catch (Exception $e) {}
		}

		return $this;
	}

	/**
	 * Delete image file
	 * @return self
	 * @hostcms-event user.onAfterDeleteImageFile
	 */
	public function deleteImageFile()
	{
		try
		{
			Core_File::isFile($this->getImageFilePath()) && Core_File::delete($this->getImageFilePath());
		} catch (Exception $e) {}

		Core_Event::notify($this->_modelName . '.onAfterDeleteImageFile', $this);

		$this->image = '';
		$this->save();

		return $this;
	}

	/**
	 * Delete information system directory
	 * @return self
	 */
	public function deleteDir()
	{
		$this->deleteImageFile();

		if (Core_File::isDir($this->getPath()))
		{
			try
			{
				Core_File::deleteDir($this->getPath());
			} catch (Exception $e) {}
		}

		return $this;
	}

	/**
	 * Mark entity as deleted
	 * @return Core_Entity
	 */
	public function markDeleted()
	{
		$oCurrentUser = Core_Auth::getCurrentUser();
		if (!$oCurrentUser || $oCurrentUser->id != $this->id)
		{
			parent::markDeleted();
		}
		return $this;
	}

	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return Core_Entity
	 * @hostcms-event user.onBeforeRedeclaredDelete
	 */
	public function delete($primaryKey = NULL)
	{
		if (is_null($primaryKey))
		{
			$primaryKey = $this->getPrimaryKey();
		}

		$this->id = $primaryKey;

		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredDelete', $this, array($primaryKey));

		$this->User_Notes->deleteAll(FALSE);
		$this->User_Settings->deleteAll(FALSE);
		$this->Admin_Form_Settings->deleteAll(FALSE);
		$this->Admin_Form_Field_Settings->deleteAll(FALSE);
		$this->Admin_Form_Autosaves->deleteAll(FALSE);

		// Helpdesks
		if (Core::moduleIsActive('helpdesk'))
		{
			$this->Helpdesk_Responsible_User_Seconds->deleteAll(FALSE);
			$this->Helpdesk_User_Letter_Templates->deleteAll(FALSE);
		}

		if (Core::moduleIsActive('company'))
		{
			$this->Company_Department_Post_Users->deleteAll(FALSE);
		}

		$this->Directory_Emails->deleteAll(FALSE);
		$this->Directory_Messengers->deleteAll(FALSE);
		$this->Directory_Phones->deleteAll(FALSE);
		$this->Directory_Socials->deleteAll(FALSE);
		$this->Directory_Websites->deleteAll(FALSE);

		if (Core::moduleIsActive('event'))
		{
			$aEvent_Users = $this->Event_Users->findAll(FALSE);
			foreach ($aEvent_Users as $oEvent_User)
			{
				$oEvent = $oEvent_User->Event;

				if ($oEvent->Event_Users->getCount(FALSE) == 1)
				{
					$oEvent->delete();
				}
				else
				{
					$oEvent_User->delete();
				}
			}
		}

		if (Core::moduleIsActive('notification'))
		{
			$this->Notification_Subscribers->deleteAll(FALSE);

			$aNotification_Users = $this->Notification_Users->findAll(FALSE);
			foreach ($aNotification_Users as $oNotification_User)
			{
				$oNotification = $oNotification_User->Notification;

				if ($oNotification->Notification_Users->getCount(FALSE) == 1)
				{
					$oNotification->delete();
				}
				else
				{
					$oNotification_User->delete();
				}
			}
		}

		if (Core::moduleIsActive('siteuser'))
		{
			$this->Siteuser_Users->deleteAll(FALSE);
		}

		if (Core::moduleIsActive('calendar'))
		{
			$this->Calendar_Caldav_Users->deleteAll(FALSE);
		}

		if (Core::moduleIsActive('restapi'))
		{
			$this->Restapi_Tokens->deleteAll(FALSE);
		}

		if (Core::moduleIsActive('deal'))
		{
			$this->Deal_Attachments->deleteAll(FALSE);
			$this->Deal_Step_Users->deleteAll(FALSE);
			$this->Deal_Template_Step_Access_Users->deleteAll(FALSE);
		}

		if (Core::moduleIsActive('lead'))
		{
			$this->Lead_Steps->deleteAll(FALSE);
		}

		if (Core::moduleIsActive('sql'))
		{
			$this->Sql_User_Tabs->deleteAll(FALSE);
		}

		if (Core::moduleIsActive('telephony'))
		{
			$this->Telephonies->deleteAll(FALSE);
			$this->Telephony_Lines->deleteAll(FALSE);
		}

		$this->User_Bookmarks->deleteAll(FALSE);
		$this->User_Worktimes->deleteAll(FALSE);
		$this->User_Workdays->deleteAll(FALSE);
		$this->User_Absences->deleteAll(FALSE);
		$this->User_Sessions->deleteAll(FALSE);
		$this->User_Webauthns->deleteAll(FALSE);

		// Удаляем директорию
		$this->deleteDir();

		return parent::delete($primaryKey);
	}

	/**
	 * Change user status
	 * @return self
	 * @hostcms-event user.onBeforeChangeActive
	 * @hostcms-event user.onAfterChangeActive
	 */
	public function changeActive()
	{
		Core_Event::notify($this->_modelName . '.onBeforeChangeActive', $this);

		$oCurrentUser = Core_Auth::getCurrentUser();

		if (!$this->active
			|| (!$oCurrentUser || $oCurrentUser->id != $this->id)
		)
		{
			$this->active = 1 - $this->active;
			$this->save();
		}

		Core_Event::notify($this->_modelName . '.onAfterChangeActive', $this);

		return $this;
	}

	/**
	 * Update last activity
	 * @return self
	 */
	public function updateLastActivity()
	{
		$this->last_activity = Core_Date::timestamp2sql(time());
		return $this->save();
	}

	/**
	 * Return number of seconds since last activity
	 * @return int
	 */
	public function getLastActivity()
	{
		return !is_null($this->last_activity) && $this->last_activity != '0000-00-00 00:00:00'
			? time() - Core_Date::sql2timestamp($this->last_activity)
			: NULL;
	}

	/**
	 * Is user online
	 * @return boolean
	 */
	public function isOnline()
	{
		$lastActivity = $this->getLastActivity();
		return !is_null($lastActivity) && $lastActivity < 60 * 20;
	}

	/**
	 * Get count of unread messages
	 * @param User_Model $oUser
	 * @return int
	 */
	public function getUnreadCount(User_Model $oUser)
	{
		// Количество непрочитанных сообщений
		$oCore_QueryBuilder_Select = Core_QueryBuilder::select()
			->select(array(Core_QueryBuilder::expression('COUNT(*)'), 'count'))
			->from('user_messages')
			->where('user_messages.read', '=', 0)
			->where('user_messages.user_id', '=', $oUser->id)
			->where('user_messages.recipient_user_id', '=', $this->id);

		$row = $oCore_QueryBuilder_Select->execute()->asAssoc()->current();

		return intval($row['count']);
	}

	/**
	 * Get full name of user
	 *
	 * @return string
	 */
	public function getFullName()
	{
		$aPartsFullName = array();

		!empty($this->surname) && $aPartsFullName[] = $this->surname;
		!empty($this->name) && $aPartsFullName[] = $this->name;
		!empty($this->patronymic) && $aPartsFullName[] = $this->patronymic;

		// В случае отсутствия ФИО возвращаем логин
		!count($aPartsFullName) && $aPartsFullName[] = $this->login;

		return implode(' ', $aPartsFullName);
	}

	/**
	 * Get company posts for user by department
	 * @return array
	 */
	public function getCompanyPostsByDepartment($iDepartmentId, $isHead = NULL)
	{
		$oCompany_Department_Posts = $this->Company_Posts; //->getAllByCompany_department_id($iDepartmentId);
		$oCompany_Department_Posts
			->queryBuilder()
			->where('company_department_post_users.company_department_id', '=', $iDepartmentId);

		!is_null($isHead)
			&& $oCompany_Department_Posts
			->queryBuilder()
			->where('company_department_post_users.head', '=', intval($isHead));

		return $oCompany_Department_Posts->findAll();
	}

	/**
	 * Get company posts for user by company
	 * @return array
	 */
	public function getCompanyPostsByCompany($iCompanyId, $isHead = NULL)
	{
		$oCompany_Posts = $this->Company_Posts; //->getAllByCompany_department_id($iDepartmentId);
		$oCompany_Posts
			->queryBuilder()
			->where('company_department_post_users.company_id', '=', $iCompanyId)
			->groupBy('company_posts.id');

		!is_null($isHead)
			&& $oCompany_Posts
			->queryBuilder()
			->where('company_department_post_users.head', '=', intval($isHead));

		return $oCompany_Posts->findAll();
	}

	/**
	 * Get user avatar
	 * @return string
	 */
	public function getAvatar()
	{
		$link = Core::moduleIsActive('cdn')
			? Cdn_Controller::link($this->getImageHref())
			: NULL;

		if (is_null($link))
		{
			$link = $this->image != '' && strlen($this->image)
				? $this->getImageHref()
				: Admin_Form_Controller::correctBackendPath("/{admin}/user/index.php?loadUserAvatar={$this->id}");
		}

		return $link;
	}

	/**
	 * Backend
	 * @return self
	 */
	public function smallAvatar()
	{
		if ($this->id)
		{
			$oCore_Html_Entity_Div = Core_Html_Entity::factory('Div')
				->class('avatar-user')
				->title($this->getFullName());

			$oCore_Html_Entity_Div
				->add(
					Core_Html_Entity::factory('Img')
						->src($this->getAvatar())
						->width(30)
						->height(30)
				);

			$oCore_Html_Entity_Div->execute();
		}
	}

	/**
	 * Get avatar with name
	 * @return string|NULL
	 */
	public function getAvatarWithName()
	{
		if ($this->id)
		{
			$oCurrentUser = Core_Auth::getCurrentUser();

			$link = $oCurrentUser && !$oCurrentUser->only_access_my_own
				? '<a data-popover="hover" data-user-id="' . $this->id . '" style="color: inherit" href="' . Admin_Form_Controller::correctBackendPath('/{admin}/user/index.php') . '?hostcms[action]=view&hostcms[checked][0][' . $this->id . ']=1" onclick="$.modalLoad({path: hostcmsBackend + \'/user/index.php\', action: \'view\', operation: \'modal\', additionalParams: \'hostcms[checked][0][' . $this->id . ']=1\', windowId: \'id_content\', width: \'90%\'}); return false">' . htmlspecialchars($this->getFullName()) . '</a>'
				: '<span>' . htmlspecialchars($this->getFullName()) . '</span>';

			return '<div class="contracrot">
				<div class="user-image"><img class="contracrot-ico" src="' . $this->getAvatar() . '"></div>
				<div class="user-name">' . $link . '</div>
			</div>';
		}

		return NULL;
	}

	/**
	 * Show avatar with name
	 */
	public function showAvatarWithName()
	{
		return $this->getAvatarWithName();
	}

	/**
	 * Backend badge
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 */
	public function fullnameBadge()
	{
		if ($this->dismissed)
		{
			$dismissedColor = '#ed4e2a';

			?><span class="badge badge-round badge-max-width margin-left-5" title="<?php echo Core::_('Mail.create_leads')?>" style="border-color: <?php echo $dismissedColor?>; color: <?php echo Core_Str::hex2darker($dismissedColor, 0.2)?>; background-color:<?php echo Core_Str::hex2lighter($dismissedColor, 0.88)?>"><?php echo Core::_('User.dismissed')?></span><?php
		}
	}

	/**
	 * Backend badge
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 */
	public function loginBadge()
	{
		echo '&nbsp;' . $this->getOnlineStatus();
	}

	/**
	 * Get span with online status
	 * @return string
	 */
	public function getOnlineStatus()
	{
		$isOnline = $this->isOnline();

		$sStatus = $isOnline
			? 'online'
			: 'offline';

		$lng = $isOnline ? 'user_active' : 'user_last_activity';

		$sStatusTitle = !is_null($this->last_activity) && $this->last_activity != '0000-00-00 00:00:00'
			? Core::_('User.' . $lng, Core_Date::sql2datetime($this->last_activity))
			: '';

		return '<span title="' . htmlspecialchars($sStatusTitle) . '" class="' . htmlspecialchars($sStatus) . '"></span>';
	}

	/**
	 * Backend callback method
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function departmentBackend()
	{
		$aTempDepartmentPost = array();

		$aCompany_Department_Post_Users = $this->Company_Department_Post_Users->findAll();
		foreach ($aCompany_Department_Post_Users as $key => $oCompany_Department_Post_User)
		{
			$aTempDepartmentPost[] = '<div ' . ( $key ? ' class="margin-top-5"' : '' ) . '>' . htmlspecialchars($oCompany_Department_Post_User->Company_Department->name) . '<br /><span class="user-post-name">'
				. htmlspecialchars($oCompany_Department_Post_User->Company_Post->name) . '</span>'
				. ($oCompany_Department_Post_User->head ? ' <i class="fa fa-star head-star" title="' . Core::_('User.head_title') . '"></i>' : '') . '</div>';
		}

		echo implode('', $aTempDepartmentPost);
	}

	/**
	 * Get user age
	 * @return string
	 */
	public function getAge()
	{
		return floor((time() - strtotime($this->birthday)) / 31556926);
	}

	/**
	 * Get user sex
	 * @return string
	 */
	public function getSex()
	{
		return $this->sex
			? '<i class="fa fa-venus pink"></i>'
			: '<i class="fa fa-mars sky"></i>';
	}

	public function getWorkdayDuration($date)
	{
		$sDate = '00<span class="colon">:</span>00';

		$oUser_Workday = $this->User_Workdays->getByDate($date);

		if (!is_null($oUser_Workday) && $oUser_Workday->begin != '00:00:00')
		{
			$beginTimestamp = Core_Date::sql2timestamp($date . ' ' . $oUser_Workday->begin);

			$time = $oUser_Workday->end != '00:00:00'
				? Core_Date::sql2timestamp($date . ' ' . $oUser_Workday->end)
				: time();

			$durationInSeconds = $time - $beginTimestamp;

			$aUser_Workday_Breaks = $oUser_Workday->User_Workday_Breaks->findAll(FALSE);

			$sumBreakTimeInSeconds = 0;

			foreach ($aUser_Workday_Breaks as $oUser_Workday_Break)
			{
				$endBreak = $oUser_Workday_Break->end != '00:00:00'
					? Core_Date::sql2timestamp($date . ' ' . $oUser_Workday_Break->end)
					: time();

				$beginBreakTimestamp = Core_Date::sql2timestamp($date . ' ' . $oUser_Workday_Break->begin);

				$sumBreakTimeInSeconds += $endBreak - $beginBreakTimestamp;
			}

			$durationInMinutes = intval(($durationInSeconds - $sumBreakTimeInSeconds) / 60);

			$sDate = sprintf('%02d<span class="colon">:</span>%02d', floor($durationInMinutes / 60), $durationInMinutes % 60);
		}

		return $sDate;
	}

	public function isUserWorkdayAvailable($date)
	{
		$aUser_Workdays = $this->User_Workdays->getAllByDate($date);

		if (count($aUser_Workdays) == 0)
		{
			return TRUE;
		}
		elseif (count($aUser_Workdays) == 1 && $aUser_Workdays[0]->end != '00:00:00')
		{
			// Работает ли сотрудник в ночную смену
			$dayNumber = date('w', Core_Date::sql2timestamp($date));

			$oUser_Worktime = $this->User_Worktimes->getByDay($dayNumber);

			// Есть режим работы на этот день
			if (!is_null($oUser_Worktime))
			{
				$iFrom = Core_Date::sql2timestamp($date . ' ' . $oUser_Worktime->from);
				$iTo = Core_Date::sql2timestamp($date . ' ' . $oUser_Worktime->to);
				// Работает в ночную и остался час до начала смены, то можно ее открывать
				if ($iFrom > $iTo && ($iFrom - 3600) <= time())
				{
					return TRUE;
				}
			}
		}

		return FALSE;
	}

	/**
	 * Получение статуса рабочего дня:
	 * 0 - рабочий день не начат и доступен для начала
	 * 1 - рабочий день не начат и не доступен для начала
	 * 2 - рабочий день начат, сотрудник работает
	 * 3 - рабочий день начат, у сотрудника перерыв
	 * 4 - рабочий день завершен
	 * 5 - рабочий день окончен, но не завершен сотрудником
	 * @param string $sDate дата в формате "YYYY-mm-dd" или пустая строка
	 * @return int
	 */
	public function getStatusWorkday($sDate = '')
	{
		// Если дата не задана или задана некорректно, берем текущую дату
		if ($sDate == '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $sDate))
		{
			$sDate = Core_Date::timestamp2sqldate(time());
		}

		if ($this->isUserWorkdayAvailable($sDate))
		{
			// Проверяем завершенность последнего рабочего дня
			$oLastUserWorkday = $this->User_Workdays->getLast(FALSE);

			if (!is_null($oLastUserWorkday) && $oLastUserWorkday->end == '00:00:00')
			{
				$iWorkdayStatus = 1;
			}
			else
			{
				// Сотрудник может начать рабочий день
				$iWorkdayStatus = 0;
			}
		}
		// Рабочий день уже начат
		elseif (!is_null($oUser_Workday = $this->User_Workdays->getByDate($sDate, FALSE)))
		{
			// Рабочий день завершен
			if($oUser_Workday->end != '00:00:00')
			{
				$iWorkdayStatus = 4;
			}
			// Рабочий день не завершен
			else
			{
				$oUser_Workday_Break = $oUser_Workday->User_Workday_Breaks->getLast(FALSE);

				// Сотрудник на перерыве
				if (!is_null($oUser_Workday_Break) && $oUser_Workday_Break->end == '00:00:00')
				{
					$iWorkdayStatus = 3;
				}
				// Сотрудник работает
				else
				{
					$iWorkdayStatus = 2;
				}

				// Рабочий день окончен, но не завершен сотрудником
				$dayNumber = date('w', Core_Date::sql2timestamp($sDate));
				$oUser_Worktime = $this->User_Worktimes->getByDay($dayNumber);

				if (!is_null($oUser_Worktime) && $oUser_Worktime->from != '00:00:00' && $oUser_Worktime->to != '00:00:00')
				{
					$iDayEnd = Core_Date::sql2timestamp($sDate . ' ' . $oUser_Worktime->to);

					if ($iDayEnd <= time())
					{
						$iWorkdayStatus = 5;
					}
				}
			}
		}
		else
		{
			$iWorkdayStatus = 1;
		}

		return $iWorkdayStatus;
	}

	/**
	 * Является ли текущий сотрудник начальником отдела
	 * @param $oDepartment отдел
	 * @return boolean
	 */
	public function isHeadOfDepartment($oDepartment)
	{
		$aDepartmentHeads = $oDepartment->getHeads();

		foreach ($aDepartmentHeads as $oDepartmentHead)
		{
			if ($oDepartmentHead->id == $this->id)
			{
				return TRUE;
			}
		}

		return FALSE;
	}

	/**
	 * Get Departments which headed by current user
	 * @param mixed $oCompany Company, default NULL
	 * @return array
	 */
	public function getDepartmentsHeadedBy($oCompany = NULL)
	{
		// Отделы, в которых текущий пользователь глава
		$oCompany_Department_Post_Users = $this->Company_Department_Post_Users;
		$oCompany_Department_Post_Users
			->queryBuilder()
			->where('company_department_post_users.head', '=', 1);

		!is_null($oCompany) && $oCompany_Department_Post_Users
			->queryBuilder()
			->where('company_department_post_users.company_id', '=', $oCompany->id);

		$aCompany_Department_Post_Users = $oCompany_Department_Post_Users->findAll();

		$aHeadOfDepartments = array();
		foreach ($aCompany_Department_Post_Users as $oCompany_Department_Post_User)
		{
			$aHeadOfDepartments[] = $oCompany_Department_Post_User->Company_Department;
		}

		return $aHeadOfDepartments;
	}

	/**
	 * Get All Departments which headed by current user or user is head of parent department
	 *
	 * @return array
	 */
	public function getAllDepartmentsHeadedBy()
	{
		$aReturn = $aCompany_Departments = $this->getDepartmentsHeadedBy();
		foreach ($aCompany_Departments as $oCompany_Department)
		{
			$aReturn = array_merge($aReturn, $oCompany_Department->getChildren());
		}

		return $aReturn;
	}

	/**
	 * Является ли текущий сотрудник начальником для определенного сотрудника в заданой компании
	 * @param $oCompany компания
	 * @param $oEmployee сотрудник, подчинененность которого необходимо проверить
	 * @return boolean
	 */
	public function isHeadOfEmployeeInCompany(Company_Model $oCompany, User_Model $oEmployee)
	{
		// Отделы, в которых текущий пользователь непосредственно глава
		$aCompany_Departments = $this->getDepartmentsHeadedBy();

		if (count($aCompany_Departments))
		{
			// Если идет проверка для самого себя и пользователь является главой отдела, который находится в самом верху
			if ($oEmployee->id == $this->id)
			{
				foreach ($aCompany_Departments as $oCompany_Department)
				{
					if ($oCompany_Department->parent_id == 0)
					{
						return TRUE;
					}
				}
			}
			else
			{
				$aHeadOfDepartmentsIDs = array();
				foreach ($aCompany_Departments as $oCompany_Department)
				{
					$aHeadOfDepartmentsIDs[] = $oCompany_Department->id;
				}

				$aCompany_Departments = $oEmployee->Company_Departments->findAll();
				foreach ($aCompany_Departments as $oCompany_Department)
				{
					do {
						// ID департамента, в котором работает $oEmployee входит в перечень, в котором $this глава
						if (in_array($oCompany_Department->id, $aHeadOfDepartmentsIDs))
						{
							return TRUE;
						}
					} while($oCompany_Department = $oCompany_Department->getParent());
				}
			}
		}

		return FALSE;
	}

	/**
	 * Является ли текущий сотрудник начальником для определенного сотрудника хотя бы в одной компании
	 * @param $oCompany компания
	 * @param $oUser сотрудник, подчинененность которого необходимо проверить
	 * @return boolean
	 */
	public function isHeadOfEmployee($oUser)
	{
		$aCompanies = Core_Entity::factory('Company')->findAll();

		foreach ($aCompanies as $oCompany)
		{
			if ($this->isHeadOfEmployeeInCompany($oCompany, $oUser))
			{
				return TRUE;
			}
		}

		return FALSE;
	}

	/**
	 * Get user's email
	 * @return string|NULL
	 */
	public function getEmail()
	{
		$aDirectory_Emails = $this->Directory_Emails->findAll(FALSE);
		return isset($aDirectory_Emails[0])
			? $aDirectory_Emails[0]->value
			: NULL;
	}

	/**
	 * Get XML for entity and children entities
	 * @return string
	 * @hostcms-event user.onBeforeRedeclaredGetXml
	 */
	public function getXml()
	{
		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredGetXml', $this);

		$this->_prepareData();

		return parent::getXml();
	}

	/**
	 * Get stdObject for entity and children entities
	 * @return stdObject
	 * @hostcms-event user.onBeforeRedeclaredGetStdObject
	 */
	public function getStdObject($attributePrefix = '_')
	{
		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredGetStdObject', $this);

		$this->_prepareData();

		return parent::getStdObject($attributePrefix);
	}

	/**
	 * Prepare entity and children entities
	 * @return self
	 */
	protected function _prepareData()
	{
		$oSite = Core_Entity::factory('Site', CURRENT_SITE);

		$this->clearXmlTags()
			->addXmlTag('dir', '/' . $this->getHref())
			->addXmlTag('last_activity', !is_null($this->last_activity) && $this->last_activity != '0000-00-00 00:00:00'
				? Core_Date::strftime($oSite->date_time_format, Core_Date::sql2timestamp($this->last_activity))
				: $this->last_activity
			)
			->addXmlTag('birthday', $this->birthday == '0000-00-00'
				? $this->birthday
				: Core_Date::strftime($oSite->date_format, Core_Date::sql2timestamp($this->birthday))
			);

		$aDirectory_Phones = $this->Directory_Phones->findAll();
		$aDirectory_Websites = $this->Directory_Websites->findAll();
		$aDirectory_Emails = $this->Directory_Emails->findAll();
		$aDirectory_Socials = $this->Directory_Socials->findAll();
		$aDirectory_Messengers = $this->Directory_Messengers->findAll();

		$this
			->addEntities($aDirectory_Phones)
			->addEntities($aDirectory_Emails)
			->addEntities($aDirectory_Websites)
			->addEntities($aDirectory_Socials)
			->addEntities($aDirectory_Messengers);

		return $this;
	}

	/**
	 * Return html profile block for popup
	 */
	public function getProfilePopupBlock()
	{
		ob_start();
		?>
		<div class="siteuser-popup-wrapper">
			<img class="avatar" src="<?php echo $this->getAvatar()?>"/>
			<div class="siteuser-popup-name">
				<div class="semi-bold"><?php echo htmlspecialchars($this->getFullName())?></div>

				<?php
				$aCompanies = Core_Entity::factory('Company')->findAll();

				if (isset($aCompanies[0]))
				{
					$oCompany = $aCompanies[0];

					$aCompany_Department_Post_Users = $this->Company_Department_Post_Users->getAllByCompany_id($oCompany->id);

					if (isset($aCompany_Department_Post_Users[0]))
					{
						$oCompany_Department_Post_User = $aCompany_Department_Post_Users[0];

						?>
						<div><div><span class="small2"><?php echo htmlspecialchars($oCompany_Department_Post_User->Company_Department->name)?></span> <span class="popup-type"><?php echo htmlspecialchars($oCompany_Department_Post_User->Company_Post->name)?></span></div></div>
						<?php
					}
				}
				?>
			</div>
		</div>
		<?php
		$aDirectory_Phones = $this->Directory_Phones->findAll(FALSE);

		if (count($aDirectory_Phones))
		{
			?><div><?php
			foreach ($aDirectory_Phones as $oDirectory_Phone)
			{
				if (strlen(Core_Str::sanitizePhoneNumber(trim($oDirectory_Phone->value))))
				{
					$oDirectory_Phone_Type = Core_Entity::factory('Directory_Phone_Type')->find($oDirectory_Phone->directory_phone_type_id);

					$sPhoneType = !is_null($oDirectory_Phone_Type->id)
						? htmlspecialchars($oDirectory_Phone_Type->name) . ": "
						: '';

					?><div><span class="popup-type"><i class="fa fa-phone fa-fw palegreen"></i> <?php echo $sPhoneType?></span><span><?php echo htmlspecialchars($oDirectory_Phone->value)?></span></div><?php
				}
			}
			?></div><?php
		}

		$aDirectory_Emails = $this->Directory_Emails->findAll(FALSE);

		if (count($aDirectory_Emails))
		{
			?><div class="margin-top-5"><?php
			foreach ($aDirectory_Emails as $oDirectory_Email)
			{
				if (strlen(trim($oDirectory_Email->value)))
				{
					$oDirectory_Email_Type = Core_Entity::factory('Directory_Email_Type')->find($oDirectory_Email->directory_email_type_id);

					$sEmailType = !is_null($oDirectory_Email_Type->id)
						? htmlspecialchars($oDirectory_Email_Type->name) . ": "
						: '';

						?><div><span class="popup-type"><i class="fa fa-envelope-o fa-fw warning"></i> <?php echo $sEmailType?></span><span><a href="mailto:<?php echo htmlspecialchars($oDirectory_Email->value)?>"><?php echo htmlspecialchars($oDirectory_Email->value)?></a></span></div><?php
				}
			}
		}

		return ob_get_clean();
	}

	/**
	 * Show in line
	 * @param integer $imageSize
	 */
	public function showInLine($imageSize = 20)
	{
		$oCore_Html_Entity_Div = Core_Html_Entity::factory('Div')
			->class('avatar-user avatar-user-users')
			->title($this->getFullName());

		$oCore_Html_Entity_Div
			->add(
				Core_Html_Entity::factory('A')
					->href(Admin_Form_Controller::correctBackendPath("/{admin}/user/index.php?hostcms[action]=view&hostcms[checked][0][" . $this->id . "]=1"))
					->onclick("$.modalLoad({path: hostcmsBackend + '/user/index.php', action: 'view', operation: 'modal', additionalParams: 'hostcms[checked][0][" . $this->id . "]=1', windowId: 'id_content'}); return false")
					->add(
						Core_Html_Entity::factory('Img')
							->src($this->getAvatar())
							->width($imageSize)
							->height($imageSize)
					)
			)
			->add(
				Core_Html_Entity::factory('A')
					->href(Admin_Form_Controller::correctBackendPath("/{admin}/user/index.php?hostcms[action]=view&hostcms[checked][0][" . $this->id . "]=1"))
					->onclick("$.modalLoad({path: hostcmsBackend + '/user/index.php', action: 'view', operation: 'modal', additionalParams: 'hostcms[checked][0][" . $this->id . "]=1', windowId: 'id_content'}); return false")
					->add(
						Core_Html_Entity::factory('Span')
						->value(htmlspecialchars($this->getFullName()))
						->class('darkgray margin-left-5')
					)
			);

		$oCore_Html_Entity_Div->execute();
	}

	/**
	 * Show user link
	 * @param string $windowId window id
	 * @param string|NULL $content content
	 */
	public function showLink($windowId, $content = NULL, $width = '80%')
	{
		$content = is_null($content)
			? $this->getFullName()
			: $content;

		$oUser = Core_Auth::getCurrentUser();

		?><a data-popover="hover" data-container="body" data-user-id="<?php echo $this->id?>" style="color: inherit" <?php
		if ($oUser->checkModuleAccess(array('user'), Core_Entity::factory('Site', CURRENT_SITE)))
		{
			?>href="<?php echo Admin_Form_Controller::correctBackendPath('/{admin}/user/index.php')?>?hostcms[action]=view&hostcms[checked][0][<?php echo $this->id?>]=1" onclick="$.modalLoad({path: hostcmsBackend + '/user/index.php', action: 'view', operation: 'modal', additionalParams: 'hostcms[checked][0][<?php echo $this->id?>]=1', windowId: '<?php echo $windowId?>', width: '<?php echo $width?>'}); return false"<?php
		}
		?>><?php echo htmlspecialchars($content)?></a><?php
	}
}