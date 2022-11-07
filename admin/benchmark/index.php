<?php
/**
 * Benchmark.
 *
 * @package HostCMS
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
require_once('../../bootstrap.php');

Core_Auth::authorization($sModule = 'benchmark');

// Код формы
$iAdmin_Form_Id = 196;
$sAdminFormAction = '/admin/benchmark/index.php';

$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title(Core::_('Benchmark.title'))
	->pageTitle(Core::_('Benchmark.title'));

$sEnable = Core_Array::getGet('enable');

// Включение модуля
if (!is_null($sEnable))
{
	$oModule = Core_Entity::factory('Module')->getByPath($sEnable);

	if (!is_null($oModule) && !$oModule->active)
	{
		$oModule->changeActive();
	}
}

// Меню формы
$oAdmin_Form_Entity_Menus = Admin_Form_Entity::factory('Menus');

// Элементы меню
$oAdmin_Form_Entity_Menus->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Benchmark.menu_rate'))
		->icon('fa fa-rocket')
		->href(
			$oAdmin_Form_Controller->getAdminActionLoadHref($oAdmin_Form_Controller->getPath(), 'check', NULL, 0, 0)
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminActionLoadAjax($oAdmin_Form_Controller->getPath(), 'check', NULL, 0, 0)
		)
)->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Benchmark.menu_site_speed'))
		->icon('fa fa-tachometer')
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref('/admin/benchmark/url/index.php', NULL, NULL, '')
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax('/admin/benchmark/url/index.php', NULL, NULL, '')
		)
);

// Добавляем все меню контроллеру
$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Menus);

// Элементы строки навигации
$oAdmin_Form_Entity_Breadcrumbs = Admin_Form_Entity::factory('Breadcrumbs');

// Элементы строки навигации
$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Benchmark.title'))
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL, '')
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL, '')
	)
);

// Добавляем все хлебные крошки контроллеру
$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Breadcrumbs);

// Действие редактирования
$oAdmin_Form_Action = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('check');

if ($oAdmin_Form_Action && $oAdmin_Form_Controller->getAction() == 'check')
{
	$oBenchmark_Controller_Check = Admin_Form_Action_Controller::factory('Benchmark_Controller_Check', $oAdmin_Form_Action);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oBenchmark_Controller_Check);
}

// Источник данных 1
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('Benchmark')
);

// Ограничение по сайту
$oAdmin_Form_Dataset->addCondition(
	array('where' =>
		array('site_id', '=', CURRENT_SITE)
	)
);

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset(
	$oAdmin_Form_Dataset
);

function benchmarkShow($oAdmin_Form_Controller)
{
	ob_start();

	$oBenchmark = Core_Entity::factory('Site', CURRENT_SITE)->Benchmarks->getLast(FALSE);

	if ($oBenchmark)
	{
		// Total
		$iBenchmark = $oBenchmark->getBenchmark();

		$aColors = array('gray', 'danger', 'orange', 'warning', 'success');
		$sColor = $aColors[ceil($iBenchmark / 25)];
		?>
		<div class="row">
			<div class="col-xs-12 col-sm-6 col-md-4">
				<div class="databox radius-bordered databox-shadowed databox-graded databox-vertical">
					<div class="databox-top no-padding ">
						<div class="databox-row">
							<div class="databox-cell cell-12 text-align-center bg-<?php echo $sColor?>">
								<span class="databox-number benchmark-databox-number"><?php echo $iBenchmark?> / 100</span>
								<span class="databox-text"><?php echo Core::_('Benchmark.menu')?></span>
							</div>
						</div>
					</div>
					<div class="databox-bottom">
						<span class="databox-text"><?php echo Core::_('Benchmark.benchmark')?></span>
						<div class="progress progress-sm">
							<div class="progress-bar progress-bar-<?php echo $sColor?>" role="progressbar" aria-valuenow="<?php echo $iBenchmark?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $iBenchmark?>%">
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-xs-12 col-sm-6 col-md-4">
				<div class="databox radius-bordered databox-shadowed databox-vertical">
					<div class="databox-top <?php echo $oBenchmark->mysql_write < $oBenchmark->etalon_mysql_write ? 'bg-orange' : 'bg-palegreen'?> no-padding">
						<div class="databox-row row-2"></div>
						<div class="databox-row row-10">
							<div class="databox-sparkline benchmark-databox-sparkline">
								<span><?php echo Core::_('Benchmark.bd_write')?></span>
							</div>
						</div>
					</div>
					<div class="databox-bottom no-padding bg-white">
						<div class="databox-row">
							<div class="databox-cell cell-6 text-align-center bordered-right bordered-platinum">
								<span class="databox-number lightcarbon benchmark-databox"><?php echo $oBenchmark->mysql_write?></span>
								<span class="databox-text sonic-silver no-margin"><?php echo Core::_('Benchmark.server')?></span>
							</div>
							<div class="databox-cell cell-6 text-align-center">
								<span class="databox-number lightcarbon benchmark-databox"><?php echo $oBenchmark->etalon_mysql_write?></span>
								<span class="databox-text sonic-silver no-margin"><?php echo Core::_('Benchmark.etalon')?></span>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-xs-12 col-sm-6 col-md-4">
				<div class="databox radius-bordered databox-shadowed databox-vertical">
					<div class="databox-top <?php echo $oBenchmark->mysql_read < $oBenchmark->etalon_mysql_read ? 'bg-orange' : 'bg-palegreen'?> no-padding">
						<div class="databox-row row-2"></div>
						<div class="databox-row row-10">
							<div class="databox-sparkline benchmark-databox-sparkline">
								<span><?php echo Core::_('Benchmark.bd_read')?></span>
							</div>
						</div>
					</div>
					<div class="databox-bottom no-padding bg-white">
						<div class="databox-row">
							<div class="databox-cell cell-6 text-align-center bordered-right bordered-platinum">
								<span class="databox-number lightcarbon benchmark-databox"><?php echo $oBenchmark->mysql_read?></span>
								<span class="databox-text sonic-silver no-margin"><?php echo Core::_('Benchmark.server')?></span>
							</div>
							<div class="databox-cell cell-6 text-align-center">
								<span class="databox-number lightcarbon benchmark-databox"><?php echo $oBenchmark->etalon_mysql_read?></span>
								<span class="databox-text sonic-silver no-margin"><?php echo Core::_('Benchmark.etalon')?></span>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-xs-12 col-sm-6 col-md-4">
				<div class="databox radius-bordered databox-shadowed databox-vertical">
					<div class="databox-top <?php echo $oBenchmark->mysql_update < $oBenchmark->etalon_mysql_update ? 'bg-orange' : 'bg-palegreen'?> no-padding">
						<div class="databox-row row-2"></div>
						<div class="databox-row row-10">
							<div class="databox-sparkline benchmark-databox-sparkline">
									<span><?php echo Core::_('Benchmark.bd_change')?></span>
							</div>
						</div>
					</div>
					<div class="databox-bottom no-padding bg-white">
						<div class="databox-row">
							<div class="databox-cell cell-6 text-align-center bordered-right bordered-platinum">
								<span class="databox-number lightcarbon benchmark-databox"><?php echo $oBenchmark->mysql_update?></span>
								<span class="databox-text sonic-silver no-margin"><?php echo Core::_('Benchmark.server')?></span>
							</div>
							<div class="databox-cell cell-6 text-align-center">
								<span class="databox-number lightcarbon benchmark-databox"><?php echo $oBenchmark->etalon_mysql_update?></span>
								<span class="databox-text sonic-silver no-margin"><?php echo Core::_('Benchmark.etalon')?></span>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-xs-12 col-sm-6 col-md-4">
				<div class="databox radius-bordered databox-shadowed databox-vertical">
					<div class="databox-top <?php echo $oBenchmark->filesystem < $oBenchmark->etalon_filesystem ? 'bg-orange' : 'bg-palegreen'?> no-padding">
						<div class="databox-row row-2"></div>
						<div class="databox-row row-10">
							<div class="databox-sparkline benchmark-databox-sparkline">
								<span><?php echo Core::_('Benchmark.filesystem')?></span>
							</div>
						</div>
					</div>
					<div class="databox-bottom no-padding bg-white">
						<div class="databox-row">
							<div class="databox-cell cell-6 text-align-center bordered-right bordered-platinum">
								<span class="databox-number lightcarbon benchmark-databox"><?php echo $oBenchmark->filesystem?></span>
								<span class="databox-text sonic-silver no-margin"><?php echo Core::_('Benchmark.server')?></span>
							</div>
							<div class="databox-cell cell-6 text-align-center">
								<span class="databox-number lightcarbon benchmark-databox"><?php echo $oBenchmark->etalon_filesystem?></span>
								<span class="databox-text sonic-silver no-margin"><?php echo Core::_('Benchmark.etalon')?></span>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-xs-12 col-sm-6 col-md-4">
				<div class="databox radius-bordered databox-shadowed databox-vertical">
					<div class="databox-top <?php echo $oBenchmark->cpu_math < $oBenchmark->etalon_cpu_math ? 'bg-orange' : 'bg-palegreen'?> no-padding">
						<div class="databox-row row-2"></div>
						<div class="databox-row row-10">
							<div class="databox-sparkline benchmark-databox-sparkline">
								<span><?php echo Core::_('Benchmark.cpu_math')?></span>
							</div>
						</div>
					</div>
					<div class="databox-bottom no-padding bg-white">
						<div class="databox-row">
							<div class="databox-cell cell-6 text-align-center bordered-right bordered-platinum">
								<span class="databox-number lightcarbon benchmark-databox"><?php echo $oBenchmark->cpu_math?></span>
								<span class="databox-text sonic-silver no-margin"><?php echo Core::_('Benchmark.server')?></span>
							</div>
							<div class="databox-cell cell-6 text-align-center">
								<span class="databox-number lightcarbon benchmark-databox"><?php echo $oBenchmark->etalon_cpu_math?></span>
								<span class="databox-text sonic-silver no-margin"><?php echo Core::_('Benchmark.etalon')?></span>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-xs-12 col-sm-6 col-md-4">
				<div class="databox radius-bordered databox-shadowed databox-vertical">
					<div class="databox-top <?php echo $oBenchmark->cpu_string < $oBenchmark->etalon_cpu_string ? 'bg-orange' : 'bg-palegreen'?> no-padding">
						<div class="databox-row row-2"></div>
						<div class="databox-row row-10">
							<div class="databox-sparkline benchmark-databox-sparkline">
								<span><?php echo Core::_('Benchmark.cpu_string')?></span>
							</div>
						</div>
					</div>
					<div class="databox-bottom no-padding bg-white">
						<div class="databox-row">
							<div class="databox-cell cell-6 text-align-center bordered-right bordered-platinum">
								<span class="databox-number lightcarbon benchmark-databox"><?php echo $oBenchmark->cpu_string?></span>
								<span class="databox-text sonic-silver no-margin"><?php echo Core::_('Benchmark.server')?></span>
							</div>
							<div class="databox-cell cell-6 text-align-center">
								<span class="databox-number lightcarbon benchmark-databox"><?php echo $oBenchmark->etalon_cpu_string?></span>
								<span class="databox-text sonic-silver no-margin"><?php echo Core::_('Benchmark.etalon')?></span>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-xs-12 col-sm-6 col-md-4">
				<div class="databox radius-bordered databox-shadowed databox-vertical">
					<div class="databox-top <?php echo $oBenchmark->network < $oBenchmark->etalon_network ? 'bg-orange' : 'bg-palegreen'?> no-padding">
						<div class="databox-row row-2"></div>
						<div class="databox-row row-10">
							<div class="databox-sparkline benchmark-databox-sparkline">
								<span><?php echo Core::_('Benchmark.download_speed')?></span>
							</div>
						</div>
					</div>
					<div class="databox-bottom no-padding bg-white">
						<div class="databox-row">
							<div class="databox-cell cell-6 text-align-center bordered-right bordered-platinum">
								<span class="databox-number lightcarbon benchmark-databox"><?php echo Core::_('Benchmark.mbps', $oBenchmark->network)?></span>
								<span class="databox-text sonic-silver no-margin"><?php echo Core::_('Benchmark.server')?></span>
							</div>
							<div class="databox-cell cell-6 text-align-center">
								<span class="databox-number lightcarbon benchmark-databox"><?php echo Core::_('Benchmark.mbps', $oBenchmark->etalon_network)?></span>
								<span class="databox-text sonic-silver no-margin"><?php echo Core::_('Benchmark.etalon')?></span>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-xs-12 col-sm-6 col-md-4">
				<div class="databox radius-bordered databox-shadowed databox-vertical">
					<div class="databox-top <?php echo $oBenchmark->mail > $oBenchmark->etalon_mail ? 'bg-orange' : 'bg-palegreen'?> no-padding">
						<div class="databox-row row-2"></div>
						<div class="databox-row row-10">
							<div class="databox-sparkline benchmark-databox-sparkline">
								<span><?php echo Core::_('Benchmark.email')?></span>
							</div>
						</div>
					</div>
					<div class="databox-bottom no-padding bg-white">
						<div class="databox-row">
							<div class="databox-cell cell-6 text-align-center bordered-right bordered-platinum">
								<span class="databox-number lightcarbon benchmark-databox"><?php echo Core::_('Benchmark.email_val',$oBenchmark->mail)?></span>
								<span class="databox-text sonic-silver no-margin"><?php echo Core::_('Benchmark.server')?></span>
							</div>
							<div class="databox-cell cell-6 text-align-center">
								<span class="databox-number lightcarbon benchmark-databox"><?php echo Core::_('Benchmark.email_val',$oBenchmark->etalon_mail)?></span>
								<span class="databox-text sonic-silver no-margin"><?php echo Core::_('Benchmark.etalon')?></span>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	<?php
	}
	?>
	<h5 class="row-title before-green"><i class="fa fa-dashboard green"></i> <?php echo Core::_('Benchmark.speedUp')?></h5>
	<div class="well">
		<?php
		function showModule($oAdmin_Form_Controller, $modulePath, $integration, $name, $description)
		{
			?><div class="row margin-bottom-10">
			<div class="col-xs-6 col-sm-4 col-md-3 col-lg-4">
				<h3><?php echo $name?>:</h3>
			</div>
			<div class="col-xs-3 col-sm-3 col-md-3 col-lg-2">
				<?php
				if (Core::moduleIsActive($modulePath))
				{
					$status = TRUE;
					$alert = 'btn-success';
					$ico = 'fa fa-check';
					$caption = Core::_('Admin_Form.enabled');
				}
				elseif (Core_Array::get(Core::$config->get('core_hostcms'), 'integration', 0) > $integration)
				{
					$alert = 'btn-darkorange';
					$status = FALSE;
					$ico = 'fa fa-times';
					$caption = Core::_('Admin_Form.disabled');
				}
				else
				{
					$alert = 'btn-darkorange';
					$status = NULL;
					$ico = 'fa fa-times';
					$caption = Core::_('Admin_Form.not-installed');
				}
				?>
				<div class="btn btn-labeled <?php echo $alert?> disabled">
					<i class="btn-label <?php echo $ico?> fa-fw"></i>
						<strong><?php echo $caption?></strong>
				</div>
			</div>
			<div class="col-xs-3 col-sm-2 col-md-1 col-lg-2">
				<?php
				if (!$status)
				{
					if (is_null($status))
					{
						$sBuyLink = defined('HOSTCMS_CONTRACT_NUMBER') && HOSTCMS_CONTRACT_NUMBER
							? 'http://www.hostcms.ru/users/licence/redaction/'
								. urlencode(str_replace('/', ' ', HOSTCMS_CONTRACT_NUMBER))
								. '/'
							: 'http://www.hostcms.ru/shop/';

						// Купить
						?>
						<a class="btn btn-labeled btn-success" href="<?php echo $sBuyLink?>" target="_blank">
							<i class="btn-label fa fa-money"></i>
							<?php echo Core::_('Admin_Form.buy')?>
						</a>
						<?php
					}
					else
					{
						// Включить
						?>
						<a class="btn btn-labeled btn-success" onclick="<?php echo $oAdmin_Form_Controller->getAdminActionLoadAjax($oAdmin_Form_Controller->getPath(), '', NULL, 0, 0, 'enable=' . $modulePath)?>">
							<i class="btn-label fa fa-lightbulb-o"></i>
							<?php echo Core::_('Admin_Form.enable')?>
						</a>
						<?php
					}
				}
				?>
			</div>
			<div class="col-xs-12 col-sm-3 col-md-5 col-lg-4 small">
				<?php echo $description?>
			</div>
		</div><?php
		}

		showModule($oAdmin_Form_Controller, 'cache', 4, Core::_('Benchmark.cache'), Core::_('Benchmark.cache_description'));
		showModule($oAdmin_Form_Controller, 'compression', 2, Core::_('Benchmark.compression'), Core::_('Benchmark.compression_description'));
		?>
	</div>

	<h5 class="row-title before-info"><i class="fa fa-database info"></i> <?php echo Core::_('Benchmark.database')?></h5>

	<?php
	// Доступные хранилища
	$aAllowedEngines = array('InnoDB', 'MyISAM', 'Aria', 'Xtradb');

	// Доступные collations
	$aTmp = Core_DataBase::instance()->setQueryType(9)
		->query("SHOW COLLATION")
		->asAssoc()
		->result();

	$aCharsetByCollation = array();
	foreach ($aTmp as $aRow)
	{
		$aCharsetByCollation[$aRow['Collation']] = $aRow['Charset'];
	}

	// Конвертирование таблиц
	if ($oAdmin_Form_Controller->getAction() == 'convertTables')
	{
		$sEngine = Core_Array::getPost('engine');

		if (in_array($sEngine, $aAllowedEngines))
		{
			$sNewStorageEngine = strtolower($sEngine);

			$oCore_DataBase = Core_DataBase::instance();

			$aChanged = array();

			$aTables = Benchmark_Controller::getTables();
			foreach ($aTables as $aRow)
			{
				if (Core_Array::get($aRow, 'Comment') != 'VIEW'
					&& strlen($aRow['Engine'])
					&& strtolower($aRow['Engine']) != $sNewStorageEngine)
				{
					try {
						// Change ROW_FORMAT from FIXED to DYNAMIC
						if (isset($aRow['Create_options']) && strpos($aRow['Create_options'], '=FIXED') !== FALSE)
						{
							$oCore_DataBase
								->setQueryType(5)
								->query("ALTER TABLE " . $oCore_DataBase->quoteColumnName($aRow['Name']) . " ROW_FORMAT=DYNAMIC");
						}

						$oCore_DataBase
							->setQueryType(5)
							->query("ALTER TABLE " . $oCore_DataBase->quoteColumnName($aRow['Name']) . " ENGINE={$sEngine}");

						$aChanged[] = $aRow['Name'];
					}
					catch (Core_Exception $e)
					{
						Core_Message::show($e->getMessage(), 'error');
					}
				}
			}

			if (count($aChanged))
			{
				?>
				<div class="alert alert-info"><?php echo Core::_('Benchmark.convertedMsg', implode(', ', $aChanged));?></div>
				<?php
			}
		}
	}

	// Изменение кодировки
	if ($oAdmin_Form_Controller->getAction() == 'convertCharsets')
	{
		$sCharset = Core_Array::getPost('charset');

		$sNewCharset = strtolower($sCharset);

		$oCore_DataBase = Core_DataBase::instance();

		$aChanged = array();

		$aTables = Benchmark_Controller::getTables();
		foreach ($aTables as $aTable)
		{
			if (Core_Array::get($aTable, 'Comment') != 'VIEW'
				&& isset($aCharsetByCollation[$aTable['Collation']]))
			{
				// Tables
				if (strtolower($aCharsetByCollation[$aTable['Collation']]) != $sNewCharset)
				{
					$aCollation = explode('_', $aTable['Collation'], 2);
					$sNewCollation = $sNewCharset . '_' . $aCollation[1];

					try {
						$oCore_DataBase
							->setQueryType(5)
							->query("ALTER TABLE " . $oCore_DataBase->quoteColumnName($aTable['Name']) . " COLLATE {$sNewCollation}");

						$aChanged[$aTable['Name']] = $aTable['Name'];
					}
					catch (Core_Exception $e)
					{
						Core_Message::show($e->getMessage(), 'error');
					}
				}

				// Columns
				$aColumns = $oCore_DataBase->setQueryType(9)
					->query("SHOW FULL COLUMNS FROM " . $oCore_DataBase->quoteColumnName($aTable['Name']))
					->asAssoc()
					->result();

				$aModify = array();
				foreach ($aColumns as $aColumn)
				{
					if (!is_null($aColumn['Collation']))
					{
						if (strtolower($aCharsetByCollation[$aColumn['Collation']]) != $sNewCharset)
						{
							$aColumnCollation = explode('_', $aColumn['Collation'], 2);
							$sNewColumCollation = $sNewCharset . '_' . $aColumnCollation[1];

							$sDefault = strtoupper($aColumn['Null']) == 'YES'
								? 'NULL'
								: 'NOT NULL';

							if (!is_null($aColumn['Default']))
							{
								$sDefault .= ' DEFAULT ' . $oCore_DataBase->quote($aColumn['Default']);
							}

							$aModify[] = 'MODIFY ' . $oCore_DataBase->quoteColumnName($aColumn['Field']) . " {$aColumn['Type']} CHARACTER SET {$sNewCharset} COLLATE {$sNewColumCollation} {$sDefault}";
						}
					}
				}

				if (count($aModify))
				{
					try {
						$oCore_DataBase
							->setQueryType(5)
							->query('ALTER TABLE ' . $oCore_DataBase->quoteColumnName($aTable['Name']) . ' ' . implode(', ', $aModify));

						// У таблицы могло и не быть изменения, а полям меняли
						$aChanged[$aTable['Name']] = $aTable['Name'];
					}
					catch (Core_Exception $e)
					{
						Core_Message::show($e->getMessage(), 'error');
					}
				}

				$aConfig = Core_Config::instance()->get('core_database');
				$aConfig['default']['charset'] = $sNewCharset;
				Core_Config::instance()->set('core_database', $aConfig);
			}
		}

		if (count($aChanged))
		{
			?>
			<div class="alert alert-info"><?php echo Core::_('Benchmark.convertedMsg', implode(', ', $aChanged))?></div>
			<?php
		}
	}

	// Reload new table's statuses
	$aTables = Benchmark_Controller::getTables();

	$aTableEngines = $aTableCharsets = array();

	foreach ($aTables as $aRow)
	{
		// Engine
		if (Core_Array::get($aRow, 'Comment') != 'VIEW')
		{
			if (strlen($aRow['Engine']))
			{
				isset($aTableEngines[$aRow['Engine']])
					? $aTableEngines[$aRow['Engine']]++
					: $aTableEngines[$aRow['Engine']] = 1;
			}

			// Charset
			if (strlen($aRow['Collation']))
			{
				$sCharset = Core_Array::get($aCharsetByCollation, $aRow['Collation'], '-');

				isset($aTableCharsets[$sCharset])
					? $aTableCharsets[$sCharset]++
					: $aTableCharsets[$sCharset] = 1;
			}
		}
	}

	asort($aTableEngines);
	asort($aTableCharsets);

	$windowId = $oAdmin_Form_Controller->getWindowId();
	?>
	<div class="row">
		<div class="col-xs-12 col-md-6">
			<div class="databox databox-xxlg databox-vertical databox-shadowed bg-white radius-bordered padding-5">
				<div class="databox-top bg-white bordered-bottom-1 bordered-platinum text-align-left padding-10">
					<div class="databox-text darkgray"><?php echo Core::_('Benchmark.tableEngines')?></div>
				</div>
				<div class="databox-bottom">
					<div class="databox-row row-12">
						<div class="databox-cell cell-7 text-center  padding-5">
							<div id="dashboard-pie-chart-sources" class="chart"></div>
							<?php
							$aColors = array('#e75b8d', '#a0d468', '#ffce55', '#5db2ff', '#fb6e52');

							$aData = array();
							$i = 0;
							foreach ($aTableEngines as $sEngineName => $iCount)
							{
								$aData[] = "{
									label: \"" . htmlspecialchars($sEngineName) . "\",
									data: [[1, {$iCount}]],
									color: '" . $aColors[$i % count($aColors)] . "'
								}";
								$i++;
							}

							?>
							<script>
							$(function(){
								var aScripts = [
									'jquery.flot.js',
									'jquery.flot.time.min.js',
									'jquery.flot.categories.min.js',
									'jquery.flot.tooltip.min.js',
									'jquery.flot.crosshair.min.js',
									'jquery.flot.selection.min.js',
									'jquery.flot.pie.min.js',
									'jquery.flot.resize.js'
								];

								$.getMultiContent(aScripts, '/modules/skin/bootstrap/js/charts/flot/').done(function() {

									var data = [<?php echo implode(",\n", $aData)?>];
									var placeholder = $("#<?php echo $windowId?> #dashboard-pie-chart-sources");
									placeholder.unbind();

									$.plot(placeholder, data, {
										series: {
											pie: {
												innerRadius: 0.45,
												show: true,
												stroke: {
													width: 4
												}
											}
										},
										legend: {
											show: false
										}
									});
								});
							});
							</script>
						</div>
						<div class="databox-cell cell-5 text-center no-padding-left">
							<div class="databox-row row-2 bordered-bottom bordered-ivory padding-10">
								<span class="databox-text sonic-silver pull-left no-margin"><?php echo Core::_('Benchmark.engine')?></span>
								<span class="databox-text sonic-silver pull-right no-margin"><?php echo Core::_('Benchmark.count')?></span>
							</div>
							<?php
							$i = 0;
							$aBadges = array('badge-pink', 'badge-palegreen', 'badge-yellow', 'badge-blue', 'badge-orange');
							foreach ($aTableEngines as $sEngineName => $iCount)
							{
								?><div class="databox-row row-2 bordered-bottom bordered-ivory padding-10">
									<span class="badge <?php echo $aBadges[$i % count($aBadges)]?> badge-empty pull-left margin-5"></span>
									<span class="databox-text darkgray pull-left no-margin hidden-xs"><?php echo htmlspecialchars($sEngineName)?></span>
									<span class="databox-text darkgray pull-right no-margin uppercase"><?php echo $iCount?></span>
								</div><?php
								$i++;
							}
							?>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="col-xs-12 col-md-6">
			<div class="well well-lg">
				<?php
				$aResult = Benchmark_Controller::getStorageEngines();

				$aAvailabledEngines = array();

				foreach ($aResult as $row)
				{
					if (in_array($row, $aAllowedEngines))
					{
						$aAvailabledEngines[] = $row;
					}
				}

				if (count($aTableEngines) > 1)
				{
					Core_Message::show(Core::_('Benchmark.severalEnginesMsg'), 'error');
				}
				?>
				<div id="horizontal-form">
					<form class="form-horizontal" role="form" action="/admin/benchmark/index.php" method="post">
						<div class="form-title">
							<?php echo Core::_('Benchmark.changeStorageEnginesTitle')?>
						</div>
						<div class="form-group">
							<label for="inputEmail3" class="col-sm-2 control-label no-padding-right">
								<?php echo Core::_('Benchmark.engine')?>
							</label>
							<div class="col-sm-10">
								<?php
								Core_Html_Entity::factory('Select')
									->options(
										array_combine($aAvailabledEngines, $aAvailabledEngines)
									)
									->class('form-control')
									->value('InnoDB')
									->name('engine')
									->execute();
								?>
								<p class="help-block"><?php echo Core::_('Benchmark.changeMsg')?></p>
							</div>
						</div>
						<div class="form-group">
							<div class="col-sm-offset-2 col-sm-10">
								<?php
								Admin_Form_Entity::factory('Button')
									->name('process')
									->type('submit')
									->value(Core::_('Benchmark.convert'))
									->class('btn btn-default')
									->onclick(
										$oAdmin_Form_Controller->clearChecked()->getAdminSendForm(array('action' => 'convertTables', 'additionalParams' => ''))
									)
									->execute();
								?>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-xs-12 col-md-6">
			<div class="databox databox-xxlg databox-vertical databox-shadowed bg-white radius-bordered padding-5">
				<div class="databox-top bg-white bordered-bottom-1 bordered-platinum text-align-left padding-10">
					<div class="databox-text darkgray"><?php echo Core::_('Benchmark.tableCharsets')?></div>
				</div>
				<div class="databox-bottom">
					<div class="databox-row row-12">
						<div class="databox-cell cell-7 text-center  padding-5">
							<div id="dashboard-pie-chart-charsets" class="chart"></div>
							<?php
							$aColors = array('#fb6e52', '#6f85bf', '#53a93f', '#11a9cc', '#981b48');

							$aData = array();
							$i = 0;
							foreach ($aTableCharsets as $sCharsetName => $iCount)
							{
								$aData[] = "{
									label: \"" . htmlspecialchars($sCharsetName) . "\",
									data: [[1, {$iCount}]],
									color: '" . $aColors[$i % count($aColors)] . "'
								}";
								$i++;
							}

							?>
							<script>
							$(function(){
								var aScripts = [
									'jquery.flot.js',
									'jquery.flot.time.min.js',
									'jquery.flot.categories.min.js',
									'jquery.flot.tooltip.min.js',
									'jquery.flot.crosshair.min.js',
									'jquery.flot.selection.min.js',
									'jquery.flot.pie.min.js',
									'jquery.flot.resize.js'
								];

								$.getMultiContent(aScripts, '/modules/skin/bootstrap/js/charts/flot/').done(function() {

									var data = [<?php echo implode(",\n", $aData)?>];
									var placeholder = $("#<?php echo $windowId?> #dashboard-pie-chart-charsets");
									placeholder.unbind();

									$.plot(placeholder, data, {
										series: {
											pie: {
												innerRadius: 0.45,
												show: true,
												stroke: {
													width: 4
												}
											}
										},
										legend: {
											show: false
										}
									});
								});
							});
							</script>
						</div>
						<div class="databox-cell cell-5 text-center no-padding-left">
							<div class="databox-row row-2 bordered-bottom bordered-ivory padding-10">
								<span class="databox-text sonic-silver pull-left no-margin"><?php echo Core::_('Benchmark.engine')?></span>
								<span class="databox-text sonic-silver pull-right no-margin"><?php echo Core::_('Benchmark.count')?></span>
							</div>
							<?php
							$i = 0;
							$aBadges = array('badge-orange', 'badge-blueberry', 'badge-success', 'badge-sky', 'badge-maroon');
							foreach ($aTableCharsets as $sCharsetName => $iCount)
							{
								?><div class="databox-row row-2 bordered-bottom bordered-ivory padding-10">
									<span class="badge <?php echo $aBadges[$i % count($aBadges)]?> badge-empty pull-left margin-5"></span>
									<span class="databox-text darkgray pull-left no-margin hidden-xs"><?php echo htmlspecialchars($sCharsetName)?></span>
									<span class="databox-text darkgray pull-right no-margin uppercase"><?php echo $iCount?></span>
								</div><?php
								$i++;
							}
							?>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="col-xs-12 col-md-6">
			<div class="well well-lg">
				<?php
				$aResult = Benchmark_Controller::getStorageCharsets();

				$aAvailabledCharsets = array();

				foreach ($aResult as $aRow)
				{
					if (strpos($aRow['Charset'], 'utf8') === 0)
					{
						$aAvailabledCharsets[] = $aRow['Charset'];
					}
				}

				if (count($aTableCharsets) > 1)
				{
					Core_Message::show(Core::_('Benchmark.severalCharsetsMsg'), 'error');
				}
				?>
				<div id="horizontal-form">
					<form class="form-horizontal" role="form" action="/admin/benchmark/index.php" method="post">
						<div class="form-title">
							<?php echo Core::_('Benchmark.changeStorageCharsetTitle')?>
						</div>
						<div class="form-group">
							<label for="inputEmail3" class="col-sm-2 control-label no-padding-right">
								<?php echo Core::_('Benchmark.engine')?>
							</label>
							<div class="col-sm-10">
								<?php
								Core_Html_Entity::factory('Select')
									->options(
										array_combine($aAvailabledCharsets, $aAvailabledCharsets)
									)
									->class('form-control')
									->value('utf8')
									->name('charset')
									->execute();
								?>
								<p class="help-block"><?php echo Core::_('Benchmark.changeMsg')?></p>
							</div>
						</div>
						<div class="form-group">
							<div class="col-sm-offset-2 col-sm-10">
								<?php
								Admin_Form_Entity::factory('Button')
									->name('process')
									->type('submit')
									->value(Core::_('Benchmark.convert'))
									->class('btn btn-default')
									->onclick(
										$oAdmin_Form_Controller->clearChecked()->getAdminSendForm(array('action' => 'convertCharsets', 'additionalParams' => ''))
									)
									->execute();
								?>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
	<?php
	$oAdmin_Form_Controller->addEntity(
		Admin_Form_Entity::factory('Code')
			->html(ob_get_clean())
	);
}

Core_Event::attach('Admin_Form_Controller.onBeforeShowContent', 'benchmarkShow');

// Показ формы
$oAdmin_Form_Controller->execute();