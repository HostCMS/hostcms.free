<?php
/**
 * Online shop.
 *
 * @package HostCMS
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2019 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
require_once('../../../../bootstrap.php');

Core_Auth::authorization($sModule = 'shop');

$shop_groups_parent_id = Core_Array::getRequest('shop_groups_parent_id', 0);
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
	}while ($oShopDirBreadcrumbs = $oShopDirBreadcrumbs->getParent());

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
		$sFileName = $sTmpPath = NULL;

		// Uploaded File
		if (isset($_FILES['csv_file']) && intval($_FILES['csv_file']['size']) > 0)
		{
			$sFileName = $_FILES['csv_file']['tmp_name'];
		}
		// External file
		else
		{
			$altFile = Core_Array::getPost('alternative_file_pointer');

			if (strpos($altFile, 'http://') === 0 || strpos($altFile, 'https://') === 0)
			{
				$Core_Http = Core_Http::instance('curl')
					->url($altFile)
					->port(80)
					->timeout(20)
					->execute();

				$aHeaders = $Core_Http->parseHeaders();
				if ($Core_Http->parseHttpStatusCode($aHeaders['status']) != 200)
				{
					Core_Message::show('Wrong status: ' . htmlspecialchars($aHeaders['status']), "error");
				}

				$sFileName = $sTmpPath = tempnam(CMS_FOLDER . TMP_DIR, 'CSV');

				Core_File::write($sTmpPath, $Core_Http->getBody());
			}
			else
			{
				$sFileName = CMS_FOLDER . $altFile;
			}
		}

		/*$sFileName = isset($_FILES['csv_file']) && intval($_FILES['csv_file']['size']) > 0
			? $_FILES['csv_file']['tmp_name']
			: CMS_FOLDER . Core_Array::getPost('alternative_file_pointer');*/

		if (is_file($sFileName) && is_readable($sFileName))
		{
			if (Core_Array::getPost('import_price_type') == 0)
			{
				Core_Event::notify('Shop_Item_Import.oBeforeImportCSV', NULL, array($sFileName));

				// Обработка CSV-файла
				$sTmpFileName = 'import_' . date('U') . '.csv';
				$sTmpFileFullpath = CMS_FOLDER . TMP_DIR . $sTmpFileName;

				try {
					Core_File::upload($sFileName, $sTmpFileFullpath);

					// Delete uploaded by cURL file
					if (!is_null($sTmpPath) && is_file($sTmpPath))
					{
						Core_File::delete($sTmpPath);
					}

					if ($fInputFile = fopen($sTmpFileFullpath, 'rb'))
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
						$oShop_Item_Import_Csv_Controller = new Shop_Item_Import_Csv_Controller($oShop->id, $shop_groups_parent_id);

						$oShop_Item_Import_Csv_Controller
							->encoding($sLocale)
							->separator($sSeparator)
							->limiter($sLimiter);

						$aCsvLine = $oShop_Item_Import_Csv_Controller->getCSVLine($fInputFile);

						$iFieldCount = is_array($aCsvLine) ? count($aCsvLine) : 0;

						fclose($fInputFile);

						if ($iFieldCount)
						{
							$iValuesCount = count($oShop_Item_Import_Csv_Controller->aCaptions);

							// Генерируем массив для выпадающего списка с цветными элементами
							$aOptions = array();
							for ($j = 0; $j < $iValuesCount; $j++)
							{
								$aOptions[$oShop_Item_Import_Csv_Controller->aEntities[$j]] = array(
									'value' => $oShop_Item_Import_Csv_Controller->aCaptions[$j],
									'attr' => array('style' => 'background-color: ' . (
										!empty($oShop_Item_Import_Csv_Controller->aColors[$j])
											? $oShop_Item_Import_Csv_Controller->aColors[$j]
											: '#000')
									)
								);
							}

							$oMainTab = Admin_Form_Entity::factory('Tab')->name('main');

							for ($i = 0; $i < $iFieldCount; $i++)
							{
								$oCurrentRow = Admin_Form_Entity::factory('Div')->class('row');

								$oCurrentRow
									->add(Admin_Form_Entity::factory('Span')
										//->caption('')
										->value($aCsvLine[$i])
										->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'))
									);

								$isset_selected = FALSE;

								// Генерируем выпадающий список с цветными элементами
								for ($j = 0; $j < $iValuesCount; $j++)
								{
									$aCsvLine[$i] = trim($aCsvLine[$i]);

									$sCaption = $oShop_Item_Import_Csv_Controller->aCaptions[$j];

									if (!$isset_selected
									&& (mb_strtolower($aCsvLine[$i]) == mb_strtolower($sCaption)
									|| (strlen($sCaption) > 0
										&& strlen($aCsvLine[$i]) > 0
										&&
										(strpos($aCsvLine[$i], $sCaption) !== FALSE
										|| strpos($sCaption, $aCsvLine[$i]) !== FALSE)
										// Чтобы не было срабатывания "Город" -> "Городской телефон"
										// Если есть целиком подходящее поле
										&& !array_search($aCsvLine[$i], $oShop_Item_Import_Csv_Controller->aCaptions))
									))
									{
										$selected = $oShop_Item_Import_Csv_Controller->aEntities[$j];

										// Для исключения двойного указания selected для одного списка
										$isset_selected = TRUE;
									}
									elseif (!$isset_selected)
									{
										$selected = -1;
									}
								}

								$oCurrentRow->add(
									Admin_Form_Entity::factory('Select')
										->name("field{$i}")
										->options($aOptions)
										->value($selected)
										->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'))
								);

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
								->add(Core::factory('Core_Html_Entity_Input')->type('hidden')->name('import_price_delay')->value(Core_Array::getPost('import_price_delay')))
								->add(Core::factory('Core_Html_Entity_Input')->type('hidden')->name('import_price_max_count')->value(Core_Array::getPost('import_price_max_count')))
								->add(Core::factory('Core_Html_Entity_Input')->type('hidden')->name('import_price_load_files_path')->value(Core_Array::getPost('import_price_load_files_path')))
								->add(Core::factory('Core_Html_Entity_Input')->type('hidden')->name('import_price_action_items')->value(Core_Array::getPost('import_price_action_items')))
								->add(Core::factory('Core_Html_Entity_Input')->type('hidden')->name('shop_groups_parent_id')->value($shop_groups_parent_id))
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
			// CML
			else
			{
				Core_Event::notify('Shop_Item_Import.oBeforeImportCML', NULL, array($sFileName));

				// Обработка CommerceML-файла
				$sTmpFileFullpath = CMS_FOLDER . TMP_DIR . 'file_'.date("U").'.cml';

				try {
					Core_File::upload($sFileName, $sTmpFileFullpath);

					Core_Session::start();

					// Reset importPosition
					$_SESSION['importPosition'] = 0;

					$oShop_Item_Import_Cml_Controller = new Shop_Item_Import_Cml_Controller($sTmpFileFullpath);
					$oShop_Item_Import_Cml_Controller->timeout = 0;
					$oShop_Item_Import_Cml_Controller->iShopId = $oShop->id;
					$oShop_Item_Import_Cml_Controller->iShopGroupId = $shop_groups_parent_id;
					$oShop_Item_Import_Cml_Controller->sPicturesPath = Core_Array::getPost('import_price_load_files_path');
					$oShop_Item_Import_Cml_Controller->importAction = Core_Array::getPost('import_price_action_items');

					$fRoznPrice_name = defined('SHOP_DEFAULT_CML_CURRENCY_NAME')
						? SHOP_DEFAULT_CML_CURRENCY_NAME
						: 'Розничная';
					$oShop_Item_Import_Cml_Controller->sShopDefaultPriceName = $fRoznPrice_name;
					$aReturn = $oShop_Item_Import_Cml_Controller->import();

					Core_Message::show(Core::_('Shop_Item.msg_download_price_complete'));
					echo Core::_('Shop_Item.count_insert_item') . ' — <b>' . $aReturn['insertItemCount'] . '</b><br/>';
					echo Core::_('Shop_Item.count_update_item') . ' — <b>' . $aReturn['updateItemCount'] . '</b><br/>';
					echo Core::_('Shop_Item.create_catalog') . ' — <b>' . $aReturn['insertDirCount'] . '</b><br/>';
					echo Core::_('Shop_Item.update_catalog') . ' — <b>' . $aReturn['updateDirCount'] . '</b><br/>';


				} catch (Exception $exc) {
					Core_Message::show($exc->getMessage(), "error");
				}

				Core_File::delete($sTmpFileFullpath);

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

		$import_price_delay = intval(Core_Array::getRequest('import_price_delay'));

		if (isset($_SESSION['Shop_Item_Import_Csv_Controller']))
		{
			$Shop_Item_Import_Csv_Controller = $_SESSION['Shop_Item_Import_Csv_Controller'];
			unset($_SESSION['Shop_Item_Import_Csv_Controller']);

			$iNextSeekPosition = $Shop_Item_Import_Csv_Controller->seek;
		}
		else
		{
			$Shop_Item_Import_Csv_Controller = new Shop_Item_Import_Csv_Controller($oShop->id, $shop_groups_parent_id);

			$aConformity = array();

			foreach ($_POST as $iKey => $sValue)
			{
				if (mb_strpos($iKey, "field") === 0)
				{
					$aConformity[] = $sValue;
				}
			}

			$iNextSeekPosition = 0;

			$sTmpFileName = basename(Core_Array::getPost('csv_filename'));

			$Shop_Item_Import_Csv_Controller
				->file($sTmpFileName)
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
				$fInputFile = fopen(CMS_FOLDER . TMP_DIR . $Shop_Item_Import_Csv_Controller->file, 'rb');
				@fgetcsv($fInputFile, 0, $Shop_Item_Import_Csv_Controller->separator, $Shop_Item_Import_Csv_Controller->limiter);
				$iNextSeekPosition = ftell($fInputFile);
				fclose($fInputFile);
			}
		}

		$Shop_Item_Import_Csv_Controller->seek = $iNextSeekPosition;

		ob_start();

		// CSV - next step
		if (($iNextSeekPosition = $Shop_Item_Import_Csv_Controller->import()) !== FALSE)
		{
			$Shop_Item_Import_Csv_Controller->seek = $iNextSeekPosition;

			if ($Shop_Item_Import_Csv_Controller->importAction == 3)
			{
				$Shop_Item_Import_Csv_Controller->importAction = 1;
			}

			$_SESSION['Shop_Item_Import_Csv_Controller'] = $Shop_Item_Import_Csv_Controller;

			$sRedirectAction = $oAdmin_Form_Controller->getAdminLoadAjax('/admin/shop/item/import/index.php', 'start_import', NULL, "shop_id={$oShop->id}&shop_group_id={$shop_group_id}&import_price_delay={$import_price_delay}");

			showStat($Shop_Item_Import_Csv_Controller);
		}
		// CSV - complete
		else
		{
			// Post all
			$Shop_Item_Import_Csv_Controller->postAll();

			$sRedirectAction = "";
			Core_Message::show(Core::_('Shop_Item.msg_download_price_complete'));
			showStat($Shop_Item_Import_Csv_Controller);

			$sTmpFileFullpath = CMS_FOLDER . TMP_DIR . $Shop_Item_Import_Csv_Controller->file;
			if (is_file($sTmpFileFullpath))
			{
				Core_File::delete($sTmpFileFullpath);
			}
		}

		$oAdmin_Form_Entity_Form->add(
			Admin_Form_Entity::factory('Code')->html(ob_get_clean())
		);

		Core_Session::close();

		if ($sRedirectAction)
		{
			$iRedirectTime = 1000 * $import_price_delay;
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
				->divAttr(array('class' => 'form-group col-xs-12 col-sm-2 hidden-1'))
				->caption(Core::_('Shop_Item.price_list_encoding'))
			)
			->add(Admin_Form_Entity::factory('Select')
			->name("shop_groups_parent_id")
			->options(array(' … ') + Shop_Item_Controller_Edit::fillShopGroup($oShop->id))
			->filter(TRUE)
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-10'))
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
					3 => Core::_('Shop_Item.import_price_action_items0')
				))
				->ico(array(
					1 => 'fa-refresh',
					2 => 'fa-ban',
					3 => 'fa-trash',
				))
				->caption(Core::_('Shop_Item.import_price_list_action_items'))
				->name('import_price_action_items')
				->divAttr(array('class' => 'form-group col-xs-12 hidden-1'))
				->value(1)
				->onclick("if (this.value == 3) { res = confirm('" . Core::_('Shop_Item.empty_shop') . "'); if (!res) { return false; } } ")
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
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-4 hidden-1'))
			)
			->add(
				Admin_Form_Entity::factory('Input')
					->name("import_price_max_count")
					->caption(Core::_('Shop_Item.import_price_list_max_count'))
					->value($aConfig['maxCount'])
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-4 hidden-1'))
			)
			->add(
				Admin_Form_Entity::factory('Input')
					->name("import_price_delay")
					->caption(Core::_('Shop_Item.import_price_list_delay'))
					->value(1)
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-4 hidden-1'))
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
	echo Core::_('Shop_Item.count_insert_item') . ' — <b>' . $Shop_Item_Import_Csv_Controller->getInsertedItemsCount() . '</b><br/>';
	echo Core::_('Shop_Item.count_update_item') . ' — <b>' . $Shop_Item_Import_Csv_Controller->getUpdatedItemsCount() . '</b><br/>';
	echo Core::_('Shop_Item.create_catalog') . ' — <b>' . $Shop_Item_Import_Csv_Controller->getInsertedGroupsCount() . '</b><br/>';
	echo Core::_('Shop_Item.update_catalog') . ' — <b>' . $Shop_Item_Import_Csv_Controller->getUpdatedGroupsCount() . '</b><br/>';
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
$oAdmin_Form_Entity_Form->clear();

$content = ob_get_clean();

ob_start();
$oAdmin_View
	->content($content)
	->show();

unset($content);

Core_Skin::instance()
	->answer()
	->ajax(Core_Array::getRequest('_', FALSE))
	//->content(iconv("UTF-8", "UTF-8//IGNORE//TRANSLIT", ob_get_clean()))
	->content(ob_get_clean())
	->title(Core::_('Shop_Item.import_price_list_link'))
	->execute();