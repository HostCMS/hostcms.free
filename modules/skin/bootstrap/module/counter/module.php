<?php

/**
 * Counter. Backend's Index Pages and Widget.
 *
 * @package HostCMS
 * @subpackage Skin
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Skin_Bootstrap_Module_Counter_Module extends Counter_Module
{
	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();

		$this->_adminPages = array(
			1 => array('title' => Core::_('Counter.menu'))
		);
	}

	/**
	 * Widget path 
	 * @var string|NULL
	 */
	protected $_path = NULL;

	/**
	 * Show admin widget
	 * @param int $type
	 * @param boolean $ajax
	 * @return self
	 */
	public function adminPage($type = 0, $ajax = FALSE)
	{
		$oModule = Core_Entity::factory('Module')->getByPath($this->getModuleName());

		$type = intval($type);
		$this->_path = "/admin/index.php?ajaxWidgetLoad&moduleId={$oModule->id}&type={$type}";

		if ($ajax)
		{
			$this->_content();
		}
		else
		{
			?><div class="col-xs-12" id="counterAdminPage">
				<script>
				$.widgetLoad({ path: '<?php echo Core_Str::escapeJavascriptVariable($this->_path)?>', context: $('#counterAdminPage') });
				</script>
			</div><?php
		}

		return TRUE;
	}

	protected function _content()
	{
		$iMonth = 12;
		?>
		<div class="widget counter">
			<div class="widget-header bordered-bottom bordered-themeprimary">
				<i class="widget-icon fa fa-bar-chart-o themeprimary"></i>
				<span class="widget-caption themeprimary"><?php echo Core::_('Counter.index_all_stat')?></span>
				<div class="widget-buttons">
					<a data-toggle="maximize">
						<i class="fa fa-expand gray"></i>
					</a>
					<a data-toggle="refresh" onclick="$(this).find('i').addClass('fa-spin'); $.widgetLoad({ path: '<?php echo Core_Str::escapeJavascriptVariable($this->_path)?>', context: $('#counterAdminPage'), 'button': $(this).find('i') });">
						<i class="fa-solid fa-rotate gray"></i>
					</a>
				</div>
			</div>
			<div class="widget-body">
				<div class="tabbable">
					<ul id="counterTabs" class="nav nav-tabs tabs-flat nav-justified">
						<li class="active">
							<a href="#website_traffic" data-toggle="tab"><?php echo Core::_('Counter.website_traffic')?></a>
						</li>
						<li class="">
							<a href="#search_bots" data-toggle="tab"><?php echo Core::_('Counter.crawlers')?></a>
						</li>
					</ul>

					<div class="tab-content tabs-flat no-padding">
						<div id="website_traffic" class="tab-pane animated fadeInUp active">
							<div class="row">
								<div class="col-xs-12">
									<div id="website-traffic-chart" class="chart chart-lg" style="width:100%"></div>
								</div>
							</div>
							<div class="row">
								<div class="col-xs-12">
									<div class="col-sm-12 col-md-6">
										<button class="btn btn-palegreen" id="setOriginalZoom"><i class="fa fa-area-chart icon-separator"></i><?php echo Core::_('Counter.reset')?></button>
									</div>
								</div>
							</div>
						</div>
						<div id="search_bots" class="tab-pane padding-left-5 padding-right-10 animated fadeInUp">
							<div class="row">
								<div class="col-xs-12">
									<div id="search-bots-chart" class="chart chart-lg" style="width:100%"></div>
								</div>
							</div>
							<div class="row">
								<div class="col-xs-12">
									<div class="col-sm-12 col-md-6">
										<button class="btn btn-palegreen" id="setOriginalZoom"><i class="fa fa-area-chart icon-separator"></i><?php echo Core::_('Counter.reset')?></button>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<?php
		$iBeginTimestamp = strtotime("-{$iMonth} month");
		$iEndTimestamp = Core_Date::date2timestamp(date('Y-m-d 23:59:59'));

		$oCounters = Core_Entity::factory('Site', CURRENT_SITE)->Counters;
		$oCounters
			->queryBuilder()
			->where('date', '>=', date('Y-m-d 00:00:00', $iBeginTimestamp))
			->clearOrderBy()
			->orderBy('date', 'ASC');

		$aCounters = $oCounters->findAll(FALSE);

		// Началом периода считается первая найденная дата
		isset($aCounters[0])
			&& $iBeginTimestamp = Core_Date::date2timestamp($aCounters[0]->date);

		$aHits = array();
		for ($iTmp = $iBeginTimestamp; $iTmp <= $iEndTimestamp; $iTmp += 86400)
		{
			$aHits["'" . date('Y-m-d', $iTmp) . "'"] = 0;
		}

		$aBots = $aHosts = $aNewUsers = $aSessions = $aHits;

		$iHitsCount = $iHostsCount = $iBotsCount = $iSessionsCount = $iNewUsersCount = 0;
		foreach ($aCounters as $oCounter)
		{
			$index = "'" . $oCounter->date . "'";

			$aSessions[$index] = $oCounter->sessions;
			$iSessionsCount += $oCounter->sessions;

			$aHits[$index] = $oCounter->hits;

			$iHitsCount += $oCounter->hits;

			$aHosts[$index] = $oCounter->hosts;
			$iHostsCount += $oCounter->hosts;

			$aNewUsers[$index] = $oCounter->new_users;
			$iNewUsersCount += $oCounter->new_users;

			$aBots[$index] = $oCounter->bots;
			$iBotsCount += $oCounter->bots;
		}

		$sTitles = implode(',', array_keys($aHits));
		$sHits = implode(',', array_values($aHits));
		$sHosts = implode(',', array_values($aHosts));
		$sBots = implode(',', array_values($aBots));
		$sSessions = implode(',', array_values($aSessions));
		$sNewUsers = implode(',', array_values($aNewUsers));

		?><script>
			$(function(){
			//$(window).bind("load", function () {
				var titles = [<?php echo $sTitles?>],
					sessions_values = [<?php echo $sSessions?>],
					hits_values = [<?php echo $sHits?>],
					hosts_values = [<?php echo $sHosts?>],
					new_users_values = [<?php echo $sNewUsers?>],
					bots_values = [<?php echo $sBots?>],
					valueTitlesSissions = new Array(),
					valueTitlesHits = new Array(),
					valueTitlesHosts = new Array(),
					valueTitlesNewUsers = new Array(),
					valueTitlesBots = new Array();

				for(var i = 0; i < sessions_values.length; i++) {
					valueTitlesSissions.push([new Date(titles[i]), sessions_values[i]]);
					valueTitlesHits.push([new Date(titles[i]), hits_values[i]]);
					valueTitlesHosts.push([new Date(titles[i]), hosts_values[i]]);
					valueTitlesNewUsers.push([new Date(titles[i]), new_users_values[i]]);
					valueTitlesBots.push([new Date(titles[i]), bots_values[i]]);
				}

				var themeprimary = getThemeColorFromCss('themeprimary'), gridbordercolor = "#eee", dataWebsiteTraffic = [{
					color: themeprimary,
					label: "<?php echo Core::_('Counter.graph_sessions')?>",
					data: valueTitlesSissions
				},
				{
					color: themesecondary,
					label: "<?php echo Core::_('Counter.graph_hits')?>",
					data: valueTitlesHits
				},
				{
					color: themethirdcolor,
					label: "<?php echo Core::_('Counter.graph_hosts')?>",
					data: valueTitlesHosts
				},
				{
					color: themefourthcolor,
					label: "<?php echo Core::_('Counter.graph_new_users')?>",
					data: valueTitlesNewUsers
				}
				/*,
				{
					color: themefifthcolor,
					label: "<?php echo Core::_('Counter.stat_bots')?>",
					data: valueTitlesBots
				}*/
				],
				dataSearchBots = [
					{
						color: themefifthcolor,
						label: "<?php echo Core::_('Counter.graph_bots')?>",
						data: valueTitlesBots
					}
				];

				var options = {
					series: {
						lines: {
							show: true
						},
						points: {
							show: true
						}
					},
					legend: {
						noColumns: 4,
						backgroundOpacity: 0.65
					},
					xaxis: {
						mode: "time",
						timeformat: "%d.%m.%Y",
						//tickDecimals: 0,
						color: gridbordercolor
					},
					yaxis: {
						min: 0,
						color: gridbordercolor
					},
					selection: {
						mode: "x"
					},
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
						content: "<b>%s</b> : <span>%x</span> : <span>%y</span>",
					},
					crosshair: {
						mode: "x"
					}
				};

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
					// all scripts loaded
					var placeholderWebsiteTraffic = $("#website-traffic-chart"),
						placeholderSearchBots = $("#search-bots-chart");

					placeholderWebsiteTraffic.bind("plotselected", function (event, ranges) {
						//var zoom = $("#zoom").is(":checked");
						//if (zoom) {
							plotWebsiteTraffic = $.plot(placeholderWebsiteTraffic, dataWebsiteTraffic, $.extend(true, {}, options, {
								xaxis: {
									min: ranges.xaxis.from,
									max: ranges.xaxis.to
								}
							}));
						//}
					});

					placeholderSearchBots.bind("plotselected", function (event, ranges) {
						//var zoom = $("#zoom").is(":checked");
						//if (zoom) {
							plotSearchBots = $.plot(placeholderSearchBots, dataSearchBots, $.extend(true, {}, options, {
								xaxis: {
									min: ranges.xaxis.from,
									max: ranges.xaxis.to
								}
							}));
						//}
					});

					/*
					$("#zoom").on('change', function(){
						$this = $(this);

						if (!$this.prop('checked'))
						{
							$('#setOriginalZoom').hide();
							plot = $.plot(placeholder, data, options);
						}
						else
						{
							$('#setOriginalZoom').show();
						}
					});
					*/

					$('#website_traffic #setOriginalZoom').on('click', function(){
						plotWebsiteTraffic = $.plot(placeholderWebsiteTraffic, dataWebsiteTraffic, options);
					});

					$('#search_bots #setOriginalZoom').on('click', function(){
						plotSearchBots = $.plot(placeholderSearchBots, dataSearchBots, options);
					});

					/*placeholderWebsiteTraffic.bind("plotunselected", function (event) {
						// Do Some Work
					});*/

					setTimeout(function() {
						var plotWebsiteTraffic = $.plot(placeholderWebsiteTraffic, dataWebsiteTraffic, options),
							plotSearchBots = $.plot(placeholderSearchBots, dataSearchBots, options);

						$("#website_traffic #clearSelection").click(function () {
							plotWebsiteTraffic.clearSelection();
						});

						$("#search_bots #clearSelection").click(function () {
							plotSearchBots.clearSelection();
						});
					}, 200);
				});
			});
		</script>
		<?php
		return $this;
	}
}