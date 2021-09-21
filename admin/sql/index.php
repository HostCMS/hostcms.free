<?php
/**
 * SQL.
 *
 * @package HostCMS
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
require_once('../../bootstrap.php');

Core_Auth::authorization($sModule = 'sql');

$sAdminFormAction = '/admin/sql/index.php';

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create();
$oAdmin_Form_Controller
	->module(Core_Module::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title(Core::_('sql.title'));

ob_start();

$oAdmin_View = Admin_View::create();
$oAdmin_View
	->module(Core_Module::factory($sModule))
	->pageTitle(Core::_('sql.title'));

// Меню формы
$oAdmin_Form_Entity_Menus = Admin_Form_Entity::factory('Menus');

// Элементы меню
$oAdmin_Form_Entity_Menus->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Sql.database'))
		->icon('fa fa-database')
		->add(
			Admin_Form_Entity::factory('Menu')
				->name(Core::_('Sql.optimize_table'))
				->icon('fa fa-database')
				->href(
					$oAdmin_Form_Controller->getAdminLoadHref('/admin/sql/optimize/index.php', '', NULL)
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminLoadAjax('/admin/sql/optimize/index.php', '', NULL)
				)
		)->add(
			Admin_Form_Entity::factory('Menu')
				->name(Core::_('Sql.repair_table'))
				->icon('fa fa-wrench')
				->href(
					$oAdmin_Form_Controller->getAdminLoadHref('/admin/sql/repair/index.php', '', NULL)
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminLoadAjax('/admin/sql/repair/index.php', '', NULL)
				)
		)
		->add(
			Admin_Form_Entity::factory('Menu')
				->name(Core::_('Sql.duplicate_indexes'))
				->icon('fa fa-key')
				->href(
					$oAdmin_Form_Controller->getAdminLoadHref('/admin/sql/index.php', 'duplicate', NULL)
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminLoadAjax('/admin/sql/index.php', 'duplicate', NULL)
				)
		)
)
->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Sql.manage'))
		->icon('fa fa-table')
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref('/admin/sql/table/index.php', '', NULL)
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax('/admin/sql/table/index.php', '', NULL)
		)
);

// Добавляем все меню контроллеру
//$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Menus);
//$oAdmin_View->addChild($oAdmin_Form_Entity_Menus);
//ob_start();
?>
<div class="table-toolbar">
	<?php $oAdmin_Form_Entity_Menus->execute()?>
	<div class="clear"></div>
</div>
<?php

$formSettings = Core_Array::getPost('hostcms', array())
	+ array(
		'action' => NULL,
		'window' => 'id_content',
	);

if ($formSettings['action'] == 'duplicate')
{
	$iCount = 0;

	$oCore_DataBase = Core_DataBase::instance();

	$oCore_DataBase->setQueryType(9)
			->query('SHOW TABLE STATUS');

	$aRows = $oCore_DataBase->asObject()->result();

	foreach ($aRows as $row)
	{
		$sTableName = $oCore_DataBase->quoteColumnName($row->Name);

		try
		{
			// Проверка на дублирующиеся индексы
			$aTableIndexes = $oCore_DataBase
				->setQueryType(NULL)
				->query("SHOW INDEX FROM {$sTableName}")
				->asAssoc()->result();

			$aIndexes = array();
			foreach ($aTableIndexes as $aIndex)
			{
				$aIndexes[$aIndex['Key_name']][] = $aIndex['Column_name'] . ($aIndex['Sub_part'] != '' ? ' (' . $aIndex['Sub_part'] . ')' : '');
			}

			$aToDelete = array();

			while ($aIndexRow1 = array_shift($aIndexes))
			{
				foreach ($aIndexes as $aIndexKey2 => $aIndexRow2)
				{
					if ($aIndexRow1 == $aIndexRow2)
					{
						!in_array($aIndexKey2, $aToDelete) && $aToDelete[] = $aIndexKey2;
					}
				}
			}

			foreach ($aToDelete as $sIndexName)
			{
				Core_Message::show(Core::_('Sql.drop_index', $sIndexName, $row->Name), 'info');

				$oCore_DataBase->setQueryType(5)
					->query("ALTER TABLE {$sTableName} DROP INDEX " . $oCore_DataBase->quoteColumnName($sIndexName));

				$iCount++;
			}
		}
		catch (Exception $e)
		{
			Core_Message::show($e->getMessage(), 'error');
		}
	}

	if ($iCount == 0)
	{
		Core_Message::show(Core::_('Sql.no_duplicate'), 'info');
	}
}

$sText = Core_Array::getPost('text');

$iCount = 0;
try
{
	// Текущий пользователь
	$oUser = Core_Auth::getCurrentUser();

	// Read Only режим
	if (defined('READ_ONLY') && READ_ONLY || $oUser->read_only && !$oUser->superuser)
	{
		throw new Core_Exception(
			Core::_('User.demo_mode'), array(), 0, FALSE
		);
	}

	$aFile = Core_Array::getFiles('file');
	!is_null($aFile) && $aFile['size'] > 0
		&& $sText = Core_File::read($aFile['tmp_name']);

	if (!is_null($sText))
	{
		if (strlen(trim($sText)) > 0)
		{
			$startTime = Core::getmicrotime();

			Core_Log::instance()->clear()
				->status(Core_Log::$MESSAGE)
				->write('Sql Query: ' . $sText);

			$iCount = Sql_Controller::instance()->execute($sText);

			$fTime = Core::getmicrotime() - $startTime;

			$iAffectedRows = Core_DataBase::instance()->getAffectedRows();

			$iColumnCount = Core_DataBase::instance()->getColumnCount();

			$iCount
				&& Core_Message::show(Core::_('Sql.success_message', $iCount));

			// It was Select Query
			if ($iColumnCount)
			{
				$iLimit = 30;

				if ($iAffectedRows && $iCount == 1)
				{
					$oTable = Core::factory('Core_Html_Entity_Table')
						->class('admin-table table table-bordered table-hover table-striped sql-table')
						// Top title
						->add($oTitleTr = Core::factory('Core_Html_Entity_Tr'));

					$iLine = 0;

					do {
						$row = Core_DataBase::instance()->asAssoc()->current();

						if ($iLine == 0 && is_array($row) && count($row))
						{
							foreach ($row as $key => $value)
							{
								$oTitleTr->add(
									Core::factory('Core_Html_Entity_Th')
										->value(htmlspecialchars($key))
								);
							}
						}

						$oDiv = Core::factory('Core_Html_Entity_Div')
							->style('height: 200px; overflow: auto');

						$oDiv->add($oTable);

						$oTr = Core::factory('Core_Html_Entity_Tr');

						if (is_array($row) && count($row))
						{
							foreach ($row as $value)
							{
								is_null($value) && $value = 'NULL';

								$oTr->add(
									Core::factory('Core_Html_Entity_Td')
										->value(Core_Str::cut(htmlspecialchars($value), 100))
								);
							}
						}
						$oTable->add($oTr);

						$iLine++;
					} while ($iLine < $iLimit);

					// Bottom title
					$oTable->add($oTitleTr);

					$oDiv->execute();

					Core::factory('Core_Html_Entity_P')
						->value(Core::_('Sql.rows_count', $iAffectedRows, $iLine, $fTime))
						->execute();
				}
			}
		}
		else
		{
			Core_Message::show(Core::_('Sql.error_message'), 'error');
		}
	}
}
catch (Exception $e)
{
	Core_Message::show($e->getMessage(), 'error');
}

Core_Message::show(Core::_('sql.warning'));

$oMainTab = Admin_Form_Entity::factory('Tab')->name('main');
$oMainTab
	->add(Admin_Form_Entity::factory('Div')->class('row')->add(
		Admin_Form_Entity::factory('Textarea')
			->name('text')
			->caption(Core::_('sql.text'))
			->rows(15)
			->divAttr(array('class' => 'form-group col-xs-12'))
			->value(
			($iCount == 0 || mb_strlen($sText) < 10240)
				? $sText
				: NULL
			)
	))
	->add(Admin_Form_Entity::factory('Div')->class('row')->add(
		Admin_Form_Entity::factory('File')
			->name('file')
			->caption(Core::_('sql.load_file'))
			->largeImage(array('show_params' => FALSE))
			->smallImage(array('show' => FALSE))
			->divAttr(array('class' => 'form-group col-xs-12'))
	))
;

Admin_Form_Entity::factory('Form')
	->controller($oAdmin_Form_Controller)
	->action($sAdminFormAction)
	->add($oMainTab)
	->add(Admin_Form_Entity::factory('Button')
		->name('button')
		->type('submit')
		//->value(Core::_('Shop_Item.import_price_list_button_load'))
		->class('applyButton btn btn-blue')
		->onclick("res =confirm('" . Core::_('Sql.warningButton') . "'); if (res){ " . $oAdmin_Form_Controller->getAdminSendForm('exec') . " } return false;"))
	->execute();

$content = ob_get_clean();

ob_start();
$oAdmin_View
	->content($content)
	->show();

Core_Skin::instance()
	->answer()
	->ajax(Core_Array::getRequest('_', FALSE))
	->content(ob_get_clean())
	->title(Core::_('sql.title'))
	->module($sModule)
	->execute();