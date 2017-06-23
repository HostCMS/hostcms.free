<?php
/**
 * Information systems.
*
* @package HostCMS
* @version 6.x
* @author Hostmake LLC
* @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
*/
require_once('../../../../bootstrap.php');

Core_Auth::authorization($sModule = 'informationsystem');

$oInformationsystem = Core_Entity::factory('Informationsystem', Core_Array::getRequest('informationsystem_id', 0));
$oInformationsystem_Dir = $oInformationsystem->Informationsystem_Dir;
$informationsystem_group_id = Core_Array::getRequest('informationsystem_group_id', 0);
$oInformationsystem_Group = Core_Entity::factory('Informationsystem_Group', $informationsystem_group_id);
$oAdmin_Form_Controller = Admin_Form_Controller::create();
$oAdmin_Form_Entity_Breadcrumbs = Admin_Form_Entity::factory('Breadcrumbs');

// Контроллер формы
$oAdmin_Form_Controller
	->module(Core_Module::factory($sModule))
	->setUp()
	->path('/admin/informationsystem/item/import/index.php')
;

ob_start();

$oAdmin_View = Admin_View::create();
$oAdmin_View
	->module(Core_Module::factory($sModule))
	->pageTitle(Core::_('Informationsystem_Item.import'));

// Первая крошка на список магазинов
$oAdmin_Form_Entity_Breadcrumbs->add(
Admin_Form_Entity::factory('Breadcrumb')
	->name(Core::_('Informationsystem.menu'))
	->href($oAdmin_Form_Controller->getAdminLoadHref(
		'/admin/informationsystem/index.php'
	))
	->onclick($oAdmin_Form_Controller->getAdminLoadAjax(
		'/admin/informationsystem/index.php'
	))
);

// Крошки по директориям магазинов
if ($oInformationsystem_Dir->id)
{
	$oInformationsystemDirBreadcrumbs = $oInformationsystem_Dir;

	$aBreadcrumbs = array();

	do
	{
		$aBreadcrumbs[] = Admin_Form_Entity::factory('Breadcrumb')
		->name($oInformationsystemDirBreadcrumbs->name)
		->href($oAdmin_Form_Controller->getAdminLoadHref(
				'/admin/informationsystem/index.php', NULL, NULL, "informationsystem_dir_id={$oInformationsystemDirBreadcrumbs->id}"
		))
		->onclick($oAdmin_Form_Controller->getAdminLoadAjax(
				'/admin/informationsystem/index.php', NULL, NULL, "informationsystem_dir_id={$oInformationsystemDirBreadcrumbs->id}"
		));
	}
	while($oInformationsystemDirBreadcrumbs = $oInformationsystemDirBreadcrumbs->getParent());

	$aBreadcrumbs = array_reverse($aBreadcrumbs);

	foreach ($aBreadcrumbs as $oBreadcrumb)
	{
		$oAdmin_Form_Entity_Breadcrumbs->add($oBreadcrumb);
	}
}

// Крошка на список товаров и групп товаров магазина
$oAdmin_Form_Entity_Breadcrumbs->add(
Admin_Form_Entity::factory('Breadcrumb')
	->name($oInformationsystem->name)
	->href($oAdmin_Form_Controller->getAdminLoadHref(
			'/admin/informationsystem/item/index.php', NULL, NULL, "informationsystem_id={$oInformationsystem->id}"
	))
	->onclick($oAdmin_Form_Controller->getAdminLoadAjax(
			'/admin/informationsystem/item/index.php', NULL, NULL, "informationsystem_id={$oInformationsystem->id}"
	))
);

// Крошки по группам товаров
if ($oInformationsystem_Group->id)
{
	$oInformationsystemGroupBreadcrumbs = $oInformationsystem_Group;

	$aBreadcrumbs = array();

	do
	{
		$aBreadcrumbs[] = Admin_Form_Entity::factory('Breadcrumb')
		->name($oInformationsystemGroupBreadcrumbs->name)
		->href($oAdmin_Form_Controller->getAdminLoadHref(
			'/admin/informationsystem/item/index.php', NULL, NULL, "informationsystem_id={$oInformationsystem->id}&informationsystem_group_id={$oInformationsystemGroupBreadcrumbs->id}"
		))
		->onclick($oAdmin_Form_Controller->getAdminLoadAjax(
			'/admin/informationsystem/item/index.php', NULL, NULL, "informationsystem_id={$oInformationsystem->id}&informationsystem_group_id={$oInformationsystemGroupBreadcrumbs->id}"
		));
	}
	while($oInformationsystemGroupBreadcrumbs = $oInformationsystemGroupBreadcrumbs->getParent());

	$aBreadcrumbs = array_reverse($aBreadcrumbs);

	foreach ($aBreadcrumbs as $oBreadcrumb)
	{
		$oAdmin_Form_Entity_Breadcrumbs->add($oBreadcrumb);
	}
}

// Крошка на текущую форму
$oAdmin_Form_Entity_Breadcrumbs->add(
Admin_Form_Entity::factory('Breadcrumb')
	->name(Core::_('Informationsystem_Item.import'))
	->href($oAdmin_Form_Controller->getAdminLoadHref(
		$oAdmin_Form_Controller->getPath(), NULL, NULL, "informationsystem_id={$oInformationsystem->id}&informationsystem_group_id={$oInformationsystem_Group->id}"
	))
	->onclick($oAdmin_Form_Controller->getAdminLoadAjax(
		$oAdmin_Form_Controller->getPath(), NULL, NULL, "informationsystem_id={$oInformationsystem->id}&informationsystem_group_id=	{$oInformationsystem_Group->id}"
	))
);

// Формируем массивы данных
$aLangConstNames = array(
	Core::_('Informationsystem_Item_Import.!download'),
	Core::_('Informationsystem_Item_Import.group_id'),
	Core::_('Informationsystem_Item_Import.group_name'),
	Core::_('Informationsystem_Item_Import.group_path'),
	Core::_('Informationsystem_Item_Import.group_sorting'),
	Core::_('Informationsystem_Item_Import.group_description'),
	Core::_('Informationsystem_Item_Import.group_seo_title'),
	Core::_('Informationsystem_Item_Import.group_seo_description'),
	Core::_('Informationsystem_Item_Import.group_seo_keywords'),
	Core::_('Informationsystem_Item_Import.group_image_large'),
	Core::_('Informationsystem_Item_Import.group_image_small'),
	Core::_('Informationsystem_Item_Import.group_guid'),
	Core::_('Informationsystem_Item_Import.parent_group_guid'),

	Core::_('Informationsystem_Item_Import.item_id'),
	Core::_('Informationsystem_Item_Import.item_name'),
	Core::_('Informationsystem_Item_Import.item_datetime'),
	Core::_('Informationsystem_Item_Import.item_description'),
	Core::_('Informationsystem_Item_Import.item_text'),
	Core::_('Informationsystem_Item_Import.item_image_large'),
	Core::_('Informationsystem_Item_Import.item_image_small'),
	Core::_('Informationsystem_Item_Import.item_tags'),
	Core::_('Informationsystem_Item_Import.item_active'),
	Core::_('Informationsystem_Item_Import.item_sorting'),
	Core::_('Informationsystem_Item_Import.item_path'),
	Core::_('Informationsystem_Item_Import.item_seo_title'),
	Core::_('Informationsystem_Item_Import.item_seo_description'),
	Core::_('Informationsystem_Item_Import.item_seo_keywords'),
	Core::_('Informationsystem_Item_Import.item_indexing'),
	Core::_('Informationsystem_Item_Import.item_end_datetime'),
	Core::_('Informationsystem_Item_Import.item_start_datetime'),
	Core::_('Informationsystem_Item_Import.item_additional_group'),
	Core::_('Informationsystem_Item_Import.item_guid'),

	Core::_('Informationsystem_Item_Import.group_active'),
	Core::_('Informationsystem_Item_Import.siteuser_id'),
);

$aColors = array(
	'#999999',
	'#E7A1B0',
	'#E7A1B0',
	'#E7A1B0',
	'#E7A1B0',
	'#E7A1B0',
	'#E7A1B0',
	'#E7A1B0',
	'#E7A1B0',
	'#E7A1B0',
	'#E7A1B0',
	'#E7A1B0',
	'#E7A1B0',

	'#92C7C7',
	'#92C7C7',
	'#92C7C7',
	'#92C7C7',
	'#92C7C7',
	'#92C7C7',
	'#92C7C7',
	'#92C7C7',
	'#92C7C7',
	'#92C7C7',
	'#92C7C7',
	'#92C7C7',
	'#92C7C7',
	'#92C7C7',
	'#92C7C7',
	'#92C7C7',
	'#92C7C7',
	'#92C7C7',
	'#92C7C7',

	'#E18B6B',
	'#E18B6B',
);

$aEntities = array(
	'',
	'informationsystem_groups_id',
	'informationsystem_groups_value',
	'informationsystem_groups_path',
	'informationsystem_groups_order',
	'informationsystem_groups_description',
	'informationsystem_groups_seo_title',
	'informationsystem_groups_seo_description',
	'informationsystem_groups_seo_keywords',
	'informationsystem_groups_image',
	'informationsystem_groups_small_image',
	'informationsystem_groups_guid',
	'informationsystem_groups_parent_guid',

	'informationsystem_items_item_id',
	'informationsystem_items_name',
	'informationsystem_items_date_time',
	'informationsystem_items_description',
	'informationsystem_items_text',
	'informationsystem_items_image',
	'informationsystem_items_small_image',
	'informationsystem_items_label',
	'informationsystem_items_is_active',
	'informationsystem_items_order',
	'informationsystem_items_path',
	'informationsystem_items_seo_title',
	'informationsystem_items_seo_description',
	'informationsystem_items_seo_keywords',
	'informationsystem_items_indexation',
	'informationsystem_items_putend_date',
	'informationsystem_items_putoff_date',
	'additional_group',
	'informationsystem_items_guid',

	'informationsystem_groups_activity',
	'site_users_id',
);

$aGroupProperties = Core_Entity::factory('Informationsystem_Group_Property_List', $oInformationsystem->id)->Properties->findAll();
foreach ($aGroupProperties as $oGroupProperty)
{
	$oPropertyDir = $oGroupProperty->Property_Dir;

	$aLangConstNames[] = $oGroupProperty->name . "&nbsp;[" . ($oPropertyDir->id ? $oPropertyDir->name : Core::_('Informationsystem_Item.root_folder')) . "]";
	$aColors[] = "#E6E6FA";
	$aEntities[] = 'prop_group-' . $oGroupProperty->id;

	if ($oGroupProperty->type == 2)
	{
		$aLangConstNames[] = Core::_('Informationsystem_Item.import_small_images') . $oGroupProperty->name . " [" . ($oPropertyDir->id ? $oPropertyDir->name : Core::_('Informationsystem_Item.root_folder')) . "]";
		$aColors[] = "#E6E6FA";
		$aEntities[] = 'propsmall-' . $oGroupProperty->id;
	}
}

$aItemProperties = Core_Entity::factory('Informationsystem_Item_Property_List', $oInformationsystem->id)->Properties->findAll();
foreach ($aItemProperties as $oItemProperty)
{
	$oPropertyDir = $oItemProperty->Property_Dir;

	$aLangConstNames[] = $oItemProperty->name . " [" . ($oPropertyDir->id ? $oPropertyDir->name : Core::_('Informationsystem_Item.root_folder')) . "]";
	$aColors[] = "#FFE4E1";
	$aEntities[] = 'prop-' . $oItemProperty->id;

	if ($oItemProperty->type == 2)
	{
		$aLangConstNames[] = Core::_('Informationsystem_Item.import_small_images') . $oItemProperty->name . " [" . ($oPropertyDir->id ? $oPropertyDir->name : Core::_('Informationsystem_Item.root_folder')) . "]";
		$aColors[] = "#FFE4E1";
		$aEntities[] = 'propsmall-' . $oItemProperty->id;
	}
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

if ($oAdmin_Form_Controller->getAction() == 'show_form')
{
	if (!$oUserCurrent->read_only)
	{
		$sFileName = isset($_FILES['csv_file']) && intval($_FILES['csv_file']['size']) > 0
			? $_FILES['csv_file']['tmp_name']
			: CMS_FOLDER . Core_Array::getPost('alternative_file_pointer');

		if (is_file($sFileName) && is_readable($sFileName))
		{
			Core_Event::notify('Informationsystem_Item_Import.oBeforeImportCSV', NULL, array($sFileName));

			// Обработка CSV-файла
			$sTmpFileName = CMS_FOLDER . TMP_DIR . 'file_'.date("U").'.csv';

			try {
				Core_File::upload($sFileName, $sTmpFileName);

				if ($fInputFile = fopen($sTmpFileName, 'rb'))
				{
					$sSeparator = Core_Array::getPost('import_separator');

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

					$sLimiter = Core_Array::getPost('import_stop');

					switch ($sLimiter)
					{
						case 0:
						default:
							$sLimiter = '"';
						break;
						case 1:
							$sLimiter = Core_Array::getPost('import_stop_text');
						break;
					}

					$sLocale = Core_Array::getPost('import_encoding');
					$oInformationsystem_Item_Import_Csv_Controller = new Informationsystem_Item_Import_Csv_Controller(
						$oInformationsystem->id,
						Core_Array::getPost('informationsystem_groups_parent_id', 0)
					);

					$oInformationsystem_Item_Import_Csv_Controller
						->encoding($sLocale)
						->separator($sSeparator)->limiter($sLimiter);

					$aCsvLine = $oInformationsystem_Item_Import_Csv_Controller->getCSVLine($fInputFile);

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
							->add(Core::factory('Core_Html_Entity_Input')->type('hidden')->name('informationsystem_group_id')->value($oInformationsystem_Group->id))
							->add(Core::factory('Core_Html_Entity_Input')->type('hidden')->name('csv_filename')->value($sTmpFileName))
							->add(Core::factory('Core_Html_Entity_Input')->type('hidden')->name('import_separator')->value($sSeparator))
							->add(Core::factory('Core_Html_Entity_Input')->type('hidden')->name('import_stop')->value($sLimiter))
							->add(Core::factory('Core_Html_Entity_Input')->type('hidden')->name('firstlineheader')->value(isset($_POST['import_name_field_f']) ? 1 : 0))
							->add(Core::factory('Core_Html_Entity_Input')->type('hidden')->name('locale')->value($sLocale))
							->add(Core::factory('Core_Html_Entity_Input')->type('hidden')->name('import_max_time')->value(Core_Array::getPost('import_max_time')))
							->add(Core::factory('Core_Html_Entity_Input')->type('hidden')->name('import_max_count')->value(Core_Array::getPost('import_max_count')))
							->add(Core::factory('Core_Html_Entity_Input')->type('hidden')->name('import_load_files_path')->value(Core_Array::getPost('import_load_files_path')))
							->add(Core::factory('Core_Html_Entity_Input')->type('hidden')->name('import_action_items')->value(Core_Array::getPost('import_action_items')))
							->add(Core::factory('Core_Html_Entity_Input')->type('hidden')->name('informationsystem_groups_parent_id')->value(Core_Array::getPost('informationsystem_groups_parent_id')))
							->add(Core::factory('Core_Html_Entity_Input')->type('hidden')->name('search_event_indexation')->value(isset($_POST['search_event_indexation']) ? 1 : 0))
							->add(Core::factory('Core_Html_Entity_Input')->type('hidden')->name('import_action_delete_image')->value(isset($_POST['import_action_delete_image']) ? 1 : 0))
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
			Core_Message::show(Core::_('Informationsystem_Item.file_does_not_specified'), "error");
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

		if (isset($_SESSION['Informationsystem_Item_Import_Csv_Controller']))
		{
			$Informationsystem_Item_Import_Csv_Controller = $_SESSION['Informationsystem_Item_Import_Csv_Controller'];
			unset($_SESSION['Informationsystem_Item_Import_Csv_Controller']);

			$iNextSeekPosition = $Informationsystem_Item_Import_Csv_Controller->seek;
		}
		else
		{
			$Informationsystem_Item_Import_Csv_Controller = new Informationsystem_Item_Import_Csv_Controller(Core_Array::getRequest('informationsystem_id', 0), Core_Array::getRequest('informationsystem_groups_parent_id', 0));

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

			$Informationsystem_Item_Import_Csv_Controller
				->file($sCsvFilename)
				->encoding(Core_Array::getPost('locale', 'UTF-8'))
				->csv_fields($aConformity)
				->time(Core_Array::getPost('import_max_time'))
				->step(Core_Array::getPost('import_max_count'))
				->separator(Core_Array::getPost('import_separator'))
				->limiter(Core_Array::getPost('import_stop'))
				->imagesPath(Core_Array::getPost('import_load_files_path'))
				->importAction(Core_Array::getPost('import_action_items'))
				->searchIndexation(Core_Array::getPost('search_event_indexation'))
				->deleteImage(Core_Array::getPost('import_action_delete_image'))
			;

			if (Core_Array::getPost('firstlineheader', 0))
			{
				$fInputFile = fopen($Informationsystem_Item_Import_Csv_Controller->file, 'rb');
				@fgetcsv($fInputFile, 0, $Informationsystem_Item_Import_Csv_Controller->separator, $Informationsystem_Item_Import_Csv_Controller->limiter);
				$iNextSeekPosition = ftell($fInputFile);
				fclose($fInputFile);
			}
		}

		$Informationsystem_Item_Import_Csv_Controller->seek = $iNextSeekPosition;

		ob_start();

		if (($iNextSeekPosition = $Informationsystem_Item_Import_Csv_Controller->import()) !== FALSE)
		{
			$Informationsystem_Item_Import_Csv_Controller->seek = $iNextSeekPosition;

			if ($Informationsystem_Item_Import_Csv_Controller->importAction == 0)
			{
				$Informationsystem_Item_Import_Csv_Controller->importAction = 1;
			}

			$_SESSION['Informationsystem_Item_Import_Csv_Controller'] = $Informationsystem_Item_Import_Csv_Controller;

			$sRedirectAction = $oAdmin_Form_Controller->getAdminLoadAjax('/admin/informationsystem/item/import/index.php', 'start_import', NULL, "informationsystem_id={$oInformationsystem->id}&informationsystem_group_id={$informationsystem_group_id}");

			showStat($Informationsystem_Item_Import_Csv_Controller);
		}
		else
		{
			$sRedirectAction = "";
			Core_Message::show(Core::_('Informationsystem_Item.msg_download_complete'));
			showStat($Informationsystem_Item_Import_Csv_Controller);
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
	$windowId = $oAdmin_Form_Controller->getWindowId();

	$oMainTab = Admin_Form_Entity::factory('Tab')->name('main');

	$aConfig = Core_Config::instance()->get('informationsystem_csv', array()) + array(
		'maxTime' => 20,
		'maxCount' => 100
	);

	$oAdmin_Form_Entity_Form->add($oMainTab
		->add(
			Admin_Form_Entity::factory('Div')->class('row')->add(Admin_Form_Entity::factory('File')
				->name("csv_file")
				->caption(Core::_('Informationsystem_Item.import_list_file'))
				->largeImage(array('show_params' => FALSE))
				->smallImage(array('show' => FALSE))
				->divAttr(array('class' => 'form-group col-xs-12 col-sm-6')))
				->add(
					Admin_Form_Entity::factory('Input')
						->name("alternative_file_pointer")
						->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'))
						->caption(Core::_('Informationsystem_Item.alternative_file_pointer_form_import'))
				)
		)
		->add(Admin_Form_Entity::factory('Div')->class('row')->add(Admin_Form_Entity::factory('Checkbox')
			->name("import_name_field_f")
			->caption(Core::_('Informationsystem_Item.import_list_name_field_f'))
			->value(TRUE)
			->divAttr(array('id' => 'import_name_field_f','class' => 'form-group col-xs-12'))))
		->add(Admin_Form_Entity::factory('Div')->class('row')
			->add(Admin_Form_Entity::factory('Radiogroup')
				->radio(array(
					Core::_('Informationsystem_Item.import_separator1'),
					Core::_('Informationsystem_Item.import_separator2'),
					Core::_('Informationsystem_Item.import_separator3'),
					Core::_('Informationsystem_Item.import_separator4')
				))
				->ico(array(
					'fa-asterisk',
					'fa-asterisk',
					'fa-asterisk',
					'fa-asterisk'
				))
				->caption(Core::_('Informationsystem_Item.import_separator'))
				->divAttr(array('class' => 'no-padding-right form-group col-xs-10 col-sm-9', 'id' => 'import_separator'))
				->name('import_separator')
				// Разделитель ';'
				->value(1))
			->add(Admin_Form_Entity::factory('Code')
				->html("<script>$(function() {
					$('#{$windowId} #import_separator').buttonset();
				});</script>"))
			->add(Admin_Form_Entity::factory('Input')
				->name("import_separator_text")
				->caption('&nbsp;')
				->divAttr(array('id' => 'import_separator_text','class' => 'no-padding-left form-group col-xs-1'))))
		->add(Admin_Form_Entity::factory('Div')->class('row')->add(Admin_Form_Entity::factory('Radiogroup')
			->radio(array(
				Core::_('Informationsystem_Item.import_stop1'),
				Core::_('Informationsystem_Item.import_stop2')
			))
			->ico(array(
				'fa-quote-right',
				'fa-bolt'
			))
			->caption(Core::_('Informationsystem_Item.import_stop'))
			->name('import_stop')
			->divAttr(array('class' => 'no-padding-right form-group col-xs-10 col-sm-9', 'id' => 'import_stop')))
			->add(Admin_Form_Entity::factory('Code')
			->html("<script>$(function() {
				$('#{$windowId} #import_stop').buttonset();
			});</script>"))
			->add(Admin_Form_Entity::factory('Input')
			->name("import_stop_text")
			->caption('&nbsp;')
			->divAttr(array('id' => 'import_stop_text','class' => 'no-padding-left form-group col-xs-1'))))
		->add(Admin_Form_Entity::factory('Div')->class('row')->add(Admin_Form_Entity::factory('Select')
			->name("import_encoding")
			->options(array(
				'Windows-1251' => Core::_('Informationsystem_Item.input_file_encoding0'),
				'UTF-8' => Core::_('Informationsystem_Item.input_file_encoding1')
			))
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-6', 'id' => 'import_encoding'))
			->caption(Core::_('Informationsystem_Item.import_encoding')))
			->add(Admin_Form_Entity::factory('Select')
			->name("informationsystem_groups_parent_id")
			->options(array(' … ') + Informationsystem_Item_Controller_Edit::fillInformationsystemGroup($oInformationsystem->id))
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'))
			->caption(Core::_('Informationsystem_Item.import_parent_group'))
			->value($oInformationsystem_Group->id)))
		->add(Admin_Form_Entity::factory('Div')->class('row')->add(Admin_Form_Entity::factory('Input')
			->name("import_load_files_path")
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'))
			->caption(Core::_('Informationsystem_Item.import_images_path'))))
		->add(Admin_Form_Entity::factory('Div')->class('row')->add(
			Admin_Form_Entity::factory('Radiogroup')
				->radio(array(
					1 => Core::_('Informationsystem_Item.import_action_items1'),
					2 => Core::_('Informationsystem_Item.import_action_items2'),
					0 => Core::_('Informationsystem_Item.import_action_items0')
				))
				->ico(array(
					1 => 'fa-refresh',
					2 => 'fa-ban',
					0 => 'fa-trash',
				))
				->caption(Core::_('Informationsystem_Item.import_action_items'))
				->name('import_action_items')
				->divAttr(array('id' => 'import_action_items','class' => 'form-group col-xs-12'))
				->value(1)
				->onclick("if (this.value == 0) { res = confirm('" . Core::_('Informationsystem_Item.empty_informationsystem') . "'); if (!res) { return false; } } ")
			)
		)
		->add(Admin_Form_Entity::factory('Div')->class('row')->add(Admin_Form_Entity::factory('Code')
			->html("<script>$(function() {
				$('#{$windowId} #import_action_items').buttonset();
			});</script>")))
		->add(Admin_Form_Entity::factory('Div')->class('row')->add(Admin_Form_Entity::factory('Checkbox')
			->name("import_action_delete_image")
			->caption(Core::_('Informationsystem_Item.import_action_delete_image'))
			->divAttr(array('id' => 'import_action_delete_image','class' => 'form-group col-xs-12'))))
		->add(Admin_Form_Entity::factory('Div')->class('row')->add(Admin_Form_Entity::factory('Checkbox')
			->name("search_event_indexation")
			->caption(Core::_('Informationsystem_Item.search_event_indexation_import'))
			->divAttr(array('id' => 'search_event_indexation','class' => 'form-group col-xs-12'))))
		->add(Admin_Form_Entity::factory('Div')->class('row')->add(Admin_Form_Entity::factory('Input')
			->name("import_max_time")
			->caption(Core::_('Informationsystem_Item.import_max_time'))
			->value($aConfig['maxTime'])
			->divAttr(array('id' => 'import_max_time', 'class' => 'form-group col-xs-12 col-sm-6')))
			->add(Admin_Form_Entity::factory('Input')
			->name("import_max_count")
			->caption(Core::_('Informationsystem_Item.import_max_count'))
			->value($aConfig['maxCount'])
			->divAttr(array('id' => 'import_max_count', 'class' => 'form-group col-xs-12 col-sm-6')))))
	;

	$sOnClick = $oAdmin_Form_Controller->getAdminSendForm('show_form');

	Core_Session::start();
	unset($_SESSION['csv_params']);
	unset($_SESSION['Informationsystem_Item_Import_Csv_Controller']);
	Core_Session::close();
}

function showStat($Informationsystem_Item_Import_Csv_Controller)
{
	echo Core::_('Informationsystem_Item.count_insert_item') . ' &#151; <b>' . $Informationsystem_Item_Import_Csv_Controller->getInsertedItemsCount() . '</b><br/>';
	echo Core::_('Informationsystem_Item.count_update_item') . ' &#151; <b>' . $Informationsystem_Item_Import_Csv_Controller->getUpdatedItemsCount() . '</b><br/>';
	echo Core::_('Informationsystem_Item.create_catalog') . ' &#151; <b>' . $Informationsystem_Item_Import_Csv_Controller->getInsertedGroupsCount() . '</b><br/>';
	echo Core::_('Informationsystem_Item.update_catalog') . ' &#151; <b>' . $Informationsystem_Item_Import_Csv_Controller->getUpdatedGroupsCount() . '</b><br/>';
}

if ($sOnClick)
{
	$oAdmin_Form_Entity_Form->add(
		Admin_Form_Entity::factory('Button')
		->name('show_form')
		->type('submit')
		->value(Core::_('Informationsystem_Item.import_button_load'))
		->class('applyButton btn btn-blue')
		->onclick($sOnClick)
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
	->title(Core::_('Informationsystem_Item.import'))
	->execute();