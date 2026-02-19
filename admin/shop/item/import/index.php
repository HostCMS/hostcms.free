<?php
/**
 * Online shop.
 *
 * @package HostCMS
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
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
	->module(Core_Module_Abstract::factory($sModule))
	->setUp()
	->path('/{admin}/shop/item/import/index.php');

ob_start();

$oAdmin_View = Admin_View::create();
$oAdmin_View
	->module(Core_Module_Abstract::factory($sModule))
	->pageTitle(Core::_('Shop_Item.import_price_list_link'))
	;

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
		->name(Core::_('Shop_Item.import_price_list_link'))
		->href($oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL, "shop_id={$oShop->id}&shop_group_id={$oShopGroup->id}"))
		->onclick($oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL, "shop_id={$oShop->id}&shop_group_id={$oShopGroup->id}"))
);

$oUserCurrent = Core_Auth::getCurrentUser();

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
	if (!$oUserCurrent->read_only && !$oUserCurrent->only_access_my_own)
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

				Core_File::write($sTmpPath, $Core_Http->getDecompressedBody());
			}
			else
			{
				$sFileName = CMS_FOLDER . $altFile;
			}
		}

		/*$sFileName = isset($_FILES['csv_file']) && intval($_FILES['csv_file']['size']) > 0
			? $_FILES['csv_file']['tmp_name']
			: CMS_FOLDER . Core_Array::getPost('alternative_file_pointer');*/

		if (Core_File::isFile($sFileName) && is_readable($sFileName))
		{
			if (Core_Array::getPost('import_price_type') == 0)
			{
				Core_Event::notify('Shop_Item_Import.oBeforeImportCSV', NULL, array($sFileName));

				// Обработка CSV-файла
				$sTmpFileName = 'import_' . time() . '.csv';
				$sTmpFileFullpath = CMS_FOLDER . TMP_DIR . $sTmpFileName;

				try {
					Core_File::upload($sFileName, $sTmpFileFullpath);

					// Delete uploaded by cURL file
					if (!is_null($sTmpPath) && Core_File::isFile($sTmpPath))
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
								$sSeparator = Core_Array::getPost('import_separator_text');
							break;
						}

						$sLimiter = Core_Array::getPost('limiter');

						switch ($sLimiter)
						{
							case 0:
							default:
								$sLimiter = '"';
							break;
							case 1:
								$sLimiter = Core_Array::getPost('limiter_text');
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
							//$iValuesCount = count($oShop_Item_Import_Csv_Controller->aCaptions);

							// Генерируем массив для выпадающего списка с цветными элементами
							$aOptions = $aAllCaptions = array();

							//for ($j = 0; $j < $iValuesCount; $j++)
							foreach ($oShop_Item_Import_Csv_Controller->aEntities as $optionValue => $aTmpOptions)
							{
								$aOptions[$optionValue] = array(
									'value' => $aTmpOptions['caption'],
									'attr' => $aTmpOptions['attr']
								);

								// spase and non-breaking space // trim non-breaking space broke utf-8!
								//$caption = ltrim($aTmpOptions['caption'], '  ');
								$sCaption = mb_strtolower(str_replace('  ', '', $aTmpOptions['caption']));

								// cut "... [47]"
								$sCaption = preg_replace('/\s*\[\d+\]/', '', $sCaption);

								$aAllCaptions[] = $sCaption;
							}

							$oMainTab = Admin_Form_Entity::factory('Tab')->name('main');

							$oMainTab->add(
								Admin_Form_Entity::factory('Div')
									->class('row')
									->add(
										Admin_Form_Entity::factory('Span')
											->value(Core::_('shop_exchange.clear_matches'))
											->divAttr(array('class' => 'col-xs-12'))
											->class('pull-right pointer underline-dashed darkgray margin-right-20')
											->onclick('$("#' . $windowId . ' select[id^=field_id_]").val("").trigger("change")')
									)
							);

							$aForbiddenFields = array(Core::_('Shop_Exchange.item_full_path'));
							$aForbiddenFields = array_map('mb_strtolower', $aForbiddenFields);

							for ($i = 0; $i < $iFieldCount; $i++)
							{
								$oCurrentRow = Admin_Form_Entity::factory('Div')
									->class('row')
									->add(Admin_Form_Entity::factory('Span')
										->value($aCsvLine[$i])
										->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'))
									);

								$aAlreadySelected = FALSE;
								$selected = NULL;

								// Генерируем выпадающий список с цветными элементами
								//for ($j = 0; $j < $iValuesCount; $j++)
								foreach ($oShop_Item_Import_Csv_Controller->aEntities as $optionValue => $aTmpOptions)
								{
									$aCsvLine[$i] = trim(mb_strtolower(str_replace('  ', '', $aCsvLine[$i])));

									//$sCaption = $oShop_Item_Import_Csv_Controller->aCaptions[$j];
									$sCaption = trim(mb_strtolower(str_replace('  ', '', $aTmpOptions['caption'])));

									// matche "47" from square brackets
									preg_match('/\[(\d+)\]/', $sCaption, $matches);
									$sId = isset($matches[1]) ? $matches[1] : NULL;

									// cut square brackets "... [47]"
									$sCaption = preg_replace('/\s*\[\d+\]/', '', $sCaption);

									if (!$aAlreadySelected && ($aCsvLine[$i] == $sCaption
										|| !is_null($sId) && $aCsvLine[$i] == $sId
										|| (strlen($sCaption) > 0 && strlen($aCsvLine[$i]) > 0
											&& (strpos($aCsvLine[$i], $sCaption) !== FALSE || strpos($sCaption, $aCsvLine[$i]) !== FALSE)
											&& !in_array($aCsvLine[$i], $aForbiddenFields)
											// Чтобы не было срабатывания "Город" -> "Городской телефон"
											// Если есть целиком подходящее поле
											&& !array_search($aCsvLine[$i], $aAllCaptions))
										)
									)
									{
										$selected = $optionValue;

										// Для исключения двойного указания selected для одного списка
										$aAlreadySelected = TRUE;
									}
									elseif (!$aAlreadySelected)
									{
										$selected = -1;
									}
								}

								$oCurrentRow
									->class('d-flex align-items-center import-row')
									->add(Core_Html_Entity::factory('I')->class('fa-solid fa-circle fa-xs'))
									->add(
										Admin_Form_Entity::factory('Select')
											->name("field{$i}")
											->options($aOptions)
											->value($selected)
											->filter(TRUE)
											->caseSensitive(FALSE)
											->divAttr(array('class' => 'form-group col-xs-12 col-sm-8'))
											->class('form-control import-select')
											->onchange('setIColor(this)')
									);

								$oMainTab->add($oCurrentRow);
							}

							$oMainTab->add(Admin_Form_Entity::factory('Div')->class('row')
								->add(Core_Html_Entity::factory('Input')->type('hidden')->name('shop_group_id')->value($oShopGroup->id))
								->add(Core_Html_Entity::factory('Input')->type('hidden')->name('csv_filename')->value($sTmpFileName))
								->add(Core_Html_Entity::factory('Input')->type('hidden')->name('import_price_separator')->value($sSeparator))
								->add(Core_Html_Entity::factory('Input')->type('hidden')->name('limiter')->value($sLimiter))
								->add(Core_Html_Entity::factory('Input')->type('hidden')->name('firstlineheader')->value(isset($_POST['firstlineheader']) ? 1 : 0))
								->add(Core_Html_Entity::factory('Input')->type('hidden')->name('locale')->value($sLocale))
								->add(Core_Html_Entity::factory('Input')->type('hidden')->name('import_price_max_time')->value(Core_Array::getPost('import_price_max_time')))
								->add(Core_Html_Entity::factory('Input')->type('hidden')->name('import_price_delay')->value(Core_Array::getPost('import_price_delay')))
								->add(Core_Html_Entity::factory('Input')->type('hidden')->name('import_entries_limit')->value(Core_Array::getPost('import_entries_limit')))
								->add(Core_Html_Entity::factory('Input')->type('hidden')->name('import_price_max_count')->value(Core_Array::getPost('import_price_max_count')))
								->add(Core_Html_Entity::factory('Input')->type('hidden')->name('import_price_load_files_path')->value(Core_Array::getPost('import_price_load_files_path')))
								->add(Core_Html_Entity::factory('Input')->type('hidden')->name('import_price_action_items')->value(Core_Array::getPost('import_price_action_items')))
								->add(Core_Html_Entity::factory('Input')->type('hidden')->name('shop_groups_parent_id')->value($shop_groups_parent_id))
								->add(Core_Html_Entity::factory('Input')->type('hidden')->name('search_event_indexation')->value(isset($_POST['search_event_indexation']) ? 1 : 0))
								->add(Core_Html_Entity::factory('Input')->type('hidden')->name('import_price_action_delete_image')->value(isset($_POST['import_price_action_delete_image']) ? 1 : 0))
								->add(Core_Html_Entity::factory('Input')->type('hidden')->name('delete_property_values')->value(isset($_POST['delete_property_values']) ? 1 : 0))
								->add(Core_Html_Entity::factory('Input')->type('hidden')->name('delete_field_values')->value(isset($_POST['delete_field_values']) ? 1 : 0))
								->add(Core_Html_Entity::factory('Input')->type('hidden')->name('delete_unsent_modifications_by_properties')->value(isset($_POST['delete_unsent_modifications_by_properties']) ? 1 : 0))
								->add(Core_Html_Entity::factory('Input')->type('hidden')->name('delete_associated_items')->value(isset($_POST['delete_associated_items']) ? 1 : 0))
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
				$sTmpFileFullpath = CMS_FOLDER . TMP_DIR . 'file_' . time() . '.cml';

				try {
					Core_File::upload($sFileName, $sTmpFileFullpath);

					Core_Session::start();

					// Reset importPosition
					$_SESSION['importPosition'] = 0;

					$oShop_Item_Import_Cml_Controller = new Shop_Item_Import_Cml_Controller($sTmpFileFullpath);
					$oShop_Item_Import_Cml_Controller->timeout = 0;
					$oShop_Item_Import_Cml_Controller->iShopId = $oShop->id;
					$oShop_Item_Import_Cml_Controller->iShopGroupId = 0;
					//$oShop_Item_Import_Cml_Controller->iShopGroupId = $shop_groups_parent_id; // CML импортирует группы на основании дерева из файла, снаружи указывать нельзя
					$oShop_Item_Import_Cml_Controller->sPicturesPath = Core_Array::getPost('import_price_load_files_path');
					$oShop_Item_Import_Cml_Controller->importAction = Core_Array::getPost('import_price_action_items');

					$fRoznPrice_name = defined('DEFAULT_CML_PRICE_NAME')
						? DEFAULT_CML_PRICE_NAME
						: Core::_('Shop_Item.retail_price');

					$oShop_Item_Import_Cml_Controller->sShopDefaultPriceName = $fRoznPrice_name;

					$aReturn = $oShop_Item_Import_Cml_Controller->import();

					Core_Message::show(Core::_('Shop_Item.msg_download_price_complete'));
					echo Core::_('Shop_Item.inserted_items', $aReturn['insertItemCount']) . '<br/>';
					echo Core::_('Shop_Item.updated_items', $aReturn['updateItemCount']) . '<br/>';
					echo Core::_('Shop_Item.created_catalogs', $aReturn['insertDirCount']) . '<br/>';
					echo Core::_('Shop_Item.updated_catalogs', $aReturn['updateDirCount']) . '<br/>';
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

		$import_price_delay = Core_Array::getRequest('import_price_delay', 0, 'int');
		$import_entries_limit = Core_Array::getRequest('import_entries_limit', 5000, 'int');

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
				if (strpos($iKey, "field") === 0)
				{
					$aConformity[intval(substr($iKey, 5))] = $sValue;
				}
			}

			$iNextSeekPosition = 0;

			$sTmpFileName = basename(Core_Array::getPost('csv_filename', '', 'str'));

			$Shop_Item_Import_Csv_Controller
				->file($sTmpFileName)
				->encoding(Core_Array::getPost('locale', 'UTF-8'))
				->csv_fields($aConformity)
				->time(Core_Array::getPost('import_price_max_time'))
				->step(Core_Array::getPost('import_price_max_count'))
				->entriesLimit($import_entries_limit > 100 ? $import_entries_limit : 100)
				->separator(Core_Array::getPost('import_price_separator'))
				->limiter(Core_Array::getPost('limiter'))
				->imagesPath(Core_Array::getPost('import_price_load_files_path'))
				->importAction(Core_Array::getPost('import_price_action_items'))
				->searchIndexation(Core_Array::getPost('search_event_indexation'))
				->deleteImage(Core_Array::getPost('import_price_action_delete_image') == 1)
				->deletePropertyValues(Core_Array::getPost('delete_property_values') == 1)
				->deleteFieldValues(Core_Array::getPost('delete_field_values') == 1)
				->deleteUnsentModificationsByProperties(Core_Array::getPost('delete_unsent_modifications_by_properties') == 1)
				->deleteAssociatedItems(Core_Array::getPost('delete_associated_items') == 1)
				->firstlineheader(Core_Array::getPost('firstlineheader', 0, 'bool'));

			/*if (Core_Array::getPost('firstlineheader', 0))
			{
				$fInputFile = fopen($Shop_Item_Import_Csv_Controller->getFilePath(), 'rb');
				@fgetcsv($fInputFile, 0, $Shop_Item_Import_Csv_Controller->separator, $Shop_Item_Import_Csv_Controller->limiter, "\\");
				$iNextSeekPosition = ftell($fInputFile);
				fclose($fInputFile);
			}*/
		}

		// Режим - импорт или проведение документов
		$mode = Core_Array::getRequest('mode', 'import');

		$Shop_Item_Import_Csv_Controller->seek = $iNextSeekPosition;

		ob_start();

		$sAdditionalParams = "shop_id={$oShop->id}&shop_group_id={$shop_group_id}&import_price_delay={$import_price_delay}&import_entries_limit={$import_entries_limit}";

		if ($mode == 'import')
		{
			// CSV - next step
			if (($iNextSeekPosition = $Shop_Item_Import_Csv_Controller->import()) !== FALSE)
			{
				$Shop_Item_Import_Csv_Controller->seek = $iNextSeekPosition;

				if ($Shop_Item_Import_Csv_Controller->importAction == 3)
				{
					$Shop_Item_Import_Csv_Controller->importAction = 1;
				}
			}
			// CSV - complete
			else
			{
				$mode = 'post';
			}

			$_SESSION['Shop_Item_Import_Csv_Controller'] = $Shop_Item_Import_Csv_Controller;

			$sRedirectAction = $oAdmin_Form_Controller->getAdminLoadAjax('/{admin}/shop/item/import/index.php', 'start_import', NULL, $sAdditionalParams . '&mode=' . $mode);

			showStat($Shop_Item_Import_Csv_Controller);
		}
		elseif ($mode == 'post')
		{
			// Post step-by-step
			if ($Shop_Item_Import_Csv_Controller->postNext())
			{
				$_SESSION['Shop_Item_Import_Csv_Controller'] = $Shop_Item_Import_Csv_Controller;

				$sRedirectAction = $oAdmin_Form_Controller->getAdminLoadAjax('/{admin}/shop/item/import/index.php', 'start_import', NULL, $sAdditionalParams . '&mode=' . $mode);

				showStat($Shop_Item_Import_Csv_Controller);
			}
			else
			{
				// Fast filter
				if ($oShop->filter)
				{
					$Shop_Filter_Group_Controller = new Shop_Filter_Group_Controller($oShop);
					$Shop_Filter_Group_Controller->rebuild();
				}

				$sRedirectAction = "";
				Core_Message::show(Core::_('Shop_Item.msg_download_price_complete'));
				showStat($Shop_Item_Import_Csv_Controller);

				$Shop_Item_Import_Csv_Controller->deleteUploadedFile();
			}
		}
		else
		{
			echo 'Unknown mode "' . htmlspecialchars($mode) . '"';
		}

		$oAdmin_Form_Entity_Form->add(
			Admin_Form_Entity::factory('Code')->html(ob_get_clean())
		);

		Core_Session::close();

		if ($sRedirectAction)
		{
			$iRedirectTime = 1000 * $import_price_delay;
			Core_Html_Entity::factory('Script')
				->type('text/javascript')
				->value('var timeout = setTimeout(function (){ ' . $sRedirectAction . '}, ' . $iRedirectTime . ');')
				->execute();

			echo Core::_('Shop_Item.continue_import',
				$oAdmin_Form_Controller->getAdminActionLoadHref($oAdmin_Form_Controller->getPath(), 'start_import', NULL, 0, 0, $sAdditionalParams),
				'clearTimeout(timeout); ' . $oAdmin_Form_Controller->getAdminActionLoadAjax($oAdmin_Form_Controller->getPath(), 'start_import', NULL, 0, 0, $sAdditionalParams)
			);
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

	$oAdmin_Form_Entity_Form->add($oMainTab);

	$aConfig = Core_Config::instance()->get('shop_csv', array()) + array(
		'maxTime' => 20,
		'maxCount' => 100,
		'entriesLimit' => 5000,
		'separator' => ';',
		'limiter' => '"'
	);

	$aSeparator = array(
		0 => ',',
		1 => ';',
		2 => "\t"
	);

	$separator_text = '';
	$separatorOptionKey = array_search($aConfig['separator'], $aSeparator, TRUE);
	if ($separatorOptionKey === FALSE)
	{
		$separatorOptionKey = 3;
		$separator_text = $aConfig['separator'];
	}

	$aLimiter = array(
		0 => '"'
	);

	$limiter_text = '';
	$limiterOptionKey = array_search($aConfig['limiter'], $aLimiter, TRUE);
	if ($limiterOptionKey === FALSE)
	{
		$limiterOptionKey = 1;
		$limiter_text = $aConfig['limiter'];
	}

	$oMainTab->add(
		Admin_Form_Entity::factory('Div')->class('row')->add(
			Admin_Form_Entity::factory('Radiogroup')
				->radio(array(
					Core::_('Shop_Item.import_price_list_file_type1'),
					Core::_('Shop_Item.import_price_list_file_type2')
				))
				->ico(array('fa-solid fa-file-csv fa-fw', 'fa-solid fa-code fa-fw'))
				->caption(Core::_('Shop_Item.export_file_type'))
				->divAttr(array('class' => 'form-group col-xs-12 rounded-radio-group'))
				->name('import_price_type')
				->onchange("radiogroupOnChange('{$windowId}', $(this).val(), [0,1])")
		)
	)
	->add(
		Admin_Form_Entity::factory('Div')->class('row')->add(
			Admin_Form_Entity::factory('File')
				->name("csv_file")
				->divAttr(array('class' => 'col-xs-12 col-sm-4 col-md-5'))
				->caption(Core::_('Shop_Item.import_price_list_file'))
				->largeImage(array('show_params' => FALSE))
				->smallImage(array('show' => FALSE))
		)
		->add(
			Admin_Form_Entity::factory('Input')
				->name("alternative_file_pointer")
				->divAttr(array('class' => 'form-group col-xs-12 col-sm-4 col-md-5'))
				->caption(Core::_('Shop_Item.alternative_file_pointer_form_import'))
		)
		->add(
			Admin_Form_Entity::factory('Select')
				->name("import_price_encoding")
				->options(array(
					'UTF-8' => Core::_('Shop_Item.input_file_encoding1'),
					'Windows-1251' => Core::_('Shop_Item.input_file_encoding0')
				))
				->divAttr(array('class' => 'form-group col-xs-12 col-sm-4 col-md-2 hidden-1'))
				->caption(Core::_('Shop_Item.price_list_encoding'))
		)
	)
	->add(
		Admin_Form_Entity::factory('Div')->class('row')->add(
			Admin_Form_Entity::factory('Checkbox')
				->name("firstlineheader")
				->caption(Core::_('Shop_Item.import_price_list_name_field_f'))
				->value(1)
				->checked(TRUE)
				->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 hidden-1'))
		)
	)
	->add(
		Admin_Form_Entity::factory('Div')->class('row')->add(
			Admin_Form_Entity::factory('Radiogroup')->radio(array(
				Core::_('Shop_Item.import_price_list_separator1'),
				Core::_('Shop_Item.import_price_list_separator2'),
				Core::_('Shop_Item.import_price_list_separator3'),
				Core::_('Shop_Item.import_price_list_separator4')
			))
			->ico(array(
				'fa-solid fa-asterisk fa-fw',
				'fa-solid fa-asterisk fa-fw',
				'fa-solid fa-asterisk fa-fw',
				'fa-solid fa-asterisk fa-fw'
			))
			->caption(Core::_('Shop_Item.import_price_list_separator'))
			->divAttr(array('class' => 'no-padding-right form-group col-xs-10 col-sm-9 rounded-radio-group hidden-1'))
			->name('import_price_separator')
			->value($separatorOptionKey)
			->add(
				Admin_Form_Entity::factory('Input')
					->name("import_separator_text")
					->value($separator_text)
					//->caption('&nbsp;')
					->size(3)
					->divAttr(array('class' => 'form-group d-inline-block margin-left-10 hidden-1'))
			)
		)
	)
	->add(
		Admin_Form_Entity::factory('Div')->class('row')->add(
			Admin_Form_Entity::factory('Radiogroup')->radio(array(
				Core::_('Shop_Item.limiter1'),
				Core::_('Shop_Item.limiter2')
			))
			->ico(array(
				'fa-solid fa-quote-right fa-fw',
				'fa-solid fa-bolt fa-fw'
			))
			->caption(Core::_('Shop_Item.limiter'))
			->name('limiter')
			->value($limiterOptionKey)
			->divAttr(array('class' => 'no-padding-right form-group col-xs-10 col-sm-9 rounded-radio-group hidden-1'))
			->add(
				Admin_Form_Entity::factory('Input')
					->name("limiter_text")
					->value($limiter_text)
					//->caption('&nbsp;')
					->size(3)
					->divAttr(array('class' => 'form-group d-inline-block margin-left-10 hidden-1'))
			)
		)
	)
	->add(
		Admin_Form_Entity::factory('Div')->class('row')
		->add(Admin_Form_Entity::factory('Select')
		->name("shop_groups_parent_id")
		->options(array(' … ') + Shop_Item_Controller_Edit::fillShopGroup($oShop->id))
		->filter(TRUE)
		->divAttr(array('class' => 'form-group col-xs-12 col-sm-12 hidden-1'))
		->caption(Core::_('Shop_Item.import_price_list_parent_group'))
		->value($oShopGroup->id))
	)
	->add(Admin_Form_Entity::factory('Div')->class('row')->add(Admin_Form_Entity::factory('Input')
		->name("import_price_load_files_path")
		->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'))
		->caption(Core::_('Shop_Item.import_price_list_images_path')))
	)
	->add(
		Admin_Form_Entity::factory('Div')->class('row')->add(
			Admin_Form_Entity::factory('Radiogroup')
			->radio(array(
				1 => Core::_('Shop_Item.import_price_action_items1'),
				2 => Core::_('Shop_Item.import_price_action_items2'),
				3 => Core::_('Shop_Item.import_price_action_items0')
			))
			->ico(array(
				1 => 'fa-solid fa-refresh fa-fw',
				2 => 'fa-solid fa-ban fa-fw',
				3 => 'fa-solid fa-trash fa-fw',
			))
			->caption(Core::_('Shop_Item.import_price_list_action_items'))
			->name('import_price_action_items')
			->divAttr(array('class' => 'form-group col-xs-12 rounded-radio-group hidden-1'))
			->value(1)
			->onclick("if (this.value == 3) { res = confirm('" . Core::_('Shop_Item.empty_shop') . "'); if (!res) { return false; } } ")
		)
	)
	->add(Admin_Form_Entity::factory('Div')->class('row')->add(Admin_Form_Entity::factory('Checkbox')
		->name("import_price_action_delete_image")
		->class('form-control colored-danger times')
		->caption(Core::_('Shop_Item.import_price_list_action_delete_image'))
		->divAttr(array('class' => 'form-group col-xs-12 hidden-1')))
	)
	->add(
		Admin_Form_Entity::factory('Div')
			->class('row')
			->add(
				Admin_Form_Entity::factory('Checkbox')
					->name("delete_property_values")
					->class('form-control colored-danger times')
					->caption(Core::_('Shop_Item.delete_property_values'))
					->divAttr(array('class' => 'form-group col-xs-12 col-md-6 hidden-1'))
					->value(1)
			)
			->add(
				Admin_Form_Entity::factory('Checkbox')
					->name("delete_field_values")
					->class('form-control colored-danger times')
					->caption(Core::_('Shop_Item.delete_field_values'))
					->divAttr(array('class' => 'form-group col-xs-12 col-md-6 hidden-1'))
					->value(1)
			)
	)
	->add(
		Admin_Form_Entity::factory('Div')
			->class('row')
			->add(
				Admin_Form_Entity::factory('Checkbox')
					->name("delete_unsent_modifications_by_properties")
					->class('form-control colored-danger times')
					->caption(Core::_('Shop_Item.delete_unsent_modifications_by_properties'))
					->divAttr(array('class' => 'form-group col-xs-12 col-md-6 hidden-1'))
			)
			->add(
				Admin_Form_Entity::factory('Checkbox')
					->name("delete_associated_items")
					->class('form-control colored-danger times')
					->caption(Core::_('Shop_Item.delete_associated_items'))
					->divAttr(array('class' => 'form-group col-xs-12 col-md-6 hidden-1'))
			)
	)
	->add(Admin_Form_Entity::factory('Div')->class('row')->add(Admin_Form_Entity::factory('Checkbox')
		->name("search_event_indexation")
		->caption(Core::_('Shop_Item.search_event_indexation_import'))
		->divAttr(array('class' => 'form-group col-xs-12 hidden-1')))
	)
	->add(
		Admin_Form_Entity::factory('Div')->class('row')->add(
			Admin_Form_Entity::factory('Input')
				->name('import_price_max_time')
				->caption(Core::_('Shop_Item.import_price_list_max_time'))
				->value($aConfig['maxTime'])
				->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 col-md-3 hidden-1'))
		)
		->add(
			Admin_Form_Entity::factory('Input')
				->name('import_price_max_count')
				->caption(Core::_('Shop_Item.import_price_list_max_count'))
				->value($aConfig['maxCount'])
				->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 col-md-3 hidden-1'))
		)
		->add(
			Admin_Form_Entity::factory('Input')
				->name('import_price_delay')
				->caption(Core::_('Shop_Item.import_price_list_delay'))
				->value(1)
				->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 col-md-3 hidden-1'))
		)->add(
			Admin_Form_Entity::factory('Input')
				->name('import_entries_limit')
				->caption(Core::_('Shop_Item.import_entries_limit'))
				->value($aConfig['entriesLimit'])
				->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 col-md-3 hidden-1'))
		)
	);

	$sOnClick = $oAdmin_Form_Controller->getAdminSendForm('show_form');

	Core_Session::start();
	unset($_SESSION['csv_params']);
	unset($_SESSION['Shop_Item_Import_Csv_Controller']);
	Core_Session::close();
}

function showStat($Shop_Item_Import_Csv_Controller)
{
	echo Core::_('Shop_Item.inserted_items', $Shop_Item_Import_Csv_Controller->getInsertedItemsCount()) . '<br/>';
	echo Core::_('Shop_Item.updated_items', $Shop_Item_Import_Csv_Controller->getUpdatedItemsCount()) . '<br/>';
	echo Core::_('Shop_Item.created_catalogs', $Shop_Item_Import_Csv_Controller->getInsertedGroupsCount()) . '<br/>';
	echo Core::_('Shop_Item.updated_catalogs', $Shop_Item_Import_Csv_Controller->getUpdatedGroupsCount()) . '<br/>';
	echo Core::_('Shop_Item.posted_documents', $Shop_Item_Import_Csv_Controller->getPosted()) . '<br/>';
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
		Core_Html_Entity::factory('Script')
			->type("text/javascript")
			->value("radiogroupOnChange('{$windowId}', 0, [0,1])")
	);
}

$language = Core_I18n::instance()->getLng();

$oAdmin_Form_Entity_Form->add(
	Core_Html_Entity::factory('Script')
		->type('text/javascript')
		->value("(function($) {
			$('select.import-select').each(function(index, obj) {
				setIColor(obj);

				$(this).select2({
					language: '{$language}',
					templateResult: function (data, container) {
						// We only really care if there is an element to pull classes from
						if (!data.element) {
							return data.text;
						}

						$(container).addClass('select2-import-select-li');

						var jElement = $(data.element),
							jWrapper = $('<span class=\'select2-import-select-option\'></span>'),
							style = jElement[0].getAttribute('style');

						jWrapper.attr('style', style);
						jWrapper.text(data.text);

						return jWrapper;
					}
				});
			})
		})(jQuery);")
);

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
	->module($sModule)
	->content(ob_get_clean())
	->title(Core::_('Shop_Item.import_price_list_link'))
	->execute();