<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * User_Model
 *
 * @package HostCMS
 * @subpackage User
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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
		'company' => array('through' => 'company_department_post_user'),
		'company_department' => array('through' => 'company_department_post_user'),
	);

	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		'admin_form_setting' => array(),
		'company_post' => array('through' => 'company_department_post_user'),
		'company_department' => array('through' => 'company_department_post_user'),
		'company_department_post_user' => array(),
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
		'deal_template_step_access_user'  => array(),
		'deal_attachment' => array(),
		'user_note' => array(),
		'user_setting' => array(),
		'user_message' => array(),
		'siteuser_user' => array(),
		'calendar_caldav_user' => array(),
		'deal_step_user' => array(),
		'user_bookmark' => array(),
		'restapi_token' => array(),
		'user_worktime' => array()
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
		/*'~email',
		'~icq',
		'~site',
		'~position'*/
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
			$oUserCurrent = Core_Entity::factory('User', 0)->getCurrent();
			$this->_preloadValues['user_id'] = is_null($oUserCurrent) ? 0 : $oUserCurrent->id;
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
		if (isset($_SESSION['current_users_id']))
		{
			$oUser = $this->find(intval($_SESSION['current_users_id']));

			if (!is_null($oUser->id))
			{
				return $oUser;
			}
		}

		return NULL;
	}

	/**
	 * Check if user has access to site
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
				throw new Core_Exception(Core::_('Core.error_log_module_disabled'),
					array('%s' => $sModuleName), 0, $bShowDebugTrace = FALSE
				);
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
	 * Check user access to object
	 * @param Core_Entity $oObject object
	 * @return boolean
	 */
	public function checkObjectAccess(Core_Entity $oObject)
	{
		if ($this->read_only || $this->dismissed)
		{
			return FALSE;
		}

		if ($this->superuser == 1 || $this->only_access_my_own == 0)
		{
			return TRUE;
		}

		$aTableColumns = $oObject->getTableColums();

		// Объект имеет поле user_id
		if (isset($aTableColumns['user_id']))
		{
			return ($oObject->user_id == 0 || $oObject->user_id == $this->id);
		}

		return FALSE;
	}

	/**
	 * Get allowed sites for User
	 * @return array
	 */
	public function getSites()
	{
		$oSite = Core_Entity::factory('Site');

		if ($this->superuser == 0)
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

		return $oSite->findAll();
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
	 * Get user avatar
	 * @return string
	 */
	public function getAvatar()
	{
		return strlen($this->image)
			? $this->getImageHref()
			: "/admin/user/index.php?loadUserAvatar={$this->id}";
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
	 * Specify image file for user
	 * @param string $fileSourcePath source file
	 * @param string $fileName target file name
	 * @return self
	 */
	/*public function saveImageFile($fileSourcePath, $fileName)
	{
		$this->createDir();

		$fileExtension = Core_File::getExtension($fileName);

		$this->image = 'avatar.' . $fileExtension;
		$this->save();

		Core_File::upload($fileSourcePath, $this->getImageFilePath());
		return $this;
	}*/

	/**
	 * Create files directory
	 * @return self
	 */
	public function createDir()
	{
		clearstatcache();

		if (!is_dir($this->getPath()))
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
	 */
	public function deleteImageFile()
	{
		try
		{
			is_file($this->getImageFilePath()) && Core_File::delete($this->getImageFilePath());
		} catch (Exception $e) {}

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

		if (is_dir($this->getPath()))
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
		$oCurrentUser = Core_Entity::factory('User', 0)->getCurrent();
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
			$this->Deal_Template_Step_Access_User->deleteAll(FALSE);
			$this->Deal_Step_Users->deleteAll(FALSE);
		}

		$this->User_Bookmarks->deleteAll(FALSE);
		$this->User_Worktimes->deleteAll(FALSE);

		// Удаляем директорию
		$this->deleteDir();

		return parent::delete($primaryKey);
	}

	/**
	 * Backend callback method
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	/*public function login($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		$sStatus = $this->isOnline()
			? 'online'
			: 'offline';

		return "{$this->login}&nbsp;<div class=\"{$sStatus}\"></div>";
	}*/

	/**
	 * Change user status
	 * @return self
	 * @hostcms-event user.onBeforeChangeActive
	 * @hostcms-event user.onAfterChangeActive
	 */
	public function changeActive()
	{
		Core_Event::notify($this->_modelName . '.onBeforeChangeActive', $this);

		$oCurrentUser = Core_Entity::factory('User', 0)->getCurrent();

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
		return !is_null($this->last_activity)
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
	 * Backend
	 * @return self
	 */
	public function smallAvatar()
	{
		$oCore_Html_Entity_Div = Core::factory('Core_Html_Entity_Div')
			->class('avatar-user');

		$oCore_Html_Entity_Div
			->add(
				Core::factory('Core_Html_Entity_Img')
					->src($this->getAvatar())
					->width(30)
					->height(30)
			);

		$oCore_Html_Entity_Div->execute();
	}

	/**
	 * Backend callback method
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 */
	public function loginBadge()
	{
		$sStatus = $this->isOnline()
			? 'online'
			: 'offline';

		echo "&nbsp;<span class=\"{$sStatus}\"></span>";
	}

	/**
	 * Backend callback method
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function department()
	{
		$aTempDepartmentPost = array();

		$aCompany_Department_Post_Users = $this->Company_Department_Post_Users->findAll();
		foreach ($aCompany_Department_Post_Users as $key => $oCompany_Department_Post_User)
		{
			$aTempDepartmentPost[] = '<div ' . ( $key ? ' class="margin-top-5"' : '' ) . '>' . htmlspecialchars($oCompany_Department_Post_User->Company_Department->name) . '<br /><span class="darkgray">'
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
}