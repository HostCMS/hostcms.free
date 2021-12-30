<?php
/**
 * Trash.
 *
 * @package HostCMS
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
require_once('../../bootstrap.php');

Core_Auth::authorization($sModule = 'trash');

// Код формы
$iAdmin_Form_Id = 183;
$sAdminFormAction = '/admin/trash/index.php';

$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title(Core::_('Trash.title'))
	->pageTitle(Core::_('Trash.title'));

// Действие "Удалить"
$oAdminFormActionDelete = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('delete');

if ($oAdminFormActionDelete && $oAdmin_Form_Controller->getAction() == 'delete')
{
	$oTrash_Controller_Delete = Admin_Form_Action_Controller::factory(
		'Trash_Controller_Delete', $oAdminFormActionDelete
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oTrash_Controller_Delete);
}

if ($oAdmin_Form_Controller->getAction() == 'deleteAll')
{
	ob_start();

	$iDelay = 1;
	$iMaxTime = (!defined('DENY_INI_SET') || !DENY_INI_SET)
		? ini_get('max_execution_time')
		: 25;

	$timeout = Core::getmicrotime();

	$oAdmin_Form_Dataset = new Trash_Dataset();

	$tableOffset = Core_Array::getGet('tableOffset', 0, 'int');

	$aTables = $oAdmin_Form_Dataset
		->offset($tableOffset)
		->limit(9999)
		->fillTables()
		->getObjects();

	$iCount = 0;

	$offset = Core_Array::getGet('offset', 0, 'int');
	$limit = 100;

	foreach ($aTables as $oTrash_Entity)
	{
		do {
			$iDeleted = $oTrash_Entity->chunkDelete($offset, $limit);
			$iCount += $iDeleted;

			$offset += ($limit - $iDeleted);

			if (Core::getmicrotime() - $timeout + 3 > $iMaxTime)
			{
				break 2;
			}

		} while ($iDeleted);

		$offset = 0;
		$tableOffset++;
	}

	$bRedirect = $iCount > 0;

	if ($bRedirect)
	{
		Core_Message::show(Core::_('Trash.deleted_elements', $iCount));

		?>
		<script type="text/javascript">
		function set_location()
		{
			<?php echo $oAdmin_Form_Controller->getAdminActionLoadAjax(array(
				'path' => $oAdmin_Form_Controller->getPath(),
				'action' => 'deleteAll',
				'datasetKey' => 0,
				'datasetValue' => 0,
				'additionalParams' => 'offset=' . $offset . '&tableOffset=' . $tableOffset)
			)?>
		}
		setTimeout('set_location()', <?php echo $iDelay * 1000?>);
		</script><?php
	}
	else
	{
		$oAdmin_Form_Controller->additionalParams('');

		Core_Message::show(Core::_('Trash.deleted_complete'));

		Core_Log::instance()->clear()
			->status(Core_Log::$SUCCESS)
			->write('All items have been completely deleted from Trash');
	}

	$oAdmin_Form_Controller
		->clearChecked()
		->addMessage(ob_get_clean());
}

// Меню формы
$oAdmin_Form_Entity_Menus = Admin_Form_Entity::factory('Menus');

// Элементы меню
$oAdmin_Form_Entity_Menus->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Trash.empty_trash'))
		->icon('fa fa-trash')
		->class("btn btn-danger")
		->onclick(
			"res = confirm('" . htmlspecialchars(Core::_('Admin_Form.confirm_dialog', Core::_('Trash.empty_trash'))) . "'); if (res) { " . $oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), 'deleteAll', NULL, '') . " } return res;"
		)
);

// Добавляем все меню контроллеру
$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Menus);

// Источник данных 0
$oAdmin_Form_Dataset = new Trash_Dataset();

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset(
	$oAdmin_Form_Dataset
);

// Показ формы
$oAdmin_Form_Controller->execute();