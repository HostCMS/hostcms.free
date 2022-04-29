<?php
/**
 * SQL.
 *
 * @package HostCMS
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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
)
->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Sql.processlist'))
		->icon('fa fa-list')
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref('/admin/sql/processlist/index.php', '', NULL)
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax('/admin/sql/processlist/index.php', '', NULL)
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
	$iCountQueries = 0;

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

				$iCountQueries++;
			}
		}
		catch (Exception $e)
		{
			Core_Message::show($e->getMessage(), 'error');
		}
	}

	if ($iCountQueries == 0)
	{
		Core_Message::show(Core::_('Sql.no_duplicate'), 'info');
	}
}

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

	$sText = Core_Array::getPost('text');

	$aFile = Core_Array::getFiles('file');
	/*!is_null($aFile) && $aFile['size'] > 0
		&& $sText = Core_File::read($aFile['tmp_name']);*/

	$iCountQueries = 0;

	$bExecuted = FALSE;

	
	if (!is_null($aFile) && $aFile['size'] > 0)
	{
		$startTime = Core::getmicrotime();

		Core_Log::instance()->clear()
			->status(Core_Log::$MESSAGE)
			->write('Sql Query From File');

		$iCountQueries = Sql_Controller::instance()->executeByFile($aFile['tmp_name']);

		$bExecuted = TRUE;

		$fTime = Core::getmicrotime() - $startTime;

		$bExecuted = TRUE;
	}
	elseif (!is_null($sText))
	{
		$iLen = strlen(trim($sText));
		if ($iLen)
		{
			$startTime = Core::getmicrotime();

			Core_Log::instance()->clear()
				->status(Core_Log::$MESSAGE)
				->write('Sql Query: ' . substr($sText, 0, 1000));

			$iCountQueries = Sql_Controller::instance()->executeByString($sText);

			$bExecuted = TRUE;

			$fTime = Core::getmicrotime() - $startTime;
		}
		else
		{
			Core_Message::show(Core::_('Sql.error_message'), 'error');
		}
	}

	if ($bExecuted)
	{
		$iAffectedRows = Core_DataBase::instance()->getAffectedRows();

		$iColumnCount = Core_DataBase::instance()->getColumnCount();

		$iCountQueries == 1
			? Core_Message::show(Core::_('Sql.success_message_with_affected', $iCountQueries, $iAffectedRows))
			: Core_Message::show(Core::_('Sql.success_message', $iCountQueries));

		// It was Select Query
		if ($iColumnCount)
		{
			$iLimit = 30;

			if ($iAffectedRows && $iCountQueries == 1)
			{
				$oTable = Core_Html_Entity::factory('Table')
					->class('admin-table table table-bordered table-hover table-striped sql-table')
					// Top title
					->add($oTitleTr = Core_Html_Entity::factory('Tr'));

				$iLine = 0;

				$oDiv = Core_Html_Entity::factory('Div')
					->style('height: 200px; resize: vertical; overflow: auto');

				$oDiv->add($oTable);

				do {
					$row = Core_DataBase::instance()->asAssoc()->current();

					if ($iLine == 0 && is_array($row) && count($row))
					{
						foreach ($row as $key => $value)
						{
							$oTitleTr->add(
								Core_Html_Entity::factory('Th')
									->value(htmlspecialchars($key))
							);
						}
					}

					$oTr = Core_Html_Entity::factory('Tr');

					if (is_array($row) && count($row))
					{
						foreach ($row as $value)
						{
							is_null($value) && $value = 'NULL';

							$oTr->add(
								Core_Html_Entity::factory('Td')
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

				Core_Html_Entity::factory('P')
					->value(Core::_('Sql.rows_count', $iAffectedRows, $iLine, $fTime))
					->execute();
			}
		}
	}
}
catch (Exception $e)
{
	Core_Message::show($e->getMessage(), 'error');
}

Core_Message::show(Core::_('sql.warning'), 'warning');

$oTextarea_Sql = Admin_Form_Entity::factory('Textarea')
	->name('text')
	->caption(Core::_('sql.text'))
	->rows(25)
	->divAttr(array('class' => 'form-group col-xs-12'))
	->value(
		($iCountQueries == 0 || strlen($sText) < 60000)
			? $sText
			: NULL
	);

$aTmpOptions = $oTextarea_Sql->syntaxHighlighterOptions;
$aTmpOptions['mode'] = 'ace/mode/sql';

$oTextarea_Sql
	->syntaxHighlighter(defined('SYNTAX_HIGHLIGHTING') ? SYNTAX_HIGHLIGHTING : TRUE)
	->syntaxHighlighterOptions($aTmpOptions);

$oMainTab = Admin_Form_Entity::factory('Tab')->name('main');
$oMainTab
	->add(Admin_Form_Entity::factory('Div')->class('row')->add($oTextarea_Sql))
	->add(Admin_Form_Entity::factory('Div')->class('row')->add(
		Admin_Form_Entity::factory('File')
			->name('file')
			->caption(Core::_('sql.load_file'))
			->largeImage(array('show_params' => FALSE))
			->smallImage(array('show' => FALSE))
			->divAttr(array('class' => 'form-group col-xs-12'))
	));

$aTables = Core_DataBase::instance()->getTables();

$oMainTab
	->add(
		Admin_Form_Entity::factory('Script')
			->value("var langTools = ace.require('ace/ext/language_tools');
			var sqlTables = [" . implode(',', array_map(function($string) { return "'" . addslashes($string) . "'"; }, $aTables)) . "];

			// create a completer object with a required callback function:
			var sqlTablesCompleter = {
				getCompletions: function(editor, session, pos, prefix, callback) {
					callback(null, sqlTables.map(function(table) {
						return {
							value: table,
							meta: 'Table'
						};
					}));
				}
			};
			// bind to langTools
			langTools.addCompleter(sqlTablesCompleter);"
		)
	);

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