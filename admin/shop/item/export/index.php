<?php
/**
* Online shop.
*
* @package HostCMS
* @version 7.x
* @author Hostmake LLC
* @copyright © 2005-2025, https://www.hostcms.ru
*/
require_once('../../../../bootstrap.php');

Core_Auth::authorization($sModule = 'shop');

!defined('DISABLE_COMPRESSION') && define('DISABLE_COMPRESSION', TRUE);

// Код формы
$iAdmin_Form_Id = 209;
$sFormAction = '/{admin}/shop/item/export/index.php';

$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

// Создаем экземпляры классов
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);

// Контроллер формы
$oAdmin_Form_Controller
	->module(Core_Module_Abstract::factory($sModule))
	->setUp()
	->path($sFormAction);

// Получаем параметры
$shopId = Core_Array::getRequest('shop_id', 0, 'int');

$oShop = Core_Entity::factory('Shop', $shopId);
$oShopDir = $oShop->Shop_Dir;

$shopGroupId = Core_Array::getRequest('shop_group_id', 0, 'int');
$oShopGroup = Core_Entity::factory('Shop_Group', $shopGroupId);

$shopGroupParentId = Core_Array::getPost('shop_groups_parent_id', 0);

$bExportCompleted = TRUE;

if (Core_Array::getRequest('action') == 'export')
{
	// Текущий пользователь
	$oUser = Core_Auth::getCurrentUser();

	if (defined('READ_ONLY') && READ_ONLY || $oUser->read_only && !$oUser->superuser)
	{
		$content = Core_Message::get(Core::_('User.demo_mode'), 'error');
		$bExportCompleted = FALSE;
	}
	else
	{
		$aActions = array();

		$aAdmin_Form_Actions = $oAdmin_Form->Admin_Form_Actions->getAllowedActionsForUser($oUser);

		// Проверка на право доступа к действию
		foreach ($aAdmin_Form_Actions as $oAdmin_Form_Action)
		{
			$aActions[] = $oAdmin_Form_Action->name;
		}

		switch(Core_Array::getRequest('export_type'))
		{
			case 0:
				$oAction = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
					->Admin_Form_Actions
					->getByName('exportItemsCsv');

				if ($oAction && in_array('exportItemsCsv', $aActions))
				{
					Core_Session::start();

					if (isset($_SESSION['Shop_Item_Export_Csv_Item_Controller']) && !isset($_POST['show_form']))
					{
						// Next export steps
						$Shop_Item_Export_Csv_Item_Controller = $_SESSION['Shop_Item_Export_Csv_Item_Controller'];
						unset($_SESSION['Shop_Item_Export_Csv_Item_Controller']);
						Core_Session::close();
					}
					else
					{
						Core_Session::close();

						// First export step
						//ob_get_clean();

						$aSeparator = array(",", ";");
						$iSeparator = Core_Array::getPost('export_price_separator', 1, 'int');

						$Shop_Item_Export_Csv_Item_Controller = new Shop_Item_Export_Csv_Item_Controller($shopId);
						$Shop_Item_Export_Csv_Item_Controller
							->separator($iSeparator > 1 ? '' : $aSeparator[$iSeparator])
							->encoding(Core_Array::getPost('import_price_encoding', "UTF-8"))
							->exportItemExternalProperties(!is_null(Core_Array::getPost('export_external_properties_allow_items')))
							->exportGroupExternalProperties(!is_null(Core_Array::getPost('export_external_properties_allow_groups')))
							->exportItemFields(!is_null(Core_Array::getPost('export_fields_allow_items')))
							->exportGroupFields(!is_null(Core_Array::getPost('export_fields_allow_groups')))
							->exportItemModifications(!is_null(Core_Array::getPost('export_modifications_allow')))
							->exportItemShortcuts(!is_null(Core_Array::getPost('export_shortcuts_allow')))
							->exportInStock(!is_null(Core_Array::getPost('export_in_stock_items')))
							->exportStocks(!is_null(Core_Array::getPost('export_stocks')))
							->exportPrices(!is_null(Core_Array::getPost('export_prices')))
							->parentGroup($shopGroupParentId)
							//->lastGroupId(Core_Array::getPost('last_group_id')) // NULL может быть
							->producer(Core_Array::getPost('shop_producer_id', 0, 'int'))
							->seller(Core_Array::getPost('shop_seller_id', 0, 'int'))
							->startItemDate(Core_Array::getPost('item_begin_date', ''))
							->endItemDate(Core_Array::getPost('item_end_date', ''))
							->exportToFile(Core_Array::getPost('export_mode', 0, 'bool'))
							->fileName('CATALOG_' . date("Y_m_d_H_i_s") . '.csv');
					}

					$Shop_Item_Export_Csv_Item_Controller->execute();

					if ($Shop_Item_Export_Csv_Item_Controller->exportToFile)
					{
						ob_start();

						if (!is_null($Shop_Item_Export_Csv_Item_Controller->lastGroupId))
						{
							Core_Session::start();
							$_SESSION['Shop_Item_Export_Csv_Item_Controller'] = $Shop_Item_Export_Csv_Item_Controller;
							Core_Session::close();

							Core_Message::show(
								Core::_('Shop_Item_Export.begin_file_export',
									TMP_DIR . $Shop_Item_Export_Csv_Item_Controller->fileName,
									$Shop_Item_Export_Csv_Item_Controller->lastGroupId
										? Core_Entity::factory('Shop_Group', $Shop_Item_Export_Csv_Item_Controller->lastGroupId)->name
										: Core::_('Admin.root')
								)
							, 'info');

							$timeout = 1;
							$sAdditionalParams = "shop_id={$shopId}&action=export&export_type=0";

							echo Core::_('Shop_Item.continue_import',
								$oAdmin_Form_Controller->getAdminLoadHref(array('path' => $oAdmin_Form_Controller->getPath(), 'additionalParams' => $sAdditionalParams)),
								'clearTimeout(timeout); '
							);

							?>
							<script type="text/javascript">
							var timeout = setTimeout(function(){
								<?php echo $oAdmin_Form_Controller->getAdminLoadAjax(
									array('path' => $oAdmin_Form_Controller->getPath(), 'additionalParams' => $sAdditionalParams)
								)?>
							}, <?php echo $timeout * 1000?>);
							</script>
							<?php
						}
						else
						{
							Core_Message::show(Core::_('Shop_Item_Export.end_file_export', TMP_DIR . $Shop_Item_Export_Csv_Item_Controller->fileName), 'success');
						}

						$content = ob_get_clean();

						$bExportCompleted = FALSE;
					}
					else
					{
						// File sent to the stream
						exit();
					}
				}
				else
				{
					Core_Message::show(Core::_('Admin_Form.msg_error_access'), 'error');
				}
			break;
			case 1:
				$oAction = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
					->Admin_Form_Actions
					->getByName('exportOrdersCsv');

				if ($oAction && in_array('exportOrdersCsv', $aActions))
				{
					//ob_get_clean();

					$aSeparator = array(",", ";");
					$iSeparator = Core_Array::getPost('export_price_separator', 1, 'int');
					$Shop_Item_Export_Csv_Order_Controller = new Shop_Item_Export_Csv_Order_Controller(
						$shopId, FALSE, FALSE, FALSE, TRUE
					);
					$Shop_Item_Export_Csv_Order_Controller
						->separator($iSeparator > 1 ? '' : $aSeparator[$iSeparator])
						->encoding(Core_Array::getPost('import_price_encoding', "UTF-8"))
						->startOrderDate(Core_Array::getPost('order_begin_date', '01.01.1970'))
						->endOrderDate(Core_Array::getPost('order_end_date', '01.01.1970'))
						->exportToFile(Core_Array::getPost('export_mode', 0, 'bool'))
						->fileName('ORDERS_' . date("Y_m_d_H_i_s") . '.csv')
						->execute();
				}
				else
				{
					Core_Message::show(Core::_('Admin_Form.msg_error_access'), 'error');
				}
			break;
			case 2:
				$oAction = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
					->Admin_Form_Actions
					->getByName('exportItemsCml');

				if ($oAction && in_array('exportItemsCml', $aActions))
				{
					//ob_get_clean();

					$oShop_Item_Export_Cml_Controller = new Shop_Item_Export_Cml_Controller($oShop);
					$oShop_Item_Export_Cml_Controller->group = Core_Entity::factory('Shop_Group', $shopGroupParentId);
					$oShop_Item_Export_Cml_Controller->exportItemExternalProperties = !is_null(Core_Array::getPost('export_external_properties_allow_items'));
					$oShop_Item_Export_Cml_Controller->exportItemModifications = !is_null(Core_Array::getPost('export_modifications_allow'));
					$oShop_Item_Export_Cml_Controller->exportInStock(!is_null(Core_Array::getPost('export_in_stock_items')));
					$oShop_Item_Export_Cml_Controller->producer(Core_Array::getPost('shop_producer_id', 0, 'int'));

					header("Pragma: public");
					header("Content-Description: File Transfer");
					header("Content-Type: application/force-download");
					header("Content-Disposition: attachment; filename = " . 'import_' .date("Y_m_d_H_i_s").'.xml'. ";");
					header("Content-Transfer-Encoding: binary");

					echo $oShop_Item_Export_Cml_Controller->exportImport();

					exit();
				}
				else
				{
					Core_Message::show(Core::_('Admin_Form.msg_error_access'), 'error');
				}
			break;
			case 3:
				$oAction = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
					->Admin_Form_Actions
					->getByName('exportOffersCml');

				if ($oAction && in_array('exportOffersCml', $aActions))
				{
					//ob_get_clean();

					$oShop_Item_Export_Cml_Controller = new Shop_Item_Export_Cml_Controller($oShop);
					$oShop_Item_Export_Cml_Controller->group = Core_Entity::factory('Shop_Group', $shopGroupParentId);
					$oShop_Item_Export_Cml_Controller->exportItemExternalProperties = !is_null(Core_Array::getPost('export_external_properties_allow_items'));
					$oShop_Item_Export_Cml_Controller->exportItemModifications = !is_null(Core_Array::getPost('export_modifications_allow'));
					$oShop_Item_Export_Cml_Controller->exportInStock(!is_null(Core_Array::getPost('export_in_stock_items')));
					$oShop_Item_Export_Cml_Controller->producer(Core_Array::getPost('shop_producer_id', 0, 'int'));

					header("Pragma: public");
					header("Content-Description: File Transfer");
					header("Content-Type: application/force-download");
					header("Content-Disposition: attachment; filename = " . 'offers_' .date("Y_m_d_H_i_s").'.xml'. ";");
					header("Content-Transfer-Encoding: binary");

					echo $oShop_Item_Export_Cml_Controller->exportOffers();

					exit();
				}
				else
				{
					Core_Message::show(Core::_('Admin_Form.msg_error_access'), 'error');
				}
			break;
		}
	}
}

$oAdmin_View = Admin_View::create();
$oAdmin_View
	->module(Core_Module_Abstract::factory($sModule))
	->pageTitle(Core::_('Shop_Item.export_shop'))
	;

$oAdmin_Form_Entity_Breadcrumbs = Admin_Form_Entity::factory('Breadcrumbs');

// Первая крошка на список магазинов
$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Shop.menu'))
		->href($oAdmin_Form_Controller->getAdminLoadHref('/{admin}/shop/index.php'))
		->onclick($oAdmin_Form_Controller->getAdminLoadAjax('/{admin}/shop/index.php'))
);

// Крошки по директориям магазинов
if ($oShopDir->id)
{
	$oShopDirBreadcrumbs = $oShopDir;

	$aBreadcrumbs = array();

	do
	{
		$aBreadcrumbs[] = Admin_Form_Entity::factory('Breadcrumb')
			->name($oShopDirBreadcrumbs->name)
			->href($oAdmin_Form_Controller->getAdminLoadHref('/{admin}/shop/index.php', NULL, NULL, "shop_dir_id={$oShopDirBreadcrumbs->id}"))
			->onclick($oAdmin_Form_Controller->getAdminLoadAjax('/{admin}/shop/index.php', NULL, NULL, "shop_dir_id={$oShopDirBreadcrumbs->id}"));
	} while ($oShopDirBreadcrumbs = $oShopDirBreadcrumbs->getParent());

	$aBreadcrumbs = array_reverse($aBreadcrumbs);

	foreach ($aBreadcrumbs as $oBreadcrumb)
	{
		$oAdmin_Form_Entity_Breadcrumbs->add($oBreadcrumb);
	}
}

// Крошка на список товаров и групп товаров магазина
$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name($oShop->name)
		->href($oAdmin_Form_Controller->getAdminLoadHref('/{admin}/shop/item/index.php', NULL, NULL, "shop_id={$oShop->id}"))
		->onclick($oAdmin_Form_Controller->getAdminLoadAjax('/{admin}/shop/item/index.php', NULL, NULL, "shop_id={$oShop->id}"))
);

// Крошки по группам товаров
if ($oShopGroup->id)
{
	$oShopGroupBreadcrumbs = $oShopGroup;

	$aBreadcrumbs = array();

	do
	{
		$aBreadcrumbs[] = Admin_Form_Entity::factory('Breadcrumb')
			->name($oShopGroupBreadcrumbs->name)
			->href($oAdmin_Form_Controller->getAdminLoadHref('/{admin}/shop/item/index.php', NULL, NULL, "shop_id={$oShop->id}&shop_group_id={$oShopGroupBreadcrumbs->id}"))
			->onclick($oAdmin_Form_Controller->getAdminLoadAjax('/{admin}/shop/item/index.php', NULL, NULL, "shop_id={$oShop->id}&shop_group_id={$oShopGroupBreadcrumbs->id}"));
	} while ($oShopGroupBreadcrumbs = $oShopGroupBreadcrumbs->getParent());

	$aBreadcrumbs = array_reverse($aBreadcrumbs);

	foreach ($aBreadcrumbs as $oBreadcrumb)
	{
		$oAdmin_Form_Entity_Breadcrumbs->add($oBreadcrumb);
	}
}

// Крошка на текущую форму
$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Shop_Item.export_shop'))
		->href($oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL, "shop_id={$oShop->id}&shop_group_id={$oShopGroup->id}"))
		->onclick($oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL, "shop_id={$oShop->id}&shop_group_id={$oShopGroup->id}"))
);

$oAdmin_Form_Entity_Form = Admin_Form_Entity::factory('Form')
	->controller($oAdmin_Form_Controller)
	->action($oAdmin_Form_Controller->getPath())
	->target('_blank');

//$oAdmin_Form_Entity_Form->add($oAdmin_Form_Entity_Breadcrumbs);
$oAdmin_View->addChild($oAdmin_Form_Entity_Breadcrumbs);
$windowId = $oAdmin_Form_Controller->getWindowId();

$oMainTab = Admin_Form_Entity::factory('Tab')->name('main');
$oAdmin_Form_Entity_Form->add($oMainTab);

if ($bExportCompleted)
{
	ob_start(); // moved from top position

	$oMainTab->add(
		Admin_Form_Entity::factory('Div')->class('row')
		->add(
			Admin_Form_Entity::factory('Radiogroup')
				->radio(array(
					Core::_('Shop_Item.import_price_list_file_type1_items'),
					Core::_('Shop_Item.import_price_list_file_type1_orders'),
					Core::_('Shop_Item.export_price_list_file_type3_import'),
					Core::_('Shop_Item.export_price_list_file_type3_offers')
				))
				->ico(array(
					'fa-solid fa-file-csv fa-fw',
					'fa-solid  fa-file-csv fa-fw',
					'fa-solid fa-code fa-fw',
					'fa-solid fa-code fa-fw'
				))
				->caption(Core::_('Shop_Item.export_file_type'))
				->divAttr(array('class' => 'form-group col-xs-12 rounded-radio-group', 'id' => 'export_types'))
				->name('export_type')
				->onchange("radiogroupOnChange('{$windowId}', $(this).val(), [0,1,2,3])")
		))
		->add(Admin_Form_Entity::factory('Div')->class('row')->add(
			Admin_Form_Entity::factory('Radiogroup')
				->radio(array(
					Core::_('Shop_Item.import_price_list_separator1'),
					Core::_('Shop_Item.import_price_list_separator2')
				))
				->ico(array(
					'fa-solid fa-bolt fa-fw',
					'fa-solid fa-bolt fa-fw'
				))
				->name('export_price_separator')
				->value(1)
				->divAttr(array('class' => 'form-group col-xs-12 col-sm-4 rounded-radio-group hidden-2 hidden-3'))
				->caption(Core::_('Shop_Item.import_price_list_separator')))
			->add(
				Admin_Form_Entity::factory('Radiogroup')
					->radio(array(
						Core::_('Shop_Item_Export.export_mode1'),
						Core::_('Shop_Item_Export.export_mode2')
					))
					->ico(array(
						'fa-solid fa-file-excel fa-fw',
						'fa-solid fa-download fa-fw'
					))
					->colors(array('btn-sky', 'btn-maroon'))
					->name('export_mode')
					->value(0)
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-4 rounded-radio-group hidden-2 hidden-3'))
					->caption(Core::_('Shop_Item_Export.export_mode')))
		)
		->add(
			Admin_Form_Entity::factory('Div')->class('row')
			->add(
				Admin_Form_Entity::factory('Select')
					->name("import_price_encoding")
					->options(array(
						'UTF-8' => Core::_('Shop_Item.input_file_encoding1'),
						'Windows-1251' => Core::_('Shop_Item.input_file_encoding0')
					))
					->value(1)
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-2 col-md-2 hidden-2 hidden-3'))
					->caption(Core::_('Shop_Item.price_list_encoding'))
			)
			// Item's date
			->add(
				Admin_Form_Entity::factory('Date')
					->caption(Core::_('Shop_Item.start_order_date'))
					->name('item_begin_date')
					->value('')
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-4 col-md-2 hidden-1 hidden-2 hidden-3', 'id' => 'order_begin_date'))
			)->add(
				Admin_Form_Entity::factory('Date')
					->caption(Core::_('Shop_Item.stop_order_date'))
					->name('item_end_date')
					->value('')
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-4 col-md-2 hidden-1 hidden-2 hidden-3','id'=>'order_end_date'))
			)
			// Order's date
			->add(
				Admin_Form_Entity::factory('Date')
					->caption(Core::_('Shop_Item.start_order_date'))
					->name('order_begin_date')
					->value(Core_Date::timestamp2sql(strtotime("-2 months")))
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 col-md-3 hidden-0 hidden-2 hidden-3', 'id' => 'order_begin_date')
			)
			)->add(
				Admin_Form_Entity::factory('Date')
					->caption(Core::_('Shop_Item.stop_order_date'))
					->name('order_end_date')
					->value(Core_Date::timestamp2sql(time()))
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 col-md-3 hidden-0 hidden-2 hidden-3','id'=>'order_end_date'))
			)
		);

		class Shop_Item_Export_Csv_Property {

			protected $_linkedObject = NULL;

			public function __construct(Shop_Model $oShop)
			{
				$this->_linkedObject = Core_Entity::factory('Shop_Item_Property_List', $oShop->id);
			}

			public function setPropertyDirs($parent_id, $parentObject)
			{
				$oAdmin_Form_Entity_Section = Admin_Form_Entity::factory('Section')
					->caption($parent_id == 0
						? Core::_('Property_Dir.main_section')
						: Core_Entity::factory('Property_Dir', $parent_id)->name
					)
					->id('accordion_' . $parent_id);

				// Properties
				$oProperties = $this->_linkedObject->Properties;
				$oProperties
					->queryBuilder()
					->where('property_dir_id', '=', $parent_id);

				$aProperties = $oProperties->findAll();

				$oAdmin_Form_Entity_Section->add(
					$oPropertyDiv = Admin_Form_Entity::factory('Div')->class('row')
				);

				foreach ($aProperties as $oProperty)
				{
					$oPropertyDiv->add(
						Admin_Form_Entity::factory('Checkbox')
							->name("property_{$oProperty->id}")
							->caption($oProperty->name)
							->divAttr(array(
								'class' => 'form-group col-xs-12 col-sm-6 col-md-4 col-lg-4',
								'id' => 'property_' . $oProperty->id)
							)
							->value(1)
							->checked(FALSE)
					);
				}

				// Property Dirs
				$oProperty_Dirs = $this->_linkedObject->Property_Dirs;

				$oProperty_Dirs
					->queryBuilder()
					->where('parent_id', '=', $parent_id);

				$aProperty_Dirs = $oProperty_Dirs->findAll();
				foreach ($aProperty_Dirs as $oProperty_Dir)
				{
					$this->setPropertyDirs($oProperty_Dir->id,  $parent_id == 0 ? $parentObject : $oAdmin_Form_Entity_Section);
				}

				$oAdmin_Form_Entity_Section->getCountChildren() && $parentObject->add($oAdmin_Form_Entity_Section);
			}
		}

		$oMainTab->add($oPropertyBlock = Admin_Form_Entity::factory('Div')->class('well with-header hidden-0 hidden-2 hidden-3'));

		$oPropertyBlock
			->add(Admin_Form_Entity::factory('Div')
				->class('header bordered-warning')
				->value(Core::_("Shop_Item_Export.property_header"))
			)
			->add($oPropertyCurrentRow = Admin_Form_Entity::factory('Div')->class('row'));

		$oShop_Item_Export_Csv_Property = new Shop_Item_Export_Csv_Property($oShop);
		$oShop_Item_Export_Csv_Property->setPropertyDirs(0, $oPropertyCurrentRow);
		// /Properties

		$oMainTab->add(
			Admin_Form_Entity::factory('Div')->class('row')->add(
				Admin_Form_Entity::factory('Select')
					->name("shop_groups_parent_id")
					->options(array(' … ') + Shop_Item_Controller_Edit::fillShopGroup($oShop->id))
					->divAttr(array('class' => 'form-group col-xs-12 hidden-1', 'id' => 'shop_groups_parent_id'))
					->caption(Core::_('Shop_Item.import_price_list_parent_group'))
					->value($oShopGroup->id)
					->filter(TRUE)
				)
			)
		->add(
			Admin_Form_Entity::factory('Div')->class('row')->add(
				Admin_Form_Entity::factory('Select')
					->name("shop_producer_id")
					->options(array(' … ') + Shop_Item_Controller_Edit::fillProducersList($oShop->id))
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 hidden-1', 'id' => 'shop_producer_id'))
					->caption(Core::_('Shop_Item.shop_producer_id'))
					->value($oShopGroup->id)
			)
			->add(
				Admin_Form_Entity::factory('Select')
					->name("shop_seller_id")
					->options(array(' … ') + Shop_Item_Controller_Edit::fillSellersList($oShop->id))
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 hidden-1 hidden-2 hidden-3', 'id' => 'shop_seller_id'))
					->caption(Core::_('Shop_Item.shop_seller_id'))
					->value($oShopGroup->id)
			)
		)

		->add(Admin_Form_Entity::factory('Div')->class('row')->add(
				Admin_Form_Entity::factory('Checkbox')
					->name("export_external_properties_allow_items")
					->caption(Core::_('Shop_Item.export_external_properties_allow_items'))
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 hidden-1', 'id' => 'export_external_properties_allow_items'))
					->value(TRUE)
			)
			->add(
				Admin_Form_Entity::factory('Checkbox')
					->name("export_external_properties_allow_groups")
					->caption(Core::_('Shop_Item.export_external_properties_allow_groups'))
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 hidden-1 hidden-2 hidden-3', 'id' => 'export_external_properties_allow_groups'))
					->value(TRUE)
			)
		)
		->add(Admin_Form_Entity::factory('Div')->class('row')->add(
				Admin_Form_Entity::factory('Checkbox')
					->name("export_fields_allow_items")
					->caption(Core::_('Shop_Item.export_fields_allow_items'))
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 hidden-1', 'id' => 'export_fields_allow_items'))
					->value(TRUE)
			)
			->add(
				Admin_Form_Entity::factory('Checkbox')
					->name("export_fields_allow_groups")
					->caption(Core::_('Shop_Item.export_fields_allow_groups'))
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 hidden-1 hidden-2 hidden-3', 'id' => 'export_fields_allow_groups'))
					->value(TRUE)
			)
		)
		->add(Admin_Form_Entity::factory('Div')->class('row')->add(
				Admin_Form_Entity::factory('Checkbox')
					->name("export_modifications_allow")
					->caption(Core::_('Shop_Item.export_modifications_allow'))
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 hidden-1', 'id' => 'export_modifications_allow'))
					->value(TRUE)
			)
			->add(
				Admin_Form_Entity::factory('Checkbox')
					->name("export_shortcuts_allow")
					->caption(Core::_('Shop_Item.export_shortcuts_allow'))
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 hidden-1 hidden-2 hidden-3'))
					->value(TRUE)
			)
		)
		->add(Admin_Form_Entity::factory('Div')->class('row')->add(
				Admin_Form_Entity::factory('Checkbox')
					->name("export_stocks")
					->caption(Core::_('Shop_Item.export_stocks'))
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 hidden-1 hidden-2 hidden-3', 'id' => 'export_stocks'))
					->value(FALSE)
			)->add(
				Admin_Form_Entity::factory('Checkbox')
					->name("export_prices")
					->caption(Core::_('Shop_Item.export_prices'))
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 hidden-1 hidden-2 hidden-3', 'id' => 'export_prices'))
					->value(FALSE)
			)
		)
		->add(Admin_Form_Entity::factory('Div')->class('row')->add(
				Admin_Form_Entity::factory('Checkbox')
					->name("export_in_stock_items")
					->caption(Core::_('Shop_Item.export_in_stock_items'))
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 hidden-1', 'id' => 'export_in_stock_items'))
					->value(FALSE)
			)
		)
		->add(Admin_Form_Entity::factory('Div')->class('row')
			->add(Core_Html_Entity::factory('Input')->type('hidden')->name('action')->value('export'))
			->add(Core_Html_Entity::factory('Input')->type('hidden')->name('shop_group_id')->value($shopGroupId))
			->add(Core_Html_Entity::factory('Input')->type('hidden')->name('shop_id')->value($shopId))
		);

	$oAdmin_Form_Entity_Form->add(
		Admin_Form_Entity::factory('Button')
		->name('show_form')
		->type('submit')
		->class('applyButton btn btn-blue')
	)
	->add(
		Core_Html_Entity::factory('Script')
			->type("text/javascript")
			->value("radiogroupOnChange('{$windowId}', 0, [0,1,2,3])")
	);

	$oAdmin_Form_Entity_Form->execute();
	$content = ob_get_clean();
}

ob_start();
$oAdmin_View
	->content($content)
	->show();

/*$oAdmin_Answer = Core_Skin::instance()->answer();

$oAdmin_Answer
	->ajax(Core_Array::getRequest('_', FALSE))
	->content(ob_get_clean())
	//->message()
	->title(Core::_('Shop_Item.export_shop'))
	->execute();*/

Core_Skin::instance()
	->answer()
	->ajax(Core_Array::getRequest('_', FALSE))
	//->content(iconv("UTF-8", "UTF-8//IGNORE//TRANSLIT", ob_get_clean()))
	->content(ob_get_clean())
	->title(Core::_('Shop_Item.export_shop'))
	->execute();