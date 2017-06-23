<?php
/**
 * Online shop.
 *
 * @package HostCMS
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
require_once('../../../../bootstrap.php');

Core_Auth::authorization($sModule = 'shop');

$oShop = Core_Entity::factory('Shop', Core_Array::getRequest('shop_id', 0));
$oShopDir = $oShop->Shop_Dir;
$shop_group_id = Core_Array::getRequest('shop_group_id', 0);
$oShopGroup = Core_Entity::factory('Shop_Group', $shop_group_id);
$oAdmin_Form_Controller = Admin_Form_Controller::create();
$oAdmin_Form_Entity_Breadcrumbs = Admin_Form_Entity::factory('Breadcrumbs');

// Контроллер формы
$oAdmin_Form_Controller
	->module(Core_Module::factory($sModule))
	->setUp()
	->path('/admin/shop/item/import/index.php')
;

ob_start();

$oAdmin_View = Admin_View::create();
$oAdmin_View
	->module(Core_Module::factory($sModule))
	->pageTitle(Core::_('Shop_Item.import_price_list_link'))
	;

// Первая крошка на список магазинов
$oAdmin_Form_Entity_Breadcrumbs->add(
Admin_Form_Entity::factory('Breadcrumb')
	->name(Core::_('Shop.menu'))
	->href($oAdmin_Form_Controller->getAdminLoadHref(
		'/admin/shop/index.php'
	))
	->onclick($oAdmin_Form_Controller->getAdminLoadAjax(
		'/admin/shop/index.php'
	))
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
		->href($oAdmin_Form_Controller->getAdminLoadHref(
				'/admin/shop/index.php', NULL, NULL, "shop_dir_id={$oShopDirBreadcrumbs->id}"
		))
		->onclick($oAdmin_Form_Controller->getAdminLoadAjax(
				'/admin/shop/index.php', NULL, NULL, "shop_dir_id={$oShopDirBreadcrumbs->id}"
		));
	}while($oShopDirBreadcrumbs = $oShopDirBreadcrumbs->getParent());

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
	->href($oAdmin_Form_Controller->getAdminLoadHref(
			'/admin/shop/item/index.php', NULL, NULL, "shop_id={$oShop->id}"
	))
	->onclick($oAdmin_Form_Controller->getAdminLoadAjax(
			'/admin/shop/item/index.php', NULL, NULL, "shop_id={$oShop->id}"
	))
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
		->href($oAdmin_Form_Controller->getAdminLoadHref(
			'/admin/shop/item/index.php', NULL, NULL, "shop_id={$oShop->id}&shop_group_id={$oShopGroupBreadcrumbs->id}"
		))
		->onclick($oAdmin_Form_Controller->getAdminLoadAjax(
			'/admin/shop/item/index.php', NULL, NULL, "shop_id={$oShop->id}&shop_group_id={$oShopGroupBreadcrumbs->id}"
		));
	} while($oShopGroupBreadcrumbs = $oShopGroupBreadcrumbs->getParent());

	$aBreadcrumbs = array_reverse($aBreadcrumbs);

	foreach ($aBreadcrumbs as $oBreadcrumb)
	{
		$oAdmin_Form_Entity_Breadcrumbs->add($oBreadcrumb);
	}
}

// Крошка на текущую форму
$oAdmin_Form_Entity_Breadcrumbs->add(
Admin_Form_Entity::factory('Breadcrumb')
	->name(Core::_('Shop_Item.import_price_list_link'))
	->href(
		$oAdmin_Form_Controller->getAdminLoadHref(
			$oAdmin_Form_Controller->getPath(), NULL, NULL, "shop_id={$oShop->id}&shop_group_id={$oShopGroup->id}"
		)
	)
	->onclick(
		$oAdmin_Form_Controller->getAdminLoadAjax(
			$oAdmin_Form_Controller->getPath(), NULL, NULL, "shop_id={$oShop->id}&shop_group_id={$oShopGroup->id}"
		)
	)
);

// Формируем массивы данных
$aLangConstNames = array(
	Core::_('Shop_Exchange.!download'),

	// groups
	Core::_('Shop_Exchange.group_id'),
	Core::_('Shop_Exchange.group_name'),
	Core::_('Shop_Exchange.group_path'),
	Core::_('Shop_Exchange.group_sorting'),
	Core::_('Shop_Exchange.group_description'),
	Core::_('Shop_Exchange.group_active'),
	Core::_('Shop_Exchange.group_seo_title'),
	Core::_('Shop_Exchange.group_seo_description'),
	Core::_('Shop_Exchange.group_seo_keywords'),
	Core::_('Shop_Exchange.group_image_large'),
	Core::_('Shop_Exchange.group_image_small'),
	Core::_('Shop_Exchange.group_guid'),
	Core::_('Shop_Exchange.parent_group_guid'),

	// currency
	Core::_('Shop_Exchange.currency_id'),

	// tax
	Core::_('Shop_Exchange.tax_id'),

	// producer
	Core::_('Shop_Exchange.producer_id'),
	Core::_('Shop_Exchange.producer_name'),

	// seller
	Core::_('Shop_Exchange.seller_id'),
	Core::_('Shop_Exchange.seller_name'),

	// measure
	Core::_('Shop_Exchange.measure_id'),
	Core::_('Shop_Exchange.measure_value'),

	// items
	Core::_('Shop_Exchange.item_id'),
	Core::_('Shop_Exchange.item_name'),
	Core::_('Shop_Exchange.item_marking'),
	Core::_('Shop_Exchange.item_datetime'),
	Core::_('Shop_Exchange.item_description'),
	Core::_('Shop_Exchange.item_text'),
	Core::_('Shop_Exchange.item_image_large'),
	Core::_('Shop_Exchange.item_image_small'),
	Core::_('Shop_Exchange.item_tags'),
	Core::_('Shop_Exchange.item_weight'),
	Core::_('Shop_Exchange.item_length'),
	Core::_('Shop_Exchange.item_width'),
	Core::_('Shop_Exchange.item_height'),
	Core::_('Shop_Exchange.item_price'),
	Core::_('Shop_Exchange.item_active'),
	Core::_('Shop_Exchange.item_sorting'),
	Core::_('Shop_Exchange.item_path'),
	Core::_('Shop_Exchange.item_seo_title'),
	Core::_('Shop_Exchange.item_seo_description'),
	Core::_('Shop_Exchange.item_seo_keywords'),
	Core::_('Shop_Exchange.item_indexing'),
	Core::_('Shop_Exchange.item_yandex_market'),
	Core::_('Shop_Exchange.item_yandex_market_bid'),
	Core::_('Shop_Exchange.item_yandex_market_cid'),
	Core::_('Shop_Exchange.item_yandex_market_manufacturer_warranty'),
	Core::_('Shop_Exchange.item_yandex_market_vendorcode'),
	Core::_('Shop_Exchange.item_yandex_market_country_of_origin'),
	Core::_('Shop_Exchange.item_parent_marking'),
	Core::_('Shop_Exchange.item_parent_guid'),
	Core::_('Shop_Exchange.digital_item_name'),
	Core::_('Shop_Exchange.digital_item_value'),
	Core::_('Shop_Exchange.digital_item_filename'),
	Core::_('Shop_Exchange.digital_item_count'),
	Core::_('Shop_Exchange.item_end_datetime'),
	Core::_('Shop_Exchange.item_start_datetime'),
	Core::_('Shop_Exchange.item_type'),
	Core::_('Shop_Exchange.siteuser_id'),
	Core::_('Shop_Exchange.item_yandex_market_sales_notes'),
	Core::_('Shop_Exchange.item_additional_group'),
	Core::_('Shop_Exchange.item_guid'),

	// item special prices
	Core::_('Shop_Exchange.specialprices_min_quantity'),
	Core::_('Shop_Exchange.specialprices_max_quantity'),
	Core::_('Shop_Exchange.specialprices_price'),
	Core::_('Shop_Exchange.specialprices_percent'),

	// item associated
	Core::_('Shop_Exchange.item_parent_associated'),
	Core::_('Shop_Exchange.item_associated_markings'),

	// order
	Core::_('Shop_Exchange.order_guid'),
	Core::_('Shop_Exchange.order_number'),
	Core::_('Shop_Exchange.order_country'),
	Core::_('Shop_Exchange.order_location'),
	Core::_('Shop_Exchange.order_city'),
	Core::_('Shop_Exchange.order_city_area'),
	Core::_('Shop_Exchange.order_name'),
	Core::_('Shop_Exchange.order_surname'),
	Core::_('Shop_Exchange.order_patronymic'),
	Core::_('Shop_Exchange.order_email'),
	Core::_('Shop_Exchange.order_akt'),
	Core::_('Shop_Exchange.order_schet_fak'),
	Core::_('Shop_Exchange.order_company_name'),
	Core::_('Shop_Exchange.order_inn'),
	Core::_('Shop_Exchange.order_kpp'),
	Core::_('Shop_Exchange.order_phone'),
	Core::_('Shop_Exchange.order_fax'),
	Core::_('Shop_Exchange.order_address'),
	Core::_('Shop_Exchange.order_order_status'),
	Core::_('Shop_Exchange.order_currency'),
	Core::_('Shop_Exchange.order_payment_system_id'),
	Core::_('Shop_Exchange.order_date'),
	Core::_('Shop_Exchange.order_pay_status'),
	Core::_('Shop_Exchange.order_pay_date'),
	Core::_('Shop_Exchange.order_description'),
	Core::_('Shop_Exchange.order_info'),
	Core::_('Shop_Exchange.order_canceled'),
	Core::_('Shop_Exchange.order_pay_status_change_date'),
	Core::_('Shop_Exchange.order_delivery_info'),

	// order items
	Core::_('Shop_Exchange.order_item_marking'),
	Core::_('Shop_Exchange.order_item_name'),
	Core::_('Shop_Exchange.order_item_quantity'),
	Core::_('Shop_Exchange.order_item_price'),
	Core::_('Shop_Exchange.order_item_rate'),
	Core::_('Shop_Exchange.order_item_type')
);

$aColors = array(
	'#F5F5F5',

	// groups
	'#E6EE9C',
	'#E6EE9C',
	'#E6EE9C',
	'#E6EE9C',
	'#E6EE9C',
	'#E6EE9C',
	'#E6EE9C',
	'#E6EE9C',
	'#E6EE9C',
	'#E6EE9C',
	'#E6EE9C',
	'#E6EE9C',
	'#E6EE9C',

	// currency
	'#80CBC4',

	// tax
	'#80DEEA',

	// producer
	'#9FA8DA',
	'#9FA8DA',

	// seller
	'#B39DDB',
	'#B39DDB',

	// measure
	'#F48FB1',
	'#F48FB1',

	// items
	'#FFCC80',
	'#FFCC80',
	'#FFCC80',
	'#FFCC80',
	'#FFCC80',
	'#FFCC80',
	'#FFCC80',
	'#FFCC80',
	'#FFCC80',
	'#FFCC80',
	'#FFCC80',
	'#FFCC80',
	'#FFCC80',
	'#FFCC80',
	'#FFCC80',
	'#FFCC80',
	'#FFCC80',
	'#FFCC80',
	'#FFCC80',
	'#FFCC80',
	'#FFCC80',
	'#FFCC80',
	'#FFCC80',
	'#FFCC80',
	'#FFCC80',
	'#FFCC80',
	'#FFCC80',
	'#FFCC80',
	'#FFCC80',
	'#FFCC80',
	'#FFCC80',
	'#FFCC80',
	'#FFCC80',
	'#FFCC80',
	'#FFCC80',
	'#FFCC80',
	'#FFCC80',
	'#FFCC80',
	'#FFCC80',
	'#FFCC80',

	// item special prices
	'#FFB74D',
	'#FFB74D',
	'#FFB74D',
	'#FFB74D',

	// item associated
	'#B0BEC5',
	'#B0BEC5',

	// order
	'#A5D6A7',
	'#A5D6A7',
	'#A5D6A7',
	'#A5D6A7',
	'#A5D6A7',
	'#A5D6A7',
	'#A5D6A7',
	'#A5D6A7',
	'#A5D6A7',
	'#A5D6A7',
	'#A5D6A7',
	'#A5D6A7',
	'#A5D6A7',
	'#A5D6A7',
	'#A5D6A7',
	'#A5D6A7',
	'#A5D6A7',
	'#A5D6A7',
	'#A5D6A7',
	'#A5D6A7',
	'#A5D6A7',
	'#A5D6A7',
	'#A5D6A7',
	'#A5D6A7',
	'#A5D6A7',
	'#A5D6A7',
	'#A5D6A7',
	'#A5D6A7',
	'#A5D6A7',

	// order items
	'#A5D6A7',
	'#A5D6A7',
	'#A5D6A7',
	'#A5D6A7',
	'#A5D6A7',
	'#A5D6A7'
);

$aEntities = array(
	'',

	'group_id',
	'group_name',
	'group_path',
	'group_sorting',
	'group_description',
	'group_active',
	'group_seo_title',
	'group_seo_description',
	'group_seo_keywords',
	'group_image',
	'group_small_image',
	'group_cml_id',
	'group_parent_cml_id',

	'currency_id',

	'tax_id',

	'producer_id',
	'producer_name',

	'seller_id',
	'seller_name',

	'mesure_id',
	'mesure_name',

	'item_id',
	'item_name',
	'item_marking',
	'item_datetime',
	'item_description',
	'item_text',
	'item_image',
	'item_small_image',
	'item_tags',
	'item_weight',
	'item_length',
	'item_width',
	'item_height',
	'item_price',
	'item_active',
	'item_sorting',
	'item_path',
	'item_seo_title',
	'item_seo_description',
	'item_seo_keywords',
	'item_indexing',
	'item_yandex_market_allow',
	'item_yandex_market_bid',
	'item_yandex_market_cid',
	'item_manufacturer_warranty',
	'item_vendorcode',
	'item_country_of_origin',
	'item_parent_marking',
	'item_parent_guid',
	'item_digital_name',
	'item_digital_text',
	'item_digital_file',
	'item_digital_count',
	'item_end_datetime',
	'item_start_datetime',
	'item_type',
	'item_siteuser_id',
	'item_yandex_market_sales_notes',
	'additional_groups',
	'item_cml_id',

	'item_special_price_from',
	'item_special_price_to',
	'item_special_price_price',
	'item_special_price_percent',

	'item_parent_associated',
	'item_associated_markings',

	'order_guid',
	'order_invoice',
	'order_shop_country_id',
	'order_shop_country_location_id',
	'order_shop_country_location_city_id',
	'order_shop_country_location_city_area_id',
	'order_name',
	'order_surname',
	'order_patronymic',
	'order_email',
	'order_acceptance_report',
	'order_vat_invoice',
	'order_company',
	'order_tin',
	'order_kpp',
	'order_phone',
	'order_fax',
	'order_address',
	'order_shop_order_status_id',
	'order_shop_currency_id',
	'order_shop_payment_system_id',
	'order_datetime',
	'order_paid',
	'order_payment_datetime',
	'order_description',
	'order_system_information',
	'order_canceled',
	'order_status_datetime',
	'order_delivery_information',

	'order_item_marking',
	'order_item_name',
	'order_item_quantity',
	'order_item_price',
	'order_item_rate',
	'order_item_type'
);

$aGroupProperties = Core_Entity::factory('Shop_Group_Property_List', $oShop->id)->Properties->findAll();
foreach ($aGroupProperties as $oGroupProperty)
{
	$oPropertyDir = $oGroupProperty->Property_Dir;

	$aLangConstNames[] = $oGroupProperty->name . "&nbsp;[" . ($oPropertyDir->id ? $oPropertyDir->name : Core::_('Shop_item.root_folder')) . "]";
	$aColors[] = "#E6EE9C";
	$aEntities[] = 'prop_group-' . $oGroupProperty->id;

	if ($oGroupProperty->type == 2)
	{
		$aLangConstNames[] = Core::_('Shop_Item.import_small_images') . $oGroupProperty->name . " [" . ($oPropertyDir->id ? $oPropertyDir->name : Core::_('Shop_item.root_folder')) . "]";
		$aColors[] = "#E6EE9C";
		$aEntities[] = 'propsmall-' . $oGroupProperty->id;
	}
}

$aItemProperties = Core_Entity::factory('Shop_Item_Property_List', $oShop->id)->Properties->findAll();
foreach ($aItemProperties as $oItemProperty)
{
	$oPropertyDir = $oItemProperty->Property_Dir;

	$aLangConstNames[] = $oItemProperty->name . " [" . ($oPropertyDir->id ? $oPropertyDir->name : Core::_('Shop_item.root_folder')) . "]";
	$aColors[] = "#FFD54F";
	$aEntities[] = 'prop-' . $oItemProperty->id;

	if ($oItemProperty->type == 2)
	{
		$aLangConstNames[] = Core::_('Shop_Item.import_small_images') . $oItemProperty->name . " [" . ($oPropertyDir->id ? $oPropertyDir->name : Core::_('Shop_item.root_folder')) . "]";
		$aColors[] = "#FFD54F";
		$aEntities[] = 'propsmall-' . $oItemProperty->id;
	}
}

$aShopPrices = Core_Entity::factory('Shop', $oShop->id)->Shop_prices->findAll();
foreach ($aShopPrices as $oShopPrice)
{
	$aLangConstNames[] = $oShopPrice->name;
	$aColors[] = "#B0BEC5";
	$aEntities[] = 'price-' . $oShopPrice->id;
}

// Выводим склады
$aShopWarehouses = Core_Entity::factory('Shop', $oShop->id)->Shop_Warehouses->findAll();
foreach ($aShopWarehouses as $oShopWarehouse)
{
	$aLangConstNames[] = Core::_('Shop_Item.warehouse_import_field', $oShopWarehouse->name);
	$aColors[] = "#F48FB1";
	$aEntities[] = 'warehouse-' . $oShopWarehouse->id;
}

$oUserCurrent = Core_Entity::factory('User', 0)->getCurrent();

$oAdmin_Form_Entity_Form = Admin_Form_Entity::factory('Form')
		->controller($oAdmin_Form_Controller)
		->action($oAdmin_Form_Controller->getPath())
		->enctype('multipart/form-data');

$oAdmin_View->addChild($oAdmin_Form_Entity_Breadcrumbs);

// Количество полей
$iFieldCount = 0;

$sOnClick = NULL;

$windowId = $oAdmin_Form_Controller->getWindowId();

if ($oAdmin_Form_Controller->getAction() == 'show_form')
{
	if (!$oUserCurrent->read_only)
	{
		$sFileName = isset($_FILES['csv_file']) && intval($_FILES['csv_file']['size']) > 0
			? $_FILES['csv_file']['tmp_name']
			: CMS_FOLDER . Core_Array::getPost('alternative_file_pointer');

		if (is_file($sFileName) && is_readable($sFileName))
		{
			if (Core_Array::getPost('import_price_type') == 0)
			{
				Core_Event::notify('Shop_Item_Import.oBeforeImportCSV', NULL, array($sFileName));

				// Обработка CSV-файла
				$sTmpFileName = CMS_FOLDER . TMP_DIR . 'file_'.date("U").'.csv';

				try {
					Core_File::upload($sFileName, $sTmpFileName);

					if ($fInputFile = fopen($sTmpFileName, 'rb'))
					{
						$sSeparator = Core_Array::getPost('import_price_separator');

						switch ($sSeparator)
						{
							case 0:
								$sSeparator = ',';
							break;
							case 1:
							default:
								$sSeparator = ';';
							break;
							case 2:
								$sSeparator = "\t";
							break;
							case 3:
								$sSeparator = Core_Array::getPost('import_price_separator_text');
							break;
						}

						$sLimiter = Core_Array::getPost('import_price_stop');

						switch ($sLimiter)
						{
							case 0:
							default:
								$sLimiter = '"';
							break;
							case 1:
								$sLimiter = Core_Array::getPost('import_price_stop_text');
							break;
						}

						$sLocale = Core_Array::getPost('import_price_encoding');
						$oShop_Item_Import_Csv_Controller = new Shop_Item_Import_Csv_Controller($oShop->id, Core_Array::getPost('shop_groups_parent_id', 0));
						$oShop_Item_Import_Csv_Controller->encoding(
							$sLocale
						)->separator($sSeparator)->limiter($sLimiter);

						$aCsvLine = $oShop_Item_Import_Csv_Controller->getCSVLine($fInputFile);

						$iFieldCount = is_array($aCsvLine) ? count($aCsvLine) : 0;

						fclose($fInputFile);

						if ($iFieldCount)
						{
							$iValuesCount = count($aLangConstNames);

							$pos = 0;

							$oMainTab = Admin_Form_Entity::factory('Tab')->name('main');

							for($i = 0; $i < $iFieldCount; $i++)
							{
								$oCurrentRow = Admin_Form_Entity::factory('Div')->class('row');

								$oCurrentRow
									->add(Admin_Form_Entity::factory('Span')
										//->caption('')
										->value($aCsvLine[$i])
										->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'))
										);

								$aOptions = array();

								$isset_selected = FALSE;

								// Генерируем выпадающий список с цветными элементами
								for($j = 0; $j < $iValuesCount; $j++)
								{
									$aCsvLine[$i] = trim($aCsvLine[$i]);

									if (!$isset_selected
									&& (mb_strtolower($aCsvLine[$i]) == mb_strtolower($aLangConstNames[$j])
									|| (strlen($aLangConstNames[$j]) > 0
									&& strlen($aCsvLine[$i]) > 0
									&&
									(strpos($aCsvLine[$i], $aLangConstNames[$j]) !== FALSE
									|| strpos($aLangConstNames[$j], $aCsvLine[$i]) !== FALSE)
									// Чтобы не было срабатывания "Город" -> "Городской телефон"
									// Если есть целиком подходящее поле
									&& !array_search($aCsvLine[$i], $aLangConstNames))
									))
									{
										$selected = $aEntities[$j];

										// Для исключения двойного указания selected для одного списка
										$isset_selected = TRUE;
									}
									elseif (!$isset_selected)
									{
										$selected = -1;
									}

									$aOptions[$aEntities[$j]] = array('value' => $aLangConstNames[$j], 'attr' => array('style' => 'background-color: ' . (!empty($aColors[$pos]) ? $aColors[$j] : '#000')));

									$pos++;
								}

								$pos = 0;

								$oCurrentRow->add(Admin_Form_Entity::factory('Select')
									->name("field{$i}")
									->options($aOptions)
									->value($selected)
									->divAttr(array('class' => 'form-group col-xs-12 col-sm-6')));

								$oMainTab->add($oCurrentRow);
							}

							$oMainTab->add(Admin_Form_Entity::factory('Div')->class('row')
								->add(Core::factory('Core_Html_Entity_Input')->type('hidden')->name('shop_group_id')->value($oShopGroup->id))
								->add(Core::factory('Core_Html_Entity_Input')->type('hidden')->name('csv_filename')->value($sTmpFileName))
								->add(Core::factory('Core_Html_Entity_Input')->type('hidden')->name('import_price_separator')->value($sSeparator))
								->add(Core::factory('Core_Html_Entity_Input')->type('hidden')->name('import_price_stop')->value($sLimiter))
								->add(Core::factory('Core_Html_Entity_Input')->type('hidden')->name('firstlineheader')->value(isset($_POST['import_price_name_field_f']) ? 1 : 0))
								->add(Core::factory('Core_Html_Entity_Input')->type('hidden')->name('locale')->value($sLocale))
								->add(Core::factory('Core_Html_Entity_Input')->type('hidden')->name('import_price_max_time')->value(Core_Array::getPost('import_price_max_time')))
								->add(Core::factory('Core_Html_Entity_Input')->type('hidden')->name('import_price_max_count')->value(Core_Array::getPost('import_price_max_count')))
								->add(Core::factory('Core_Html_Entity_Input')->type('hidden')->name('import_price_load_files_path')->value(Core_Array::getPost('import_price_load_files_path')))
								->add(Core::factory('Core_Html_Entity_Input')->type('hidden')->name('import_price_action_items')->value(Core_Array::getPost('import_price_action_items')))
								->add(Core::factory('Core_Html_Entity_Input')->type('hidden')->name('shop_groups_parent_id')->value(Core_Array::getPost('shop_groups_parent_id')))
								->add(Core::factory('Core_Html_Entity_Input')->type('hidden')->name('search_event_indexation')->value(isset($_POST['search_event_indexation']) ? 1 : 0))
								->add(Core::factory('Core_Html_Entity_Input')->type('hidden')->name('import_price_action_delete_image')->value(isset($_POST['import_price_action_delete_image']) ? 1 : 0))
							);

							$oAdmin_Form_Entity_Form->add($oMainTab);
						}
						else
						{
							throw new Core_Exception("File is empty!");
						}
					}
					else
					{
						throw new Core_Exception("Can't open file");
					}

				} catch (Exception $exc) {
					Core_Message::show($exc->getMessage(), "error");
				}

				$sOnClick = $oAdmin_Form_Controller->getAdminSendForm('start_import');
			}
			else
			{
				Core_Event::notify('Shop_Item_Import.oBeforeImportCML', NULL, array($sFileName));

				// Обработка CommerceML-файла
				$sTmpFileName = CMS_FOLDER . TMP_DIR . 'file_'.date("U").'.cml';

				try {
					Core_File::upload($sFileName, $sTmpFileName);

					Core_Session::start();

					// Reset importPosition
					$_SESSION['importPosition'] = 0;

					$oShop_Item_Import_Cml_Controller = new Shop_Item_Import_Cml_Controller($sTmpFileName);
					$oShop_Item_Import_Cml_Controller->timeout = 0;
					$oShop_Item_Import_Cml_Controller->iShopId = $oShop->id;
					$oShop_Item_Import_Cml_Controller->iShopGroupId = Core_Array::getPost('shop_groups_parent_id', 0);
					$oShop_Item_Import_Cml_Controller->sPicturesPath = Core_Array::getPost('import_price_load_files_path');
					$oShop_Item_Import_Cml_Controller->importAction = Core_Array::getPost('import_price_action_items');
					$fRoznPrice_name = defined('SHOP_DEFAULT_CML_CURRENCY_NAME')
						? SHOP_DEFAULT_CML_CURRENCY_NAME
						: 'Розничная';
					$oShop_Item_Import_Cml_Controller->sShopDefaultPriceName = $fRoznPrice_name;
					$aReturn = $oShop_Item_Import_Cml_Controller->import();

					Core_Message::show(Core::_('Shop_Item.msg_download_price_complete'));
					echo Core::_('Shop_Item.count_insert_item') . ' &#151; <b>' . $aReturn['insertItemCount'] . '</b><br/>';
					echo Core::_('Shop_Item.count_update_item') . ' &#151; <b>' . $aReturn['updateItemCount'] . '</b><br/>';
					echo Core::_('Shop_Item.create_catalog') . ' &#151; <b>' . $aReturn['insertDirCount'] . '</b><br/>';
					echo Core::_('Shop_Item.update_catalog') . ' &#151; <b>' . $aReturn['updateDirCount'] . '</b><br/>';
				} catch (Exception $exc) {
					Core_Message::show($exc->getMessage(), "error");
				}

				Core_File::delete($sTmpFileName);

				$sOnClick = "";
			}
		}
		else
		{
			Core_Message::show(Core::_('Shop_Item.file_does_not_specified'), "error");
			$sOnClick = "";
		}
	}
	else
	{
		Core_Message::show(Core::_('User.demo_mode'), "error");
	}
}
elseif ($oAdmin_Form_Controller->getAction() == 'start_import')
{
	if (!$oUserCurrent->read_only)
	{
		Core_Session::start();

		if (isset($_SESSION['Shop_Item_Import_Csv_Controller']))
		{
			$Shop_Item_Import_Csv_Controller = $_SESSION['Shop_Item_Import_Csv_Controller'];
			unset($_SESSION['Shop_Item_Import_Csv_Controller']);

			$iNextSeekPosition = $Shop_Item_Import_Csv_Controller->seek;
		}
		else
		{
			$Shop_Item_Import_Csv_Controller = new Shop_Item_Import_Csv_Controller(Core_Array::getRequest('shop_id', 0), Core_Array::getRequest('shop_groups_parent_id', 0));

			$aConformity = array();

			foreach ($_POST as $iKey => $sValue)
			{
				if (mb_strpos($iKey, "field") === 0)
				{
					$aConformity[] = $sValue;
				}
			}

			$iNextSeekPosition = 0;

			$sCsvFilename = Core_Array::getPost('csv_filename');

			$Shop_Item_Import_Csv_Controller
				->file($sCsvFilename)
				->encoding(Core_Array::getPost('locale', 'UTF-8'))
				->csv_fields($aConformity)
				->time(Core_Array::getPost('import_price_max_time'))
				->step(Core_Array::getPost('import_price_max_count'))
				->separator(Core_Array::getPost('import_price_separator'))
				->limiter(Core_Array::getPost('import_price_stop'))
				->imagesPath(Core_Array::getPost('import_price_load_files_path'))
				->importAction(Core_Array::getPost('import_price_action_items'))
				->searchIndexation(Core_Array::getPost('search_event_indexation'))
				->deleteImage(Core_Array::getPost('import_price_action_delete_image'))
			;

			if (Core_Array::getPost('firstlineheader', 0))
			{
				$fInputFile = fopen($Shop_Item_Import_Csv_Controller->file, 'rb');
				@fgetcsv($fInputFile, 0, $Shop_Item_Import_Csv_Controller->separator, $Shop_Item_Import_Csv_Controller->limiter);
				$iNextSeekPosition = ftell($fInputFile);
				fclose($fInputFile);
			}
		}

		$Shop_Item_Import_Csv_Controller->seek = $iNextSeekPosition;

		ob_start();

		if (($iNextSeekPosition = $Shop_Item_Import_Csv_Controller->import()) !== FALSE)
		{
			$Shop_Item_Import_Csv_Controller->seek = $iNextSeekPosition;

			if ($Shop_Item_Import_Csv_Controller->importAction == 0)
			{
				$Shop_Item_Import_Csv_Controller->importAction = 1;
			}

			$_SESSION['Shop_Item_Import_Csv_Controller'] = $Shop_Item_Import_Csv_Controller;

			$sRedirectAction = $oAdmin_Form_Controller->getAdminLoadAjax('/admin/shop/item/import/index.php', 'start_import', NULL, "shop_id={$oShop->id}&shop_group_id={$shop_group_id}");

			showStat($Shop_Item_Import_Csv_Controller);
		}
		else
		{
			$sRedirectAction = "";
			Core_Message::show(Core::_('Shop_Item.msg_download_price_complete'));
			showStat($Shop_Item_Import_Csv_Controller);
		}

		$oAdmin_Form_Entity_Form->add(
			Admin_Form_Entity::factory('Code')->html(ob_get_clean())
		);

		Core_Session::close();

		if ($sRedirectAction)
		{
			$iRedirectTime = 1000;
			Core::factory('Core_Html_Entity_Script')
				->type('text/javascript')
				->value('setTimeout(function (){ ' . $sRedirectAction . '}, ' . $iRedirectTime . ')')
				->execute();
		}

		$sOnClick = "";
	}
	else
	{
		Core_Message::show(Core::_('User.demo_mode'), "error");
	}
}
else
{
	$oMainTab = Admin_Form_Entity::factory('Tab')->name('main');

	$aConfig = Core_Config::instance()->get('shop_csv', array()) + array(
		'maxTime' => 20,
		'maxCount' => 100
	);

	$oAdmin_Form_Entity_Form->add($oMainTab
		->add(
			Admin_Form_Entity::factory('Div')->class('row')->add(
				Admin_Form_Entity::factory('Radiogroup')
					->radio(array(
						Core::_('Shop_Item.import_price_list_file_type1'),
						Core::_('Shop_Item.import_price_list_file_type2')
					))
					->ico(array(
						'fa-file-excel-o',
						'fa-file-code-o'
					))
					->caption(Core::_('Shop_Item.export_file_type'))
					->divAttr(array(/*'id' => 'import_types', */'class' => 'form-group col-xs-12'))
					->name('import_price_type')
					->onchange("radiogroupOnChange('{$windowId}', $(this).val(), [0,1])")
			)
		)
		->add(
			Admin_Form_Entity::factory('Div')->class('row')->add(Admin_Form_Entity::factory('File')
				->name("csv_file")
				->caption(Core::_('Shop_Item.import_price_list_file'))
				->largeImage(array('show_params' => FALSE))
				->smallImage(array('show' => FALSE))
				->divAttr(array('class' => 'form-group col-xs-12 col-sm-6')))
				->add(
					Admin_Form_Entity::factory('Input')
						->name("alternative_file_pointer")
						->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'))
						->caption(Core::_('Shop_Item.alternative_file_pointer_form_import'))
				)
		)
		->add(
			Admin_Form_Entity::factory('Div')->class('row')->add(Admin_Form_Entity::factory('Checkbox')
				->name("import_price_name_field_f")
				->caption(Core::_('Shop_Item.import_price_list_name_field_f'))
				->value(TRUE)
				->divAttr(array('class' => 'form-group col-xs-12 hidden-1')))
		)
		->add(Admin_Form_Entity::factory('Div')->class('row')
			->add(Admin_Form_Entity::factory('Radiogroup')
				->radio(array(
					Core::_('Shop_Item.import_price_list_separator1'),
					Core::_('Shop_Item.import_price_list_separator2'),
					Core::_('Shop_Item.import_price_list_separator3'),
					Core::_('Shop_Item.import_price_list_separator4')
				))
				->ico(array(
					'fa-asterisk',
					'fa-asterisk',
					'fa-asterisk',
					'fa-asterisk'
				))
				->caption(Core::_('Shop_Item.import_price_list_separator'))
				->divAttr(array('class' => 'no-padding-right form-group col-xs-10 col-sm-9 hidden-1'))
				->name('import_price_separator')
				// Разделитель ';'
				->value(1)
			)
			->add(Admin_Form_Entity::factory('Input')
				->name("import_price_separator_text")
				->caption('&nbsp;')
				->divAttr(array('class' => 'no-padding-left form-group col-xs-1 hidden-1')))
		)
		->add(Admin_Form_Entity::factory('Div')->class('row')->add(Admin_Form_Entity::factory('Radiogroup')
			->radio(array(
				Core::_('Shop_Item.import_price_list_stop1'),
				Core::_('Shop_Item.import_price_list_stop2')
			))
			->ico(array(
				'fa-quote-right',
				'fa-bolt'
			))
			->caption(Core::_('Shop_Item.import_price_list_stop'))
			->name('import_price_stop')
			->divAttr(array('class' => 'no-padding-right form-group col-xs-10 col-sm-9 hidden-1')))
			->add(Admin_Form_Entity::factory('Input')
			->name("import_price_stop_text")
			->caption('&nbsp;')
			->divAttr(array('class' => 'no-padding-left form-group col-xs-1 hidden-1')))
		)
		->add(
			Admin_Form_Entity::factory('Div')->class('row')->add(
				Admin_Form_Entity::factory('Select')
				->name("import_price_encoding")
				->options(array(
					'Windows-1251' => Core::_('Shop_Item.input_file_encoding0'),
					'UTF-8' => Core::_('Shop_Item.input_file_encoding1')
				))
				->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 hidden-1'))
				->caption(Core::_('Shop_Item.price_list_encoding'))
			)
			->add(Admin_Form_Entity::factory('Select')
			->name("shop_groups_parent_id")
			->options(array(' … ') + Shop_Item_Controller_Edit::fillShopGroup($oShop->id))
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'))
			->caption(Core::_('Shop_Item.import_price_list_parent_group'))
			->value($oShopGroup->id)))
		->add(Admin_Form_Entity::factory('Div')->class('row')->add(Admin_Form_Entity::factory('Input')
			->name("import_price_load_files_path")
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'))
			->caption(Core::_('Shop_Item.import_price_list_images_path'))))
		->add(Admin_Form_Entity::factory('Div')->class('row')->add(
			Admin_Form_Entity::factory('Radiogroup')
				->radio(array(
					1 => Core::_('Shop_Item.import_price_action_items1'),
					2 => Core::_('Shop_Item.import_price_action_items2'),
					0 => Core::_('Shop_Item.import_price_action_items0')
				))
				->ico(array(
					1 => 'fa-refresh',
					2 => 'fa-ban',
					0 => 'fa-trash',
				))
				->caption(Core::_('Shop_Item.import_price_list_action_items'))
				->name('import_price_action_items')
				->divAttr(array('class' => 'form-group col-xs-12 hidden-1'))
				->value(1)
				->onclick("if (this.value == 0) { res = confirm('" . Core::_('Shop_Item.empty_shop') . "'); if (!res) { return false; } } ")
			)
		)
		->add(Admin_Form_Entity::factory('Div')->class('row')->add(Admin_Form_Entity::factory('Checkbox')
			->name("import_price_action_delete_image")
			->caption(Core::_('Shop_Item.import_price_list_action_delete_image'))
			->divAttr(array('class' => 'form-group col-xs-12 hidden-1')))
		)
		->add(Admin_Form_Entity::factory('Div')->class('row')->add(Admin_Form_Entity::factory('Checkbox')
			->name("search_event_indexation")
			->caption(Core::_('Shop_Item.search_event_indexation_import'))
			->divAttr(array('class' => 'form-group col-xs-12 hidden-1')))
		)
		->add(
			Admin_Form_Entity::factory('Div')->class('row')->add(
				Admin_Form_Entity::factory('Input')
					->name("import_price_max_time")
					->caption(Core::_('Shop_Item.import_price_list_max_time'))
					->value($aConfig['maxTime'])
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 hidden-1'))
			)
			->add(
				Admin_Form_Entity::factory('Input')
					->name("import_price_max_count")
					->caption(Core::_('Shop_Item.import_price_list_max_count'))
					->value($aConfig['maxCount'])
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 hidden-1'))
			)
		)
	)
	;

	$sOnClick = $oAdmin_Form_Controller->getAdminSendForm('show_form');

	Core_Session::start();
	unset($_SESSION['csv_params']);
	unset($_SESSION['Shop_Item_Import_Csv_Controller']);
	Core_Session::close();
}

function showStat($Shop_Item_Import_Csv_Controller)
{
	echo Core::_('Shop_Item.count_insert_item') . ' &#151; <b>' . $Shop_Item_Import_Csv_Controller->getInsertedItemsCount() . '</b><br/>';
	echo Core::_('Shop_Item.count_update_item') . ' &#151; <b>' . $Shop_Item_Import_Csv_Controller->getUpdatedItemsCount() . '</b><br/>';
	echo Core::_('Shop_Item.create_catalog') . ' &#151; <b>' . $Shop_Item_Import_Csv_Controller->getInsertedGroupsCount() . '</b><br/>';
	echo Core::_('Shop_Item.update_catalog') . ' &#151; <b>' . $Shop_Item_Import_Csv_Controller->getUpdatedGroupsCount() . '</b><br/>';
}

if ($sOnClick)
{
	$oAdmin_Form_Entity_Form->add(
		Admin_Form_Entity::factory('Button')
			->name('show_form')
			->type('submit')
			->value(Core::_('Shop_Item.import_price_list_button_load'))
			->class('applyButton btn btn-blue')
			->onclick($sOnClick)
	)->add(
		Core::factory('Core_Html_Entity_Script')
			->type("text/javascript")
			->value("radiogroupOnChange('{$windowId}', 0, [0,1])")
	);
}

$oAdmin_Form_Entity_Form->execute();
$content = ob_get_clean();

ob_start();
$oAdmin_View
	->content($content)
	->show();

Core_Skin::instance()
	->answer()
	->ajax(Core_Array::getRequest('_', FALSE))
	//->content(iconv("UTF-8", "UTF-8//IGNORE//TRANSLIT", ob_get_clean()))
	->content(ob_get_clean())
	->title(Core::_('Shop_Item.import_price_list_link'))
	->execute();