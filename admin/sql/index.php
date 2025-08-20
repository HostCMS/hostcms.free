<?php
/**
 * SQL.
 *
 * @package HostCMS
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
 */
require_once('../../bootstrap.php');

Core_Auth::authorization($sModule = 'sql');

// Текущий пользователь
$oUser = Core_Auth::getCurrentUser();

$sAdminFormAction = '/{admin}/sql/index.php';

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create();
$oAdmin_Form_Controller
	->module(Core_Module_Abstract::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title(Core::_('sql.title'));

$action = Core_Array::get(Core_Array::getPost('hostcms', array()), 'action');

if ($action == 'clear')
{
	$tab_id = Core_Array::getPost('tab_id', 0, 'int');

	if ($tab_id)
	{
		$oSql_User_Tab = $oUser->Sql_User_Tabs->getById($tab_id);

		if (!is_null($oSql_User_Tab))
		{
			$oSql_User_Tab->content = '';
			$oSql_User_Tab->save();
		}
	}
}

if ($action == 'delete')
{
	$tab_id = Core_Array::getGet('tabid', 0, 'int');

	if ($tab_id)
	{
		$oSql_User_Tab = $oUser->Sql_User_Tabs->getById($tab_id);

		if (!is_null($oSql_User_Tab))
		{
			$oSql_User_Tab->markDeleted();
		}
	}
}

if ($action == 'rename')
{
	$tab_id = Core_Array::getPost('tabid', 0, 'int');
	$name = Core_Array::getPost('name', '', 'strval');

	if ($tab_id && strlen($name))
	{
		$oSql_User_Tab = $oUser->Sql_User_Tabs->getById($tab_id);

		if (!is_null($oSql_User_Tab))
		{
			$oSql_User_Tab->name = $name;
			$oSql_User_Tab->save();
		}
	}
}

if (!is_null(Core_Array::getPost('add_tab')))
{
	$aJson = array(
		'id' => 0
	);

	$name = 'Новая вкладка';

	$oSql_User_Tab = Core_Entity::factory('Sql_User_Tab');
	$oSql_User_Tab->user_id = $oUser->id;
	$oSql_User_Tab->name = $name;
	$oSql_User_Tab->save();

	$aJson = array(
		'id' => $oSql_User_Tab->id,
		'name' => $oSql_User_Tab->name
	);

	Core::showJson($aJson);
}

ob_start();

$oAdmin_View = Admin_View::create();
$oAdmin_View
	->module(Core_Module_Abstract::factory($sModule))
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
					$oAdmin_Form_Controller->getAdminLoadHref('/{admin}/sql/optimize/index.php', '', NULL)
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminLoadAjax('/{admin}/sql/optimize/index.php', '', NULL)
				)
		)->add(
			Admin_Form_Entity::factory('Menu')
				->name(Core::_('Sql.repair_table'))
				->icon('fa fa-wrench')
				->href(
					$oAdmin_Form_Controller->getAdminLoadHref('/{admin}/sql/repair/index.php', '', NULL)
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminLoadAjax('/{admin}/sql/repair/index.php', '', NULL)
				)
		)
		->add(
			Admin_Form_Entity::factory('Menu')
				->name(Core::_('Sql.duplicate_indexes'))
				->icon('fa fa-key')
				->href(
					$oAdmin_Form_Controller->getAdminLoadHref('/{admin}/sql/index.php', 'duplicate', NULL)
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminLoadAjax('/{admin}/sql/index.php', 'duplicate', NULL)
				)
		)
)
->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Sql.manage'))
		->icon('fa fa-table')
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref('/{admin}/sql/table/index.php', '', NULL)
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax('/{admin}/sql/table/index.php', '', NULL)
		)
)
->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Sql.variables'))
		->icon('fa fa-list')
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref('/{admin}/sql/variable/index.php', '', NULL)
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax('/{admin}/sql/variable/index.php', '', NULL)
		)
)
->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Sql.processlist'))
		->icon('fa fa-list')
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref('/{admin}/sql/processlist/index.php', '', NULL)
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax('/{admin}/sql/processlist/index.php', '', NULL)
		)
);

$oAdmin_Form_Controller->limit = 20;

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
		$sTableName = $oCore_DataBase->quoteTableName($row->Name);

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

$bExec = $action == '' || $action == 'exec';

if ($bExec)
{
	try
	{
		// Read Only режим
		if (defined('READ_ONLY') && READ_ONLY || $oUser->read_only && !$oUser->superuser)
		{
			throw new Core_Exception(
				Core::_('User.demo_mode'), array(), 0, FALSE
			);
		}

		$oSql_User_Tab = $iAffectedRows = $oDiv = NULL;
		$iCountQueries = 0;
		$bExecuted = FALSE;

		// MySQL 5.7.8 or later and not MariaDB (max_statement_time 10.1.1+)
		$fullVersion = Core_DataBase::instance()->getVersion();
		if (strpos($fullVersion, 'MariaDB') === FALSE)
		{
			list($version) = explode('-', $fullVersion);
			version_compare($version, '5.7.8') >= 0
				&& Core_Database::instance()->query("SET SESSION `max_execution_time` = 0;");
		}

		$sText = Core_Array::getPost('text');

		$aFile = Core_Array::getFiles('file');
		/*!is_null($aFile) && $aFile['size'] > 0
			&& $sText = Core_File::read($aFile['tmp_name']);*/

		if (!is_null($aFile) && $aFile['size'] > 0)
		{
			$startTime = Core::getmicrotime();

			Core_Log::instance()->clear()
				->status(Core_Log::$MESSAGE)
				->write('Sql Query From File');

			$iCountQueries = Sql_Controller::instance()->executeByFile($aFile['tmp_name']);

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

				$tab_id = Core_Array::getPost('tab_id', 0, 'intval');

				$oSql_User_Tab = Core_Entity::factory('Sql_User_Tab')->getById($tab_id);
				if (!is_null($oSql_User_Tab))
				{
					$page = Core_Array::getPost('page');
					$limit = Core_Array::getPost('limit', 25, 'intval');
					if (!is_null($page))
					{
						$oSql_User_Tab->page = intval($page);
						$oSql_User_Tab->limit = $limit;
					}

					$oSql_User_Tab->content = $sText;
					$oSql_User_Tab->save();
				}

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

			// $iCountQueries == 1
			// 	? Core_Message::show(Core::_('Sql.success_message_with_affected', $iCountQueries, $iAffectedRows))
			// 	: Core_Message::show(Core::_('Sql.success_message', $iCountQueries));

			// It was Select Query
			if ($iColumnCount)
			{
				// $iLimit = 30;
				$iLimit = $oSql_User_Tab ? $oSql_User_Tab->limit : 25;
				$iPage = $oSql_User_Tab ? $oSql_User_Tab->page : 1;

				if ($iAffectedRows && $iCountQueries == 1)
				{
					$oTable = Core_Html_Entity::factory('Table')
						->class('admin-table table table-bordered table-hover table-striped sql-table')
						// Top title
						->add($oTitleTr = Core_Html_Entity::factory('Tr'));

					$oDiv = Core_Html_Entity::factory('Div')
						->style('height: 250px; resize: vertical; overflow: auto');

					$oDiv->add($oTable);

					// Offset
					if ($iPage > 1)
					{
						$iLine = 0;
						do {
							$row = Core_DataBase::instance()->asAssoc()->current();
							$iLine++;
						} while ($row && $iLine < ($iPage - 1) * $iLimit);
					}

					// Lines
					$iLine = 0;
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
										->value(Core_Str::cut(htmlspecialchars(
											mb_check_encoding($value, 'UTF-8')
												? $value
												: '0x' . bin2hex($value)
										), 100))
								);
							}
						}
						$oTable->add($oTr);

						$row && $iLine++;
					} while ($row && $iLine < $iLimit);

					// Bottom title
					$oTable->add($oTitleTr);

					// $oDiv->execute();

					// Core_Html_Entity::factory('P')
					// 	->value(Core::_('Sql.rows_count', $iAffectedRows, $iLine, $fTime))
					// 	->execute();
				}
			}
		}
	}
	catch (Exception $e)
	{
		Core_Message::show($e->getMessage(), 'error');
	}

	is_null($iAffectedRows)
		&& Core_Message::show(Core::_('sql.warning'), 'warning');
}

$action == 'clear' && Core_Message::show(Core::_('sql.clear_success'), 'success');

// $oMainTab = Admin_Form_Entity::factory('Tab')->name('main');

$aSql_User_Tabs = $oUser->Sql_User_Tabs->findAll(FALSE);

if (!count($aSql_User_Tabs))
{
	$oSql_User_Tab = Core_Entity::factory('Sql_User_Tab');
	$oSql_User_Tab->user_id = $oUser->id;
	$oSql_User_Tab->name = 'Основная';
	$oSql_User_Tab->save();

	$aSql_User_Tabs = array($oSql_User_Tab);
}

$tab_id = Core_Array::getPost('tab_id');

// var_dump($tab_id);

$oTabs = Admin_Form_Entity::factory('Tabs')->class('sql-user-tabs');

foreach ($aSql_User_Tabs as $key => $oSql_User_Tab)
{
	$oTab = Admin_Form_Entity::factory('Tab')
		->id('userTab_' . $oSql_User_Tab->id)
		->caption($oSql_User_Tab->name)
		->name($oSql_User_Tab->name);

	$key && $oTab->icon('fa-solid fa-xmark');

	if ($tab_id == $oSql_User_Tab->id)
	{
		$oTabs->current($key);
	}

	$oForm = Admin_Form_Entity::factory('Form');

	$oTextarea_Sql = Admin_Form_Entity::factory('Textarea')
		->name('text')
		->caption(Core::_('sql.text'))
		->rows(25)
		->divAttr(array('class' => 'form-group col-xs-12'))
		->value(
			($bExec && $iCountQueries == 0 || strlen((string) $oSql_User_Tab->content) < 60000)
				? $oSql_User_Tab->content
				: NULL
		);

	$oTextarea_Sql
		->syntaxHighlighter(defined('SYNTAX_HIGHLIGHTING') ? SYNTAX_HIGHLIGHTING : TRUE)
		->syntaxHighlighterMode('sql');

	if ($bExec && $oSql_User_Tab && $oSql_User_Tab->id == $tab_id)
	{
		$oForm
			->add(
				Core_Html_Entity::factory('Code')
					->value(
						$iCountQueries == 1 && $iColumnCount == 0
							? Core_Message::get(Core::_('Sql.success_message_with_affected', $iCountQueries, $iAffectedRows))
							: Core_Message::get(Core::_('Sql.success_message', $iCountQueries))
					)
			);

		!is_null($oDiv) && $oForm
			->add($oDiv)
			->add(
				Core_Html_Entity::factory('P')
					->value(Core::_('Sql.rows_count', $iAffectedRows, $iLine, $fTime))
			);
	}

	$oForm
		->add(Admin_Form_Entity::factory('Div')->class('row')->add($oTextarea_Sql))
		->add($oSecondRow = Admin_Form_Entity::factory('Div')->class('row')
			->add(
				Admin_Form_Entity::factory('File')
					->name('file')
					->caption(Core::_('sql.load_file'))
					->largeImage(array('show_params' => FALSE))
					->smallImage(array('show' => FALSE))
					->divAttr(array('class' => 'col-xs-12 no-margin-bottom'))
			)
		);

	if ($bExec && $oSql_User_Tab && $oSql_User_Tab->id == $tab_id)
	{
		$aPages = array(1 => 1);
		if ($oSql_User_Tab->limit > 0)
		{
			$iMaxPages = ceil($iAffectedRows / $oSql_User_Tab->limit);
			$aTmp = range(1, $iMaxPages);
			$aPages = array_combine($aTmp, $aTmp);
		}

		$oSecondRow->add(
			Admin_Form_Entity::factory('Select')
				->caption(Core::_('Sql.page'))
				->name('page')
				->options($aPages)
				->value($oSql_User_Tab->page)
				->divAttr(array('class' => 'form-group col-xs-6 col-sm-2 no-margin-bottom'))
		);

		$oSecondRow->add(
			Admin_Form_Entity::factory('Select')
				->caption(Core::_('Sql.limit'))
				->name('limit')
				->options(array(25 => 25, 50 => 50, 100 => 100, 250 => 250, 500 => 500))
				->value($oSql_User_Tab->limit)
				->divAttr(array('class' => 'form-group col-xs-6 col-sm-2 no-margin-bottom'))
		);
	}

	$aTables = Core_DataBase::instance()->getTables();

	$oTab
		->add(
			Admin_Form_Entity::factory('Script')
				->value("
					var sqlTables = [" . implode(',', array_map(function($string) { return "'" . addslashes($string) . "'"; }, $aTables)) . "];
					syntaxhighlighter.addCompleter(sqlTables);
				")
		);

	$oTab->add($oForm
		->controller($oAdmin_Form_Controller)
		->action(Admin_Form_Controller::correctBackendPath($sAdminFormAction))
		->add(
			Admin_Form_Entity::factory('Input')
				->type('hidden')
				->name('tab_id')
				->value($oSql_User_Tab->id)
		)
		->add(Admin_Form_Entity::factory('Button')
			->name('button')
			->type('submit')
			//->value(Core::_('Shop_Item.import_price_list_button_load'))
			->class('applyButton btn btn-blue margin-bottom-10')
			->onclick("res=confirm('" . Core::_('Sql.warningButton') . "'); if (res){ " . $oAdmin_Form_Controller->getAdminSendForm('exec') . " } return false;"))
		// ->execute()
		->add(Admin_Form_Entity::factory('Button')
			->name('button')
			->type('submit')
			->class('applyButton btn btn-darkorange margin-bottom-10')
			->value(Core::_('Sql.clear'))
			->onclick("res=confirm('" . Core::_('Sql.clearButton') . "'); if (res){ " . $oAdmin_Form_Controller->getAdminSendForm('clear') . " } return false;"))
	);

	$oTabs->add($oTab);
}

$oTabBase = Admin_Form_Entity::factory('Tab')
	->id('user_tab_plus')
	->icon('fa-solid fa-plus');

$oTabs
	->controller($oAdmin_Form_Controller)
	->add($oTabBase)
	->execute();

?>
<script>
$(function(){
	// $('.nav-tabs.sql-user-tabs i.fa-xmark').on('click', { elm: $('.nav-tabs.sql-user-tabs i.fa-xmark') }, $.sqlDeleteTab);
	$('.nav-tabs.sql-user-tabs i.fa-xmark').on('click', function(){
		$.sqlDeleteTab($(this));
	});

	$('.nav-tabs.sql-user-tabs li a').on('dblclick', $.sqlRenameTab);

	$('ul.sql-user-tabs a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
		var _a = $(e.target),
			_li = _a.closest('li'),
			_liPrev = $('ul.sql-user-tabs li').eq(0);
			id = _liPrev.find('a').attr('href'),
			_div = $('.tab-content.sql-user-tabs').find(id);

		if (_a.attr('href') == '#user_tab_plus')
		{
			$.ajax({
				url: '<?php echo Admin_Form_Controller::correctBackendPath("/{admin}/sql/index.php")?>',
				type: 'POST',
				dataType: 'json',
				data: { 'add_tab': 1 },
				success: function(result) {
					if (result.id)
					{
						var $cloneLi = _liPrev.clone(),
							$cloneDiv = _div.clone();

						$cloneLi.find('a')
							.attr('href', '#userTab_' + result.id)
							.html('<span class="tab-name">' + result.name + '</span><i class="fa-solid fa-xmark" onclick="$.sqlDeleteTab(this);" title=""></i>');

						$cloneDiv.attr('id', 'userTab_' + result.id);
						$cloneDiv.find('input[name = "file"]').val();

						// $cloneDiv.find('.ace_editor').remove();

						syntaxhighlighter.remove($cloneDiv);

						$cloneDiv.find('.alert').remove();
						$cloneDiv.find('table').parent('div').remove();
						$cloneDiv.find('p').remove();

						var _textarea = $cloneDiv.find('textarea'),
							_oldId = _textarea.attr('id'),
							_newId = 'textarea_new_' + result.id;

						_textarea
							.attr('id', _newId)
							.val('');

						$cloneDiv.find('[name = tab_id]').val(result.id);

						var _script = $cloneDiv.find('script'),
							_scriptText = _script.text();

						_scriptText = _scriptText.replace(_oldId, _newId);
						_script.text(_scriptText);

						$cloneLi.insertBefore(_li);
						$cloneDiv.insertBefore(_div);

						eval(_scriptText);

						$('.nav-tabs.sql-user-tabs li a[href="#userTab_' + result.id + '"]').on('dblclick', $.sqlRenameTab);
						$('.nav-tabs.sql-user-tabs a[href="#userTab_' + result.id + '"]').tab('show');
					}
				}
			});
		}
		return false;
	});
});
</script>
<?php

$content = ob_get_clean();

ob_start();
$oAdmin_View
	->content($content)
	->show();

// $oAdmin_Form_Controller->showSettings();

Core_Skin::instance()
	->answer()
	->ajax(Core_Array::getRequest('_', FALSE))
	->content(ob_get_clean())
	->title(Core::_('sql.title'))
	->module($sModule)
	->execute();