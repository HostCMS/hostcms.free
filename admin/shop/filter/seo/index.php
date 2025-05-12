<?php
/**
 * Online shop.
 *
 * @package HostCMS
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
 */
require_once('../../../../bootstrap.php');

Core_Auth::authorization($sModule = 'shop');

// Код формы
$iAdmin_Form_Id = 274;
$sAdminFormAction = '/{admin}/shop/filter/seo/index.php';

$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

$shop_id = intval(Core_Array::getGet('shop_id'));

$shop_group_id = intval(Core_Array::getGet('shop_group_id', 0));

$oShop = Core_Entity::factory('Shop')->find($shop_id);

$shop_filter_seo_dir_id = Core_Array::getGet('shop_filter_seo_dir_id', 0);

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module_Abstract::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title(Core::_('Shop_Filter_Seo.title'))
	->pageTitle(Core::_('Shop_Filter_Seo.title'));

$aAvailableProperties = array(0, 11, 1, 7, 8, 9);
Core::moduleIsActive('list') && $aAvailableProperties[] = 3;

// $aAvailablePropertyFilters = array(1,2,3,4,5,6,7);

if (Core_Array::getPost('load_properties') && Core_Array::getPost('shop_id'))
{
	$aJSON = array();

	$shop_id = intval(Core_Array::getPost('shop_id'));
	$shop_group_id = intval(Core_Array::getPost('shop_group_id'));

	$linkedObject = Core_Entity::factory('Shop_Item_Property_List', $shop_id);

	// Массив свойств товаров, разрешенных для группы $shop_group_id
	$aProperties = $linkedObject->getPropertiesForGroup($shop_group_id);

	$aTmpProperties = array();

	foreach ($aProperties as $oProperty)
	{
		in_array($oProperty->type, $aAvailableProperties)
			// && in_array($oProperty->Shop_Item_Property->filter, $aAvailablePropertyFilters)
			&& $oProperty->Shop_Item_Property->filter // 0 - не показывать в фильтре
			&& $aTmpProperties[$oProperty->id] = $oProperty->name;
	}

	if (count($aTmpProperties))
	{
		ob_start();

		Admin_Form_Entity::factory('Select')
			->options($aTmpProperties)
			->name('modal_property_id')
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'))
			->class('form-control property-select')
			->onchange('$.getSeoFilterPropertyValues(this)')
			->execute();

		$aJSON['html'] = ob_get_clean();
	}
	else
	{
		$aJSON['html'] = '<div class="alert alert-danger fade in">' . Core::_('Shop_Filter_Seo.empty_properties') . '</div>';
	}

	$aJSON['count'] = count($aTmpProperties);

	Core::showJson($aJSON);
}

if (Core_Array::getPost('get_values') /*&& Core_Array::getPost('property_id')*/)
{
	$aJSON = array(
		'status' => 'error',
		'html' => ''
	);

	$windowId = $oAdmin_Form_Controller->getWindowId();

	$property_id = intval(Core_Array::getPost('property_id'));

	$oProperty = Core_Entity::factory('Property')->getById($property_id);

	if (!is_null($oProperty) && in_array($oProperty->type, $aAvailableProperties))
	{
		ob_start();

		switch ($oProperty->type)
		{
			case 3:
				if (Core::moduleIsActive('list'))
				{
					$aList_Items = $oProperty->List->List_Items->findAll(FALSE);

					$aValues = array();
					foreach ($aList_Items as $oList_Item)
					{
						$aValues[$oList_Item->id] = $oList_Item->value;
					}

					$oAdmin_Form_Entity = Admin_Form_Entity::factory('Select')
						->options($aValues);
				}
				else
				{
					$oAdmin_Form_Entity = NULL;
				}
			break;
			case 7:
				$oAdmin_Form_Entity = Admin_Form_Entity::factory('Checkbox')
					->value(1);
			break;
			case 8: // Date
				$oAdmin_Form_Entity = Admin_Form_Entity::factory('Date');
			break;
			case 9: // Datetime
				$oAdmin_Form_Entity = Admin_Form_Entity::factory('Datetime');
			break;
			default:
				$oAdmin_Form_Entity = Admin_Form_Entity::factory('Input');
		}

		if (!is_null($oAdmin_Form_Entity))
		{
			$divAttr = $oProperty->Shop_Item_Property->filter == 6
				? 'form-group col-xs-12 col-sm-4'
				: 'form-group col-xs-12 col-sm-8';

			$oAdmin_Form_Entity
				->name('modal_property_value')
				->divAttr(array('class' => $divAttr))
				->controller($oAdmin_Form_Controller)
				->onkeydown("return $.saveApplySeoFilterCondition(event, '{$windowId}')")
				->execute();

			// от-до
			if ($oProperty->Shop_Item_Property->filter == 6)
			{
				$oAdmin_Form_Entity
					->name('modal_property_value_to')
					->divAttr(array('class' => $divAttr))
					->controller($oAdmin_Form_Controller)
					->execute();
			}
		}

		$aJSON = array(
			'status' => 'success',
			'html' => ob_get_clean()
		);
	}

	Core::showJson($aJSON);
}

if (Core_Array::getPost('add_property') && Core_Array::getPost('property_id'))
{
	$aJSON = array(
		'status' => '',
		'html' => ''
	);

	$property_value = trim(strval(Core_Array::getPost('property_value')));
	$property_value_to = trim(strval(Core_Array::getPost('property_value_to')));

	$property_id = intval(Core_Array::getPost('property_id'));

	$oProperty = Core_Entity::factory('Property')->getById($property_id);

	if (!is_null($oProperty) && in_array($oProperty->type, $aAvailableProperties) && strlen($property_value))
	{
		ob_start();

		$bAddProperty = TRUE;

		switch($oProperty->type)
		{
			case 3:
				$aList_Items = $oProperty->List->List_Items->findAll(FALSE);

				$aValues = array();
				foreach ($aList_Items as $oList_Item)
				{
					$aValues[$oList_Item->id] = $oList_Item->value;
				}

				$oAdmin_Form_Entity = Admin_Form_Entity::factory('Select')
					->options($aValues);
			break;
			case 7:
				$property_value = intval($property_value);

				$oAdmin_Form_Entity = Admin_Form_Entity::factory('Checkbox')
					->checked($property_value);

				!$property_value
					&& $bAddProperty = FALSE;
			break;
			case 8: // Date
				$oAdmin_Form_Entity = Admin_Form_Entity::factory('Date');
			break;
			case 9: // Datetime
				$oAdmin_Form_Entity = Admin_Form_Entity::factory('Datetime');
			break;
			default:
				$oAdmin_Form_Entity = Admin_Form_Entity::factory('Input');
		}

		if ($bAddProperty)
		{
			Admin_Form_Entity::factory('Code')
				->html('
					<label class="col-xs-12 col-sm-2 control-label text-align-left">' . htmlspecialchars($oProperty->name) . '</label>
				')
				->execute();

			$oAdmin_Form_Entity
				->name('property_value[' . $oProperty->id . '][]')
				->divAttr(array('class' => 'col-xs-12 col-sm-4 property-data'))
				->value($property_value)
				->controller($oAdmin_Form_Controller)
				->execute();

			// от-до
			if ($oProperty->Shop_Item_Property->filter == 6)
			{
				$oAdmin_Form_Entity
					->name('property_value_to[' . $oProperty->id . '][]')
					->divAttr(array('class' => 'col-xs-12 col-sm-4'))
					->value($property_value_to)
					->controller($oAdmin_Form_Controller)
					->execute();
			}

			$aJSON = array(
				'status' => 'success',
				'html' => ob_get_clean()
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
		->name(Core::_('Admin_Form.add'))
		->icon('fa fa-plus')
		->href(
			$oAdmin_Form_Controller->getAdminActionLoadHref($oAdmin_Form_Controller->getPath(), 'edit', NULL, 1, 0)
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminActionLoadAjax($oAdmin_Form_Controller->getPath(), 'edit', NULL, 1, 0)
		)
)->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Shop_Filter_Seo_Dir.title'))
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

// Идентификатор родительской группы
$iShopDirId = intval(Core_Array::getGet('shop_dir_id', 0));

$oAdmin_Form_Entity_Breadcrumbs = Admin_Form_Entity::factory('Breadcrumbs');

$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Shop.menu'))
		->href($oAdmin_Form_Controller->getAdminLoadHref($sShopFormPath = '/{admin}/shop/index.php', NULL, NULL, ''))
		->onclick($oAdmin_Form_Controller->getAdminLoadAjax($sShopFormPath, NULL, NULL, ''))
);

// Крошки строим только если: мы не в корне или идет редактирование
if ($iShopDirId)
{
	// Далее генерируем цепочку хлебных крошек от текущей группы к корневой
	$oShopDir = Core_Entity::factory('Shop_Dir')->find($iShopDirId);

	// Массив хлебных крошек
	$aBreadcrumbs = array();

	do
	{
		$additionalParams = 'shop_dir_id=' . intval($oShopDir->id);

		$aBreadcrumbs[] = Admin_Form_Entity::factory('Breadcrumb')
			->name($oShopDir->name)
			->href($oAdmin_Form_Controller->getAdminLoadHref($sShopFormPath, NULL, NULL, $additionalParams))
			->onclick($oAdmin_Form_Controller->getAdminLoadAjax($sShopFormPath, NULL, NULL, $additionalParams));
	} while ($oShopDir = $oShopDir->getParent());

	$aBreadcrumbs = array_reverse($aBreadcrumbs);

	foreach ($aBreadcrumbs as $oAdmin_Form_Entity_Breadcrumb)
	{
		$oAdmin_Form_Entity_Breadcrumbs->add($oAdmin_Form_Entity_Breadcrumb);
	}
}

$additionalParams = 'shop_id=' . $shop_id;
$sShopPath = '/{admin}/shop/item/index.php';

// Ссылка на название магазина
$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name($oShop->name)
		->href($oAdmin_Form_Controller->getAdminLoadHref($sShopPath, NULL, NULL, $additionalParams))
		->onclick($oAdmin_Form_Controller->getAdminLoadAjax($sShopPath, NULL, NULL, $additionalParams))
);

if ($shop_group_id)
{
	$oShopGroup = Core_Entity::factory('Shop_Group')->find($shop_group_id);

	if (!is_null($oShopGroup->id))
	{
		$aBreadcrumbs = array();

		do
		{
			$additionalParams = 'shop_id=' . $shop_id . '&shop_group_id=' . $oShopGroup->id;

			$aBreadcrumbs[] = Admin_Form_Entity::factory('Breadcrumb')
				->name($oShopGroup->name)
				->href($oAdmin_Form_Controller->getAdminLoadHref($sShopPath, NULL, NULL, $additionalParams))
				->onclick($oAdmin_Form_Controller->getAdminLoadAjax($sShopPath, NULL, NULL, $additionalParams));
		} while ($oShopGroup = $oShopGroup->getParent());

		$aBreadcrumbs = array_reverse($aBreadcrumbs);

		foreach ($aBreadcrumbs as $oAdmin_Form_Entity_Breadcrumb)
		{
			$oAdmin_Form_Entity_Breadcrumbs->add(
				$oAdmin_Form_Entity_Breadcrumb
			);
		}
	}
}

$additionalParams = 'shop_id=' . $shop_id . '&shop_group_id=' . $shop_group_id;

// Добавляем крошку на текущую форму
$oAdmin_Form_Entity_Breadcrumbs->add(Admin_Form_Entity::factory('Breadcrumb')
	->name(Core::_('Shop_Filter_Seo.title'))
	->href($oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL, $additionalParams))
	->onclick($oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL, $additionalParams)));

if ($shop_filter_seo_dir_id)
{
	$oShop_Filter_Seo_Dir = Core_Entity::factory('Shop_Filter_Seo_Dir', $shop_filter_seo_dir_id);

	$aBreadcrumbs = array();

	do
	{
		$aBreadcrumbs[] = Admin_Form_Entity::factory('Breadcrumb')
			->name($oShop_Filter_Seo_Dir->name)
			->href($oAdmin_Form_Controller->getAdminLoadHref($sAdminFormAction, NULL, NULL, "shop_id={$shop_id}&shop_group_id={$shop_group_id}&shop_filter_seo_dir_id={$oShop_Filter_Seo_Dir->id}"))
			->onclick($oAdmin_Form_Controller->getAdminLoadAjax($sAdminFormAction, NULL, NULL, "shop_id={$shop_id}&shop_group_id={$shop_group_id}&shop_filter_seo_dir_id={$oShop_Filter_Seo_Dir->id}"));
	} while ($oShop_Filter_Seo_Dir = $oShop_Filter_Seo_Dir->getParent());

	$aBreadcrumbs = array_reverse($aBreadcrumbs);

	foreach ($aBreadcrumbs as $oBreadcrumb)
	{
		$oAdmin_Form_Entity_Breadcrumbs->add($oBreadcrumb);
	}
}

$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Breadcrumbs);

// Действие редактирования
$oAdmin_Form_Action = $oAdmin_Form->Admin_Form_Actions->getByName('edit');

 if ($oAdmin_Form_Action && $oAdmin_Form_Controller->getAction() == 'edit')
{
	$Shop_Filter_Seo_Controller_Edit = Admin_Form_Action_Controller::factory(
		'Shop_Filter_Seo_Controller_Edit', $oAdmin_Form_Action
	);

	// Хлебные крошки для контроллера редактирования
	$Shop_Filter_Seo_Controller_Edit
		->addEntity(
			$oAdmin_Form_Entity_Breadcrumbs
		);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($Shop_Filter_Seo_Controller_Edit);
}

// Действие "Применить"
$oAdminFormActionApply = $oAdmin_Form->Admin_Form_Actions->getByName('apply');

if ($oAdminFormActionApply && $oAdmin_Form_Controller->getAction() == 'apply')
{
	$oControllerApply = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Apply', $oAdminFormActionApply
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oControllerApply);
}

// Удаление условий
$oAdminFormActionDeleteCondition = $oAdmin_Form->Admin_Form_Actions->getByName('deleteCondition');

if ($oAdminFormActionDeleteCondition && $oAdmin_Form_Controller->getAction() == 'deleteCondition')
{
	$Shop_Filter_Seo_Property_Controller_Delete = Admin_Form_Action_Controller::factory(
		'Shop_Filter_Seo_Property_Controller_Delete', $oAdminFormActionDeleteCondition
	);

	$oAdmin_Form_Controller->addAction($Shop_Filter_Seo_Property_Controller_Delete);
}

$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(Core_Entity::factory('Shop_Filter_Seo_Dir'));
$oAdmin_Form_Dataset->changeField('active', 'type', 1);
$oAdmin_Form_Dataset->changeField('shop_producer_id', 'type', 1);
$oAdmin_Form_Dataset->addCondition(array('where' => array('shop_id', '=', $shop_id)));
$oAdmin_Form_Dataset->addCondition(array('where' => array('parent_id', '=', $shop_filter_seo_dir_id )));
$oAdmin_Form_Controller->addDataset($oAdmin_Form_Dataset);

// Источник данных 0
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('Shop_Filter_Seo')
);

// Доступ только к своим
$oUser = Core_Auth::getCurrentUser();
!$oUser->superuser && $oUser->only_access_my_own
	&& $oAdmin_Form_Dataset->addUserConditions();

// Фильтр по группе
if (isset($oAdmin_Form_Controller->request['admin_form_filter_1556']) && $oAdmin_Form_Controller->request['admin_form_filter_1556'] != '')
{
	$mFilterValue = $oAdmin_Form_Controller->convertLike(strval($oAdmin_Form_Controller->request['admin_form_filter_1556']));

	$oAdmin_Form_Dataset->addCondition(
		array('select' => array('shop_filter_seos.*'))
	)->addCondition(
		array('leftJoin' => array('shop_groups', 'shop_filter_seos.shop_group_id', '=', 'shop_groups.id'))
	)
	->addCondition(
		array('where' => array('shop_groups.name', 'LIKE', $mFilterValue))
	);

	$oAdmin_Form_Controller->request['admin_form_filter_1556'] = NULL;
}

$oAdmin_Form_Dataset
	->addCondition(array('where' => array('shop_filter_seos.shop_id', '=', $oShop->id)))
	->addCondition(array('where' => array('shop_filter_seos.shop_filter_seo_dir_id', '=', $shop_filter_seo_dir_id)));

// Список значений для фильтра и поля
$oAdmin_Form_Dataset
	->changeField('shop_producer_id', 'list', Shop_Item_Controller_Edit::fillProducersList($oShop->id));

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset(
	$oAdmin_Form_Dataset
);

// Показ формы
$oAdmin_Form_Controller->execute();