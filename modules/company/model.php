<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Company_Model
 *
 * @package HostCMS
 * @subpackage Company
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Company_Model extends Core_Entity
{
	/**
	 * Callback structure
	 * @var int
	 */
	public $structure = 1;

	/**
	 * Callback activity
	 * @var int
	 */
	public $activity = 1;

	/**
	 * Callback location
	 * @var int
	 */
	public $location = 1;

	/**
	 * Callback cashboxes
	 * @var int
	 */
	public $cashboxes = 1;

	/**
	 * Callback cashboxes
	 * @var int
	 */
	public $accounts = 1;

	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		'company_department' => array(),
		'company_site' => array(),
		'company_cashbox' => array(),
		'company_account' => array(),
		'chartaccount_operation' => array(),
		'chartaccount_closure_period' => array(),
		'chartaccount_entry' => array(),
		'shop' => array('model' => 'Shop', 'foreign_key' => 'shop_company_id'),
		'shop_warehouse' => array('model' => 'Shop_Warehouse', 'foreign_key' => 'shop_company_id'),
		'shop_warrant' => array(),
		'shop_warehouse_purchaseorder' => array(),
		'shop_warehouse_invoice' => array(),
		'shop_warehouse_supply' => array(),
		'shop_warehouse_purchasereturn' => array(),
		'site' => array('through' => 'company_site'),
		'siteuser_company_contract' => array(),
		'company_directory_email' => array(),
		'directory_email' => array('through' => 'company_directory_email', 'foreign_key' => 'company_id'),
		'company_directory_phone' => array(),
		'directory_phone' => array('through' => 'company_directory_phone', 'foreign_key' => 'company_id'),
		'company_directory_address' => array(),
		'directory_address' => array('through' => 'company_directory_address', 'foreign_key' => 'company_id'),
		'company_directory_website' => array(),
		'directory_website' => array('through' => 'company_directory_website', 'foreign_key' => 'company_id'),
		'company_directory_social' => array(),
		'directory_social' => array('through' => 'company_directory_social', 'foreign_key' => 'company_id'),
		'company_directory_messenger' => array(),
		'directory_messenger' => array('through' => 'company_directory_messenger', 'foreign_key' => 'company_id'),
		'user' => array('through' => 'company_department_post_user'),
		'company_department_post_user' => array(),
		'company_activity' => array(),
		'company_location' => array(),
		'dms_case_archive' => array(),
		'dms_case_destruction' => array(),
		'dms_document' => array(),
		'dms_nomenclature' => array(),
		'dms_nomenclature_dir' => array()
	);

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'user' => array()
	);

	/**
	 * Forbidden tags. If list of tags is empty, all tags will show.
	 * @var array
	 */
	protected $_forbiddenTags = array(
		'deleted',
		'user_id',
		/*'~address',
		'~phone',
		'~fax',
		'~site',
		'~email'*/
	);

	/**
	 * List of skipped columns from table
	 * @var array
	 */
	protected $_skipColumns = array(
		'~bic',
		'~current_account',
		'~correspondent_account',
		'~bank_name',
		'~bank_address',
		/*'bic',
		'current_account',
		'correspondent_account',
		'bank_name',
		'bank_address',*/
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
	 * Company departments tree with users and users posts
	 *
	 * @return array array('departments' => array(
				array(
					'department' => $oDepartment,
					'users' => array(),
					'departments' => array( ... )
				),
				array( .... ),
				....
			)
		)
	 */
	public function getDepartmentUsersPostsTree($iCompanyDepartmentParentId = 0, $oUser = NULL, $bUserIsHeader = NULL)
	{
		$aReturn = array();

		$aCompany_Departments = $this->Company_Departments->getAllByParent_id($iCompanyDepartmentParentId);

		foreach ($aCompany_Departments as $oCompany_Department)
		{
			if (!is_null($oUser) && !$bUserIsHeader)
			{
				$bUserIsHeader = $oUser->isHeadOfDepartment($oCompany_Department);
			}

			$aCompanyDepartmentInfo = array();

			if (is_null($oUser) || $bUserIsHeader)
			{
				$aCompanyDepartmentInfo['department'] = $oCompany_Department;
				$aCompanyDepartmentInfo += $this->getDepartmentUsersPostsTree($oCompany_Department->id, $oUser, $bUserIsHeader);

				$oDepartmentUsers = $oCompany_Department->Users;
				$oDepartmentUsers
					->queryBuilder()
					->groupBy('users.id');

				$aDepartmentUsers = $oDepartmentUsers->findAll();

				foreach ($aDepartmentUsers as $oDepartmentUser)
				{
					$aUserInfo = array();
					$aUserInfo['user'] = $oDepartmentUser;

					$aUserCompanyPosts = $oDepartmentUser->getCompanyPostsByDepartment($oCompany_Department->id);
					foreach ($aUserCompanyPosts as $oUserCompanyPost)
					{
						$aUserInfo['user_posts'][] = $oUserCompanyPost;
					}

					$aCompanyDepartmentInfo['users'][] = $aUserInfo;
				}

				//$aReturn['departments'][] = $aCompanyDepartmentInfo;
			}
			else
			{
				$aCompanyDepartmentInfo += $this->getDepartmentUsersPostsTree($oCompany_Department->id, $oUser, $bUserIsHeader);
			}

			$aReturn['departments'][] = $aCompanyDepartmentInfo;

			if (!is_null($oUser))
			{
				$bUserIsHeader = FALSE;
			}
		}

		return $aReturn;
	}

	/**
	 * Показ прав доступа к этапу сделки отделов и сотрудников с учетом организационной структуры компании
	 * @var array $aDepartment информация об отделе
	 */
	public function showDepartmentsAndUsers4DealTemplateStepAccess($deal_template_step_id, $aDepartment = NULL, $oPotentialHeadOfDepartment = NULL)
	{
		if (is_null($aDepartment))
		{
			// Построение массива, содержащего структуру компании
			$aDepartment = $this->getDepartmentUsersPostsTree(0, $oPotentialHeadOfDepartment);

			if (!isset($aDepartment['departments']))
			{
				return NULL;
			}
		}

		if (is_array($aDepartment) && count($aDepartment))
		{
			if (isset($aDepartment["department"]))
			{
				$issetChildrenItems = isset($aDepartment["users"]) || isset($aDepartment["departments"]);

				$aUsers = $aDepartment["department"]->getHeads();

				$aHeadIds = array();

				foreach ($aUsers as $oUser)
				{
					$aHeadIds[] = $oUser->id;
				}

				?><div id="department<?php echo $aDepartment["department"]->id?>"><div class="depatment_info"><div class="title_department"><?php echo ($issetChildrenItems ? '<i class="fa fa-caret-down fa-fw"></i>' : '') . htmlspecialchars($aDepartment["department"]->name)?><span class="icons_permissions no-actions"><?php

				$oDeal_Template_Step_Access_Department = $aDepartment["department"]->Deal_Template_Step_Access_Departments->getByDeal_template_step_id($deal_template_step_id);

				for ($bitNumber = 0; $bitNumber < 4 ; $bitNumber++)
				{
					$bitValue = !is_null($oDeal_Template_Step_Access_Department)
						? Core_Bit::getBit($oDeal_Template_Step_Access_Department->access, $bitNumber)
						: 0;

					switch($bitNumber)
					{
						case 0:
							$actionName = 'create';
							$actionTitle = Core::_('Deal_Template_Step.actionTitleCreate');
							break;

						case 1:
							$actionName = 'edit';
							$actionTitle = Core::_('Deal_Template_Step.actionTitleEdit');
							break;

						case 2:
							$actionName = 'show';
							$actionTitle = Core::_('Deal_Template_Step.actionTitleShow');
							break;

						case 3:
							$actionName = 'delete';
							$actionTitle = Core::_('Deal_Template_Step.actionTitleDelete');
							break;
					}
					?><i id="department_<?php echo $aDepartment["department"]->id . '_' . $deal_template_step_id . '_' . $bitNumber?>" title="<?php echo $actionTitle?>" data-action="<?php echo $actionName?>" data-allowed="<?php echo $bitValue?>" class="fa fa-fw <?php echo ($bitValue ? 'fa-circle' : 'fa-circle-o')?>"></i><?php
				}
				?></span>
				</div></div>
				<?php
				if ($issetChildrenItems)
				{
					?><div class="wrap"><?php
				}
			}
			else
			{
				?><div id="company<?php echo $this->id?>"><?php
			}

			if (isset($aDepartment["users"]))
			{
				?>
				<div class="users">
					<div class="list_users">
						<?php
						foreach ($aDepartment["users"] as $aUserInfo)
						{
						?>
							<div class="user">
								<div class="user_info">
									<img class="user_ico img-circle" src="<?php echo htmlspecialchars($aUserInfo['user']->getAvatar() . '?rand=' . rand())?>" />
									<div class="user_details">
										<div class="user_name semi-bold">
										<?php
										echo htmlspecialchars($aUserInfo['user']->getFullName());

										if (in_array($aUserInfo['user']->id, $aHeadIds))
										{
										?>
											<i class="fas fa-crown margin-left-5 gold"></i>
										<?php
										}
										?>
										</div>
										<?php
										// Все должности сотрудника в отделе
										$aCompany_Posts = $aUserInfo['user']->getCompanyPostsByDepartment($aDepartment["department"]->id);

										if (count($aCompany_Posts))
										{
										?>
											<div class="posts small">
											<?php

											$aCompanyPostName = array();

											foreach ($aCompany_Posts as $oCompany_Post)
											{
												$aCompanyPostName[] = htmlspecialchars($oCompany_Post->name);
											}

											echo implode(', ', $aCompanyPostName);
											?></div>
										<?php
										}

										$oDeal_Template_Step = Core_Entity::factory('Deal_Template_Step', $deal_template_step_id);
										$oDeal_Template_Step->showActions4User($aUserInfo['user']);
										?>
									</div>
								</div>
							</div>
						<?php
						}
					?>
					</div>
				</div>
				<?php
			}

			if (isset($aDepartment["departments"]))
			{
				?><div class="departments"><?php

				foreach ($aDepartment["departments"] as $aDepartmentInfo)
				{
					$this->showDepartmentsAndUsers4DealTemplateStepAccess($deal_template_step_id, $aDepartmentInfo, $oPotentialHeadOfDepartment);
				}

				?></div><?php
			}

			if (isset($issetChildrenItems) && $issetChildrenItems)
			{
				?></div><?php
			}
			?></div><?php
		}
	}

	/**
	 * Company departments tree
	 * @var array
	 */
	static protected $_aDepartmentsTree = array();

	/**
	 * Build visual representation of group tree
	 * @param int $iInformationsystemId information system ID
	 * @param int $iInformationsystemGroupParentId parent ID
	 * @param int $aExclude exclude group ID
	 * @param int $iLevel current nesting level
	 * @return array
	 */
	public function fillDepartments($iCompanyDepartmentParentId = 0, $aExclude = array(), $iLevel = 0)
	{
		$iCompanyDepartmentParentId = intval($iCompanyDepartmentParentId);
		$iLevel = intval($iLevel);

		if ($iLevel == 0)
		{
			$aTmp = Core_QueryBuilder::select('id', 'parent_id', 'name')
				->from('company_departments')
				->where('company_id', '=', $this->id)
				->where('deleted', '=', 0)
				->orderBy('name')
				->execute()->asAssoc()->result();

			foreach ($aTmp as $aDepartment)
			{
				self::$_aDepartmentsTree[$aDepartment['parent_id']][] = $aDepartment;
			}
		}

		$aReturn = array();

		if (isset(self::$_aDepartmentsTree[$iCompanyDepartmentParentId]))
		{
			$countExclude = count($aExclude);
			foreach (self::$_aDepartmentsTree[$iCompanyDepartmentParentId] as $childrenDepartment)
			{
				if ($countExclude == 0 || !in_array($childrenDepartment['id'], $aExclude))
				{
					//$aReturn["'" . $childrenDepartment['id'] . "'"] = str_repeat('  ', $iLevel) . $childrenDepartment['name'];
					$aReturn[$childrenDepartment['id']] = str_repeat('  ', $iLevel) . $childrenDepartment['name'];
					$aReturn += $this->fillDepartments($childrenDepartment['id'], $aExclude, $iLevel + 1);
				}
			}
		}

		$iLevel == 0 && self::$_aDepartmentsTree = array();

		return $aReturn;
	}

	/**
	 * Build visual representation of group tree
	 * @param int $iCompanyDepartmentParentId company department parent ID
	 * @param int $aExclude exclude group ID
	 * @param int $iLevel current nesting level
	 * @return array
	 */
	public function fillDepartmentsAndUsers($iCompanyDepartmentParentId = 0, $aExclude = array(), $iLevel = 0)
	{
		$iCompanyDepartmentParentId = intval($iCompanyDepartmentParentId);
		$iLevel = intval($iLevel);

		if ($iLevel == 0)
		{
			self::$_aDepartmentsTree = array();
			$aTmp = Core_QueryBuilder::select('id', 'parent_id', 'name')
				->from('company_departments')
				->where('company_id', '=', $this->id)
				->where('deleted', '=', 0)
				->orderBy('name')
				->execute()->asAssoc()->result();

			foreach ($aTmp as $aDepartment)
			{
				self::$_aDepartmentsTree[$aDepartment['parent_id']][] = $aDepartment;
			}
		}

		$aReturn = array();

		if (isset(self::$_aDepartmentsTree[$iCompanyDepartmentParentId]))
		{
			$countExclude = count($aExclude);
			foreach (self::$_aDepartmentsTree[$iCompanyDepartmentParentId] as $childrenDepartment)
			{
				if (!$countExclude || !in_array($childrenDepartment['id'], $aExclude))
				{
					// $iMarginLeft = ($iLevel + 1) * 15;
					$iMarginLeft = $iLevel * 15;

					$oOptgroup = new stdClass();
					$oOptgroup->attributes = array(
						'label' => $childrenDepartment['name'],
						'class' => 'company-department',
						'style' => "margin-left: {$iMarginLeft}px"
					);
					$oOptgroup->children = array();

					$oCompanyDepartment = Core_Entity::factory('Company_Department', $childrenDepartment['id']);
					$aDepartmentUsers = $oCompanyDepartment->Users->findAll();

					foreach ($aDepartmentUsers as $oDepartmentUser)
					{
						$aUserCompanyPosts = array();
						$aObjectUserCompanyPosts = $oDepartmentUser->getCompanyPostsByDepartment($childrenDepartment['id']);

						foreach ($aObjectUserCompanyPosts as $oObjectUserCompanyPost)
						{
							$aUserCompanyPosts[] = $oObjectUserCompanyPost->name;
						}
						$sUserCompanyPosts = implode('###', $aUserCompanyPosts);

						$sOptionValue = $oDepartmentUser->getFullName() . '%%%' . $oCompanyDepartment->name
							. '%%%' . (!empty($sUserCompanyPosts) ? $sUserCompanyPosts : '')
							. '%%%' . $oDepartmentUser->getAvatar() . '?rand=' . rand();

						$oOptgroup->children[$oDepartmentUser->id] = array(
							'value' => $sOptionValue,
							'attr' => array('class' => 'user-name', 'style' => "margin-left: {$iMarginLeft}px", 'xxx' => 'yyy')
						);
					}

					$oOptgroup->children += $this->fillDepartmentsAndUsers($childrenDepartment['id'], $aExclude, $iLevel + 1);

					$aReturn['company_department_' . $childrenDepartment['id']] = $oOptgroup;
				}
			}
		}

		//$iLevel == 0 && self::$_aDepartmentsTree = array();

		return $aReturn;
	}

	/**
	 * Backend badge
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function nameBadge($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		$count = $this->Company_Department_Post_Users->getCount(FALSE, 'user_id', TRUE);
		$count && Core_Html_Entity::factory('Span')
			->class('badge badge-hostcms badge-square')
			->title(Core::_('Company_Department.caption_block_users'))
			->value('<i class="fa fa-user"></i> ' . $count)
			->execute();

		$oCompany_Site = $this->Company_Sites->getBySite_id(CURRENT_SITE);

		!is_null($oCompany_Site) &&
			Core_Html_Entity::factory('Span')
				->value('<i class="fa fa-check-circle-o palegreen"></i>')
				->title(Core::_('Company.sites'))
				->execute();
	}

	/**
	 * Backend badge
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function structureBadge($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		$count = $this->Company_Departments->getCount();
		$count && Core_Html_Entity::factory('Span')
			->class('badge badge-ico badge-azure white')
			->value($count < 100 ? $count : '∞')
			->title($count)
			->execute();
	}

	/**
	 * Backend badge
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function activityBadge($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		$count = $this->Company_Activities->getCount();
		$count && Core_Html_Entity::factory('Span')
			->class('badge badge-ico badge-darkorange white')
			->value($count < 100 ? $count : '∞')
			->title($count)
			->execute();
	}

	/**
	 * Backend badge
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function locationBadge($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		$count = $this->Company_Locations->getCount();
		$count && Core_Html_Entity::factory('Span')
			->class('badge badge-ico badge-yellow white')
			->value($count < 100 ? $count : '∞')
			->title($count)
			->execute();
	}

	/**
	 * Backend badge
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function cashboxesBadge($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		$count = $this->Company_Cashboxes->getCount();
		$count && Core_Html_Entity::factory('Span')
			->class('badge badge-ico badge-palegreen white')
			->value($count < 100 ? $count : '∞')
			->title($count)
			->execute();
	}

	/**
	 * Backend badge
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function accountsBadge($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		$count = $this->Company_Accounts->getCount();
		$count && Core_Html_Entity::factory('Span')
			->class('badge badge-ico badge-pink white')
			->value($count < 100 ? $count : '∞')
			->title($count)
			->execute();
	}

	/**
	 * Get company avatar
	 * @return string
	 */
	public function getAvatar()
	{
		return strlen((string) $this->image)
			? $this->getImageHref()
			: "/admin/company/index.php?loadCompanyAvatar={$this->id}";
	}

	public function imgBackend()
	{
		return '<img width="25" class="company-image" src="' . $this->getAvatar() . '"/>';
	}

	/**
	 * Get company href
	 * @return string
	 */
	public function getHref()
	{
		$aConfig = Core_Config::instance()->get('company_config', array()) + array(
			'uploaddir' => 'upload/'
		);

		return $aConfig['uploaddir'] . 'company_' . intval($this->id) . '/';
	}

	/**
	 * Get company path include CMS_FOLDER
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
		return '/' . $this->getHref() . $this->image;
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
	 */
	public function deleteImageFile()
	{
		try
		{
			Core_File::isFile($this->getImageFilePath()) && Core_File::delete($this->getImageFilePath());
		} catch (Exception $e) {}

		$this->image = '';
		$this->save();

		return $this;
	}

	/**
	 * Delete person directory
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
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return Core_Entity
	 * @hostcms-event company.onBeforeRedeclaredDelete
	 */
	public function delete($primaryKey = NULL)
	{
		if (is_null($primaryKey))
		{
			$primaryKey = $this->getPrimaryKey();
		}

		$this->id = $primaryKey;

		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredDelete', $this, array($primaryKey));

		$this->Company_Departments->deleteAll(FALSE);
		$this->Company_Department_Post_Users->deleteAll(FALSE);
		$this->Company_Sites->deleteAll(FALSE);
		$this->Company_Activities->deleteAll(FALSE);
		$this->Company_Locations->deleteAll(FALSE);
		$this->Company_Cashboxes->deleteAll(FALSE);
		$this->Company_Accounts->deleteAll(FALSE);

		$this->Directory_Addresses->deleteAll(FALSE);
		$this->Directory_Emails->deleteAll(FALSE);
		$this->Directory_Phones->deleteAll(FALSE);
		$this->Directory_Websites->deleteAll(FALSE);
		$this->Directory_Socials->deleteAll(FALSE);
		$this->Directory_Messengers->deleteAll(FALSE);

		if (Core::moduleIsActive('dms'))
		{
			$this->Dms_Case_Archives->deleteAll(FALSE);
			$this->Dms_Case_Destructions->deleteAll(FALSE);
			$this->Dms_Documents->deleteAll(FALSE);
			$this->Dms_Nomenclatures->deleteAll(FALSE);
			$this->Dms_Nomenclature_Dirs->deleteAll(FALSE);
		}

		if (Core::moduleIsActive('chartaccount'))
		{
			$this->Chartaccount_Operations->deleteAll(FALSE);
			$this->Chartaccount_Closure_Periods->deleteAll(FALSE);
			$this->Chartaccount_Entries->deleteAll(FALSE);
		}

		if (Core::moduleIsActive('shop'))
		{
			$this->Shop_Warrants->deleteAll(FALSE);
			$this->Shop_Warehouse_Purchaseorders->deleteAll(FALSE);
			$this->Shop_Warehouse_Invoices->deleteAll(FALSE);
			$this->Shop_Warehouse_Supplies->deleteAll(FALSE);
			$this->Shop_Warehouse_Purchasereturns->deleteAll(FALSE);
		}

		if (Core::moduleIsActive('siteuser'))
		{
			$this->Siteuser_Company_Contracts->deleteAll(FALSE);
		}

		// Удаляем директорию
		$this->deleteDir();

		return parent::delete($primaryKey);
	}

	/**
	 * Get XML for entity and children entities
	 * @return string
	 * @hostcms-event company.onBeforeRedeclaredGetXml
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
	 * @hostcms-event company.onBeforeRedeclaredGetStdObject
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
		$oCompany_Account = $this->Company_Accounts->getDefault();

		if (!is_null($oCompany_Account))
		{
			$this
				->addXmlTag('bic', $oCompany_Account->bic)
				->addXmlTag('current_account', $oCompany_Account->current_account)
				->addXmlTag('correspondent_account', $oCompany_Account->correspondent_account)
				->addXmlTag('bank_name', $oCompany_Account->bank_name)
				->addXmlTag('bank_address', $oCompany_Account->bank_address);
		}

		$aDirectory_Addresses = $this->Directory_Addresses->findAll();
		$aDirectory_Phones = $this->Directory_Phones->findAll();
		$aDirectory_Websites = $this->Directory_Websites->findAll();
		$aDirectory_Emails = $this->Directory_Emails->findAll();
		$aDirectory_Socials = $this->Directory_Socials->findAll();
		$aDirectory_Messengers = $this->Directory_Messengers->findAll();

		$this
			->addEntities($aDirectory_Addresses)
			->addEntities($aDirectory_Phones)
			->addEntities($aDirectory_Emails)
			->addEntities($aDirectory_Websites)
			->addEntities($aDirectory_Socials)
			->addEntities($aDirectory_Messengers);

		$aCompany_Accounts = $this->Company_Accounts->findAll();
		$this->addEntities($aCompany_Accounts);

		return $this;
	}

	/**
	 * Get full address
	 * @return string
	 */
	public function getFullAddress()
	{
		$sFullCompanyAddress = '';

		$aDirectory_Addresses = $this->Directory_Addresses->findAll();
		if (isset($aDirectory_Addresses[0]))
		{
			$aCompanyAddress = array(
				$aDirectory_Addresses[0]->postcode,
				$aDirectory_Addresses[0]->country,
				$aDirectory_Addresses[0]->city,
				$aDirectory_Addresses[0]->value
			);

			$aCompanyAddress = array_filter($aCompanyAddress, 'strlen');
			$sFullCompanyAddress = implode(', ', $aCompanyAddress);
		}

		return $sFullCompanyAddress;
	}

	/**
	 * Get phone
	 * @return string
	 */
	public function getPhone()
	{
		$sCompanyPhone = '';

		$aDirectory_Phones = $this->Directory_Phones->findAll();
		if (isset($aDirectory_Phones[0]))
		{
			$sCompanyPhone = $aDirectory_Phones[0]->value;
		}

		return $sCompanyPhone;
	}

	/**
	 * Return html profile block
	 * @param string $class class for block
	 * @return string
	 */
	public function getProfileBlock($class = '')
	{
		/*$oUser = Core_Auth::getCurrentUser();
		$sFullName = $this->name;

		$oAdmin_Form = Core_Entity::factory('Admin_Form', 64);

		$nameLink = $oAdmin_Form->Admin_Form_Actions->checkAllowedActionForUser($oUser, 'view')
			? '<a href="/admin/siteuser/representative/index.php?hostcms[action]=view&hostcms[checked][0][' . $this->id . ']=1&siteuser_id=' . $this->siteuser_id . '" onclick="$.modalLoad({path: \'/admin/siteuser/representative/index.php\', action: \'view\', operation: \'modal\', additionalParams: \'hostcms[checked][0][' . $this->id . ']=1&siteuser_id=' . $this->siteuser_id . '\', windowId: \'id_content\', width: \'70%\'}); return false">' . htmlspecialchars($sFullName) . '</a>'
			: htmlspecialchars($this->name);*/

		/*$imgLink = $oAdmin_Form->Admin_Form_Actions->checkAllowedActionForUser($oUser, 'edit')
			? '<a href="/admin/siteuser/representative/index.php?hostcms[action]=edit&hostcms[checked][0][' . $this->id . ']=1&siteuser_id=' . $this->siteuser_id . '" onclick="$.modalLoad({path: \'/admin/siteuser/representative/index.php\', action: \'edit\', operation: \'modal\', additionalParams: \'hostcms[checked][0][' . $this->id . ']=1&siteuser_id=' . $this->siteuser_id . '&parentWindowId=id_content\', view: \'list\', windowId: \'id_content\', width: \'70%\'}); return false">
				<i class="fa fa-building"></i>
				<i class="fa fa-pencil"></i>
			</a>'
			: '<i class="fa fa-building"></i>';*/

		$tin = !empty($this->tin) ? '<div class="tin">' . Core::_('Company.tin_list', $this->tin) . '</div>' : '';

		return '<li class="ticket-item ' . $class . '" data-popover="hover" data-company-id="' . $this->id . '">
			<div class="row">
				<div class="ticket-user ticket-siteuser col-xs-12"><img class="siteuser-avatar lazy" data-src="' . $this->getAvatar() .'" />
					<div class="user-name"><div>' . htmlspecialchars($this->name) . '</div>' . $tin . '</div>
				</div>
			</div>
		</li>';
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
			<div class="semi-bold"><?php echo htmlspecialchars($this->name)?></div>
		</div>
		<?php

		$aDirectory_Addresses = $this->Directory_Addresses->findAll(FALSE);

		if (count($aDirectory_Addresses))
		{
			?><div class="margin-top-5"><?php
			foreach ($aDirectory_Addresses as $oDirectory_Address)
			{
				if (strlen(trim($oDirectory_Address->value)))
				{
					$oDirectory_Address_Type = Core_Entity::factory('Directory_Address_Type')->find($oDirectory_Address->directory_address_type_id);

					$sAddressType = !is_null($oDirectory_Address_Type->id)
						? htmlspecialchars($oDirectory_Address_Type->name) . ": "
						: '';

					?><div><span class="popup-type"><i class="fa fa-map-marker fa-fw darkorange"></i> <?php echo $sAddressType?></span><span><?php echo htmlspecialchars($oDirectory_Address->getFullAddress())?></span></div><?php
				}
			}
		}

		$aDirectory_Phones = $this->Directory_Phones->findAll(FALSE);

		if (count($aDirectory_Phones))
		{
			?><div class="margin-top-5"><?php
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
	 * Get user's list for companies
	 * <code>
	 * $oSite = Core_Entity::factory('Site', CURRENT_SITE);
	 * $aUserOptions = $oSite->Companies->getUsersOptions();
	 *
	 * $oNotificationSubscribersSelect = Admin_Form_Entity::factory('Select')
	 * 	->caption('Caption')
	 * 	->options($aUserOptions);
	 * </code>
	 * @return array
	 */
	public function getUsersOptions()
	{
		$aUserOptions = array();
		$aCompanies = $this->findAll();
		foreach ($aCompanies as $oCompany)
		{
			$oOptgroupCompany = new stdClass();
			$oOptgroupCompany->attributes = array('label' => $oCompany->name, 'class' => 'company');
			$oOptgroupCompany->children = $oCompany->fillDepartmentsAndUsers();

			$aUserOptions[] = $oOptgroupCompany;
		}

		return $aUserOptions;
	}

	/**
	 * Add company CommerceML
	 * @param Core_SimpleXMLElement $oXml
	 */
	public function addCml(Core_SimpleXMLElement $oXml)
	{
		$oCompanyXml = $oXml->addChild('Владелец');
		$oCompanyXml->addChild('Ид', $this->guid);
		$oCompanyXml->addChild('Наименование', $this->name);

		$this->tin != ''
			&& $oCompanyXml->addChild('ИНН', $this->tin);

		$this->kpp != ''
			&& $oCompanyXml->addChild('КПП', $this->kpp);

		return $this;
	}


	/**
	 * Check user access to admin form action
	 * @param string $actionName admin form action name
	 * @param User_Model $oUser user object
	 * @return bool
	 */
	public function checkBackendAccess($actionName, $oUser)
	{
		// Create Company
		if (!$this->id)
		{
			return TRUE;
		}

		if ($oUser->superuser)
		{
			return TRUE;
		}

		$aSites = $this->Sites->findAll();

		if (count($aSites))
		{
			$aUser_Sites = $oUser->getSites();

			foreach ($aSites as $oSite)
			{
				foreach ($aUser_Sites as $oUser_Site)
				{
					if ($oSite->id == $oUser_Site->id)
					{
						return TRUE;
					}
				}
			}
		}

		return FALSE;
	}

	static protected $_oldCompanyFields = array('bic', 'current_account', 'correspondent_account', 'bank_name', 'bank_address');

	public function __get($property)
	{
		if (in_array($property, self::$_oldCompanyFields))
		{
			switch ($property)
			{
				case 'bic':
					$oCompany_Account = $this->_getCompanyDefaultAccount();
					$return = !is_null($oCompany_Account)
						? $oCompany_Account->bic
						: '';
				break;
				case 'current_account':
					$oCompany_Account = $this->_getCompanyDefaultAccount();
					$return = !is_null($oCompany_Account)
						? $oCompany_Account->current_account
						: '';
				break;
				case 'correspondent_account':
					$oCompany_Account = $this->_getCompanyDefaultAccount();
					$return = !is_null($oCompany_Account)
						? $oCompany_Account->correspondent_account
						: '';
				break;
				case 'bank_name':
					$oCompany_Account = $this->_getCompanyDefaultAccount();
					$return = !is_null($oCompany_Account)
						? $oCompany_Account->bank_name
						: '';
				break;
				case 'bank_address':
					$oCompany_Account = $this->_getCompanyDefaultAccount();
					$return = !is_null($oCompany_Account)
						? $oCompany_Account->bank_address
						: '';
				break;
				default:
					$return = NULL;
			}

			return $return;
		}

		// Before table changed
		if (isset($this->$property))
		{
			return parent::__get($property);
		}

		return parent::__get($property);
	}

	protected function _getCompanyDefaultAccount()
	{
		return Core_Entity::factory('Company', $this->id)->Company_Accounts->getDefault();
	}

	public function __call($name, $arguments)
	{
		if (in_array($name, self::$_oldCompanyFields))
		{
			//$this->$name = $arguments[0];
			return !count($arguments)
				? $this->$name
				: $this;
		}

		return parent::__call($name, $arguments);
	}

	public function __isset($property)
	{
		if (in_array($property, self::$_oldCompanyFields))
		{
			return TRUE;
		}

		return parent::__isset($property);
	}
}