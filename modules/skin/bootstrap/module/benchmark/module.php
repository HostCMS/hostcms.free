<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Benchmark. Backend's Index Pages and Widget.
 *
 * @package HostCMS
 * @subpackage Skin
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Skin_Bootstrap_Module_Benchmark_Module extends Benchmark_Module
{
	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();

		$this->_adminPages = array(
			1 => array('title' => Core::_('Benchmark.menu'))
		);
	}

	public function widget()
	{
		$oBenchmark = Core_Entity::factory('Benchmark');
		$oBenchmark
			->queryBuilder()
			->where('benchmarks.site_id', '=', CURRENT_SITE)
			->clearOrderBy()
			->orderBy('benchmarks.id', 'DESC')
			->limit(1);

		$aBenchmarks = $oBenchmark->findAll(FALSE);

		$iBenchmark = isset($aBenchmarks[0])
			? $aBenchmarks[0]->getBenchmark()
			: 0;

		$aColors = array('gray', 'danger', 'orange', 'warning', 'success');
		$sColor = $aColors[ceil($iBenchmark / 25)];

		?><!-- Benchmark -->
		<div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
			<div class="databox radius-bordered databox-shadowed hostcms-widget-databox">
				<div class="databox-left bg-<?php echo $sColor?>">
					<div class="databox-piechart">
						<div id="benchmarkWidget" class="easyPieChart" data-barcolor="#fff" data-linecap="butt" data-percent="<?php echo $iBenchmark?>" data-animate="500" data-linewidth="3" data-size="47" data-trackcolor="rgba(255,255,255,0.1)">
							<span class="white font-90"><?php echo $iBenchmark?>%</span>
						</div>
					</div>
				</div>
				<div class="databox-right">
					<span class="databox-number <?php echo $sColor?>"><?php echo $iBenchmark?> / 100</span>
					<div class="databox-text"><?php echo Core::_('Benchmark.menu')?></div>
					<div class="databox-stat <?php echo $sColor?> radius-bordered">
						<i class="stat-icon icon-lg fa fa-trophy"></i>
					</div>
				</div>
			</div>
		</div>

		<script>
		$(function() {
			setTimeout(function() {
					var benchmarkWidget = $('#benchmarkWidget');

					var barColor = getcolor(benchmarkWidget.data('barcolor')) || themeprimary,
						trackColor = getcolor(benchmarkWidget.data('trackcolor')) || false,
						scaleColor = getcolor(benchmarkWidget.data('scalecolor')) || false,
						lineCap = benchmarkWidget.data('linecap') || "round",
						lineWidth = benchmarkWidget.data('linewidth') || 3,
						size = benchmarkWidget.data('size') || 110,
						animate = benchmarkWidget.data('animate') || false;

					benchmarkWidget.easyPieChart({
						barColor: barColor,
						trackColor: trackColor,
						scaleColor: scaleColor,
						lineCap: lineCap,
						lineWidth: lineWidth,
						size: size,
						animate : animate
					});

			}, 500);
		});
		</script>
	<?php
	}
}