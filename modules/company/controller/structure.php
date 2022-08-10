<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Company_Controller_Structure
 *
 * @package HostCMS
 * @subpackage Company
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Company_Controller_Structure extends Admin_Form_Controller_View
{
	/**
	 * Company_Model
	 * @var object
	 */
	protected $_oCompany = NULL;

	protected $_aCompany_Departmens = array();
	protected $_aCompany_Users = array();

	public function execute()
	{
		$company_id = Core_Array::getRequest('company_id');
		$this->_oCompany = Core_Entity::factory('Company', $company_id);

		$oAdmin_Form_Controller = $this->_Admin_Form_Controller;
		$oAdmin_Form = $oAdmin_Form_Controller->getAdminForm();

		$oAdmin_View = Admin_View::create($this->_Admin_Form_Controller->Admin_View)
			->pageTitle($oAdmin_Form_Controller->pageTitle)
			->module($oAdmin_Form_Controller->module);

		$aAdminFormControllerChildren = array();

		foreach ($oAdmin_Form_Controller->getChildren() as $oAdmin_Form_Entity)
		{
			if ($oAdmin_Form_Entity instanceof Skin_Bootstrap_Admin_Form_Entity_Breadcrumbs
				|| $oAdmin_Form_Entity instanceof Skin_Bootstrap_Admin_Form_Entity_Menus)
			{
				$oAdmin_View->addChild($oAdmin_Form_Entity);
			}
			else
			{
				$aAdminFormControllerChildren[] = $oAdmin_Form_Entity;
			}
		}

		// При показе формы могут быть добавлены сообщения в message, поэтому message показывается уже после отработки формы
		ob_start();
		/*?>
		<div class="table-toolbar">
			<?php $this->_Admin_Form_Controller->showFormMenus()?>
			<div class="clear"></div>
		</div>
		<?php*/
		foreach ($aAdminFormControllerChildren as $oAdmin_Form_Entity)
		{
			$oAdmin_Form_Entity->execute();
		}

		$this->_showContent();
		$content = ob_get_clean();

		$oAdmin_View
			->content($content)
			->message($oAdmin_Form_Controller->getMessage())
			->show();

		//$oAdmin_Form_Controller->applyEditable();
		$oAdmin_Form_Controller->showSettings();

		return $this;
	}

	/**
	 * Show form content in administration center
	 * @return self
	 */
	protected function _showContent()
	{
		$oAdmin_Form_Controller = $this->_Admin_Form_Controller;
		$oAdmin_Form = $oAdmin_Form_Controller->getAdminForm();

		$oAdmin_Language = $oAdmin_Form_Controller->getAdminLanguage();

		$aAdmin_Form_Fields = $oAdmin_Form->Admin_Form_Fields->findAll();

		$oSortingField = $oAdmin_Form_Controller->getSortingField();

		if (empty($aAdmin_Form_Fields))
		{
			throw new Core_Exception('Admin form does not have fields.');
		}

		$windowId = $oAdmin_Form_Controller->getWindowId();

		$oUser = Core_Auth::getCurrentUser();

		$oMainWrapper = Admin_Form_Entity::factory('Div');

		// Массив названий разрешенных действий
		$aAllowedActions = array();

		$aActions = array('editDepartment', 'deleteDepartment', 'editUserDepartment', 'deleteUserFromDepartment');

		$aAdmin_Form_Actions = $oAdmin_Form->Admin_Form_Actions->getAllowedActionsForUser($oUser);
		foreach ($aAdmin_Form_Actions as $oAdmin_Form_Action)
		{
			if (in_array($oAdmin_Form_Action->name, $aActions))
			{
				$aAllowedActions[] = $oAdmin_Form_Action->name;
			}
		}

		if ($oUser->superuser)
		{
			$aAllowedActions[] = 'changeAccessModulesAndActions';
		}

		// Сотруднику доступно добавление отдела
		if (in_array('editDepartment', $aAllowedActions) || in_array('changeAccessModulesAndActions', $aAllowedActions))
		{
			$oMainWrapper->add(
				// Кнопка "Добавить отдел"
				Admin_Form_Entity::factory('Div')
					->class('row')
					->add(
						Admin_Form_Entity::factory('Div')
							->class('col-lg-12')
							->add(
								Admin_Form_Entity::factory('Div')
									->class('widget flat no-margin-bottom')
									->add(
										Admin_Form_Entity::factory('Div')
											->class('widget-body')
											->add(
												Admin_Form_Entity::factory('Div')
													->class('row')
													->add(
														Admin_Form_Entity::factory('Div')
															->class('col-lg-3')
															->add(
																Admin_Form_Entity::factory('a')
																	->id('editDepartment')
																	->class('btn btn-palegreen')
																	->add(
																		Admin_Form_Entity::factory('Code')
																			->html('<i class="fa fa-plus"></i>' . Core::_('Company_Department.addDepartmentButtonTitle'))
																	)
															)
													)
											)
									)
							)
					)
			)->add(
				Admin_Form_Entity::factory('Code')
					->html("<script>$('#{$windowId} #editDepartment').on('click', function () {
						$.adminLoad({path: '{$oAdmin_Form_Controller->getPath()}', additionalParams: 'hostcms[checked][0][0]=1&company_id={$this->_oCompany->id}', action: 'editDepartment', windowId: '{$windowId}'});
					});</script>")
			);
		}

		// Устанавливаем ограничения на источники
		$oAdmin_Form_Controller->setDatasetConditions();

		$aDatasets = $oAdmin_Form_Controller->getDatasets();

		$aCompany_Departments = $aDatasets[0]->load();
		if (count($aCompany_Departments))
		{
			foreach ($aCompany_Departments as $oCompany_Department)
			{
				$this->_aCompany_Departmens[$oCompany_Department->parent_id][] = $oCompany_Department;
			}

			$aCompany_Users = $aDatasets[1]->load();
			foreach ($aCompany_Users as $oCompany_User)
			{
				$aUser_Company_Departments = $oCompany_User->Company_Departments->findAll(FALSE);

				foreach ($aUser_Company_Departments as $oCompany_Department)
				{
					$this->_aCompany_Users[$oCompany_Department->id][$oCompany_User->id] = $oCompany_User;
				}
			}

			$oMainWrapper
				->add(
					Admin_Form_Entity::factory('Div')
						->class('row')
						->add(
							Admin_Form_Entity::factory('Div')
								->class('col-xs-12')
								->add(
									Admin_Form_Entity::factory('Div')
										->class('company dd bordered' . (in_array('editDepartment', $aAllowedActions) ? ' company-editable' : ''))
										->add(
											Admin_Form_Entity::factory('Code')
												->html($this->_showLevel(0, $aAllowedActions))
										)
								)
						)
				);

			$oMainWrapper
				->add(
					Admin_Form_Entity::factory('Code')
						->html("<script>
								jQuery(function ($){

									var aScripts = [
										'jquery.nestable.min.js'
									];

									$.getMultiContent(aScripts, '/modules/skin/bootstrap/js/nestable/').done(function() {
									// all scripts loaded"
									. (
										in_array('editDepartment', $aAllowedActions)
											? "
											var divCompanyStructure = $('#{$windowId} .dd');
											divCompanyStructure.nestable({maxDepth: 30});

											divCompanyStructure.data('serializedCompanyStructure', divCompanyStructure.nestable('serialize'));


											$('.dd-handle', divCompanyStructure).on('mousedown touchstart', '.dropdown-backdrop', function(e){
												e.stopPropagation();
											});

											$('.control-buttons-wrapper', divCompanyStructure).on('mousedown touchstart', function(e){
												e.stopPropagation();
											});

											//$('.dd-handle .btn', divCompanyStructure).on('mousedown touchstart', function (e) {
											$('.dd-handle [data-action]', divCompanyStructure).on('mousedown touchstart', function (e) {
												e.stopPropagation();
											});

											$('.dd-handle .dropdown-menu', divCompanyStructure).on('mousedown touchstart', function (e) {
												e.stopPropagation();
											});

											$('.dd-handle .databox', divCompanyStructure).on('mousedown touchstart', function (e) {
												e.stopPropagation();
											});

											$('.dd-handle .widget', divCompanyStructure).on('mousedown touchstart', function (e) {
												e.stopPropagation();
											});

											// Функция получения идентификатора родительского отдела по идентификатору отдела
											function getParentIdByDepartmentId (serializedCompanyStructure, departmentId, currentParentDepartmentId)
											{
												var currentParentDepartmentId = currentParentDepartmentId || 0;

												for (var i = 0; i < serializedCompanyStructure.length; i++)
												{
													if (serializedCompanyStructure[i].id == departmentId)
													{
														return currentParentDepartmentId;
													}
													else if (serializedCompanyStructure[i].children)
													{
														var parentDepartmentId = getParentIdByDepartmentId(serializedCompanyStructure[i].children, departmentId, serializedCompanyStructure[i].id);

														if (parentDepartmentId)
														{
															return parentDepartmentId;
														}
													}
												}
											}

											function nestableOnStartEvent(event)
											{
												var \$this = $(this),
													departmentId = \$this.closest('li.dd-item').data('id'),
													divCompanyStructure = $('#{$windowId} .dd'),
													parentDepartmentId = getParentIdByDepartmentId(divCompanyStructure.data('serializedCompanyStructure'), departmentId);

												divCompanyStructure.data({'departmentId': departmentId, 'parentDepartmentId': parentDepartmentId});
											}

											function nestableOnEndEvent(event)
											{
												var	divCompanyStructure = $('#{$windowId} .dd'),
													parentDepartmentId = divCompanyStructure.data('parentDepartmentId'),
													departmentId = divCompanyStructure.data('departmentId'),
													newParentDepartmentId = $('li[data-id = ' + departmentId + '] ', divCompanyStructure).closest('ol.dd-list').closest('li.dd-item').data('id') || 0;

												if (newParentDepartmentId != parentDepartmentId)
												{
													bootbox.setLocale('ru');
													bootbox.confirm({
														title: '" . Core::_('Company_Department.edit_form_title') . "',
														message: '" . Core::_('Company_Department.moveMessage') . "',
														callback: function (result) {
															var divCompanyStructure = $('#{$windowId} .dd'),
																paramAdminLoad = {
																'path': '{$oAdmin_Form_Controller->getPath()}',
																'additionalParams': 'company_id={$this->_oCompany->id}&hostcms[checked][0][' + departmentId +']=1',
																'windowId' : '{$windowId}'
															}

															if (result) {
																paramAdminLoad.additionalParams += '&department_id=' + departmentId + '&new_parent_id=' + newParentDepartmentId;
																paramAdminLoad.operation = 'changeParentDepartment';
																paramAdminLoad.action = 'editDepartment';
															}

															$.adminLoad(paramAdminLoad);
															divCompanyStructure.data('serializedCompanyStructure', divCompanyStructure.nestable('serialize'));
														}
													});
												}
											}

											divCompanyStructure
												.on('mousedown', '.dd-handle', nestableOnStartEvent)
												.on('change', nestableOnEndEvent);

											$(document).on('touchstart', '.dd-handle', nestableOnStartEvent);"
											: ""
									)
									. (
										count($aAllowedActions)
											? "
											// Действия с отделами и сотрудниками
											$('#{$windowId} .department [data-action]').on('click', function(){
													var \$this = $(this),
													actionName = \$this.data('action'),
													departmentId = \$this.closest('li.dd-item').data('id'),
													paramAdminLoad = {
														path: '{$oAdmin_Form_Controller->getPath()}',
														additionalParams: 'company_id={$this->_oCompany->id}&department_id=' + \$this.closest('li.dd-item').data('id'),
														windowId:'{$windowId}'
													};

													switch(actionName)
													{
														"
														. (
															in_array('deleteDepartment', $aAllowedActions)
																? "
																case 'delete':
																	bootbox.setLocale('ru');
																	bootbox.confirm({
																		title: '" . Core::_('Company_Department.delete_form_title') . "',
																		message: '" . Core::_('Company_Department.deleteMessage') . "',
																		callback: function (result) {
																			if (result) {
																				paramAdminLoad.operation = 'deleteDepartment';
																				paramAdminLoad.action = 'deleteDepartment';
																				paramAdminLoad.additionalParams += '&hostcms[checked][0][' + departmentId +']=1';
																				$.adminLoad(paramAdminLoad);
																			}
																		}
																	});
																	break;"
																: ""
														)
														. (
															in_array('editDepartment', $aAllowedActions)
																? "
																case 'edit':

																	paramAdminLoad.action = 'editDepartment';
																	paramAdminLoad.additionalParams += '&hostcms[checked][0][' + departmentId +']=1';
																	$.adminLoad(paramAdminLoad);
																	break;"
																: ""
														)
														. (
															in_array('editUserDepartment', $aAllowedActions)
																? "
																case 'add_user':
																	paramAdminLoad.action = 'editUserDepartment';
																	paramAdminLoad.additionalParams += '&hostcms[checked][1][0]=1';
																	$.adminLoad(paramAdminLoad);
																	break;
																case 'edit_user':
																	var userElement = \$this.closest('.user');
																	paramAdminLoad.action = 'editUserDepartment';
																	paramAdminLoad.additionalParams += '&hostcms[checked][1][' + userElement.data('user-id') +']=1' + '&company_post_id=' + userElement.data('company-post-id');
																	$.adminLoad(paramAdminLoad);
																	break;"
																: ""

														)
														. (
															in_array('deleteUserFromDepartment', $aAllowedActions)
																? "
																case 'delete_user':
																	bootbox.setLocale('ru');
																	bootbox.confirm({
																		title: '" . Core::_('Company_Department.delete_user_title') . "',
																		message: '" . Core::_('Company_Department.deleteUserMessage') . "',
																		callback: function (result) {
																			if (result) {
																				var userElement = \$this.closest('.user');
																				paramAdminLoad.operation = 'deleteUserFromDepartment';
																				paramAdminLoad.action = 'deleteUserFromDepartment';
																				paramAdminLoad.additionalParams += '&hostcms[checked][1][' + userElement.data('user-id') + ']=1' + '&company_post_id=' + userElement.data('company-post-id');

																				$.adminLoad(paramAdminLoad);
																			}
																		}
																	});
																	break;"
																: ""
														)
														. "
													}
												}
											);"
											: ""
									)
									. "
									});
								});

								jQuery(function ($){
									$('#{$windowId} .department-users .scroll-wrapper').each(function (){
											var \$this = $(this);

											if (\$this.find('tr.user').length > 3)
											{
												\$this.slimscroll({
													// height: '215px',
													alwaysVisible: true,
													height: 'auto',
													//color: 'rgba(0,0,0,0.3)',
													color: themeprimary,
													size: '5px'
												});
											}

											//console.log($(this), $(this).outerHeight());
									});

									$('#{$windowId} .dd button').click();

									$('#{$windowId} .department-users.widget.collapsed .widget-buttons *[data-toggle=\"collapse\"]').click();
							})
							</script>")
				);
		}

		$oMainWrapper->execute();

		return $this;
	}

	protected function _showLevel($parent_id, $aAllowedActions = array())
	{
		// $aHeadIds = array();

		if (isset($this->_aCompany_Departmens[$parent_id]))
		{
			ob_start();
			?>
			<ol class="dd-list">
			<?php
			foreach ($this->_aCompany_Departmens[$parent_id] as $oCompany_Department)
			{
			?>
				<li class="dd-item" data-id="<?php echo $oCompany_Department->id?>">
					<div class="dd-handle department"><?php echo htmlspecialchars($oCompany_Department->name)?>

					<?php
					// У сотрудника есть право редактирования структуры компании
					if (count($aAllowedActions))
					{
					?>
						<div class="control-buttons-wrapper department-top-actions">
							<div class="btn-group department-control-buttons">
								<?php
								if (in_array('editUserDepartment', $aAllowedActions))
								{
								?>
									<a href="javascript:void(0);" class="bordered-palegreen" data-action="add_user" title="<?php echo Core::_('Company_Department.addUserTitleAction')?>" alt="<?php echo Core::_('Company_Department.addUserTitleAction');?>">
										<i class="fa fa-user-plus palegreen"></i>
									</a>
								<?php
								}

								if (in_array('editDepartment', $aAllowedActions))
								{
								?>
									<a href="javascript:void(0);" class="bordered-gray" data-action="edit" title="<?php echo Core::_('Company_Department.editTitleAction', htmlspecialchars($oCompany_Department->name))?>" alt="<?php echo Core::_('Company_Department.editTitleAction', htmlspecialchars($oCompany_Department->name))?>">
										<i class="fa fa-pencil darkgray"></i>
									</a>
								<?php
								}

								if (in_array('deleteDepartment', $aAllowedActions))
								{
								?>
									<a href="javascript:void(0);" class="bordered-darkorange" data-action="delete" title="<?php echo Core::_('Company_Department.deleteTitleAction')?>" alt="<?php echo Core::_('Company_Department.deleteTitleAction')?>">
										<i class="fa fa-trash-o darkorange "></i>
									</a>
								<?php
								}

								// Сотруднику разрешено изменение структуры компании и ее штата сотрудников
								if (in_array('changeAccessModulesAndActions', $aAllowedActions))
								{
								?>
									<a href="/admin/user/site/index.php?company_department_id=<?php echo $oCompany_Department->id?>" onclick="$.adminLoad({path: '/admin/user/site/index.php',action: '',operation: '',additionalParams: 'company_department_id=<?php echo $oCompany_Department->id?>',current: '1',windowId: 'id_content'}); return false" class="bordered-sky" data-action="module_access" title="<?php echo Core::_('Company_Department.moduleTitleAction')?>" alt="<?php echo Core::_('Company_Department.moduleTitleAction')?>">
										<i class="fa fa-cogs sky"></i>
									</a>
									<a href="/admin/user/site/index.php?company_department_id=<?php echo $oCompany_Department->id?>&mode=action" onclick="$.adminLoad({path: '/admin/user/site/index.php',action: '',operation: '',additionalParams: 'mode=action&company_department_id=<?php echo $oCompany_Department->id?>',current: '1',windowId: 'id_content'}); return false" class="bordered-yellow" data-action="action_access" title="<?php echo Core::_('Company_Department.actionTitleAction'); ?>" alt="<?php echo Core::_('Company_Department.actionTitleAction'); ?>">
										<i class="fa fa-bolt yellow"></i>
									</a>
								<?php
								}
								?>
							</div>
						</div>
					<?php
					}

					$aUsers = isset($this->_aCompany_Users[$oCompany_Department->id])
						? $this->_aCompany_Users[$oCompany_Department->id]
						: array();

					if (count($aUsers))
					{
					?>
						<!-- collapsed -->
						<div class="department-users widget flat collapsed bordered-platinum no-margin-bottom margin-top-10">
							<div class="widget-header">
								<span class="widget-caption"><?php echo Core::_('Company_Department.caption_block_users') ?></span>
								<div class="widget-buttons">
									<span class="count-users badge badge-sky"><?php echo count($aUsers); ?></span>
								</div>
								<div class="widget-buttons pull-left widget-button-chevron">
									<a href="#" data-toggle="collapse">
										<i class="fa fa-chevron-down sky"></i>
									</a>
								</div>
							</div>
							<div class="widget-body" style="display: none;">
								<div class="scroll-wrapper">
									<div class="table-scrollable border-transparent">
										<table class="table table-hover company-structure">
											<tbody>
											<?php
											foreach ($aUsers as $oUser)
											{
												$aUser_Company_Posts = $oUser->getCompanyPostsByDepartment($oCompany_Department->id);

												foreach ($aUser_Company_Posts as $oUser_Company_Post)
												{
												?>
												<tr class="user" data-user-id="<?php echo $oUser->id?>" data-company-post-id="<?php echo $oUser_Company_Post->id?>">
													<td>
													<?php
													echo "<img width=\"30\" height=\"30\" src=\"" . $oUser->getAvatar() . "\" class=\"img-circle margin-right-10\" />";

													$bHead = $oUser->isHeadOfDepartment($oCompany_Department);
													?>
													<span class="user-name"><?php echo $oUser->showLink($this->_Admin_Form_Controller->getWindowId())?></span> <span class="darkgray small">[<?php echo htmlspecialchars($oUser->login)?>]</span>

													<div class="<?php echo $oUser->isOnline() ? 'online' : 'offline'; ?>"></div>

													<?php
													if ($bHead)
													{
														?>
														<i class="fa fa-star gold"></i>
														<?php
													}
													?>
													</td>
													<td>
														<span class="user-department-post"><?php echo htmlspecialchars($oUser_Company_Post->name);?></span>
													</td>
													<?php
													if (in_array('editUserDepartment', $aAllowedActions)
														|| in_array('deleteUserFromDepartment', $aAllowedActions))
													{
													?>
													<td>
														<div class="control-buttons">
														<?php
														if (in_array('editUserDepartment', $aAllowedActions))
														{
														?>
															<a href="javascript:void(0);" data-action="edit_user" title="<?php echo Core::_('Company_Department.editUserDepartmentPostTitleAction'); ?>" alt="<?php echo Core::_('Company_Department.editUserDepartmentPostTitleAction'); ?>" class="bordered-darkgray">
																<i class="fa fa-pencil darkgray"></i>
															</a>
														<?php
														}

														if (in_array('deleteUserFromDepartment', $aAllowedActions))
														{
														?>

															<a href="javascript:void(0);" data-action="delete_user" title="<?php echo Core::_('Company_Department.deleteUserDepartmentPostTitleAction'); ?>" alt="<?php echo Core::_('Company_Department.deleteUserDepartmentPostTitleAction'); ?>" class="bordered-darkorange">
																<i class="fa fa-user-times darkorange"></i>
															</a>
														<?php
														}
														?>
														</div>
													</td>
													<?php
													}
													?>
												</tr>
												<?php
												}
											}
											?>
											</tbody>
										</table>
									</div>
								</div>
							</div>
						</div>
						<?php
					}
					?>
					</div>
					<?php
					echo $this->_showLevel($oCompany_Department->id, $aAllowedActions);
					// конец вывода </div>
					?>
				</li>
			<?php
			}
			?>
			</ol>
			<?php
			return ob_get_clean();
		}
	}
}