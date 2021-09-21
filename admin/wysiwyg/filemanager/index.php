<?php
/**
 * Wysiwyg Filemanager.
 *
 * @package HostCMS
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
require_once('../../../bootstrap.php');

Core_Auth::authorization($sModule = 'wysiwyg');

// Код формы
$iAdmin_Form_Id = 130;
$sAdminFormAction = '/admin/wysiwyg/filemanager/index.php';

$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title(Core::_('Wysiwyg_Filemanager.title'))
	->pageTitle(Core::_('Wysiwyg_Filemanager.title'));

// Корневая директория для пользователя
$oUser = Core_Auth::getCurrentUser();
$root_dir = ltrim(Core_File::pathCorrection($oUser->root_dir), DIRECTORY_SEPARATOR);

$cdir = Core_Array::getRequest('cdir', '');

Core_Session::start();

if ($cdir == '')
{
	$cdir = isset($_SESSION['filemanager_cdir'])
		? $_SESSION['filemanager_cdir']
		: $root_dir;
}

$cdir = Core_File::pathCorrection($cdir);
$cdir = substr($cdir, 0, strrpos($cdir, DIRECTORY_SEPARATOR)) . DIRECTORY_SEPARATOR;

if (strlen(Core_Array::getRequest('dir')))
{
	$cdir = Core_File::pathCorrection($cdir)
		. trim(Core_File::pathCorrection(Core_Array::getRequest('dir')), DIRECTORY_SEPARATOR)
		. DIRECTORY_SEPARATOR;
}
elseif (is_null($cdir)/* || $cdir == DIRECTORY_SEPARATOR*/) // при выборе на главной теряется слэш
{
	$cdir = $root_dir;
}

// Строка пути НЕ начинается относительно корневого пути
if (strlen($root_dir) > 0 && mb_strpos(trim($cdir, DIRECTORY_SEPARATOR), trim($root_dir, DIRECTORY_SEPARATOR)) !== 0)
{
	$bAJAX = Core_Array::getRequest('_', FALSE);

	if ($bAJAX)
	{
		$oAdmin_Answer = Core_Skin::instance()->answer();

		$oAdmin_Answer
			->ajax($bAJAX)
			->message(Core_Message::get(Core::_('Wysiwyg_Filemanager.denied_dir'), 'error'))
			->title(Core::_('Wysiwyg_Filemanager.denied_dir'))
			->execute();
		exit();
	}
	else
	{
		$cdir = $root_dir;

		$cdir = Core_File::pathCorrection($cdir);
		$cdir = substr($cdir, 0, strrpos($cdir, DIRECTORY_SEPARATOR)) . DIRECTORY_SEPARATOR;
	}
}

// Save current cdir
strlen($cdir) && $_SESSION['filemanager_cdir'] = $cdir;

$oAdmin_Form_Controller->addExternalReplace('{cdir}', rawurlencode($cdir));

$aConfig = Core_Config::instance()->get('wysiwyg_filemanager_config', array()) + array(
	'thumbnails' => TRUE,
	'parallelUploads' => 10,
	'maxFilesize' => 5
);

// Хлебные крошки
$oAdmin_Form_Entity_Breadcrumbs = Admin_Form_Entity::factory('Breadcrumbs');

$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Wysiwyg_Filemanager.root'))
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL, 'cdir=' . DIRECTORY_SEPARATOR)
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL, 'cdir=' . DIRECTORY_SEPARATOR)
	));

if ($cdir != '')
{
	$aCdir = explode(DIRECTORY_SEPARATOR, trim($cdir, DIRECTORY_SEPARATOR));

	$tmpCdir = DIRECTORY_SEPARATOR;
	foreach ($aCdir as $sCdir)
	{
		$additional_param = 'cdir=' . rawurlencode($tmpCdir) . '&dir=' . rawurlencode($sCdir);
		$oAdmin_Form_Entity_Breadcrumbs->add(
		Admin_Form_Entity::factory('Breadcrumb')
			->name($sCdir)
			->href(
				$oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL, $additional_param)
			)
			->onclick(
				$oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL, $additional_param)
		));

		$tmpCdir .= $sCdir . DIRECTORY_SEPARATOR;
	}
}
$oAdmin_Form_Controller->addEntity(
	$oAdmin_Form_Entity_Breadcrumbs
);

// Create dir and upload file
$oAdmin_Form_Entity_Code = Admin_Form_Entity::factory('Code');

$aChecked = $oAdmin_Form_Controller->getChecked();

$oMainTabs = Admin_Form_Entity::factory('Tabs')
	->controller($oAdmin_Form_Controller)
	->add(
		$oMainTab = Admin_Form_Entity::factory('Tab')->name('main')
	);

$oCore_Html_Entity_Form_File = Core::factory('Core_Html_Entity_Form')
	->action($sAdminFormAction)
	->method('post')
	->enctype('multipart/form-data')
	->class('margin-top-40 margin-bottom-20')
	// Load file
	->add($oMainTabs);
	
$windowId = $oAdmin_Form_Controller->getWindowId();	

$oMainTab
	->add(
		Core::factory('Core_Html_Entity_Div')
			->id('dropzone')
			->add(
				Core::factory('Core_Html_Entity_Div')
				->class('dz-message needsclick')
				->value('<i class="fa fa-arrow-circle-o-up"></i> ' . Core::_('Wysiwyg_Filemanager.upload_message'))
			)
	)
	->add(Admin_Form_Entity::factory('Code')->html('
		<script type="text/javascript">
			$(function() {
				$("#' . $windowId . ' #dropzone").dropzone({
					url: "/admin/wysiwyg/filemanager/index.php?hostcms[action]=uploadFile&hostcms[checked][1][0]=1&cdir=' . rawurlencode($cdir) .'",
					parallelUploads: ' . $aConfig['parallelUploads'] . ',
					maxFilesize: ' . $aConfig['maxFilesize'] . ',
					paramName: "file",
					uploadMultiple: true,
					clickable: true,
					init: function() {
						this.on("thumbnail", function(file) {
							var thumbnail = $(file.previewElement);

							thumbnail.on("click", function(){
							  window.opener.HostCMSFileManager.insertFile("' . rawurlencode(DIRECTORY_SEPARATOR . ltrim($cdir, DIRECTORY_SEPARATOR)) . '" + file.name); return false;
							});
						});
					}
				});
			});
		</script>
	'))
	->add(
		Core::factory('Core_Html_Entity_Div')
			->class('row')
			->add(
				Admin_Form_Entity::factory('Input')
					->caption(Core::_('Wysiwyg_Filemanager.fm_form_dir'))
					->name('dir_name')
					->type('text')
					->controller($oAdmin_Form_Controller)
					->divAttr(array('class' => 'form-group col-xs-4 col-sm-4 col-md-4 col-lg-4'))
			)
			->add(
				Admin_Form_Entity::factory('Input')
					->name('dir_mode')
					->type('text')
					->size(6)
					->caption(Core::_('Wysiwyg_Filemanager.chmod'))
					->value('0' . decoct(CHMOD))
					->controller($oAdmin_Form_Controller)
					->divAttr(array('class' => 'form-group col-xs-2 col-sm-2 col-md-2 col-lg-2'))
			)
			->add(
				Core::factory('Core_Html_Entity_Div')
					->class('form-group col-xs-3 col-sm-3 col-md-3 col-lg-3')
					->add(
						Admin_Form_Entity::factory('Button')
							->name('load_file')
							->class('saveButton btn btn-blue margin-top-21')
							->value(Core::_('Wysiwyg_Filemanager.fm_form_dir_button'))
							->onclick($oAdmin_Form_Controller
								->checked(array(0 => array(0)))
								->getAdminSendForm('createDirectory', NULL, 'cdir=' . rawurlencode($cdir))
							)
					)
			)
	);

// Restore checked list
$oAdmin_Form_Controller->checked($aChecked);

ob_start();
$oCore_Html_Entity_Form_File->execute();

Core::factory('Core_Html_Entity_Script')
	->value('$(window).off(\'beforeunload\');')
	->execute();

$oAdmin_Form_Controller->addEntity(
	$oAdmin_Form_Entity_Code->html(ob_get_clean())
);

// Действие редактирования
$oAdmin_Form_Action = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('edit');

if ($oAdmin_Form_Action && $oAdmin_Form_Controller->getAction() == 'edit')
{
	$oDocument_Controller_Edit = Admin_Form_Action_Controller::factory(
		'Wysiwyg_Filemanager_Controller_Edit', $oAdmin_Form_Action
	);

	$oDocument_Controller_Edit
		->addEntity($oAdmin_Form_Entity_Breadcrumbs);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oDocument_Controller_Edit);
}

// Действие "Применить"
$oAdmin_Form_Action = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('apply');

if ($oAdmin_Form_Action && $oAdmin_Form_Controller->getAction() == 'apply')
{
	$oApplyController = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Apply', $oAdmin_Form_Action
	);

	$oAdmin_Form_Controller->addAction($oApplyController);
}

// Действие создание директории
$oAdmin_Form_Action = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('createDirectory');

if ($oAdmin_Form_Action && $oAdmin_Form_Controller->getAction() == 'createDirectory')
{
	$oWysiwyg_Filemanager_Controller_Create_Directory = Admin_Form_Action_Controller::factory(
		'Wysiwyg_Filemanager_Controller_Create_Directory', $oAdmin_Form_Action
	);

	$oWysiwyg_Filemanager_Controller_Create_Directory
		->cdir($cdir)
		->name(Core_Array::getPost('dir_name'));

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oWysiwyg_Filemanager_Controller_Create_Directory);
}

// Действие загрузка файла
$oAdmin_Form_Action = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('uploadFile');

if ($oAdmin_Form_Action && $oAdmin_Form_Controller->getAction() == 'uploadFile')
{
	$oWysiwyg_Filemanager_Controller_Upload_File = Admin_Form_Action_Controller::factory(
		'Wysiwyg_Filemanager_Controller_Upload_File', $oAdmin_Form_Action
	);

	$oWysiwyg_Filemanager_Controller_Upload_File
		->cdir($cdir)
		->file(Core_Array::getFiles('file'));

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oWysiwyg_Filemanager_Controller_Upload_File);
}

$path = CMS_FOLDER . ltrim($cdir, DIRECTORY_SEPARATOR);

// Источник данных "Директории"
$oAdmin_Form_Dataset = new Wysiwyg_Filemanager_Dataset(array('dir', 'link'));
$oAdmin_Form_Dataset
	->setPath($path)
	->addExternalField('name')
	->addExternalField('datetime');
// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset(
	$oAdmin_Form_Dataset
);

$oAdmin_Form_Dataset = new Wysiwyg_Filemanager_Dataset('file');
$oAdmin_Form_Dataset
	->changeField('name', 'type', 1)
	->setPath($path)
	->addExternalField('name')
	->addExternalField('datetime');
// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset(
	$oAdmin_Form_Dataset
);

if (!$oAdmin_Form_Controller->getAjax())
{
	?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta charset="utf-8" />
<title><?php echo $oAdmin_Form_Controller->getPageTitle()?></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="apple-touch-icon" href="/modules/skin/bootstrap/ico/icon-iphone-retina.png" />
<link rel="shortcut icon" type="image/x-icon" href="/modules/skin/bootstrap/ico/favicon.ico" />
<link rel="icon" type="image/png" href="/modules/skin/bootstrap/ico/favicon.png" />
<?php Core_Skin::instance()->showHead()?>
</head>
<body class="hostcms6 hostcmsWindow">
<div id="id_content" class="fileManager">
<?php
}

//Core_Skin::instance()->answer()->openWindow(FALSE);
Core_Skin::instance()->setMode('blank');
// Показ формы
$oAdmin_Form_Controller
	->skin(FALSE)
	->execute();

if (!$oAdmin_Form_Controller->getAjax())
{
	?></div><?php
}