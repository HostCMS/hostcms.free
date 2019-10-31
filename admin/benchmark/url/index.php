<?php
/**
 * Benchmark.
 *
 * @package HostCMS
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2019 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
require_once('../../../bootstrap.php');

Core_Auth::authorization($sModule = 'benchmark');

// Код формы
$iAdmin_Form_Id = 197;
$sAdminFormAction = '/admin/benchmark/url/index.php';

$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

if (!is_null($constantName = Core_Array::getPost('constantName', NULL))
	&& !is_null($constantValue = Core_Array::getPost('constantValue', NULL))
	&& isset($_SERVER['HTTP_X_REQUESTED_WITH'])
	&& $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')
{
	$oConstant = Core_Entity::factory('Constant')->getByName($constantName);
	if (is_null($oConstant))
	{
		$oConstant = Core_Entity::factory('Constant');
		$oConstant->name = $constantName;
	}

	$oConstant->active = 1;
	$oConstant->value = intval($constantValue) ? 'true' : 'false';
	$oConstant->save();
	die();
}

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title(Core::_('Benchmark.menu_site_speed'))
	->pageTitle(Core::_('Benchmark.menu_site_speed'));

// Элементы строки навигации
$oAdmin_Form_Entity_Breadcrumbs = Admin_Form_Entity::factory('Breadcrumbs');

// Элементы строки навигации
$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Benchmark.title'))
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref('/admin/benchmark/index.php', NULL, NULL, '')
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax('/admin/benchmark/index.php', NULL, NULL, '')
		)
	)->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Benchmark.menu_site_speed'))
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL, '')
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL, '')
	));

// Добавляем все хлебные крошки контроллеру
$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Breadcrumbs);

// Источник данных 1
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('Benchmark_Url')
);

$sWindowId = $oAdmin_Form_Controller->getWindowId();

$iStructureId = Core_Array::getGet('structure_id', 0);

if ($iStructureId)
{
	$oAdmin_Form_Dataset
		->addCondition(
			array(
			'select' => array('structures.name', 'benchmark_urls.*',
				array(Core_QueryBuilder::expression('ROUND(`waiting_time` / 1000, 3)'), 'waiting_time_avr'),
				array(Core_QueryBuilder::expression('ROUND(`load_page_time` / 1000, 3)'), 'load_page_time_avr'),
				array(Core_QueryBuilder::expression('ROUND(`dns_lookup` / 1000, 3)'), 'dns_lookup_avr'),
				array(Core_QueryBuilder::expression('ROUND(`connect_server` / 1000, 3)'), 'connect_server_avr')
				))
		)
		->addCondition(
			array('join' => array('structures', 'benchmark_urls.structure_id', '=', 'structures.id'))
		)->addCondition(
			array('where' => array('benchmark_urls.structure_id', '=', intval($iStructureId)))
		)
		->addCondition(
			array('where' => array('benchmark_urls.datetime', '>', date('Y-m-d 00:00:00', strtotime("-1 month"))))
		)
		->changeField('name', 'type', 1);
}
else
{
	$oAdmin_Form_Dataset->addCondition(
		array(
		'select' => array('structures.name', 'benchmark_urls.*',
				array(Core_QueryBuilder::expression('ROUND(SUM(`waiting_time`) / COUNT(*) / 1000, 3)'), 'waiting_time_avr'),
				array(Core_QueryBuilder::expression('ROUND(SUM(`load_page_time`) / COUNT(*) / 1000, 3)'), 'load_page_time_avr'),
				array(Core_QueryBuilder::expression('ROUND(SUM(`dns_lookup`) / COUNT(*)/ 1000, 3)'), 'dns_lookup_avr'),
				array(Core_QueryBuilder::expression('ROUND(SUM(`connect_server`) / COUNT(*) / 1000, 3)'), 'connect_server_avr')
			)
		)
	)
	->addCondition(
		array('join' => array('structures', 'benchmark_urls.structure_id', '=', 'structures.id'))
	)->addCondition(
		array('where' => array('benchmark_urls.datetime', '>', date('Y-m-d 00:00:00', strtotime("-1 month"))))
	)->addCondition(
		array('groupBy' => array('structure_id'))
	)
	->changeField('ip', 'class', 'hidden')
	->changeField('datetime', 'class', 'hidden');

	ob_start();

	$sCheckedStart = defined('BENCHMARK_ENABLE') && BENCHMARK_ENABLE
		? 'checked="checked"'
		: '';

	$sCheckedAddCounter = defined('BENCHMARK_ADD_COUNTER') && BENCHMARK_ADD_COUNTER
		? 'checked="checked"'
		: '';

	?>
	<script>
	function changeConstant(name, value)
	{
		$.ajax({
			type: "POST",
			url: "/admin/benchmark/url/index.php",
			data: {constantName: name, constantValue: +value}
		});
	}
	</script>

	<div class="row">
		<div class="col-xs-12">
			<div class="well">
				<div class="row">
					<div class="col-xs-6 col-sm-3 col-md-2">
						<label>
							<input class="checkbox-slider toggle colored-success" <?php echo $sCheckedStart?> type="checkbox" onchange="changeConstant('BENCHMARK_ENABLE', this.checked)">
							<span class="text"></span>
						</label>
					</div>
					<div class="col-xs-6 col-sm-9 col-md-10">
						<span class="text sky" style="font-size: 12pt;"><?php echo Core::_('Benchmark.start_monitoring')?></span>
					</div>

				</div>

				<div class="row margin-top-10">
					<div class="col-xs-6 col-sm-3 col-md-2">
						<label>
							<input class="checkbox-slider toggle colored-success" <?php echo $sCheckedAddCounter?> type="checkbox" onchange="changeConstant('BENCHMARK_ADD_COUNTER', this.checked)">
							<span class="text"></span>
						</label>
					</div>
					<div class="col-xs-6 col-sm-9 col-md-10">
						<span class="text sky" style="font-size: 12pt;"><?php echo Core::_('Benchmark.add_counter')?></span>
					</div>
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

ob_start();

$iOneMonthAgo = strtotime("-1 month");

/* Month */
$iTmpTimestamp = strtotime(date('Y-m-d 00:00:00', $iOneMonthAgo));
$endTime = strtotime(date('Y-m-d 23:59:59'));

$aTmp = array();
while ($iTmpTimestamp < $endTime)
{
	$aTmp[$iTmpTimestamp] = 0;
	$iTmpTimestamp += 3600 * 24;
}

$oBenchmark_Url = Core_Entity::factory('Benchmark_Url');

if ($iStructureId)
{
	$oBenchmark_Url
		->queryBuilder()
		->select(
			array(Core_QueryBuilder::expression('ROUND(`waiting_time` / 1000, 3)'), 'waiting_time_avr'),
			array(Core_QueryBuilder::expression('ROUND(`load_page_time` / 1000, 3)'), 'load_page_time_avr'),
			array(Core_QueryBuilder::expression('ROUND(`dns_lookup` / 1000, 3)'), 'dns_lookup_avr'),
			array(Core_QueryBuilder::expression('ROUND(`connect_server` / 1000, 3)'), 'connect_server_avr')
		)
		->where('benchmark_urls.structure_id', '=', $iStructureId);
}
else
{
	$oBenchmark_Url
		->queryBuilder()
		->select(
			array(Core_QueryBuilder::expression('ROUND(SUM(`waiting_time`) / COUNT(*) / 1000, 3)'), 'waiting_time_avr'),
			array(Core_QueryBuilder::expression('ROUND(SUM(`load_page_time`) / COUNT(*) / 1000, 3)'), 'load_page_time_avr'),
			array(Core_QueryBuilder::expression('ROUND(SUM(`dns_lookup`) / COUNT(*) / 1000, 3)'), 'dns_lookup_avr'),
			array(Core_QueryBuilder::expression('ROUND(SUM(`connect_server`) / COUNT(*) / 1000, 3)'), 'connect_server_avr')
		)
		->join('structures', 'benchmark_urls.structure_id', '=', 'structures.id')
		->where('structures.site_id', '=', CURRENT_SITE);
}

$oBenchmark_Url
	->queryBuilder()
	->where('benchmark_urls.datetime', '>', date('Y-m-d 00:00:00', $iOneMonthAgo))
	->groupBy('DATE(benchmark_urls.datetime)')
	->clearOrderBy();

$aBenchmark_Urls = $oBenchmark_Url->findAll(FALSE);
foreach ($aBenchmark_Urls as $oBenchmark_Url)
{
	$aTmp[strtotime(date('Y-m-d 00:00:00', Core_Date::sql2timestamp($oBenchmark_Url->datetime)))] = $oBenchmark_Url;
}

$aTitlesPerDay = $aLoadDomValuesPerDay = $aLoadPageValuesPerDay = $aDnsValuesPerDay = $aServerValuesPerDay = array();

foreach ($aTmp as $key => $oBenchmark_Url)
{
	$aTitlesPerDay[] = $key * 1000;
	$aLoadDomValuesPerDay[] = is_object($oBenchmark_Url) ? $oBenchmark_Url->waiting_time_avr : 0;
	$aLoadPageValuesPerDay[] = is_object($oBenchmark_Url) ? $oBenchmark_Url->load_page_time_avr : 0;
	$aDnsValuesPerDay[] = is_object($oBenchmark_Url) ? $oBenchmark_Url->dns_lookup_avr : 0;
	$aServerValuesPerDay[] = is_object($oBenchmark_Url) ? $oBenchmark_Url->connect_server_avr : 0;
}

/* Day */
$iOneDayAgo = strtotime("-1 day");

$oBenchmark_Url = Core_Entity::factory('Benchmark_Url');
$oBenchmark_Url
	->queryBuilder()
	->select(
		array('SUM(waiting_time) / COUNT(*) / 1000', 'waiting_time_avr'),
		array('SUM(load_page_time) / COUNT(*) / 1000', 'load_page_time_avr'),
		array('SUM(dns_lookup) / COUNT(*) / 1000', 'dns_lookup_avr'),
		array('SUM(connect_server) / COUNT(*) / 1000', 'connect_server_avr')
	)
	->join('structures', 'benchmark_urls.structure_id', '=', 'structures.id')
	->where('structures.site_id', '=', CURRENT_SITE)
	->where('benchmark_urls.datetime', '>=', date('Y-m-d H:59:59', $iOneDayAgo))
	->groupBy('HOUR(benchmark_urls.datetime)')
	->clearOrderBy();

$iStructureId && $oBenchmark_Url
	->queryBuilder()
	->where('structures.id', '=', $iStructureId);

$aBenchmark_Urls = $oBenchmark_Url->findAll(FALSE);

$aTitlesPerHour = $aLoadDomValuesPerHour = $aLoadPageValuesPerHour
	= $aDnsValuesPerHour = $aServerValuesPerHour = array();

$iTmpTimestamp = strtotime(date('Y-m-d H:59:59', $iOneDayAgo));
$aTmp = array();

while ($iTmpTimestamp < time())
{
	$aTmp[$iTmpTimestamp] = 0;
	$iTmpTimestamp += 3600;
}

foreach ($aBenchmark_Urls as $oBenchmark_Url)
{
	$aTmp[strtotime(date('Y-m-d H:59:59', Core_Date::sql2timestamp($oBenchmark_Url->datetime)))] = $oBenchmark_Url;
}

foreach ($aTmp as $key => $oBenchmark_Url)
{
	$aTitlesPerHour[] = $key * 1000;
	$aLoadDomValuesPerHour[] = is_object($oBenchmark_Url) ? $oBenchmark_Url->waiting_time_avr : 0;
	$aLoadPageValuesPerHour[] = is_object($oBenchmark_Url) ? $oBenchmark_Url->load_page_time_avr : 0;
	$aDnsValuesPerHour[] = is_object($oBenchmark_Url) ? $oBenchmark_Url->dns_lookup_avr : 0;
	$aServerValuesPerHour[] = is_object($oBenchmark_Url) ? $oBenchmark_Url->connect_server_avr : 0;
}
?>

<div class="widget counter">
	<div class="widget-body">
		<div class="tabbable">
			<ul id="counterTabs" class="nav nav-tabs tabs-flat nav-justified">
				<li class="active">
					<a href="#benchmark_month" data-toggle="tab"><?php echo Core::_('Benchmark.benchmark_day')?></a>
				</li>
				<li class="">
					<a href="#benchmark_day" data-toggle="tab"><?php echo Core::_('Benchmark.benchmark_hour')?></a>
				</li>
			</ul>
			<div class="tab-content tabs-flat no-padding">
				<div id="benchmark_month" class="tab-pane animated fadeInUp active">
					<div class="row">
						<div class="col-xs-12">
							<div id="benchmark-month-chart" class="chart chart-lg"></div>
						</div>
					</div>
					<div class="row">
						<div class="col-xs-12">
							<div class="col-sm-12 col-md-6">
								<button class="btn btn-palegreen" id="setOriginalZoom"><i class="fa fa-area-chart icon-separator"></i><?php echo Core::_('Benchmark.reset')?></button>
							</div>
						</div>
					</div>
				</div>
				<div id="benchmark_day" class="tab-pane padding-left-5 padding-right-10 animated fadeInUp">
					<div class="row">
						<div class="col-xs-12">
							<div id="benchmark-day-chart" class="chart chart-lg" style="width:100%"></div>
						</div>
					</div>
					<div class="row">
						<div class="col-xs-12">
							<div class="col-sm-12 col-md-6">
								<button class="btn btn-palegreen" id="setOriginalZoom"><i class="fa fa-area-chart icon-separator"></i><?php echo Core::_('Benchmark.reset')?></button>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
$(function(){
	var
		titles_per_day = [<?php echo implode(',', $aTitlesPerDay)?>],

		load_dom_values_per_day = [<?php echo implode(',', $aLoadDomValuesPerDay)?>],
		load_page_values_per_day = [<?php echo implode(',', $aLoadPageValuesPerDay)?>],
		dns_values_per_day = [<?php echo implode(',', $aDnsValuesPerDay)?>],
		server_values_per_day = [<?php echo implode(',', $aServerValuesPerDay)?>],

		valueTitlesLoadDomPerDay = new Array(),
		valueTitlesLoadPagePerDay = new Array(),
		valueTitlesDnsPerDay = new Array(),
		valueTitlesServerPerDay = new Array(),

		/* Day */
		//titles_per_hour_keys = [<?php echo implode(',', array_keys($aTitlesPerHour))?>],
		titles_per_hour = [<?php echo implode(',', $aTitlesPerHour)?>],
		titles_per_hour = [<?php echo implode(',', $aTitlesPerHour)?>],
		load_dom_values_per_hour = [<?php echo implode(',', $aLoadDomValuesPerHour)?>],
		load_page_values_per_hour = [<?php echo implode(',', $aLoadPageValuesPerHour)?>],
		dns_values_per_hour = [<?php echo implode(',', $aDnsValuesPerHour)?>],
		server_values_per_hour = [<?php echo implode(',', $aServerValuesPerHour)?>];

	for(var i = 0; i < load_dom_values_per_day.length; i++) {
		valueTitlesLoadDomPerDay.push([new Date(titles_per_day[i]), load_dom_values_per_day[i]]);
		valueTitlesLoadPagePerDay.push([new Date(titles_per_day[i]), load_page_values_per_day[i]]);
		valueTitlesDnsPerDay.push([new Date(titles_per_day[i]), dns_values_per_day[i]]);
		valueTitlesServerPerDay.push([new Date(titles_per_day[i]), server_values_per_day[i]]);
	}

	var valueTitlesLoadDomPerHour = new Array(),
	valueTitlesLoadPagePerHour = new Array(),
	valueTitlesDnsPerHour = new Array(),
	valueTitlesServerPerHour = new Array(),
	matchValues = new Array();

	for(var i = 0; i < load_dom_values_per_hour.length; i++) {
		valueTitlesLoadDomPerHour.push([new Date(titles_per_hour[i]), load_dom_values_per_hour[i]]);
		valueTitlesLoadPagePerHour.push([new Date(titles_per_hour[i]), load_page_values_per_hour[i]]);
		valueTitlesDnsPerHour.push([new Date(titles_per_hour[i]), dns_values_per_hour[i]]);
		valueTitlesServerPerHour.push([new Date(titles_per_hour[i]), server_values_per_hour[i]]);
	}

	var themeprimary = getThemeColorFromCss('themeprimary'), gridbordercolor = "#eee",  dataPerDay = [{
		color: themeprimary,
		label: "<?php echo Core::_('Benchmark.load_page_time') ?>",
		data: valueTitlesLoadPagePerDay,
		yaxis: 1,
		bars: {
			//order: 1,
			show: true,
			borderWidth: 0,
			barWidth: 24 * 30 * 60 * 1000, // Ширина - полдня в миллисекундах
			lineWidth: .5,
			fillColor: {
				colors: [{
					opacity: 0.4
				}, {
					opacity: 1
				}]
			}
		}
	},
	{
		color: themesecondary,
		label: "<?php echo Core::_('Benchmark.waiting_time') ?>",
		data: valueTitlesLoadDomPerDay,
		yaxis: 1,
		lines: {
				show: true,
				fill: true,
				lineWidth: .1,
				fillColor: {
					colors: [{
						opacity: 0.2
					}, {
						opacity: 0.8
					}]
				}
			},
		points: {
			show: false
		},
		shadowSize: 0
	},
	{
		color: themethirdcolor,
		label: "<?php echo Core::_('Benchmark.dns_lookup') ?>",
		data: valueTitlesDnsPerDay,
		yaxis: 2
	},
	{
		color: themefourthcolor,
		label: "<?php echo Core::_('Benchmark.connect_server') ?>",
		data: valueTitlesServerPerDay,
		yaxis: 2
	}],
	dataPerHour = [
		{
			color: themeprimary,
			label: "<?php echo Core::_('Benchmark.load_page_time') ?>",
			data: valueTitlesLoadPagePerHour,
			yaxis: 1,
			bars: {
				//order: 1,
				show: true,
				borderWidth: 0,
				barWidth: 30 * 60 * 1000, // Ширина - полчаса в миллисекундах
				lineWidth: .5,
				fillColor: {
					colors: [{
						opacity: 0.4
					}, {
						opacity: 1
					}]
				}
			}
			//points: {show: true}
		},
		{
			color: themesecondary,
			label: "<?php echo Core::_('Benchmark.waiting_time') ?>",
			data: valueTitlesLoadDomPerHour,
			yaxis: 1,
			lines: {
				show: true,
				fill: true,
				lineWidth: .1,
				fillColor: {
					colors: [{
						opacity: 0.2
					}, {
						opacity: 0.8
					}]
				}
			},
			points: {
				show: false
			},
			shadowSize: 0

		},
		{
			color: themethirdcolor,
			label: "<?php echo Core::_('Benchmark.dns_lookup') ?>",
			data: valueTitlesDnsPerHour,
			yaxis: 2,
			lines: {
				show: true
				/*
				fill: false,
				fillColor: {
					colors: [{
						opacity: 0.3
					}, {
						opacity: 0
					}]
				},
				*/
			}

		},
		{
			color: themefourthcolor,
			label: "<?php echo Core::_('Benchmark.connect_server') ?>",
			data: valueTitlesServerPerHour,
			yaxis: 2,
			lines: {
				show: true
				/*
				fill: false,
				fillColor: {
					colors: [{
						opacity: 0.3
					}, {
						opacity: 0
					}]
				}*/
			}
		}
	];

	var optionsForDayGraph = {
		/*
		series: {
			lines: {show: true},
			//bars:  {show: true},
			//points: {show: true}
		},
		*/
		legend: {
			noColumns: 4,
			backgroundOpacity: 0.65
		},
		xaxis: {
			mode: "time",
			//timeformat: "%d.%m.%Y %H:%M:%S",
			timeformat: "%e",
			tickSize: [1, 'day'],
			color: gridbordercolor,
			timezone: "browser"
		},
		yaxes: [{
			min: 0,
			color: gridbordercolor
		},{
			min: 0,
			color: gridbordercolor,
			position: "right"
		}],
		grid: {
			hoverable: true,
			clickable: false,
			borderWidth: 0,
			aboveData: false
		},
		tooltip: true,
		tooltipOpts: {
			defaultTheme: false,
			dateFormat: "%d.%m.%Y",
			//content: "<b>%s</b> : <span>%x</span> : <span>%y.2 сек.</span>",
			content: function(label, xval, yval, flotItem) {
				//console.log(arguments);
				var dateValue = new Date(xval);
				return "<b>" + label + "</b> : <span>" + dateValue.getDate() + "." + (dateValue.getMonth() + 1) + "." + dateValue.getFullYear() + "</span> : <span>" + yval.toFixed(3) + " сек.</span>";
			}
		},
		crosshair: {
			mode: "x"
		},
		selection: {
			mode: "x"
		}
	};
	var optionsForHourGraph = {
		/*
		series: {
			lines: {show: true,  zero: true},
			bars:  {show: true},
			points: {show: true}
		},*/
		legend: {
			noColumns: 4,
			backgroundOpacity: 0.65
		},
		xaxis: {
			mode: "time",
			//timeformat: "%d.%m.%Y %H:%M:%S",
			timeformat: "%H",
			tickSize: [1, 'hour'],
			color: gridbordercolor,
			timezone: "browser"
		},
		yaxes: [{
			min: 0,
			color: gridbordercolor
		},{
			min: 0,
			color: gridbordercolor,
			position: "right"
		}],
		grid: {
			hoverable: true,
			clickable: false,
			borderWidth: 0,
			aboveData: false
		},
		tooltip: true,
		tooltipOpts: {
			defaultTheme: false,
			dateFormat: null,
			//content: "<b>%s</b> : <span>%x</span> : <span>%y.6 сек.</span>",
			content: function(label, xval, yval, flotItem) {
				//console.log(arguments);
				var dateValue = new Date(xval);
				return "<b>" + label + "</b> : <span>" + dateValue.getDate() + "." + (dateValue.getMonth() + 1) + "." + dateValue.getFullYear() + "</span> : <span>" + yval.toFixed(3) + " сек.</span>";
			}
		},
		crosshair: {
			mode: "x"
		},
		selection: {
			mode: "x"
		}
	};

	var aScripts = [
		'jquery.flot.js',
		'jquery.flot.time.min.js',
		'jquery.flot.categories.min.js',
		'jquery.flot.tooltip.min.js',
		'jquery.flot.crosshair.min.js',
		'jquery.flot.resize.js',
		'jquery.flot.selection.min.js',
		'jquery.flot.pie.min.js'
	];

	$.getMultiContent(aScripts, '/modules/skin/bootstrap/js/charts/flot/').done(function() {
		// all scripts loaded

		var placeholderPerDay = $("#benchmark-month-chart"),
		placeholderPerHour = $("#benchmark-day-chart");

		placeholderPerDay.bind("plotselected", function (event, ranges) {
			plotPerDay = $.plot(placeholderPerDay, dataPerDay, $.extend(true, {}, optionsForDayGraph, {
				xaxis: {
					min: ranges.xaxis.from,
					max: ranges.xaxis.to
				}
			}));
		});

		placeholderPerHour.bind("plotselected", function (event, ranges) {
			plotPerHour = $.plot(placeholderPerHour, dataPerHour, $.extend(true, {}, optionsForHourGraph, {
				xaxis: {
					min: ranges.xaxis.from,
					max: ranges.xaxis.to
				}
			}));
		});

		$('#benchmark_month #setOriginalZoom').on('click', function(){
			plotPerDay = $.plot(placeholderPerDay, dataPerDay, optionsForDayGraph);
		});

		$('#benchmark_day #setOriginalZoom').on('click', function(){
			plotPerHour = $.plot(placeholderPerHour, dataPerHour, optionsForHourGraph);
		});

		var plotPerDay = $.plot(placeholderPerDay, dataPerDay, optionsForDayGraph),
			plotPerHour = $.plot(placeholderPerHour, dataPerHour, optionsForHourGraph);


		$("#benchmark_month #clearSelection").click(function () {
			plotPerDay.clearSelection();
		});

		$("#benchmark_day #clearSelection").click(function () {
			plotPerHour.clearSelection();
		});

		// Вызываем однократно обработчик нажатия кнопки, для правильной отрисовки графика
		$('.page-content').one('shown.bs.tab', 'a[data-toggle="tab"]', function(e){
			$('#<?php echo $sWindowId ?> ' + $(e.target).attr('href') + ' #setOriginalZoom').click();
		});
	});
});
</script>
<?php
$oAdmin_Form_Controller->addEntity(
	Admin_Form_Entity::factory('Code')
		->html(ob_get_clean())
);

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset(
	$oAdmin_Form_Dataset
);

// Показ формы
$oAdmin_Form_Controller->execute();
