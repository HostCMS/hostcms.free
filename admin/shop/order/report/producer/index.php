<?php

/**
* Online shop.
*
* @package HostCMS
* @version 6.x
* @author Hostmake LLC
* @copyright © 2005-2019 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
*/
require_once('../../../../../bootstrap.php');

Core_Auth::authorization($sModule = 'shop');

// Получаем параметры, getRequest, т.к. изначально данные идут GET'ом, затем hidden-полями формы, т.е. POST'ом
$oShop = Core_Entity::factory('Shop', Core_Array::getRequest('shop_id', 0));
$oShopDir = $oShop->Shop_Dir;
$oShopGroup = Core_Entity::factory('Shop_Group', Core_Array::getRequest('shop_group_id', 0));

$oAdmin_Form_Controller = Admin_Form_Controller::create();
$oAdmin_Form_Entity_Breadcrumbs = Admin_Form_Entity::factory('Breadcrumbs');

// Контроллер формы
$oAdmin_Form_Controller->module(Core_Module::factory($sModule))->setUp()->path('/admin/shop/order/report/producer/index.php');

$sSuffix = $oShop->Shop_Currency->name;

ob_start();

$oAdmin_View = Admin_View::create();
$oAdmin_View
	->module(Core_Module::factory($sModule));

// Первая крошка на список магазинов
$oAdmin_Form_Entity_Breadcrumbs
	->add(Admin_Form_Entity::factory('Breadcrumb')
	->name(Core::_('Shop.menu'))
	->href($oAdmin_Form_Controller->getAdminLoadHref('/admin/shop/index.php'))
	->onclick($oAdmin_Form_Controller->getAdminLoadAjax('/admin/shop/index.php')))
;

// Крошки по директориям магазинов
if ($oShopDir->id)
{
	$oShopDirBreadcrumbs = $oShopDir;

	$aBreadcrumbs = array();

	do
	{
		$aBreadcrumbs[] = Admin_Form_Entity::factory('Breadcrumb')
			->name($oShopDirBreadcrumbs->name)
			->href($oAdmin_Form_Controller->getAdminLoadHref(
					'/admin/shop/index.php', NULL, NULL, "shop_dir_id={$oShopDirBreadcrumbs->id}"))
		->onclick($oAdmin_Form_Controller->getAdminLoadAjax(
					'/admin/shop/index.php', NULL, NULL, "shop_dir_id={$oShopDirBreadcrumbs->id}"));
	}while ($oShopDirBreadcrumbs = $oShopDirBreadcrumbs->getParent());

	$aBreadcrumbs = array_reverse($aBreadcrumbs);

	foreach ($aBreadcrumbs as $oBreadcrumb)
	{
		$oAdmin_Form_Entity_Breadcrumbs->add($oBreadcrumb);
	}
}

// Крошка на список товаров и групп товаров магазина
$oAdmin_Form_Entity_Breadcrumbs
	->add(Admin_Form_Entity::factory('Breadcrumb')
		->name($oShop->name)
		->href($oAdmin_Form_Controller->getAdminLoadHref('/admin/shop/item/index.php', NULL, NULL, "shop_id={$oShop->id}"))
	->onclick($oAdmin_Form_Controller->getAdminLoadAjax('/admin/shop/item/index.php', NULL, NULL, "shop_id={$oShop->id}")));

// Крошки по группам товаров
if ($oShopGroup->id)
{
	$oShopGroupBreadcrumbs = $oShopGroup;
	$aBreadcrumbs = array();

	do
	{
		$aBreadcrumbs[] = Admin_Form_Entity::factory('Breadcrumb')
			->name($oShopGroupBreadcrumbs->name)
			->href($oAdmin_Form_Controller->getAdminLoadHref(
				'/admin/shop/item/index.php', NULL, NULL, "shop_id={$oShop->id}&shop_group_id={$oShopGroupBreadcrumbs->id}"))
		->onclick($oAdmin_Form_Controller->getAdminLoadAjax(
					'/admin/shop/item/index.php', NULL, NULL, "shop_id={$oShop->id}&shop_group_id={$oShopGroupBreadcrumbs->id}"));
	}while ($oShopGroupBreadcrumbs = $oShopGroupBreadcrumbs->getParent());

	$aBreadcrumbs = array_reverse($aBreadcrumbs);

	foreach ($aBreadcrumbs as $oBreadcrumb)
	{
		$oAdmin_Form_Entity_Breadcrumbs->add($oBreadcrumb);
	}
}

// Крошка на текущую форму
$oAdmin_Form_Entity_Breadcrumbs->add(Admin_Form_Entity::factory('Breadcrumb')
	->name(Core::_('Shop_Item.show_brands_order_link'))
	->href($oAdmin_Form_Controller->getAdminLoadHref(
		$oAdmin_Form_Controller->getPath(), NULL, NULL, "shop_id={$oShop->id}&shop_group_id={$oShopGroup->id}"))
	->onclick($oAdmin_Form_Controller->getAdminLoadAjax(
		$oAdmin_Form_Controller->getPath(), NULL, NULL, "shop_id={$oShop->id}&shop_group_id={$oShopGroup->id}")));

$oAdmin_Form_Entity_Form = Admin_Form_Entity::factory('Form')
	->controller($oAdmin_Form_Controller)
	->action($oAdmin_Form_Controller->getPath())
	->target('_blank');

$oAdmin_View->addChild($oAdmin_Form_Entity_Breadcrumbs);


// Обработка данных формы
if (!is_null(Core_Array::getPost('do_show_report')))
{
	$iDateFrom = Core_Date::datetime2timestamp(Core_Array::getPost('sales_order_begin_date') . ' 00:00:00');
	$iDateTo = Core_Date::datetime2timestamp(Core_Array::getPost('sales_order_end_date') . ' 23:59:59');

	$limitProducers = Core_Array::getPost('sales_order_show_producers_limit');

	$aOrdered = array();

	switch (Core_Array::getPost('sales_order_grouping'))
	{
		case 1: // группировка по неделям
			$sFormat = '%U %Y';
			$inc = '+1 week';
		break;
		case 2: // группировка по дням
			$sFormat = '%d %m %Y';
			$inc = '+1 day';
		break;
		default: // группировка по месяцам
			$sFormat = '%m %Y';
			$inc = '+1 month';
		break;
	}

	for ($iTmp = $iDateFrom; $iTmp <= $iDateTo; $iTmp = strtotime($inc, $iTmp))
	{
		$aOrdered[strftime($sFormat, $iTmp)] = array();
	}

	$aOrderedSum = $aOrdered;

	$sDateFrom = Core_Date::timestamp2sql($iDateFrom);
	$sDateTo = Core_Date::timestamp2sql($iDateTo);

	$aTotalProducers = array();

	$oDefault_Currency = $oShop->Shop_Currency;

	$limit = 10000;
	$offset = 0;

	do {
		$oQueryBuilderSelect = Core_QueryBuilder::select(
			'shop_orders.*',
			'shop_order_items.shop_item_id',
			'shop_order_items.quantity',
			'shop_order_items.price',
			'shop_order_items.rate',
			'shop_items.shop_producer_id'
		);

		$oQueryBuilderSelect
			->from('shop_orders')
			->join('shop_order_items', 'shop_orders.id', '=', 'shop_order_items.shop_order_id')
			->join('shop_items', 'shop_items.id', '=', 'shop_order_items.shop_item_id')
			->where('shop_orders.shop_id', '=', $oShop->id)
			->where('shop_orders.canceled', '=', 0)
			->where('shop_orders.deleted', '=', 0)
			->where('shop_order_items.deleted', '=', 0)
			->where('shop_orders.datetime', 'BETWEEN', array($sDateFrom, $sDateTo))
			->where('shop_items.shop_producer_id', '!=', 0)
			->clearOrderBy()
			->orderBy('shop_orders.id', 'ASC')
			->limit($limit)
			->offset($offset);

		if (!is_null(Core_Array::getPost('sales_order_show_only_paid_items')))
		{
			$oQueryBuilderSelect->where('shop_orders.paid', '=', 1);
		}

		$aOrdersResult = $oQueryBuilderSelect->execute()->asAssoc()->result(FALSE);

		foreach ($aOrdersResult as $aRow)
		{
			!isset($aTotalProducers[$aRow['shop_producer_id']]) && $aTotalProducers[$aRow['shop_producer_id']] = 0;
			$aTotalProducers[$aRow['shop_producer_id']] += $aRow['quantity'];

			$sTmpTime = strftime($sFormat, Core_Date::sql2timestamp($aRow['datetime']));

			!isset($aOrdered[$sTmpTime][$aRow['shop_producer_id']]) && $aOrdered[$sTmpTime][$aRow['shop_producer_id']] = 0;
			$aOrdered[$sTmpTime][$aRow['shop_producer_id']] += $aRow['quantity'];

			$fTax = Shop_Controller::instance()->round($aRow['price'] * $aRow['rate'] / 100);
			$fAmount = Shop_Controller::instance()->round($aRow['price'] + $fTax);

			$fCurrencyCoefficient = $aRow['shop_currency_id'] > 0 && $oDefault_Currency->id > 0
				? Shop_Controller::instance()->getCurrencyCoefficientInShopCurrency(
					Core_Entity::factory('Shop_Currency', $aRow['shop_currency_id']), $oDefault_Currency
				)
				: 0;

			!isset($aOrderedSum[$sTmpTime][$aRow['shop_producer_id']]) && $aOrderedSum[$sTmpTime][$aRow['shop_producer_id']] = 0;
			$aOrderedSum[$sTmpTime][$aRow['shop_producer_id']] += $fAmount * $fCurrencyCoefficient;
		}

		$offset += $limit;
	} while (count($aOrdersResult));

	arsort($aTotalProducers);

	$aTotalProducers = array_slice($aTotalProducers, 0, $limitProducers, TRUE);

	$aClearArray = array_fill_keys(array_keys($aTotalProducers), 0);

	foreach ($aOrdered as $key => $value)
	{
		$aOrdered[$key] += $aClearArray;
		$aOrderedSum[$key] += $aClearArray;
	}

	$sDateFrom  = Core_Date::sql2date($sDateFrom);
	$sDateTo  = Core_Date::sql2date($sDateTo);

	// Заголовок
	$oAdmin_View->pageTitle(Core::_('Shop_Item.sales_report_brands_title', $oShop->name, $sDateFrom, $sDateTo, ""));
	?>
	<div class="widget counter">
		<div class="widget-body">
			<div class="row">
				<div class="col-xs-12">
					<div id="shopOrdersReportDiagram" class="chart chart-lg"></div>
				</div>
			</div>
			<div class="row">
				<div class="col-xs-12 margin-top-20">
					<div class="col-sm-12 col-md-6">
						<button class="btn btn-palegreen" id="setOriginalZoom"><i class="fa fa-area-chart icon-separator"></i><?php echo Core::_('Shop_Item.reset')?></button>
						<button class="btn btn-azure margin-left-10" id="setLegend"><i class="fa fa-bars icon-separator"></i><?php echo Core::_('Shop_Item.legend')?></button>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php

	if (count($aOrdered))
	{
		?>
		<div class="widget counter">
			<div class="widget-body">
				<table  class="admin-table table table-bordered table-hover table-striped">
					<thead>
						<tr>
							<th colspan="2"></th>
							<th width="150"><?php echo Core::_('Shop_Item.form_sales_order_count_items')?></th>
							<th width="150"><?php echo Core::_('Shop_Item.form_sales_order_total_summ')?></th>
						</tr>
					</thead>
		<?php

		$aMonths = array(
			'01' => Core::_('Shop_Item.form_sales_order_month_january'),
			'02' => Core::_('Shop_Item.form_sales_order_month_february'),
			'03' => Core::_('Shop_Item.form_sales_order_month_march'),
			'04' => Core::_('Shop_Item.form_sales_order_month_april'),
			'05' => Core::_('Shop_Item.form_sales_order_month_may'),
			'06' => Core::_('Shop_Item.form_sales_order_month_june'),
			'07' => Core::_('Shop_Item.form_sales_order_month_july'),
			'08' => Core::_('Shop_Item.form_sales_order_month_august'),
			'09' => Core::_('Shop_Item.form_sales_order_month_september'),
			'10' => Core::_('Shop_Item.form_sales_order_month_october'),
			'11' => Core::_('Shop_Item.form_sales_order_month_november'),
			'12' => Core::_('Shop_Item.form_sales_order_month_december')
		);

		$iShopOrderItemsCount = 0;
		$iShopOrderItemsSum = 0;
		?>
		<tbody>
		<?php
		$aNewOrdered = array();

		foreach ($aOrdered as $key => $rowOrder)
		{
			$mas_date = explode(' ', $key);

			switch (Core_Array::getPost('sales_order_grouping'))
			{
				case 0: // группировка по месяцам
					// Разделяем месяц и год
					$period_title = Core_Array::get($aMonths, $mas_date[0]) . ' ' . $mas_date[1];
				break;
				case 1: // группировка по неделям
					$DayLen = 24 * 60 * 60;

					$WeekLen = 7 * $DayLen;

					$year = $mas_date[1]; //1993;
					$week = $mas_date[0]; //1;

					$StJ = gmmktime(0, 0, 0, 1, 1, $year); // 1 января, 00:00:00

					// Определим начало недели, к которой относится 1 января
					$DayStJ = gmdate("w", $StJ);
					$DayStJ = ($DayStJ == 0 ? 7 : $DayStJ);
					$StWeekJ = $StJ - ($DayStJ - 1) * $DayLen;

					// Если 1 января относится к 1й неделе, то в $week получается одна "лишняя" неделя
					if (gmdate("W", $StJ) == "01")
					{
						$week--;
					}

					// прибавили к началу "январской" недели номер нашей недели
					$start = $StWeekJ + $week * $WeekLen;

					// К началу прибавляем недели (получаем след. понедельник, 00:00) и отняли одну секунду - т.е. воскресенье, 23:59
					$end = $start + $WeekLen - 5 * 60 * 60;

					$period_title = $week . Core::_('Shop_Item.form_sales_order_week') . strftime(DATE_FORMAT, $start) . '&mdash;' . strftime(DATE_FORMAT, $end);
				break;
				default: // группировка по дням
					$period_title = implode('.', $mas_date);
				break;
			}

			$aNewOrdered[$period_title] = $rowOrder;
			?>
			<tr>
				<td colspan="2"><strong><?php echo $period_title?></strong></td>
				<td></td>
				<td></td>
			</tr>
			<?php
			arsort($rowOrder);

			foreach ($rowOrder as $shop_producer_id => $countItems)
			{
				?>
				<tr class="row_table report_height">
					<td colspan="2">
						<?php echo Core_Entity::factory('Shop_Producer', $shop_producer_id)->name?>
					</td>
					<td>
						<?php echo $countItems ?>
					</td>
					<td>
						<?php
						if (isset($aOrderedSum[$key][$shop_producer_id]))
						{
							echo $aOrderedSum[$key][$shop_producer_id];

							$iShopOrderItemsSum += $aOrderedSum[$key][$shop_producer_id];
						}
						?>
					</td>
				</tr>
				<?php
				$iShopOrderItemsCount += $countItems;
			}
		}
		?>
		<tr class="admin_table_filter row_table admin_table_sub_title">
			<td colspan="2" style="text-align: right; padding-right: 20px;">∑</td>
			<td><?php echo $iShopOrderItemsCount?></td>
			<td><?php echo htmlspecialchars($iShopOrderItemsSum . ' ' . $oShop->Shop_Currency->name)?></td>
		</tr>
					</tbody>
				</table>
			</div>
		</div>

		<script type="text/javascript">
			$(function(){
				/*Sets Themed Colors Based on Themes*/
				themeprimary = getThemeColorFromCss('themeprimary');
				themesecondary = getThemeColorFromCss('themesecondary');
				themethirdcolor = getThemeColorFromCss('themethirdcolor');
				themefourthcolor = getThemeColorFromCss('themefourthcolor');
				themefifthcolor = getThemeColorFromCss('themefifthcolor');

				<?php
				$aTmpData = array();

				$aColors = array('themeprimary', 'themesecondary', 'themethirdcolor', 'themefourthcolor', 'themefifthcolor');
				$iCountColors = count($aColors);

				$j = 0;

				foreach ($aTotalProducers as $iProducerId => $totalCount)
				{
					$aTmpValues = array();

					foreach ($aNewOrdered as $xValue => $aYValues)
					{
						$aTmpValues[] = '["' . $xValue . '", ' . $aYValues[$iProducerId] .']';
					}

					$aTmpData[] = '{
						color: ' . $aColors[$j % $iCountColors] . ',
						label: "' . htmlspecialchars(Core_Entity::factory('Shop_Producer', $iProducerId)->name) . '",
						data: [' . implode(',', $aTmpValues) . ']
					}';

					$j++;
				}

				?>

				var data = [<?php echo implode(",\n", $aTmpData)?>];

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
						noColumns: 5
					},
					xaxis: {
						color: gridbordercolor,
						mode: "categories"
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
						content: "<b>%s</b>: <span>%y</span>",
					},
					crosshair: {
						mode: "x"
					}
				};

				var placeholder = $("#shopOrdersReportDiagram");

				placeholder.bind("plotselected", function (event, ranges) {
					plot = $.plot(placeholder, data, $.extend(true, {}, options, {
						xaxis: {
							min: ranges.xaxis.from,
							max: ranges.xaxis.to
						}
					}));
				});

				$('#setOriginalZoom').on('click', function(){
					plot = $.plot(placeholder, data, options);
				});

				$('#setLegend').on('click', function(){
					$('.legend').toggleClass('hidden');
				});

				var plot = $.plot(placeholder, data, options);
			});
		</script>
		<?php
	}
	else
	{
		?><p><?php echo Core::_('Shop_Item.form_sales_order_empty_orders')?></p><?php
	}
}
else
{
	$oAdmin_View->pageTitle(Core::_('Shop_Item.show_brands_order_link'));

	$windowId = $oAdmin_Form_Controller->getWindowId();

	$oMainTab = Admin_Form_Entity::factory('Tab')->name('main');
	$oMainTab->add(Admin_Form_Entity::factory('Div')->class('row')->add(
		Admin_Form_Entity::factory('Radiogroup')
			->radio(array(Core::_('Shop_Item.form_sales_order_grouping_monthly'),
				Core::_('Shop_Item.form_sales_order_grouping_weekly'),
				Core::_('Shop_Item.form_sales_order_grouping_daily')
			))
			->ico(array(
				'fa-calendar',
				'fa-calendar',
				'fa-calendar'
			))
			->caption(Core::_('Shop_Item.form_sales_order_select_grouping'))
			->name('sales_order_grouping')
			->divAttr(array('id' => 'sales_order_grouping', 'class' => 'form-group col-xs-12'))
	))
	->add(Admin_Form_Entity::factory('Div')->class('row')->add(
		Admin_Form_Entity::factory('Date')
			->caption(Core::_('Shop_Item.form_sales_order_begin_date'))
			->name('sales_order_begin_date')
			->value(Core_Date::timestamp2sql(strtotime("-2 months")))
			->divAttr(array('class' => 'form-group col-xs-12 col-md-3'))
	)->add(
		Admin_Form_Entity::factory('Date')
			->caption(Core::_('Shop_Item.form_sales_order_end_date'))
			->name('sales_order_end_date')
			->value(Core_Date::timestamp2sql(time()))
			->divAttr(array('class' => 'form-group col-xs-12 col-md-3'))
	))
	->add(Admin_Form_Entity::factory('Div')->class('row')->add(
		Admin_Form_Entity::factory('Checkbox')
			->name('sales_order_show_only_paid_items')
			->caption(Core::_('Shop_Item.form_sales_order_show_paid_items'))
			->value(0)
			->divAttr(array('class' => 'form-group col-xs-12'))
	))
	->add(Admin_Form_Entity::factory('Div')->class('row')->add(
		Admin_Form_Entity::factory('Input')
			->name('sales_order_show_producers_limit')
			->caption(Core::_('Shop_Item.sales_order_show_producers_limit'))
			->value(10)
			->divAttr(array('class' => 'form-group col-xs-12 col-md-4'))
	))
	->add(
		Core::factory('Core_Html_Entity_Input')->type('hidden')->name('shop_id')->value(Core_Array::getGet('shop_id'))
	)->add(
		Core::factory('Core_Html_Entity_Input')->type('hidden')->name('shop_group_id')->value(Core_Array::getGet('shop_group_id'))
	)
	;

	$oAdmin_Form_Entity_Form->add($oMainTab);

	$oAdmin_Form_Entity_Form->add(
		Admin_Form_Entity::factory('Button')
		->name('do_show_report')
		->type('submit')
		->class('applyButton btn btn-blue')
	);

	$oAdmin_Form_Entity_Form->execute();
}

$content = ob_get_clean();

ob_start();
$oAdmin_View
	->content($content)
	->show();

Core_Skin::instance()
	->answer()
	->ajax(Core_Array::getRequest('_', FALSE))
	->content(ob_get_clean())
	->title(Core::_('Shop_Item.show_brands_order_link'))
	->execute();