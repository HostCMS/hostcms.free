<?php
/**
 * Tags.
 *
 * @package HostCMS
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
 */
require_once('../../bootstrap.php');

Core_Auth::authorization($sModule = 'tag');

// Код формы
$iAdmin_Form_Id = 173;
$sAdminFormAction = '/{admin}/tag/index.php';

$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

$tag_dir_id = intval(Core_Array::getGet('tag_dir_id', 0, 'int'));

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module_Abstract::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title(Core::_('Tag.main_form_caption_h1'))
	->pageTitle(Core::_('Tag.main_form_caption_h1'));

if (!is_null(Core_Array::getGet('loadFilterTagsList')) && !is_null(Core_Array::getGet('term')))
{
	$aJSON = array();

	$entity = Core_Array::getGet('entity', '', 'trim');

	$sQuery = Core_Str::stripTags(Core_Array::getGet('term', '', 'trim'));

	if ($entity != '' && in_array($entity, array('lead', 'deal', 'event', 'shop_order', 'shop_warehouse'))
		&& $sQuery != ''
	)
	{
		$oTags = Core_Entity::factory('Tag');
		$oTags->queryBuilder()
			->join('tag_' . $entity . 's', 'tag_' . $entity . 's.tag_id', '=', 'tags.id')
			->where('tags.name', 'LIKE', '%' . $sQuery . '%')
			->limit(10)
			->clearOrderBy()
			->orderBy('tags.name', 'ASC');

		$aTags = $oTags->findAll(FALSE);

		foreach ($aTags as $oTag)
		{
			$sParents = $oTag->Tag_Dir->dirPathWithSeparator();

			$postfix = strlen($sParents) ? ' [' . $sParents . ']' : '';

			$aJSON[] = array(
				'id' => $oTag->name,
				'text' => $oTag->name . $postfix,
			);
		}
	}

	Core::showJson($aJSON);
}

// Меню формы
$oAdmin_Form_Entity_Menus = Admin_Form_Entity::factory('Menus');

// Элементы меню
$oAdmin_Form_Entity_Menus->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Tag.main_menu'))
		->icon('fa fa-plus')
		->href(
			$oAdmin_Form_Controller->getAdminActionLoadHref($oAdmin_Form_Controller->getPath(), 'edit', NULL, 1, 0)
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminActionLoadAjax($oAdmin_Form_Controller->getPath(), 'edit', NULL, 1, 0)
		)
)
->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Tag_Dir.menu_group'))
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
$additionalParams = 'tag_dir_id=' . $tag_dir_id;

$sGlobalSearch = trim(strval(Core_Array::getGet('globalSearch')));

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

// Элементы строки навигации
$oAdmin_Form_Entity_Breadcrumbs = Admin_Form_Entity::factory('Breadcrumbs');

// Элементы строки навигации
$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Tag.title'))
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL, '')
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL, '')
	)
);

if ($tag_dir_id)
{
	// Если передана родительская группа - строим хлебные крошки
	$oTagDir = Core_Entity::factory('Tag_Dir')->find($tag_dir_id);

	if (!is_null($oTagDir->id))
	{
		$aBreadcrumbs = array();

		do
		{
			$additionalParams = 'tag_dir_id=' . intval($oTagDir->id);

			$aBreadcrumbs[] = Admin_Form_Entity::factory('Breadcrumb')
				->name($oTagDir->name)
				->href(
					$oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL, $additionalParams)
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL, $additionalParams)
				);
		} while ($oTagDir = $oTagDir->getParent());

		$aBreadcrumbs = array_reverse($aBreadcrumbs);

		foreach ($aBreadcrumbs as $oAdmin_Form_Entity_Breadcrumb)
		{
			$oAdmin_Form_Entity_Breadcrumbs->add(
				$oAdmin_Form_Entity_Breadcrumb
			);
		}

		// Добавляем все хлебные крошки контроллеру
		$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Breadcrumbs);
	}
}

// Действие редактирования
$oAdmin_Form_Action = $oAdmin_Form
	->Admin_Form_Actions
	->getByName('edit');

if ($oAdmin_Form_Action/* && $oAdmin_Form_Controller->getAction() == 'edit'*/)
{
	$oTag_Controller_Edit = new Tag_Controller_Edit(
		$oAdmin_Form_Action
	);

	$oTag_Controller_Edit
		->addEntity($oAdmin_Form_Entity_Breadcrumbs);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oTag_Controller_Edit);
}

// Действие "Применить"
$oAdminFormActionApply = $oAdmin_Form
	->Admin_Form_Actions
	->getByName('apply');

if ($oAdminFormActionApply && $oAdmin_Form_Controller->getAction() == 'apply')
{
	$oTagDirControllerApply = new Admin_Form_Action_Controller_Type_Apply
	(
		$oAdminFormActionApply
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oTagDirControllerApply);
}

// Действие "Копировать"
$oAdminFormActionCopy = $oAdmin_Form
	->Admin_Form_Actions
	->getByName('copy');

if ($oAdminFormActionCopy && $oAdmin_Form_Controller->getAction() == 'copy')
{
	$oControllerCopy = new Admin_Form_Action_Controller_Type_Copy(
		$oAdminFormActionCopy
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oControllerCopy);
}

// Действие "Объединить"
$oAdminFormActionMerge = $oAdmin_Form
	->Admin_Form_Actions
	->getByName('merge');

if ($oAdminFormActionMerge && $oAdmin_Form_Controller->getAction() == 'merge')
{
	$oTagControllerMerge = new Admin_Form_Action_Controller_Type_Merge
	(
		$oAdminFormActionMerge
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oTagControllerMerge);
}

// Действие "Перенести"
$oAdminFormActionMove = $oAdmin_Form
	->Admin_Form_Actions
	->getByName('move');

if ($oAdminFormActionMove && $oAdmin_Form_Controller->getAction() == 'move')
{
	$oTagControllerMove = new Admin_Form_Action_Controller_Type_Move
	(
		$oAdminFormActionMove
	);

	$aExclude = array();
	$aChecked = $oAdmin_Form_Controller->getChecked();
	foreach ($aChecked as $datasetKey => $checkedItems)
	{
		// Exclude just dirs
		if ($datasetKey == 0)
		{
			foreach ($checkedItems as $key => $value)
			{
				$aExclude[] = $key;
			}
		}
	}

	$oTagControllerMove
		->title(Core::_('Tag.move_tags_groups_title'))
		->selectCaption(Core::_('Tag.move_tags_groups_id'))
		// Список директорий генерируется другим контроллером
		->selectOptions(array(' … ') + $oTag_Controller_Edit->fillTagDir(0, $aExclude))
		->value($tag_dir_id);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oTagControllerMove);
}

// Действие "Загрузка списка меток(тегов)"
$oAdminFormActionLoadTagsList = $oAdmin_Form->Admin_Form_Actions->getByName('loadTagsList');

if ($oAdminFormActionLoadTagsList && $oAdmin_Form_Controller->getAction() == 'loadTagsList')
{
	$oTag_Controller_Ajaxload = new Tag_Controller_Ajaxload
	(
		$oAdminFormActionLoadTagsList
	);

	$oTag_Controller_Ajaxload
		->query(
			Core_Array::getRequest('term')
		);

	$oAdmin_Form_Controller->addAction($oTag_Controller_Ajaxload);
}

// Источник данных 0
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('Tag_Dir')
);

if (strlen($sGlobalSearch))
{
	$oAdmin_Form_Dataset
		->addCondition(array('open' => array()))
			->addCondition(array('where' => array('tag_dirs.id', '=', is_numeric($sGlobalSearch) ? intval($sGlobalSearch) : 0)))
			->addCondition(array('setOr' => array()))
			->addCondition(array('where' => array('tag_dirs.name', 'LIKE', '%' . $sGlobalSearch . '%')))
		->addCondition(array('close' => array()));
}
else
{
	// Ограничение источника 0 по родительской группе
	$oAdmin_Form_Dataset->addCondition(array('where' => array('tag_dirs.parent_id', '=', $tag_dir_id)));
}

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset(
	$oAdmin_Form_Dataset
);

// Источник данных 1
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('Tag')
);

if (strlen($sGlobalSearch))
{
	$oAdmin_Form_Dataset
		->addCondition(array('open' => array()))
			->addCondition(array('where' => array('tags.id', '=', is_numeric($sGlobalSearch) ? intval($sGlobalSearch) : 0)))
			->addCondition(array('setOr' => array()))
			->addCondition(array('where' => array('tags.name', 'LIKE', '%' . $sGlobalSearch . '%')))
			->addCondition(array('setOr' => array()))
			->addCondition(array('where' => array('tags.path', 'LIKE', '%' . $sGlobalSearch . '%')))
		->addCondition(array('close' => array()));
}
else
{
	// Ограничение источника 1 по родительской группе
	$oAdmin_Form_Dataset->addCondition(array('where' => array('tags.tag_dir_id', '=', $tag_dir_id)));
}

$oAdmin_Form_Dataset->changeField('name', 'type', 1);

// Доступ только к своим
$oUser = Core_Auth::getCurrentUser();
!$oUser->superuser && $oUser->only_access_my_own
	&& $oAdmin_Form_Dataset->addUserConditions();

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset(
	$oAdmin_Form_Dataset
);

// Показ формы
$oAdmin_Form_Controller->execute();