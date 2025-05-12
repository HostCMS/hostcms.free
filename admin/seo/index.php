<?php
/**
 * Seo
 *
 * @package HostCMS
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
 */
require_once('../../bootstrap.php');

Core_Auth::authorization($sModule = 'seo');

// Код формы
$iAdmin_Form_Id = 215;
$sAdminFormAction = '/{admin}/seo/index.php';
$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module_Abstract::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title(Core::_('Seo.title'))
	->pageTitle(Core::_('Seo.title'));

// Меню формы
$oAdmin_Form_Entity_Menus = Admin_Form_Entity::factory('Menus');

// Элементы меню
$oAdmin_Form_Entity_Menus->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Admin_Form.add'))
		->icon('fa fa-plus')
		->href(
			$oAdmin_Form_Controller->getAdminActionLoadHref($oAdmin_Form_Controller->getPath(), 'edit', NULL, 0, 0)
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminActionLoadAjax($oAdmin_Form_Controller->getPath(), 'edit', NULL, 0, 0)
		)
)->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Seo.drivers'))
		->icon('fa fa-gear')
		->href(
			$oAdmin_Form_Controller->getAdminActionLoadHref('/{admin}/seo/driver/index.php', NULL, NULL, 0, 0)
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminActionLoadAjax('/{admin}/seo/driver/index.php', NULL, NULL, 0, 0)
		)
);

// Добавляем все меню контроллеру
$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Menus);

// Элементы строки навигации
$oAdmin_Form_Entity_Breadcrumbs = Admin_Form_Entity::factory('Breadcrumbs');

$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Seo.menu'))
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL, '')
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL, '')
		)
);

$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Breadcrumbs);

// Действие редактирования
$oAdmin_Form_Action = $oAdmin_Form->Admin_Form_Actions->getByName('edit');

if ($oAdmin_Form_Action && $oAdmin_Form_Controller->getAction() == 'edit')
{
	$oSeo_Site_Controller_Edit = Admin_Form_Action_Controller::factory(
		'Seo_Site_Controller_Edit', $oAdmin_Form_Action
	);

	$oSeo_Site_Controller_Edit->addEntity($oAdmin_Form_Entity_Breadcrumbs);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oSeo_Site_Controller_Edit);
}

// Действие "Применить"
$oAdminFormActionApply = $oAdmin_Form->Admin_Form_Actions->getByName('apply');

if ($oAdminFormActionApply && $oAdmin_Form_Controller->getAction() == 'apply')
{
	$oSeoSiteControllerApply = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Apply', $oAdminFormActionApply
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oSeoSiteControllerApply);
}

ob_start();

$oSeo_Sites = Core_Entity::factory('Site', CURRENT_SITE)->Seo_Sites;
$oSeo_Sites->queryBuilder()
	->where('seo_sites.active', '=', 1)
	->open()
		->where('seo_sites.last_update', '<', Core_Date::timestamp2sql(strtotime('-15 minutes')))
		->setOr()
		->where('seo_sites.last_update', 'IS', NULL)
	->close();

$aSeo_Sites = $oSeo_Sites->findAll(FALSE);

function mysort($a, $b)
{
	if ($a['total'] > $b['total'])
	{
		return -1;
	}
	elseif ($a['total'] == $b['total'])
	{
		return 0;
	}
	else
	{
		return 1;
	}
}

foreach ($aSeo_Sites as $oSeo_Site)
{
	if (strlen($oSeo_Site->token))
	{
		try{
			$oSeo_Driver_Controller = Seo_Controller::instance($oSeo_Site->Seo_Driver->driver);

			$oSeo_Driver_Controller
				->setSeoSite($oSeo_Site)
				->execute();

			$host_id = $oSeo_Driver_Controller->getHostId();

			$aSitePopularQueries = $oSeo_Driver_Controller->getSitePopularQueries($host_id);

			$oSeo_Site->Seo_Queries->deleteAll(FALSE);

			foreach ($aSitePopularQueries as $query => $aData)
			{
				$oSeo_Query = Core_Entity::factory('Seo_Query');
				$oSeo_Query
					->seo_site_id($oSeo_Site->id)
					->value($query)
					->clicks(Core_Array::get($aData, 'clicks', 0))
					->shows(Core_Array::get($aData, 'shows', 0))
					->sorting(0)
					->save();
			}

			$aSitePopularPages = $oSeo_Driver_Controller->getSitePopularPages($host_id);

			$oSeo_Site->Seo_Pages->deleteAll(FALSE);

			foreach ($aSitePopularPages as $page => $aData)
			{
				$oSeo_Page = Core_Entity::factory('Seo_Page');
				$oSeo_Page
					->seo_site_id($oSeo_Site->id)
					->value($page)
					->clicks(Core_Array::get($aData, 'clicks', 0))
					->shows(Core_Array::get($aData, 'shows', 0))
					->sorting(0)
					->save();
			}

			$oSeo_Site->last_update = Core_Date::timestamp2sql(time());
			$oSeo_Site->save();
		}
		catch (Exception $e){
			$oAdmin_Form_Controller->addMessage(
				Core_Message::get($e->getMessage(), 'error')
			);
		}
	}
}

$aTmpQueries = $aTmpPages = array();

$aSeo_Sites = Core_Entity::factory('Site', CURRENT_SITE)->Seo_Sites->getAllByActive(1);

foreach ($aSeo_Sites as $oSeo_Site)
{
	if (strlen($oSeo_Site->token))
	{
		$aSeo_Queries = $oSeo_Site->Seo_Queries->findAll(FALSE);

		foreach ($aSeo_Queries as $oSeo_Query)
		{
			$query = $oSeo_Query->value;

			if (!isset($aTmpQueries[$query]))
			{
				$aTmpQueries[$query] = array('total' => 0);
			}

			$aTmpQueries[$query][$oSeo_Site->Seo_Driver->driver]['clicks'] = $oSeo_Query->clicks;
			$aTmpQueries[$query][$oSeo_Site->Seo_Driver->driver]['shows'] = $oSeo_Query->shows;

			$aTmpQueries[$query]['total'] += $oSeo_Query->clicks;
		}

		$aSeo_Pages = $oSeo_Site->Seo_Pages->findAll(FALSE);

		foreach ($aSeo_Pages as $oSeo_Page)
		{
			$page = $oSeo_Page->value;

			if (!isset($aTmpPages[$page]))
			{
				$aTmpPages[$page] = array('total' => 0);
			}

			$aTmpPages[$page][$oSeo_Site->Seo_Driver->driver]['clicks'] = $oSeo_Page->clicks;
			$aTmpPages[$page][$oSeo_Site->Seo_Driver->driver]['shows'] = $oSeo_Page->shows;

			$aTmpPages[$page]['total'] += $oSeo_Page->clicks;
		}
	}
}

$aConfig = Core_Config::instance()->get('seo_config', array()) + array(
	'topQueriesLimit' => 100,
	'topPagesLimit' => 100
);

uasort($aTmpQueries, "mysort");
$aTmpQueries = array_slice($aTmpQueries, 0, Core_Array::get($aConfig, 'topQueriesLimit', 100));
$iCount = count($aTmpQueries);
$aTmpQueriesFirstBlock = array_slice($aTmpQueries, 0, round($iCount/2));
$aTmpQueriesSecondBlock = array_slice($aTmpQueries, round($iCount/2), round($iCount/2));

uasort($aTmpPages, "mysort");
$aTmpPages = array_slice($aTmpPages, 0, Core_Array::get($aConfig, 'topPagesLimit', 100));
$iCount = count($aTmpPages);
$aTmpPagesFirstBlock = array_slice($aTmpPages, 0, round($iCount/2));
$aTmpPagesSecondBlock = array_slice($aTmpPages, round($iCount/2), round($iCount/2));

function showBlock($aTmpQueries, $aSeo_Sites, $counter)
{
?>
	<div class="well">
		<table class="table table-hover table-popular-queries">
			<tbody>
			<?php
				foreach ($aTmpQueries as $query => $aTmpQuery)
				{
					if (strpos($query, 'http://') !== FALSE || strpos($query, 'https://') !== FALSE)
					{
						$url = "<a href='" . $query . "' target='_blank'>" . Core_Str::cut($query, 50) . "</a>";
					}
					else
					{
						$url = $query;
					}
					?>
					<tr>
						<td><?php echo $counter?></td>
						<td width="50%"><?php echo $url?></td>
						<?php
						foreach ($aSeo_Sites as $oSeo_Site)
						{
							$driver = $oSeo_Site->Seo_Driver->driver;
							$oSeo_Driver_Controller = Seo_Controller::instance($driver);
							?>
							<td class="seo-icon">
							<?php
							if (isset($aTmpQuery[$driver]))
							{
								echo $oSeo_Driver_Controller->getIcon();

								?>
								<div class="seo-driver-data">
									<span class="seo-driver-data-click" title="<?php echo Core::_('Seo.seo_driver_data_click')?>"><?php echo $aTmpQuery[$driver]['clicks']?></span>
									<span> / </span>
									<span class="seo-driver-data-show" title="<?php echo Core::_('Seo.seo_driver_data_show')?>"><?php echo $aTmpQuery[$driver]['shows']?></span>
								</div>
								<?php
							}
							?>
							</td>
							<?php
						}
						?>
					</tr>
					<?php
					$counter++;
				}
			?>
			</tbody>
		</table>
	</div>
<?php
}

$iBeginTimestamp = strtotime("-6 month");

$aLinks = $aRatings = $aSearchable = $aDownloaded = $aDownloaded2xx
	= $aDownloaded3xx = $aDownloaded4xx = $aDownloaded5xx = $aFailed = $aExcluded = array();

foreach ($aSeo_Sites as $oSeo_Site)
{
	// Seo Links
	$oSeo_Links = $oSeo_Site->Seo_Links;
	$oSeo_Links
		->queryBuilder()
		->where('date', '>=', date('Y-m-d', $iBeginTimestamp));

	$aSeo_Links = $oSeo_Links->findAll(FALSE);

	foreach ($aSeo_Links as $oSeo_Link)
	{
		$index = "'" . $oSeo_Link->date . "'";

		$aLinks[$oSeo_Site->id][$index] = $oSeo_Link->value;
	}

	// Seo Ratings
	$oSeo_Ratings = $oSeo_Site->Seo_Ratings;
	$oSeo_Ratings
		->queryBuilder()
		->where('date', '>=', date('Y-m-d', $iBeginTimestamp));

	$aSeo_Ratings = $oSeo_Ratings->findAll(FALSE);

	foreach ($aSeo_Ratings as $oSeo_Rating)
	{
		$index = "'" . $oSeo_Rating->date . "'";

		$aRatings[$oSeo_Site->id][$index] = $oSeo_Rating->value;
	}

	// Seo Indexed
	$oSeo_Indexeds = $oSeo_Site->Seo_Indexeds;
	$oSeo_Indexeds
		->queryBuilder()
		->where('date', '>=', date('Y-m-d', $iBeginTimestamp));

	$aSeo_Indexeds = $oSeo_Indexeds->findAll(FALSE);

	foreach ($aSeo_Indexeds as $oSeo_Indexed)
	{
		$index = "'" . $oSeo_Indexed->date . "'";

		$aSearchable[$oSeo_Site->id][$index] = $oSeo_Indexed->searchable;
		$aDownloaded[$oSeo_Site->id][$index] = $oSeo_Indexed->downloaded;
		$aDownloaded2xx[$oSeo_Site->id][$index] = $oSeo_Indexed->downloaded_2xx;
		$aDownloaded3xx[$oSeo_Site->id][$index] = $oSeo_Indexed->downloaded_3xx;
		$aDownloaded4xx[$oSeo_Site->id][$index] = $oSeo_Indexed->downloaded_4xx;
		$aDownloaded5xx[$oSeo_Site->id][$index] = $oSeo_Indexed->downloaded_5xx;
		$aFailed[$oSeo_Site->id][$index] = $oSeo_Indexed->failed_to_download;
		$aExcluded[$oSeo_Site->id][$index] = $oSeo_Indexed->excluded;
	}
}

$aKeys = array_keys($aSeo_Sites);
$last_key = end($aKeys);

if (count($aTmpQueriesFirstBlock))
{
?>
<div class="row">
	<div class="col-xs-12">
		<h5 class="row-title before-darkorange">
			<i class="fa fa-question-circle-o darkorange"></i>
			<?php echo Core::_('Seo.popular_query_header')?>
		</h5>
	</div>
</div>
<div class="row">
	<div class="col-xs-12 col-md-6">
		<?php showBlock($aTmpQueriesFirstBlock, $aSeo_Sites, 1)?>
	</div>
	<div class="col-xs-12 col-md-6">
		<?php showBlock($aTmpQueriesSecondBlock, $aSeo_Sites, round($iCount/2 + 1))?>
	</div>
</div>
<?php
}

if (count($aTmpPagesFirstBlock))
{
?>
<div class="row">
	<div class="col-xs-12">
		<h5 class="row-title before-azure">
			<i class="fa fa-file-text-o azure"></i>
			<?php echo Core::_('Seo.popular_page_header')?>
		</h5>
	</div>
</div>
<div class="row">
	<div class="col-xs-12 col-md-6">
		<?php showBlock($aTmpPagesFirstBlock, $aSeo_Sites, 1)?>
	</div>
	<div class="col-xs-12 col-md-6">
		<?php showBlock($aTmpPagesSecondBlock, $aSeo_Sites, round($iCount/2 + 1))?>
	</div>
</div>
<?php
}

if (count($aLinks))
{
?>
<div class="row">
	<div class="col-xs-12">
		<h5 class="row-title before-palegreen">
			<i class="fa fa-external-link palegreen"></i>
			<?php echo Core::_('Seo.external_link_header')?>
		</h5>
	</div>
</div>
<div class="row">
	<div class="col-xs-12">
		<div class="widget counter">
			<div class="widget-body">
				<div id="seo-links">
					<div class="row">
						<div class="col-sm-12">
							<div id="seo-links-chart" class="chart chart-lg"></div>
						</div>
					</div>
					<div class="row">
						<div class="col-xs-12">
							<div class="col-sm-12 col-md-6">
								<button class="btn btn-palegreen" id="setOriginalZoom"><i class="fa fa-area-chart icon-separator"></i><?php echo Core::_('Seo.reset')?></button>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php
}

if (count($aRatings))
{
?>
<div class="row">
	<div class="col-xs-12">
		<h5 class="row-title before-info">
			<i class="fa fa-line-chart info"></i>
			<?php echo Core::_('Seo.tic_header')?>
		</h5>
	</div>
</div>
<div class="row">
	<div class="col-xs-12">
		<div class="widget counter">
			<div class="widget-body">
				<div id="seo-ratings">
					<div class="row">
						<div class="col-sm-12">
							<div id="seo-ratings-chart" class="chart chart-lg"></div>
						</div>
					</div>
					<div class="row">
						<div class="col-xs-12">
							<div class="col-sm-12 col-md-6">
								<button class="btn btn-palegreen" id="setOriginalZoom"><i class="fa fa-area-chart icon-separator"></i><?php echo Core::_('Seo.reset')?></button>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php
}

if (count($aSearchable))
{
?>
<div class="row">
	<div class="col-xs-12">
		<h5 class="row-title before-magenta">
			<i class="fa fa-database magenta"></i>
			<?php echo Core::_('Seo.indexed_header')?>
		</h5>
	</div>
</div>
<div class="row">
	<div class="col-xs-12">
		<div class="widget counter">
			<div class="widget-body">
				<div id="seo-indexed">
					<div class="row">
						<div class="col-sm-12">
							<div id="seo-indexed-chart" class="chart chart-lg"></div>
						</div>
					</div>
					<div class="row">
						<div class="col-xs-12">
							<div class="col-sm-12 col-md-6">
								<button class="btn btn-palegreen" id="setOriginalZoom"><i class="fa fa-area-chart icon-separator"></i><?php echo Core::_('Seo.reset')?></button>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php
}

if (count($aSeo_Sites))
{
	$windowId = $oAdmin_Form_Controller->getWindowId();

?><script type="text/javascript">
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
		<?php
		foreach ($aSeo_Sites as $oSeo_Site)
		{
			if (isset($aLinks[$oSeo_Site->id]))
			{
			?>
			var title_links<?php echo $oSeo_Site->id?> = [<?php echo implode(',', array_keys($aLinks[$oSeo_Site->id]))?>],
			link_values<?php echo $oSeo_Site->id?> = [<?php echo implode(',', array_values($aLinks[$oSeo_Site->id]))?>],
			valueTitlesLinks<?php echo $oSeo_Site->id?> = new Array();
			<?php
			}

			if (isset($aRatings[$oSeo_Site->id]))
			{
			?>
			var title_ratings<?php echo $oSeo_Site->id?> = [<?php echo implode(',', array_keys($aRatings[$oSeo_Site->id]))?>],
			rating_values<?php echo $oSeo_Site->id?> = [<?php echo implode(',', array_values($aRatings[$oSeo_Site->id]))?>],
			valueTitlesRatings<?php echo $oSeo_Site->id?> = new Array();
			<?php
			}

			if (isset($aSearchable[$oSeo_Site->id]))
			{
			?>
			var title_indexed<?php echo $oSeo_Site->id?> = [<?php echo implode(',', array_keys($aSearchable[$oSeo_Site->id]))?>],
			searchable_values<?php echo $oSeo_Site->id?> = [<?php echo implode(',', array_values($aSearchable[$oSeo_Site->id]))?>],
			downloaded_values<?php echo $oSeo_Site->id?> = [<?php echo implode(',', array_values($aDownloaded[$oSeo_Site->id]))?>],
			downloaded2xx_values<?php echo $oSeo_Site->id?> = [<?php echo implode(',', array_values($aDownloaded2xx[$oSeo_Site->id]))?>],
			downloaded3xx_values<?php echo $oSeo_Site->id?> = [<?php echo implode(',', array_values($aDownloaded3xx[$oSeo_Site->id]))?>],
			downloaded4xx_values<?php echo $oSeo_Site->id?> = [<?php echo implode(',', array_values($aDownloaded4xx[$oSeo_Site->id]))?>],
			downloaded5xx_values<?php echo $oSeo_Site->id?> = [<?php echo implode(',', array_values($aDownloaded5xx[$oSeo_Site->id]))?>],
			failed_values<?php echo $oSeo_Site->id?> = [<?php echo implode(',', array_values($aFailed[$oSeo_Site->id]))?>],
			excluded_values<?php echo $oSeo_Site->id?> = [<?php echo implode(',', array_values($aExcluded[$oSeo_Site->id]))?>],
			valueTitlesSearchable<?php echo $oSeo_Site->id?> = new Array(),
			valueTitlesDownloaded<?php echo $oSeo_Site->id?> = new Array(),
			valueTitlesDownloaded2xx<?php echo $oSeo_Site->id?> = new Array(),
			valueTitlesDownloaded3xx<?php echo $oSeo_Site->id?> = new Array(),
			valueTitlesDownloaded4xx<?php echo $oSeo_Site->id?> = new Array(),
			valueTitlesDownloaded5xx<?php echo $oSeo_Site->id?> = new Array(),
			valueTitlesFailed<?php echo $oSeo_Site->id?> = new Array(),
			valueTitlesExcluded<?php echo $oSeo_Site->id?> = new Array();
			<?php
			}
		}

		foreach ($aSeo_Sites as $oSeo_Site)
		{
			if (isset($aLinks[$oSeo_Site->id]))
			{
			?>
			for(var i = 0; i < link_values<?php echo $oSeo_Site->id?>.length; i++) {
				valueTitlesLinks<?php echo $oSeo_Site->id?>.push([new Date(title_links<?php echo $oSeo_Site->id?>[i]), link_values<?php echo $oSeo_Site->id?>[i]]);
			}
			<?php
			}

			if (isset($aRatings[$oSeo_Site->id]))
			{
			?>
			for(var i = 0; i < rating_values<?php echo $oSeo_Site->id?>.length; i++) {
				valueTitlesRatings<?php echo $oSeo_Site->id?>.push([new Date(title_ratings<?php echo $oSeo_Site->id?>[i]), rating_values<?php echo $oSeo_Site->id?>[i]]);
			}
			<?php
			}

			if (isset($aSearchable[$oSeo_Site->id]))
			{
			?>
			for(var i = 0; i < searchable_values<?php echo $oSeo_Site->id?>.length; i++) {
				valueTitlesSearchable<?php echo $oSeo_Site->id?>.push([new Date(title_indexed<?php echo $oSeo_Site->id?>[i]), searchable_values<?php echo $oSeo_Site->id?>[i]]);
				valueTitlesDownloaded<?php echo $oSeo_Site->id?>.push([new Date(title_indexed<?php echo $oSeo_Site->id?>[i]), downloaded_values<?php echo $oSeo_Site->id?>[i]]);
				valueTitlesDownloaded2xx<?php echo $oSeo_Site->id?>.push([new Date(title_indexed<?php echo $oSeo_Site->id?>[i]), downloaded2xx_values<?php echo $oSeo_Site->id?>[i]]);
				valueTitlesDownloaded3xx<?php echo $oSeo_Site->id?>.push([new Date(title_indexed<?php echo $oSeo_Site->id?>[i]), downloaded3xx_values<?php echo $oSeo_Site->id?>[i]]);
				valueTitlesDownloaded4xx<?php echo $oSeo_Site->id?>.push([new Date(title_indexed<?php echo $oSeo_Site->id?>[i]), downloaded4xx_values<?php echo $oSeo_Site->id?>[i]]);
				valueTitlesDownloaded5xx<?php echo $oSeo_Site->id?>.push([new Date(title_indexed<?php echo $oSeo_Site->id?>[i]), downloaded5xx_values<?php echo $oSeo_Site->id?>[i]]);
				valueTitlesFailed<?php echo $oSeo_Site->id?>.push([new Date(title_indexed<?php echo $oSeo_Site->id?>[i]), failed_values<?php echo $oSeo_Site->id?>[i]]);
				valueTitlesExcluded<?php echo $oSeo_Site->id?>.push([new Date(title_indexed<?php echo $oSeo_Site->id?>[i]), excluded_values<?php echo $oSeo_Site->id?>[i]]);
			}
			<?php
			}
		}
		?>

		var gridbordercolor = "#eee", dataLinks = [
			<?php

			foreach ($aSeo_Sites as $key => $oSeo_Site)
			{
				$oSeo_Driver_Controller = Seo_Controller::instance($oSeo_Site->Seo_Driver->driver);

				if (isset($aLinks[$oSeo_Site->id]) && count($aLinks[$oSeo_Site->id]))
				{
					?>{
						color: "<?php echo $oSeo_Driver_Controller->getColor()?>",
						label: "<?php echo htmlspecialchars($oSeo_Site->Seo_Driver->name)?>",
						data: valueTitlesLinks<?php echo $oSeo_Site->id?>
					}<?php

					if ($key != $last_key)
					{
						echo ", ";
					}
				}
			}
			?>
		], dataRatings = [
			<?php

			foreach ($aSeo_Sites as $key => $oSeo_Site)
			{
				$oSeo_Driver_Controller = Seo_Controller::instance($oSeo_Site->Seo_Driver->driver);

				if (isset($aRatings[$oSeo_Site->id]) && count($aRatings[$oSeo_Site->id]))
				{
					?>{
						color: "<?php echo $oSeo_Driver_Controller->getColor()?>",
						label: "<?php echo htmlspecialchars($oSeo_Driver_Controller->getRatingName())?>",
						data: valueTitlesRatings<?php echo $oSeo_Site->id?>
					}<?php

					if ($key != $last_key)
					{
						echo ", ";
					}
				}
			}
			?>
		], dataIndexed = [
			<?php
			foreach ($aSeo_Sites as $key => $oSeo_Site)
			{
				if (isset($aSearchable[$oSeo_Site->id]) && count($aSearchable[$oSeo_Site->id]))
				{
					?>{
						color: "#A0D468",
						label: "<?php echo Core::_('Seo.searchable')?>",
						data: valueTitlesSearchable<?php echo $oSeo_Site->id?>
					}<?php

					if ($key != $last_key)
					{
						echo ", ";
					}
				}

				if (isset($aDownloaded[$oSeo_Site->id]) && count($aDownloaded[$oSeo_Site->id]))
				{
					?>{
						color: "#2DC3E8",
						label: "<?php echo Core::_('Seo.downloaded')?>",
						data: valueTitlesDownloaded<?php echo $oSeo_Site->id?>
					}<?php

					if ($key != $last_key)
					{
						echo ", ";
					}
				}

				if (isset($aDownloaded2xx[$oSeo_Site->id]) && count($aDownloaded2xx[$oSeo_Site->id]))
				{
					?>{
						color: "#E0FF92",
						label: "<?php echo Core::_('Seo.downloaded2xx')?>",
						data: valueTitlesDownloaded2xx<?php echo $oSeo_Site->id?>
					}<?php

					if ($key != $last_key)
					{
						echo ", ";
					}
				}

				if (isset($aDownloaded3xx[$oSeo_Site->id]) && count($aDownloaded3xx[$oSeo_Site->id]))
				{
					?>{
						color: "#FFCE55",
						label: "<?php echo Core::_('Seo.downloaded3xx')?>",
						data: valueTitlesDownloaded3xx<?php echo $oSeo_Site->id?>
					}<?php

					if ($key != $last_key)
					{
						echo ", ";
					}
				}

				if (isset($aDownloaded4xx[$oSeo_Site->id]) && count($aDownloaded4xx[$oSeo_Site->id]))
				{
					?>{
						color: "#ff0000",
						label: "<?php echo Core::_('Seo.downloaded4xx')?>",
						data: valueTitlesDownloaded4xx<?php echo $oSeo_Site->id?>
					}<?php

					if ($key != $last_key)
					{
						echo ", ";
					}
				}

				if (isset($aDownloaded5xx[$oSeo_Site->id]) && count($aDownloaded5xx[$oSeo_Site->id]))
				{
					?>{
						color: "#FB6E52",
						label: "<?php echo Core::_('Seo.downloaded5xx')?>",
						data: valueTitlesDownloaded5xx<?php echo $oSeo_Site->id?>
					}<?php

					if ($key != $last_key)
					{
						echo ", ";
					}
				}

				if (isset($aFailed[$oSeo_Site->id]) && count($aFailed[$oSeo_Site->id]))
				{
					?>{
						color: "#D73D32",
						label: "<?php echo Core::_('Seo.failed')?>",
						data: valueTitlesFailed<?php echo $oSeo_Site->id?>
					}<?php

					if ($key != $last_key)
					{
						echo ", ";
					}
				}

				if (isset($aExcluded[$oSeo_Site->id]) && count($aExcluded[$oSeo_Site->id]))
				{
					?>{
						color: "#999999",
						label: "<?php echo Core::_('Seo.excluded')?>",
						data: valueTitlesExcluded<?php echo $oSeo_Site->id?>
					}<?php

					if ($key != $last_key)
					{
						echo ", ";
					}
				}
			}
			?>
		];

		var options = {
			series: {
				lines: {
					show: true
				},
				points: {
					show: true,
					radius: 1
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
			},
		};

		// Links
		<?php
		if (count($aLinks))
		{
			?>
			var placeholderSeoLinks = $("#<?php echo $windowId?> #seo-links-chart");

			placeholderSeoLinks.bind("plotselected", function (event, ranges) {
				plotSeoLinks = $.plot(placeholderSeoLinks, dataLinks, $.extend(true, {}, options, {
					xaxis: {
						min: ranges.xaxis.from,
						max: ranges.xaxis.to
					}
				}));
			});

			$('#<?php echo $windowId?> #seo-links #setOriginalZoom').on('click', function(){
				plotSeoLinks = $.plot(placeholderSeoLinks, dataLinks, options);
			});

			var plotSeoLinks = $.plot(placeholderSeoLinks, dataLinks, options);

			$("#<?php echo $windowId?> #seo-links #clearSelection").click(function () {
				plotSeoLinks.clearSelection();
			});
			<?php
		}
		?>

		// Rating
		<?php
		if (count($aRatings))
		{
			?>
			var placeholderSeoRatings = $("#<?php echo $windowId?> #seo-ratings-chart");

			placeholderSeoRatings.bind("plotselected", function (event, ranges) {
				plotSeoRatings = $.plot(placeholderSeoRatings, dataRatings, $.extend(true, {}, options, {
					xaxis: {
						min: ranges.xaxis.from,
						max: ranges.xaxis.to
					}
				}));
			});

			$('#<?php echo $windowId?> #seo-ratings #setOriginalZoom').on('click', function(){
				plotSeoRatings = $.plot(placeholderSeoRatings, dataRatings, options);
			});

			var plotSeoRatings = $.plot(placeholderSeoRatings, dataRatings, options);

			$("#<?php echo $windowId?> #seo-ratings #clearSelection").click(function () {
				plotSeoRatings.clearSelection();
			});
			<?php
		}
		?>

		// Indexed
		<?php
		if (count($aSearchable))
		{
			?>
			var placeholderSeoIndexed = $("#<?php echo $windowId?> #seo-indexed-chart");

			placeholderSeoIndexed.bind("plotselected", function (event, ranges) {
				plotSeoIndexed = $.plot(placeholderSeoIndexed, dataIndexed, $.extend(true, {}, options, {
					xaxis: {
						min: ranges.xaxis.from,
						max: ranges.xaxis.to
					}
				}));
			});

			$('#<?php echo $windowId?> #seo-indexed #setOriginalZoom').on('click', function(){
				plotSeoIndexed = $.plot(placeholderSeoIndexed, dataIndexed, options);
			});

			var plotSeoIndexed = $.plot(placeholderSeoIndexed, dataIndexed, options);

			$("#<?php echo $windowId?> #seo-indexed #clearSelection").click(function () {
				plotSeoIndexed.clearSelection();
			});
			<?php
		}
		?>
		});
	});
</script>
<?php
}

$oAdmin_Form_Controller->addEntity(
	Admin_Form_Entity::factory('Code')
		->html(ob_get_clean())
);

// Источник данных 0
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(Core_Entity::factory('Seo_Site'));

$oAdmin_Form_Dataset->addCondition(
	array('where' =>
		array('site_id', '=', CURRENT_SITE)
	)
);

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset($oAdmin_Form_Dataset);

// Показ формы
$oAdmin_Form_Controller->execute();