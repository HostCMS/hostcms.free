<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Site User. Backend's Index Pages and Widget.
 *
 * @package HostCMS
 * @subpackage Skin
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Skin_Bootstrap_Module_Siteuser_Module extends Siteuser_Module
{
	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();

		$this->_adminPages = array(
			1 => array('title' => Core::_('Siteuser.menu'))
		);
	}

	public function widget()
	{
		// Данные выводятся за 1 неделю
		$aCount = array();
		$iBeginTimestamp = strtotime("-6 days");
		$iEndTimestamp = Core_Date::date2timestamp(date('Y-m-d 23:59:59'));
		for ($iTmp = $iBeginTimestamp; $iTmp <= $iEndTimestamp; $iTmp += 86400)
		{
			$aCount[date('Y-m-d', $iTmp)] = 0;
		}

		$oCore_QueryBuilder_Select = Core_QueryBuilder::select(
				array(Core_QueryBuilder::expression('DATE(datetime)'), 'date'),
				array(Core_QueryBuilder::expression('COUNT(id)'), 'count')
			)
			->from('siteusers')
			->where('siteusers.datetime', '>=', date('Y-m-d 00:00:00', $iBeginTimestamp))
			->where('siteusers.deleted', '=', 0)
			->where('siteusers.site_id', '=', CURRENT_SITE)
			->where('siteusers.active', '=', 1)
			->groupBy('date')
			->orderBy('date', 'ASC');

		$aRows = $oCore_QueryBuilder_Select->execute()->asAssoc()->result();
		foreach ($aRows as $aRow)
		{
			$aCount[$aRow['date']] = $aRow['count'];
		}

		$iSiteusers = Core_Entity::factory('Site', CURRENT_SITE)->Siteusers->getCount();
		?><!-- Siteuser -->
		<div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
			<div class="databox radius-bordered databox-shadowed hostcms-widget-databox">
				<div class="databox-left bg-white">
					<div class="databox-sparkline">
						<span id="siteuserWidget" data-height="40px" data-width="100%" data-barcolor="#57b5e3" data-negbarcolor="#a0d468" data-zerocolor="#d73d32"
							 data-barwidth="5px" data-barspacing="1px">
							<?php echo implode(',', $aCount)?>
						</span>
					</div>
				</div>
				<div class="databox-right bordered bordered-platinum">
					<span class="databox-number sky"><?php echo $iSiteusers?></span>
					<div class="databox-text"><?php echo Core::_('Siteuser.siteusers')?></div>
					<div class="databox-stat sky radius-bordered">
						<i class="stat-icon icon-lg fa fa-users"></i>
					</div>
				</div>
			</div>
		</div>

		<script>
		$(function() {
			$.getMultiContent(['jquery.sparkline.js'], '/modules/skin/bootstrap/js/charts/sparkline/').done(function() {
				// all scripts loaded
				setTimeout(function() {
					var siteuserWidget = $('#siteuserWidget');
					siteuserWidget.sparkline('html', {
						type: 'bar',
						chartRangeMin: 0,
						disableHiddenCheck: true,
						height: siteuserWidget.data('height'),
						width: siteuserWidget.data('width'),
						barColor: getcolor(siteuserWidget.data('barcolor')),
						negBarColor: getcolor(siteuserWidget.data('negbarcolor')),
						zeroColor: getcolor(siteuserWidget.data('zerocolor')),
						barWidth: siteuserWidget.data('barwidth'),
						barSpacing: siteuserWidget.data('barspacing'),
						stackedBarColor: siteuserWidget.data('stackedbarcolor')
					});
				}, 300);
			});
		});
		</script>
		<?php
	}
}