<?php
/**
 * Crm project entities.
 *
 * @package HostCMS
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
require_once('../../../../bootstrap.php');

Core_Auth::authorization($sModule = 'crm_project');

// Код формы
$iAdmin_Form_Id = 311;
$sAdminFormAction = '/admin/crm/project/entity/index.php';

$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

$iCrmProjectId = intval(Core_Array::getGet('crm_project_id', 0));
$oCrm_Project = Core_Entity::factory('Crm_Project', $iCrmProjectId);

$sCrmProjectPath = '/admin/crm/project/index.php';

$pageTitle =  $oCrm_Project->name;

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title($pageTitle)
	->pageTitle($pageTitle)
	->addView('entity', 'Crm_Project_Entity_View')
	->view('entity');

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

if (!$oCrm_Project->id || $oCrm_Project->site_id != CURRENT_SITE)
{
	throw new Core_Exception('Crm project does not exist.');
}

if (!Core::moduleIsActive('event'))
{
	throw new Core_Exception('Module "Event" not active');
}

$windowId = $oAdmin_Form_Controller->getWindowId();

$additionalParams = Core_Str::escapeJavascriptVariable(
	str_replace(array('"'), array('&quot;'), $oAdmin_Form_Controller->additionalParams)
);

// Меню формы
$oAdmin_Form_Entity_Menus = Admin_Form_Entity::factory('Menus');

// Элементы меню
if (Core::moduleIsActive('event'))
{
$oAdmin_Form_Entity_Menus
	->add(
		Admin_Form_Entity::factory('Menu')
			->name(Core::_('Crm_Project.add_event'))
			->icon('fa fa-plus')
			->onclick(
				"$.modalLoad({path: '/admin/event/index.php', action: 'edit', operation: 'modal', additionalParams: 'hostcms[checked][0][0]=1&{$additionalParams}', windowId: '{$windowId}'}); return false"
			)
	);
}

if (Core::moduleIsActive('deal'))
{
	$oAdmin_Form_Entity_Menus->add(
		Admin_Form_Entity::factory('Menu')
			->name(Core::_('Crm_Project.add_deal'))
			->icon('fa fa-plus')
			->onclick(
				"$.modalLoad({path: '/admin/deal/index.php', action: 'edit', operation: 'modal', additionalParams: 'hostcms[checked][0][0]=1&{$additionalParams}', windowId: '{$windowId}'}); return false"
			)
	);
}

$oAdmin_Form_Entity_Menus->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Crm_Project.add_note'))
		->icon('fa fa-plus')
		->onclick(
			"$.modalLoad({path: '/admin/crm/project/note/index.php', action: 'edit', operation: 'modal', additionalParams: 'hostcms[checked][0][0]=1&{$additionalParams}', windowId: '{$windowId}'}); return false"
		)
);

// Добавляем все меню контроллеру
$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Menus);

// Построение хлебных крошек
$oAdminFormEntityBreadcrumbs = Admin_Form_Entity::factory('Breadcrumbs');

// Первая хлебная крошка будет всегда
$oAdminFormEntityBreadcrumbs
	->add(
		Admin_Form_Entity::factory('Breadcrumb')
			->name(Core::_('Crm_Project.title'))
			->href(
				$oAdmin_Form_Controller->getAdminLoadHref($sCrmProjectPath, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'list')
			)
			->onclick(
				$oAdmin_Form_Controller->getAdminLoadAjax($sCrmProjectPath, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'list')
			)
	)
	->add(
		Admin_Form_Entity::factory('Breadcrumb')
			->name($pageTitle)
			->href(
				$oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath())
			)
			->onclick(
				$oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath())
			)
	);

// Хлебные крошки добавляем контроллеру
$oAdmin_Form_Controller->addEntity($oAdminFormEntityBreadcrumbs);

// Действие редактирования
$oAdmin_Form_Action = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('deleteEntity');

if ($oAdmin_Form_Action && $oAdmin_Form_Controller->getAction() == 'deleteEntity')
{
	$oCrm_Project_Entity_Controller_Delete = Admin_Form_Action_Controller::factory(
		'Crm_Project_Entity_Controller_Delete', $oAdmin_Form_Action
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oCrm_Project_Entity_Controller_Delete);
}

// Источник данных 0
$oAdmin_Form_Dataset = new Crm_Project_Entity_Dataset($oCrm_Project);

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset(
	$oAdmin_Form_Dataset
);

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
							url: '/admin/crm/project/entity/index.php',
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