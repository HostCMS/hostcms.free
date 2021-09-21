<?php
/**
 * Report.
 *
 * @package HostCMS
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
require_once('../../bootstrap.php');

Core_Auth::authorization($sModule = 'report');

$sAdminFormAction = '/admin/report/index.php';

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create();
$oAdmin_Form_Controller
	->module(Core_Module::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title(Core::_('Report.title'));

ob_start();

$oAdmin_View = Admin_View::create();
$oAdmin_View
	->module(Core_Module::factory($sModule))
	->pageTitle(Core::_('Report.title'))
	;

if (!is_null(Core_Array::getPost('ajaxLoadTabContent')))
{
	$aJson = array();

	$module_id = intval(Core_Array::getPost('module_id'));

	$oModule = Core_Entity::factory('Module')->getById($module_id);

	if (!is_null($oModule))
	{
		$oCore_Module = Core_Module::factory($oModule->path);
		if ($oCore_Module)
		{
			$external_data = Core_Array::getPost('external_data');
			if (is_array($external_data))
			{
				$isActive = Core_Session::isAcive();
				!$isActive && Core_Session::start();

				foreach ($external_data as $key => $value)
				{
					$_SESSION['report'][$key] = $value;
				}

				!$isActive && Core_Session::close();
			}

			$reportName = Core_Array::getPost('report_name');

			$aOptions = array(
				'start_datetime' => Core_Array::getPost('range_start_date'),
				'end_datetime' => Core_Array::getPost('range_end_date'),
				'previous_start_datetime' => Core_Array::getPost('previous_range_start_date'),
				'previous_end_datetime' => Core_Array::getPost('previous_range_end_date'),
				'group_by' => Core_Array::getPost('group_by'),
				'compare_previous_period' => Core_Array::getPost('compare_previous_period')
			);

			$aJson = $oCore_Module->getReport($reportName, $aFields = array('captionHTML', 'content'), $aOptions);
		}
	}

	Core::showJson($aJson);
}

$aModuleReports = array();

$oModules = Core_Entity::factory('Module');
$oModules->queryBuilder()
	->where('modules.active', '=', 1)
	->clearOrderBy()
	->orderBy('modules.sorting', 'ASC');

$aModules = $oModules->findAll(FALSE);

$bFirst = TRUE;
foreach ($aModules as $oModule)
{
	$oCore_Module = Core_Module::factory($oModule->path);
	if ($oCore_Module)
	{
		if (method_exists($oCore_Module, 'getReports'))
		{
			$aReports = $oCore_Module->getReports();

			if (count($aReports))
			{
				foreach ($aReports as $reportName => $callback)
				{
					if (is_callable($callback))
					{
						$aFields = !$bFirst ? array('caption', 'captionHTML') : array('caption', 'captionHTML', 'content');
						$bFirst = FALSE;
						$aModuleReports[$oModule->id][$reportName] = $oCore_Module->getReport($reportName, $aFields);
					}
				}
			}
		}
	}
}

$oMainTab = Admin_Form_Entity::factory('Tab')->name('main');
$oLefttabs = Admin_Form_Entity::factory('Lefttabs');

$oMainTab
	->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row report-header'))
	->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'));

foreach ($aModuleReports as $moduleId => $aModuleReport)
{
	foreach ($aModuleReport as $reportName => $aReport)
	{
		$oLefttabs->add(
			Admin_Form_Entity::factory('Lefttab')
				->caption($aReport['caption'])
				->color(isset($aReport['color']) ? $aReport['color'] : 'sky')
				->captionHTML($aReport['captionHTML'])
				->setUnlimitedProperties(TRUE)
				->set('data-module-id', $moduleId)
				->set('data-report-name', $reportName)
				->add(
					Admin_Form_Entity::factory('Code')->html(Core_Array::get($aReport, 'content'))
				)
		);
	}
}

$iEnd = time();
$iStart = strtotime("-6 month", $iEnd);

$startDatetime = date('Y-m-d', $iStart);
$endDatetime = date('Y-m-d', $iEnd);

$previousEndDatetime = date('Y-m-d', strtotime("-1 day", $iStart));

switch (Core_Array::getPost('group_by', 1))
{
	case 0: // дни
		$condition = "-6 month";
	break;
	default:
	case 1: // недели
		$dates = range($iStart, $iEnd, 604800);
		$aWeeks = array_map(function($v){return date('W', $v);}, $dates);
		$weeks = count($aWeeks);
		$condition = "-{$weeks} weeks";
	break;
	case 2: // месяцы
		$condition = "-6 month";
	break;
}

$previousStartDatetime = date('Y-m-d', strtotime($condition, $iStart));
ob_start();
?>
<div class="col-xs-12 col-sm-6 col-lg-3 report-timeInterval">
	<div class="wrap-tabs-left-collapse"><i class="fa fa-bars tabs-left-collapse"></i></div>
	<span class="text margin-right-10"><?php echo Core::_('Report.data_for')?> </span><span id="daterange" class="label label-primary"><?php echo date('d.m.Y', Core_Date::sql2timestamp($startDatetime))?> — <?php echo date('d.m.Y', Core_Date::sql2timestamp($endDatetime))?></span>
	<input type="hidden" name="range_start_date" value="<?php echo $startDatetime?>" />
	<input type="hidden" name="range_end_date" value="<?php echo $endDatetime?>" />
</div>
<div class="col-xs-12 col-sm-5 col-md-4 col-lg-2 report-group">
	<div class="group-by">
		<span><?php echo Core::_('Report.group_by')?> </span>
		<div class="group-by-period">
			<span data-value="0" class="text"><?php echo Core::_('Report.day')?></span>
			<span data-value="1" class="label label-primary"><?php echo Core::_('Report.week')?></span>
			<span data-value="2" class="text"><?php echo Core::_('Report.month')?></span>
		</div>
		<input type="hidden" name="group_by" value="1" />
	</div>
</div>
<div class="col-xs-12 col-sm-6 col-lg-3 report-comparePrevious">
	<div class="compare-period">
		<?php echo Core::_('Report.compare_previous_period')?>
		<label>
			<input class="checkbox-slider toggle colored-success" name="compare_previous_period" onchange="$(this).val(+this.checked); sendRequest({tab: $('.report-tabs .nav-tabs li.active')}); $('.previous-ranges span#previous_daterange').toggleClass('disabled')" value="0" type="checkbox" />
			<span class="text"></span>
		</label>
	</div>
</div>
<div class="no-padding-right col-xs-12 col-sm-5 col-lg-3 report-previousTimeInterval">
	<span class="text margin-right-10"><?php echo Core::_('Report.previuos_period')?> </span><span class="previous-ranges"><span id="previous_daterange" class="label label-azure disabled"><?php echo date('d.m.Y', Core_Date::sql2timestamp($previousStartDatetime))?> — <?php echo date('d.m.Y', Core_Date::sql2timestamp($previousEndDatetime))?></span></span>
	<input type="hidden" name="previous_range_start_date" value="<?php echo $previousStartDatetime?>" />
	<input type="hidden" name="previous_range_end_date" value="<?php echo $previousEndDatetime?>" />
</div>
<div class="col-xs-12 col-sm-1 report-print">
	<a class="btn btn-default btn-xs icon-only black report-print-button" href="javascript:void(0);"><i class="fa fa-print"></i></a>
</div>
<?php
$oMainRow1->add(
	Admin_Form_Entity::factory('Code')
		->html(ob_get_clean())
);

$oMainRow2
	->add(Admin_Form_Entity::factory('Div')->class('col-xs-12 report-tabs')
		->add($oLefttabs)
	);

Admin_Form_Entity::factory('Form')
	->controller($oAdmin_Form_Controller)
	->action($sAdminFormAction)
	->add($oMainTab)
	->execute();
?>
<script>

	$('.tabs-left-collapse').on('click', function() {

		if (!$(this).hasClass('active'))
		{
			$(this).addClass('active');
			$('.report-tabs>.tabs-left>.nav-tabs').addClass('hide');
		}
		else
		{
			$(this).removeClass('active');
			$('.report-tabs>.tabs-left>.nav-tabs').removeClass('hide');
		}
	});

	// {tab: li, data: {shop_id: 2} }
	function sendRequest(settings)
	{
		settings = $.extend({
			data: {}
		}, settings);

		var dataRequest = {
			ajaxLoadTabContent: 1,
			module_id: settings.tab.data('module-id'),
			report_name: settings.tab.data('report-name'),
			compare_previous_period: $('input[name="compare_previous_period"]').val(),
			group_by: $('input[name="group_by"]').val(),
			range_start_date: $('input[name="range_start_date"]').val(),
			range_end_date: $('input[name="range_end_date"]').val(),
			previous_range_start_date: $('input[name="previous_range_start_date"]').val(),
			previous_range_end_date: $('input[name="previous_range_end_date"]').val(),
			external_data: settings.data
		};

		var context = settings.tab.find('a').attr('href');

		$.ajax({
			url: '/admin/report/index.php',
			data: dataRequest,
			dataType: 'json',
			context: $(context),
			type: 'POST',
			success: function(data) {
				data.timeInterval == 0 ? $('.report-timeInterval').addClass('hidden') : $('.report-timeInterval').removeClass('hidden');
				data.group == 0 ? $('.report-group').addClass('hidden') : $('.report-group').removeClass('hidden');
				data.comparePrevious == 0 ? $('.report-comparePrevious').addClass('hidden') : $('.report-comparePrevious').removeClass('hidden');
				data.previousTimeInterval == 0 ? $('.report-previousTimeInterval').addClass('hidden') : $('.report-previousTimeInterval').removeClass('hidden');
				data.print == 0 ? $('.report-print').addClass('hidden') : $('.report-print').removeClass('hidden');

				this.html(data.content);
				settings.tab.find('a .tab-description').html(data.captionHTML);
			}
		});
	}

	$(function() {
		var aScripts = [
			'printThis.js'
		];
		$.getMultiContent(aScripts, '/modules/skin/bootstrap/js/').done(function() {
			$('.report-print-button').on('click', function(){
				$('.report-tabs .tab-content .tab-pane.active').printThis({
					canvas: true
				});
			});
		});

		var selectorGroupBy = $('.report-header .group-by-period span');

		$(selectorGroupBy).on('click', function(){
			selectorGroupBy.each(function(i) {
				$(this).attr('class', 'text');
			});

			$(this).toggleClass('text label label-primary');

			$('input[name="group_by"]').val($(this).data('value'));

			sendRequest({tab: $('.report-tabs .nav-tabs li.active')});
		});

		$('#daterange').daterangepicker({
			locale: {
				applyLabel: '<?php echo Core::_("Report.applyLabel")?>',
				cancelLabel: '<?php echo Core::_("Report.cancelLabel")?>',
				format: 'DD/MM/YYYY'
			},
			startDate: '<?php echo date("d/m/Y", $iStart);?>',
			endDate: '<?php echo date("d/m/Y", $iEnd);?>',
		}).on('apply.daterangepicker', function (e, picker) {
			var startDate = picker.startDate.format('YYYY-MM-DD')
				endDate = picker.endDate.format('YYYY-MM-DD');

			$('input[name="range_start_date"]').val(startDate);
			$('input[name="range_end_date"]').val(endDate);

			var startDateText = picker.startDate.format('DD.MM.YYYY'),
				endDateText = picker.endDate.format('DD.MM.YYYY');

			$('span#daterange').text(startDateText + ' — ' + endDateText);

			sendRequest({tab: $('.report-tabs .nav-tabs li.active')});
		});

		$('#previous_daterange').daterangepicker({
			locale: {
				applyLabel: '<?php echo Core::_("Report.applyLabel")?>',
				cancelLabel: '<?php echo Core::_("Report.cancelLabel")?>',
				format: 'DD/MM/YYYY'
			},
			startDate: '<?php echo date("d/m/Y", strtotime($condition, $iStart));?>',
			endDate: '<?php echo date("d/m/Y", strtotime("-1 day", $iStart));?>',
			opens: 'left'
		}).on('apply.daterangepicker', function (e, picker) {
			var previousStartDate = picker.startDate.format('YYYY-MM-DD')
				previousEndDate = picker.endDate.format('YYYY-MM-DD');

			$('input[name="previous_range_start_date"]').val(previousStartDate);
			$('input[name="previous_range_end_date"]').val(previousEndDate);

			var previousStartDateText = picker.startDate.format('DD.MM.YYYY'),
				previousEndDateText = picker.endDate.format('DD.MM.YYYY');

			$('span#previous_daterange').text(previousStartDateText + ' — ' + previousEndDateText);

			sendRequest({tab: $('.report-tabs .nav-tabs li.active')});
		});

		$('.report-tabs .nav-tabs a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
			sendRequest({tab: $(e.target).parent()});
		});

		/*var firstTabA = $('.report-tabs .nav-tabs a:first');
		firstTabA.parent().removeClass('active');
		firstTabA.tab('show');*/


		$(window).on('resize', resizeThrottler);

		var resizeTimeout;

		function resizeThrottler()
		{
			if ( !resizeTimeout )
			{
				resizeTimeout = setTimeout(function()
				{
					resizeTimeout = null;
					actualResizeHandler();

			   }, 1000);
			}
		}

		function actualResizeHandler() {

			// Изменение ширины графиков
			$('.report-tabs>.tabbable>.tab-content>.tab-pane.active .chart').each(function (){

				// Проверка на активность вкладок (внутренних), находящихся на других вкладках (внешних)
				if (!$(this).parentsUntil('.report-tabs').filter(':hidden').length)
				{
					var plot = $(this).data('plot');

					if (plot && plot.resize && plot.setupGrid && plot.draw)
					{
						plot.resize();
						plot.setupGrid();
						plot.draw();
					}
				}
			});
		}
	});
</script>
<?php
$content = ob_get_clean();

ob_start();
$oAdmin_View
	->content($content)
	->show();

Core_Skin::instance()
	->answer()
	->ajax(Core_Array::getRequest('_', FALSE))
	->content(ob_get_clean())
	->title(Core::_('Report.title'))
	->module($sModule)
	->execute();