<?php
/**
 * Crm Project.
 *
 * @package HostCMS
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
 */
require_once('../../../bootstrap.php');

Core_Auth::authorization($sModule = 'crm_project');

// Код формы
$iAdmin_Form_Id = 310;
$sAdminFormAction = '/{admin}/crm/project/index.php';

$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module_Abstract::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title(Core::_('Crm_Project.menu'))
	->pageTitle(Core::_('Crm_Project.menu'));

if (!is_null(Core_Array::getGet('loadProjects')) && !is_null(Core_Array::getGet('term')))
{
	$aJSON = array();

	$sQuery = trim(Core_DataBase::instance()->escapeLike(Core_Str::stripTags(strval(Core_Array::getGet('term')))));

	$sQueryLike = '%' . str_replace(' ', '%', $sQuery) . '%';

	if (strlen($sQuery))
	{
		$oCrm_Projects = Core_Entity::factory('Crm_Project');
		$oCrm_Projects->queryBuilder()
			->where('crm_projects.site_id', '=', CURRENT_SITE)
			->where('crm_projects.name', 'LIKE', $sQueryLike)
			->limit(Core::$mainConfig['autocompleteItems']);

		$aCrm_Projects = $oCrm_Projects->findAll(FALSE);

		foreach ($aCrm_Projects as $oCrm_Project)
		{
			$icon = $oCrm_Project->crm_icon_id
				? $oCrm_Project->Crm_Icon->value
				: '';

			$aJSON[] = array(
				'id' => $oCrm_Project->id,
				'text' => ($icon != '' ? '<i class="' . $icon . '"></i> ' : '') . htmlspecialchars($oCrm_Project->name)
			);
		}
	}

	Core::showJson($aJSON);
}

if (!is_null(Core_Array::getGet('autocomplete'))
	&& !is_null(Core_Array::getGet('queryString'))
)
{
	$aJSON = array();

	$sQuery = trim(Core_Str::stripTags(strval(Core_Array::getGet('queryString'))));

	/*$aJSON[] = array(
		'id' => 0,
		'label' => Core::_('Crm_Project.root')
	);*/

	if (strlen($sQuery))
	{
		$sQueryLike = '%' . str_replace(' ', '%', $sQuery) . '%';

		$oCrm_Projects = Core_Entity::factory('Crm_Project');
		$oCrm_Projects->queryBuilder()
			->where('crm_projects.site_id', '=', CURRENT_SITE)
			->where('crm_projects.name', 'LIKE', $sQueryLike)
			->limit(Core::$mainConfig['autocompleteItems']);

		$aCrm_Projects = $oCrm_Projects->findAll(FALSE);

		foreach ($aCrm_Projects as $oCrm_Project)
		{
			$aJSON[] = array(
				'id' => $oCrm_Project->id,
				'label' => htmlspecialchars($oCrm_Project->name)
			);
		}
	}

	Core::showJson($aJSON);
}

if (!is_null(Core_Array::getPost('showCrmIconsModal')))
{
	$aJSON = array(
		'html' => ''
	);

	$color = Core_Array::getPost('color', '#aebec4', 'trim');
	$selector = Core_Array::getPost('selector', 'crm-project-icon', 'trim');

	ob_start();
	?>
		<div class="modal fade" id="crmProjectIconsModal" tabindex="-1" role="dialog" aria-labelledby="crmProjectIconsModalLabel">
			<div class="modal-dialog modal-lg" role="document">
				<div class="modal-content no-padding-bottom">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title"><?php echo Core::_('Crm_Project.crm_icon_id')?></h4>
					</div>
					<div class="modal-body">
						<input type="text" class="icon-filter margin-bottom-20 w-100 input-lg" class="form-control" placeholder="Введите название иконки, например, 'arrow'"/>
						<div class="crm-icon-wrapper">
							<div class="crm-icon-modal">
								<?php
									$aCrm_Icons = Core_Entity::factory('Crm_Icon')->findAll(FALSE);
									foreach ($aCrm_Icons as $oCrm_Icon)
									{
										$value = htmlspecialchars($oCrm_Icon->value);

										?><span onclick="$.selectCrmIcon(this, '<?php echo htmlspecialchars($selector)?>');" class="crm-project-id" data-id="<?php echo $oCrm_Icon->id?>" data-value="<?php echo $value?>" style="background-color: <?php echo $color?>"><i class="<?php echo $value?>"></i></span><?php
									}
								?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<script>
			$(function() {
				$(".icon-filter").on('keyup', function(){
					var selectIcon = $(this).val();

					if (selectIcon.length)
					{
						filter(selectIcon);
					}
					else
					{
						$('.crm-icon-modal .crm-project-id').show();
					}
				});

				function filter(e) {
					$('.crm-icon-modal .crm-project-id').hide()
						.filter(function() {
							// console.log($(this).data('value'));
							return $(this).data('value').toLowerCase().indexOf(e.toLowerCase()) > -1;
						})
						.show();
				}
			});
		</script>
	<?php
	$aJSON['html'] = ob_get_clean();

	Core::showJson($aJSON);
}

// Меню формы
$oAdmin_Form_Entity_Menus = Admin_Form_Entity::factory('Menus');

// Элементы меню
$oAdmin_Form_Entity_Menus->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Admin_Form.add'))
		->icon('fa fa-plus')
		->href(
			$oAdmin_Form_Controller->getAdminActionLoadHref($oAdmin_Form_Controller->getPath(), 'edit', NULL, 0, 0)
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminActionLoadAjax($oAdmin_Form_Controller->getPath(), 'edit', NULL, 0, 0)
		)
);

// Добавляем все меню контроллеру
$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Menus);

// Глобальный поиск
$additionalParams = '';

$sGlobalSearch = Core_Array::getGet('globalSearch', '', 'trim');

$oAdmin_Form_Controller->addEntity(
	Admin_Form_Entity::factory('Code')
		->html('
			<div class="row search-field margin-bottom-20">
				<div class="col-xs-12">
					<form action="' . $oAdmin_Form_Controller->getPath() . '" method="GET">
						<input type="text" name="globalSearch" class="form-control" placeholder="' . Core::_('Admin.placeholderGlobalSearch') . '" value="' . htmlspecialchars($sGlobalSearch) . '" />
						<i class="fa fa-times-circle no-margin" onclick="' . $oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), '', '', $additionalParams) . '"></i>
						<button type="submit" class="btn btn-default global-search-button" onclick="' . $oAdmin_Form_Controller->getAdminSendForm('', '', $additionalParams) . '"><i class="fa-solid fa-magnifying-glass fa-fw"></i></button>
					</form>
				</div>
			</div>
		')
);

$sGlobalSearch = str_replace(' ', '%', Core_DataBase::instance()->escapeLike($sGlobalSearch));

// Элементы строки навигации
$oAdmin_Form_Entity_Breadcrumbs = Admin_Form_Entity::factory('Breadcrumbs');

// Элементы строки навигации
$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Crm_Project.menu'))
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL, '')
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL, '')
	)
);

// Действие редактирования
$oAdmin_Form_Action = $oAdmin_Form->Admin_Form_Actions->getByName('edit');

if ($oAdmin_Form_Action && $oAdmin_Form_Controller->getAction() == 'edit')
{
	$oCrm_Project_Controller_Edit = Admin_Form_Action_Controller::factory(
		'Crm_Project_Controller_Edit', $oAdmin_Form_Action
	);

	$oCrm_Project_Controller_Edit->addEntity($oAdmin_Form_Entity_Breadcrumbs);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oCrm_Project_Controller_Edit);
}

// Действие "Применить"
$oAdminFormActionApply = $oAdmin_Form->Admin_Form_Actions->getByName('apply');

if ($oAdminFormActionApply && $oAdmin_Form_Controller->getAction() == 'apply')
{
	$oXslDirControllerApply = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Apply', $oAdminFormActionApply
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oXslDirControllerApply);
}

// Источник данных 1
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('Crm_Project')
);

// Доступ только к своим
$oUser = Core_Auth::getCurrentUser();
!$oUser->superuser && $oUser->only_access_my_own
	&& $oAdmin_Form_Dataset->addUserConditions();

if (strlen($sGlobalSearch))
{
	$oAdmin_Form_Dataset
		->addCondition(array('open' => array()))
			->addCondition(array('where' => array('crm_projects.id', '=', is_numeric($sGlobalSearch) ? intval($sGlobalSearch) : 0)))
			->addCondition(array('setOr' => array()))
			->addCondition(array('where' => array('crm_projects.name', 'LIKE', '%' . $sGlobalSearch . '%')))
		->addCondition(array('close' => array()));
}
else
{
	$oAdmin_Form_Dataset
		->addCondition(array('where' => array('crm_projects.site_id', '=', CURRENT_SITE)));
}

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset(
	$oAdmin_Form_Dataset
);

// Показ формы
$oAdmin_Form_Controller->execute();