<?php
/**
 * Events.
 *
 * @package HostCMS
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
require_once('../../bootstrap.php');

Core_Auth::authorization($sModule = 'event');

// File download
if (Core_Array::getGet('downloadFile'))
{
	$oEvent_Attachment = Core_Entity::factory('Event_Attachment')->find(intval(Core_Array::getGet('downloadFile')));
	if (!is_null($oEvent_Attachment->id))
	{
		$oUser = Core_Auth::getCurrentUser();

		$oEvent_User = $oEvent_Attachment->Event->Event_Users->getByuser_id($oUser->id);

		if (!is_null($oEvent_User))
		{
			$filePath = $oEvent_Attachment->getFilePath();
			Core_File::download($filePath, $oEvent_Attachment->file_name, array('content_disposition' => 'inline'));
		}
		else
		{
			throw new Core_Exception('Access denied');
		}
	}
	else
	{
		throw new Core_Exception('Access denied');
	}

	exit();
}

// Код формы
$iAdmin_Form_Id = 220;
$sAdminFormAction = '/admin/event/index.php';

$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

$parent_id = intval(Core_Array::getGet('parent_id', 0));
$bShow_subs = !is_null(Core_Array::getGet('show_subs'));

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title(Core::_('Event.events_title'))
	->pageTitle(Core::_('Event.events_title'))
	->addView('kanban', 'Event_Controller_Kanban');

if ($bShow_subs && $parent_id)
{
	$oAdmin_Form_Controller
		->Admin_View(
			Admin_View::getClassName('Admin_Internal_View')
		)
		->addView('event', 'Event_Controller_Related_Event')
		->view('event');
}

$windowId = $oAdmin_Form_Controller->getWindowId();

$siteuser_id = intval(Core_Array::getGet('siteuser_id'));
$siteuser_id && $windowId != 'id_content' && $oAdmin_Form_Controller->Admin_View(
	Admin_View::getClassName('Admin_Internal_View')
);

if (!is_null(Core_Array::getPost('showPopover')))
{
	$aJSON = array(
		'html' => ''
	);

	$oCurrentUser = Core_Auth::getCurrentUser();

	$company_id = Core_Array::getPost('company_id', 0, 'int');
	$person_id = Core_Array::getPost('person_id', 0, 'int');
	$user_id = Core_Array::getPost('user_id', 0, 'int');

	if ($user_id)
	{
		$oUser = Core_Entity::factory('User')->getById($user_id);

		if (!is_null($oUser))
		{
			$aJSON['html'] = $oUser->getProfilePopupBlock();
		}
	}
	else
	{
		$oEntity = $company_id
			? Core_Entity::factory('Siteuser_Company')->getById($company_id)
			: Core_Entity::factory('Siteuser_Person')->getById($person_id);

		if (!is_null($oEntity) && $oCurrentUser->checkObjectAccess($oEntity))
		{
			$aJSON['html'] = $oEntity->getProfilePopupBlock();
		}
	}

	Core::showJson($aJSON);
}

if (Core_Array::getPost('id') && (Core_Array::getPost('target_id') || Core_Array::getPost('sender_id')))
{
	$aJSON = array(
		'status' => 'error'
	);

	$iEventId = intval(Core_Array::getPost('id'));
	$iTargetStatusId = intval(Core_Array::getPost('target_id'));
	$iSenderStatusId = intval(Core_Array::getPost('sender_id'));

	$oEvents = Core_Entity::factory('Event');

	$iSenderStatusId == -1
		&& $oEvents->setMarksDeleted(NULL);

	$oEvent = $oEvents->getById($iEventId);

	if (!is_null($oEvent))
	{
		if ($iTargetStatusId >= 0)
		{
			$oEvent_Status = Core_Entity::factory('Event_Status')->find($iTargetStatusId);
			if ($iTargetStatusId == 0 || !is_null($oEvent_Status->id))
			{
				$previousStatusId = $oEvent->event_status_id;

				// При отмене удаленного явно возвращаем в 0
				$oEvent->deleted = 0;
				$oEvent->event_status_id = $iTargetStatusId;
				$oEvent->save();

				if ($previousStatusId != $oEvent->event_status_id)
				{
					$oEvent->notifyBotsChangeStatus();
				}

				$aJSON['status'] = 'success';

				if (intval(Core_Array::getPost('update_data')))
				{
					$aTargetData = $oEvent->updateKanban($iTargetStatusId);

					$aJSON['update'][$iTargetStatusId] = $aTargetData;

					$aSenderData = $oEvent->updateKanban($iSenderStatusId);

					$aJSON['update'][$iSenderStatusId] = $aSenderData;
				}
			}
		}
		elseif ($iTargetStatusId == -1)
		{
			$oEvent->markDeleted();
		}
		else
		{
			$aJSON['status'] = 'errorEventStatusId';
		}
	}
	else
	{
		$aJSON['status'] = 'errorEvent';
	}

	Core::showJson($aJSON);
}

$oCurrentUser = Core_Auth::getCurrentUser();

$additionalParams = Core_Str::escapeJavascriptVariable(
	str_replace(array('"'), array('&quot;'), $oAdmin_Form_Controller->additionalParams)
);

// Меню формы
$oAdmin_Form_Entity_Menus = Admin_Form_Entity::factory('Menus');

// Элементы меню
$oAdmin_Form_Entity_Menus->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Event.events_menu_add_event'))
		->icon('fa fa-plus')
		->img('/admin/images/add.gif')
		->href(
			$bShow_subs
				? NULL
				: $oAdmin_Form_Controller->getAdminActionLoadHref($oAdmin_Form_Controller->getPath(), 'edit', NULL, 0, 0)
		)
		->onclick(
			$bShow_subs || $siteuser_id
				? $oAdmin_Form_Controller->getAdminActionModalLoad($oAdmin_Form_Controller->getPath(), 'edit', 'modal', 0, 0, $additionalParams)
				: $oAdmin_Form_Controller->getAdminActionLoadAjax($oAdmin_Form_Controller->getPath(), 'edit', NULL, 0, 0)
		)
);

if (!$siteuser_id && is_null(Core_Array::getGet('hideMenu')))
{
	$oAdmin_Form_Entity_Menus->add(
		Admin_Form_Entity::factory('Menu')
			->name(Core::_('Event.events_menu_directories'))
			->icon('fa fa-book')
			->add(
				Admin_Form_Entity::factory('Menu')
					->name(Core::_('Event.events_menu_types'))
					->icon('fa fa-bars')
					->img('/admin/images/add.gif')
					->href(
						$oAdmin_Form_Controller->getAdminLoadHref(array('path' => $sPath = '/admin/event/type/index.php'))
					)
					->onclick(
						$oAdmin_Form_Controller->getAdminLoadAjax(array('path' => $sPath))
					)
			)
			->add(
				Admin_Form_Entity::factory('Menu')
					->name(Core::_('Event.events_menu_groups'))
					->icon('fa fa-folder-o')
					->img('/admin/images/add.gif')
					->href(
						$oAdmin_Form_Controller->getAdminLoadHref(array('path' => $sPath = '/admin/event/group/index.php'))
					)
					->onclick(
						$oAdmin_Form_Controller->getAdminLoadAjax(array('path' => $sPath))
					)
			)
			->add(
				Admin_Form_Entity::factory('Menu')
					->name(Core::_('Event.events_menu_statuses'))
					->icon('fa fa-circle')
					->img('/admin/images/add.gif')
					->href(
						$oAdmin_Form_Controller->getAdminLoadHref(array('path' => $sPath = '/admin/event/status/index.php'))
					)
					->onclick(
						$oAdmin_Form_Controller->getAdminLoadAjax(array('path' => $sPath))
					)
			)
	);
}

// Добавляем все меню контроллеру
$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Menus);

if (!$siteuser_id && !$oCurrentUser->read_only && is_null(Core_Array::getGet('hideMenu')))
{
	$oAdmin_Form_Controller->addEntity(
		Admin_Form_Entity::factory('Code')
			->html('<div class="add-event margin-bottom-20">
				<form action="/admin/event/index.php" method="POST">
					<div class="input-group">
						<input type="text" name="event_name" class="form-control" placeholder="' . Core::_('Event.placeholderEventName') . '">
						<span class="input-group-btn bg-azure bordered-azure">
							<button id="sendForm" class="btn btn-azure" type="submit" onclick="' . $oAdmin_Form_Controller->getAdminSendForm('addEvent', NULL, '') . '">
								<i class="fa fa-check no-margin"></i>
							</button>
						</span>
						<input type="hidden" name="hostcms[checked][0][0]" value="1"/>
					</div>
				</form>
			</div>')
	);
}

$oAdmin_Form_Entity_Breadcrumbs = Admin_Form_Entity::factory('Breadcrumbs');

// Добавляем крошку на текущую форму
$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Event.events_title'))
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL, '')
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL, '')
		)
);

if ($parent_id)
{
	$oParentEvent = Core_Entity::factory('Event')->find($parent_id);

	if (!is_null($oParentEvent->id))
	{
		$aBreadcrumbs = array();

		do
		{
			$additionalParams = '&parent_id=' . $oParentEvent->id;

			$aBreadcrumbs[] = Admin_Form_Entity::factory('Breadcrumb')
				->name($oParentEvent->name)
				->href(
					$oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL, $additionalParams)
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL, $additionalParams)
				);
		} while ($oParentEvent = $oParentEvent->getParent());

		$aBreadcrumbs = array_reverse($aBreadcrumbs);

		foreach ($aBreadcrumbs as $oAdmin_Form_Entity_Breadcrumb)
		{
			$oAdmin_Form_Entity_Breadcrumbs->add(
				$oAdmin_Form_Entity_Breadcrumb
			);
		}
	}
}

$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Breadcrumbs);

// Действие редактирования
$oAdmin_Form_Action = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('edit');

if ($oAdmin_Form_Action && $oAdmin_Form_Controller->getAction() == 'edit')
{
	$oEvent_Controller_Edit = Admin_Form_Action_Controller::factory(
		'Event_Controller_Edit', $oAdmin_Form_Action
	);

	// Хлебные крошки для контроллера редактирования
	$oEvent_Controller_Edit
		->addEntity(
			$oAdmin_Form_Entity_Breadcrumbs
		);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oEvent_Controller_Edit);
}

// Действие "Применить"
$oAdminFormActionApply = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('apply');

if ($oAdminFormActionApply && $oAdmin_Form_Controller->getAction() == 'apply')
{
	$oControllerApply = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Apply', $oAdminFormActionApply
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oControllerApply);
}

// Действие "Копировать"
$oAdminFormActionCopy = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('copy');

if ($oAdminFormActionCopy && $oAdmin_Form_Controller->getAction() == 'copy')
{
	$oControllerCopy = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Copy', $oAdminFormActionCopy
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oControllerCopy);
}

// Действие "Изменить группу"
$oAdminFormActionChangeGroup = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('changeGroup');

if ($oAdminFormActionChangeGroup && $oAdmin_Form_Controller->getAction() == 'changeGroup')
{
	$oEventControllerGroup = Admin_Form_Action_Controller::factory(
		'Event_Controller_Group', $oAdminFormActionChangeGroup
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oEventControllerGroup);
}

// Действие "Изменить статус"
$oAdminFormActionChangeStatus = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('changeStatus');

if ($oAdminFormActionChangeStatus && $oAdmin_Form_Controller->getAction() == 'changeStatus')
{
	$oEventControllerStatus = Admin_Form_Action_Controller::factory(
		'Event_Controller_Status', $oAdminFormActionChangeStatus
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oEventControllerStatus);
}

// Действие "Удалить файл"
$oAdminFormActionDeleteFile = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('deleteFile');

if ($oAdminFormActionDeleteFile && $oAdmin_Form_Controller->getAction() == 'deleteFile')
{
	$oController_Type_Delete_File = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Delete_File', $oAdminFormActionDeleteFile
	);

	$oController_Type_Delete_File
		->methodName('deleteFile')
		->divId('file_' . $oAdmin_Form_Controller->getOperation());

	// Добавляем контроллер удаления файла контроллеру формы
	$oAdmin_Form_Controller->addAction($oController_Type_Delete_File);
}

// Действие "Добавить дело"
$oAdminFormActionAddEvent = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('addEvent');

if ($oAdminFormActionAddEvent && $oAdmin_Form_Controller->getAction() == 'addEvent')
{
	$oControllerAddEvent = Admin_Form_Action_Controller::factory(
		'Event_Controller_Add', $oAdminFormActionAddEvent
	);

	$sEventName = trim(strval(Core_Array::getRequest('event_name')));

	$oControllerAddEvent->event_name($sEventName);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oControllerAddEvent);
}

// Источник данных 0
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('Event')
);

$parent_id
	&& $oAdmin_Form_Dataset->addCondition(array('where' => array('parent_id', '=', $parent_id)));

// Только если идет фильтрация, Контрагент TopFilter, фильтр по идентификатору
if (isset($oAdmin_Form_Controller->request['topFilter_1582'])
	&& $oAdmin_Form_Controller->request['topFilter_1582'] != '')
{
	$oAdmin_Form_Dataset->addCondition(
		array('leftJoin' => array('event_siteusers', 'events.id', '=', 'event_siteusers.event_id'))
	);
}

// Только если идет фильтрация, Контрагент, фильтр по тексту
if (isset($oAdmin_Form_Controller->request['admin_form_filter_1497'])
	&& $oAdmin_Form_Controller->request['admin_form_filter_1497'] != '')
{
	$oAdmin_Form_Dataset
		->addCondition(
			array('select' => array(
					array(Core_QueryBuilder::expression('GROUP_CONCAT(COALESCE(siteuser_people.surname, \'\'), \' \', COALESCE(siteuser_people.name, \'\'), \' \', COALESCE(siteuser_people.patronymic, \'\'), \' \', COALESCE(siteuser_companies.name, \'\') SEPARATOR \' \')'), 'counterparty')
				)
			)
		)
		->addCondition(
			array('join' => array('event_siteusers', 'events.id', '=', 'event_siteusers.event_id'))
		)
		->addCondition(
			array('leftJoin' => array('siteuser_companies', 'event_siteusers.siteuser_company_id', '=', 'siteuser_companies.id'))
		)
		->addCondition(
			array('leftJoin' => array('siteuser_people', 'event_siteusers.siteuser_person_id', '=', 'siteuser_people.id'))
		);
}

$oAdmin_Form_Dataset
	->addCondition(
		array('select' => array('events.*'))
	)
	->addCondition(
		array('join' => array('event_users', 'events.id', '=', 'event_users.event_id'))
	)
	->addCondition(
		array('where' => array('event_users.user_id', '=', $oCurrentUser->id))
	)->addCondition(
		array('groupBy' => array('events.id'))
	);

if ($siteuser_id)
{
	$oAdmin_Form_Dataset->addCondition(
		array('join' => array(array('event_siteusers', 'es'), 'events.id', '=', 'es.event_id'))
	)->addCondition(
		array('leftJoin' => array(array('siteuser_companies', 'sc'), 'es.siteuser_company_id', '=', 'sc.id'))
	)
	->addCondition(
		array('leftJoin' => array(array('siteuser_people', 'sp'), 'es.siteuser_person_id', '=', 'sp.id'))
	)
	->addCondition(
		array('open' => array())
	)
		->addCondition(
			array('where' => array('sc.siteuser_id', '=', $siteuser_id))
		)
		->addCondition(
			array('setOr' => array())
		)
		->addCondition(
			array('where' => array('sp.siteuser_id', '=', $siteuser_id))
		)
	->addCondition(
		array('close' => array())
	);

	$oAdmin_Form_Dataset->changeAction('edit', 'modal', 1);
}

// Список значений для фильтра и поля
$aEvent_Groups = Core_Entity::factory('Event_Group')->findAll();
$aList[0] = "—";
foreach ($aEvent_Groups as $oEvent_Group)
{
	$aList[$oEvent_Group->id] = $oEvent_Group->name;
}

$oAdmin_Form_Dataset->changeField('event_group_id', 'list', $aList);

!Core::moduleIsActive('siteuser')
	&& $oAdmin_Form_Controller->deleteAdminFormFieldById(1497);

$oAdmin_Form_Controller->addFilter('dataCounterparty', array($oAdmin_Form_Controller, '_filterCallbackCounterparty'));

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset($oAdmin_Form_Dataset);

function dataCounterparty($value, $oAdmin_Form_Field)
{
	if (!is_null($value) && $value !== '')
	{
		if (strpos($value, 'person_') === 0)
		{
			// Change where() fieldname
			$oAdmin_Form_Field->name = 'event_siteusers.siteuser_person_id';
			$value = substr($value, 7);
		}
		elseif (strpos($value, 'company_') === 0)
		{
			// Change where() fieldname
			$oAdmin_Form_Field->name = 'event_siteusers.siteuser_company_id';
			$value = substr($value, 8);
		}
		else
		{
			//throw new Core_Exception('Wrong `dataCounterparty` value!');
		}
	}

	return $value;
}

$oAdmin_Form_Controller->addFilterCallback('dataCounterparty', 'dataCounterparty');

$aEvent_Types = Core_Entity::factory('Event_Type')->findAll(FALSE);
$aList = array();
foreach ($aEvent_Types as $oEvent_Type)
{
	$aList[$oEvent_Type->id] = array('value' => $oEvent_Type->name);
}

$oAdmin_Form_Dataset
	->changeField('event_type_id', 'type', 8)
	->changeField('event_type_id', 'list', $aList);

$aEvent_Statuses = Core_Entity::factory('Event_Status')->findAll(FALSE);
$aList = array();
foreach ($aEvent_Statuses as $oEvent_Status)
{
	$aList[$oEvent_Status->id] = array('value' => $oEvent_Status->name);
}

$oAdmin_Form_Dataset
	->changeField('event_status_id', 'type', 8)
	->changeField('event_status_id', 'list', $aList);

Core_Event::attach('Admin_Form_Controller.onAfterShowContent', function($oAdmin_Form_Controller) {
	$windowId = $oAdmin_Form_Controller->getWindowId();
	?>
	<script>
		$('[data-popover="hover"]').on('mouseenter', function(event) {
			var $this = $(this);

			if (!$this.data("bs.popover"))
			{
				$this.popover({
					placement:'top',
					trigger:'manual',
					html:true,
					content: function() {
						var content = '';

						$.ajax({
							url: '/admin/event/index.php',
							data: { showPopover: 1, person_id: $(this).data('person-id'), company_id: $(this).data('company-id'), user_id: $(this).data('user-id') },
							dataType: 'json',
							type: 'POST',
							async: false,
							success: function(response) {
								content = response.html;
							}
						});

						return content;
					},
					container: "#<?php echo $windowId?>"
				});

				$this.attr('data-popoverAttached', true);

				$this.on('hide.bs.popover', function(e) {
					$this.attr('data-popoverAttached')
						? $this.removeAttr('data-popoverAttached')
						: e.preventDefault();
				})
				.on('show.bs.popover', function(e) {
					!$this.attr('data-popoverAttached') && e.preventDefault();
				})
				.on('shown.bs.popover', function(e) {
					$('#' + $this.attr('aria-describedby')).on('mouseleave', function(e) {
						!$this.parent().find(e.relatedTarget).length && $this.popover('destroy');
					});
				})
				.on('mouseleave', function(e) {
					!$(e.relatedTarget).parent('#' + $this.attr('aria-describedby')).length
					&& $this.attr('data-popoverAttached')
					&& $this.popover('destroy');
				});

				$this.popover('show');
			}
		});
	</script>
	<?php
});

// Показ формы
$oAdmin_Form_Controller->execute();