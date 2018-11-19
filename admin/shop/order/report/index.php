<?php

/**
* Online shop.
*
* @package HostCMS
* @version 6.x
* @author Hostmake LLC
* @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
*/
require_once('../../../../bootstrap.php');

Core_Auth::authorization($sModule = 'shop');

// Получаем параметры, getRequest, т.к. изначально данные идут GET'ом, затем hidden-полями формы, т.е. POST'ом
$oShop = Core_Entity::factory('Shop', Core_Array::getRequest('shop_id', 0));
$oShopDir = $oShop->Shop_Dir;
$oShopGroup = Core_Entity::factory('Shop_Group', Core_Array::getRequest('shop_group_id', 0));

$oAdmin_Form_Controller = Admin_Form_Controller::create();
$oAdmin_Form_Entity_Breadcrumbs = Admin_Form_Entity::factory('Breadcrumbs');

// Контроллер формы
$oAdmin_Form_Controller->module(Core_Module::factory($sModule))->setUp()->path('/admin/shop/order/report/index.php');

$sSuffix = $oShop->Shop_Currency->name;

ob_start();

$oAdmin_View = Admin_View::create();
$oAdmin_View
	->module(Core_Module::factory($sModule))
	//->pageTitle(Core::_('Shop_Item.show_sales_order_link'))
	;

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
	->name(Core::_('Shop_Item.show_sales_order_link'))
	->href($oAdmin_Form_Controller->getAdminLoadHref(
		$oAdmin_Form_Controller->getPath(), NULL, NULL, "shop_id={$oShop->id}&shop_group_id={$oShopGroup->id}"))
	->onclick($oAdmin_Form_Controller->getAdminLoadAjax(
		$oAdmin_Form_Controller->getPath(), NULL, NULL, "shop_id={$oShop->id}&shop_group_id={$oShopGroup->id}")));

$oAdmin_Form_Entity_Form = Admin_Form_Entity::factory('Form')
	->controller($oAdmin_Form_Controller)
	->action($oAdmin_Form_Controller->getPath())
	->target('_blank');
//$oAdmin_Form_Entity_Form->add($oAdmin_Form_Entity_Breadcrumbs);
$oAdmin_View->addChild($oAdmin_Form_Entity_Breadcrumbs);

//////////////////////


// Обработка данных формы
if (!is_null(Core_Array::getPost('do_show_report')))
{
	$sDateFrom = Core_Date::datetime2sql(Core_Array::getPost('sales_order_begin_date') . ' 00:00:00');
	$sDateTo = Core_Date::datetime2sql(Core_Array::getPost('sales_order_end_date') . ' 23:59:59');

	$oQueryBuilderSelect = Core_QueryBuilder::select(array(Core_QueryBuilder::expression('COUNT(DISTINCT (shop_orders.id))'), 'count_orders'),
			array(Core_QueryBuilder::expression('SUM(quantity)'), 'count_items'),
			array(Core_QueryBuilder::expression('SUM(shop_order_items.price * quantity)'), 'total_sum'));

	switch(Core_Array::getPost('sales_order_grouping'))
	{
		case 1: // группировка по неделям
			$sFormatDateTitle = '%u';
			$oQueryBuilderSelect->select(array(Core_QueryBuilder::expression("DATE_FORMAT(shop_orders.datetime, '%Y')"), 'year_title'));
			break;
		case 2: // группировка по дням
			$sFormatDateTitle = '%d.%m.%Y';
			break;
		default: // группировка по месяцам
			$sFormatDateTitle = '%m %Y';
		break;
	}

	$oQueryBuilderSelect
		->select(array(Core_QueryBuilder::expression("DATE_FORMAT(shop_orders.datetime, '{$sFormatDateTitle}')"), 'date_title'))
		->select(array(Core_QueryBuilder::expression("DATE_FORMAT(shop_orders.datetime, '%Y')"), 'year'))
		->select(array(Core_QueryBuilder::expression("DATE_FORMAT(shop_orders.datetime, '%Y%m%d')"), 'order_title'))
		->from('shop_orders')
		->leftJoin('shop_order_items', 'shop_orders.id', '=', 'shop_order_items.shop_order_id', array(
				array('AND' => array('shop_order_items.deleted', '=', 0))
		))
		->where('shop_orders.shop_id', '=', $oShop->id)
		->where('shop_orders.canceled', '=', 0)
		->where('shop_orders.datetime', '>=', $sDateFrom)
		->where('shop_orders.datetime', '<=', $sDateTo)
		->groupBy('date_title')
		->groupBy('year')
		->orderBy('order_title')
	;

	if ($shop_system_of_pay_id = Core_Array::getPost('shop_system_of_pay_id',0))
	{
		$oQueryBuilderSelect->where('shop_orders.shop_payment_system_id', '=', $shop_system_of_pay_id);
	}

	if (!is_null($iSeller = Core_Array::getPost('shop_seller_id')) && $iSeller > 0)
	{
		$oQueryBuilderSelect
			->join('shop_items', 'shop_items.id', '=', 'shop_order_items.shop_item_id')
			->where('shop_items.shop_seller_id', '=', $iSeller);
	}

	if (!is_null(Core_Array::getPost('sales_order_show_only_paid_items')))
	{
		$oQueryBuilderSelect->where('shop_orders.paid', '=', 1);
	}

	if (Core::moduleIsActive('siteuser'))
	{
		if (!is_null($iSiteuserId = Core_Array::getPost('siteuser_id')) && $iSiteuserId > 0)
		{
			$oQueryBuilderSelect->where('shop_orders.siteuser_id', '=', $iSiteuserId);
		}
	}

	$iOrderStatusID = Core_Array::getPost('shop_order_status_id', 0);
	if ($iOrderStatusID != 0)
	{
		$oQueryBuilderSelect->where('shop_orders.shop_order_status_id', '=', $iOrderStatusID);
	}

	//echo $oQueryBuilderSelect->execute()->getLastQuery();
	$aOrdersResult = $oQueryBuilderSelect->execute()->asAssoc()->result();

	// Создаем второй запрос, отличается от первого выборкой полей в SELECT'е, и дополнительным GROUP BY
	$oQueryBuilderSelect
		->clearSelect()
		->select(array(Core_QueryBuilder::expression("DATE_FORMAT(shop_orders.datetime, '{$sFormatDateTitle}')"), 'date_title'), 'shop_orders.id')
		->select(array(Core_QueryBuilder::expression("DATE_FORMAT(shop_orders.datetime, '%Y')"), 'year'))
		->clearOrderBy()
		->groupBy('shop_order_items.shop_order_id');

	$aOrdersResultPeriod = $oQueryBuilderSelect->execute()->asAssoc()->result();

	$aOrdersResultPeriodParsed = array();

	foreach ($aOrdersResultPeriod as $aTmpArray)
	{
		$aOrdersResultPeriodParsed[
			$aTmpArray['date_title'].' ' . $aTmpArray['year']
		][] = $aTmpArray['id'];
	}

	$sDateFrom  = Core_Date::sql2date($sDateFrom);
	$sDateTo  = Core_Date::sql2date($sDateTo);

	// Заголовок
	$oAdmin_View
		->pageTitle(Core::_('Shop_Item.sales_report_title', $oShop->name, $sDateFrom, $sDateTo, ""));
	?>
	<div class="widget counter">
		<div class="widget-body">
			<div class="row">
				<div class="col-xs-12">
					<div id="shopOrdersReportDiagram" class="chart chart-lg"></div>
				</div>
			</div>
		</div>
	</div>
	<?php

	if (count($aOrdersResult) > 0)
	{
		?>
		<div class="widget counter">
			<div class="widget-body">
				<table  class="admin-table table table-bordered table-hover table-striped">
					<thead>
						<tr>
							<th></th>
							<th width="100"><?php echo Core::_('Shop_Item.catalog_marking')?></th>
							<th width="100"><?php echo Core::_('Shop_Item.form_sales_order_count_orders')?></th>
							<th width="100"><?php echo Core::_('Shop_Item.form_sales_order_count_items')?></th>
							<th width="150"><?php echo Core::_('Shop_Item.form_sales_order_total_summ')?></th>
							<th width="150"><?php echo Core::_('Shop_Item.form_sales_order_order_status')?></th>
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

		// Максимальное количесво подписей в легенде
		$iTotalNames = 4;
		$iSkipItems = intval(count($aOrdersResult) / $iTotalNames);
		$iSkipItems < 1 && $iSkipItems = 1;

		$i = 0;
		?>
		<script type="text/javascript">
			var shopOrdersReportDiagramData = [], sumOrdersValues = [], countItemsValues = [], countOrdersValues = [], timePeriodTitles = [];
		</script>
		<?php

		foreach ($aOrdersResult as $key => $rowOrdersResult)
		{
			switch (Core_Array::getPost('sales_order_grouping'))
			{
				case 0: // группировка по месяцам
					// Разделяем месяц и год
					$mas_date = explode(' ',$rowOrdersResult['date_title']);

					$period_title = Core_Array::get($aMonths, $mas_date[0]) . ' ' . $mas_date[1];
				break;
				case 1: // группировка по неделям
					$DayLen = 24 * 60 * 60;

					$WeekLen = 7 * $DayLen;

					$year = $rowOrdersResult['year_title'];//1993;
					$week = $rowOrdersResult['date_title'];//1;

					$StJ = gmmktime(0,0,0,1,1,$year); // 1 января, 00:00:00

					// Определим начало недели, к которой относится 1 января
					$DayStJ = gmdate("w",$StJ);
					$DayStJ = ($DayStJ == 0 ? 7 : $DayStJ);
					$StWeekJ = $StJ - ($DayStJ-1) * $DayLen;

					// Если 1 января относится к 1й неделе, то в $week получается одна "лишняя" неделя
					if ( gmdate("W",$StJ) == "01" )$week--;

					// прибавили к началу "январской" недели номер нашей недели
					$start = $StWeekJ + $week * $WeekLen;

					// К началу прибавляем недели (получаем след. понедельник, 00:00) и отняли одну секунду - т.е. воскресенье, 23:59
					$end = $start + $WeekLen - 5*60*60;

					$period_title = $rowOrdersResult['date_title'] . Core::_('Shop_Item.form_sales_order_week') . date('d.m.Y', $start) . '&mdash;' . date('d.m.Y', $end);
				break;
				default: // группировка по дням
					$period_title = $rowOrdersResult['date_title'];
				break;
			}

			?>
			<tbody>
			<tr>
				<td><strong><?php echo $period_title?></strong></td>
				<td>&nbsp;</td>
				<td><?php echo $rowOrdersResult['count_orders']?></td>
				<td><?php echo $rowOrdersResult['count_items']?></td>
				<td><?php echo sprintf("%.2f %s", round($rowOrdersResult['total_sum'], 2), htmlspecialchars($oShop->Shop_Currency->name))?></td>
				<td></td>
			</tr>

			<script type="text/javascript">
					sumOrdersValues.push([<?php echo $i . ', ' . sprintf("%.2f", $rowOrdersResult['total_sum'])?>]);
					countItemsValues.push([<?php echo $i . ', ' .  $rowOrdersResult['count_items']?>]);
					countOrdersValues.push([<?php echo $i . ', ' .  $rowOrdersResult['count_orders']?>]);

					timePeriodTitles[<?php echo $i?>] = <?php echo '\'' . str_replace("&mdash;", "-", $period_title) . '\''?>
			</script>
			<?php
			++$i;

			if (!is_null(Core_Array::getPost('sales_order_show_list_items')))
			{
				if (count($aOrdersResultPeriodParsed[$rowOrdersResult['date_title'] . ' ' . $rowOrdersResult['year']]) > 0)
				{
					$oShop_Orders = Core_Entity::factory('Shop_Order');
					$oShop_Orders->queryBuilder()
						->where('id', 'IN', $aOrdersResultPeriodParsed[$rowOrdersResult['date_title'] . ' ' . $rowOrdersResult['year']]);
					$aShop_Orders = $oShop_Orders->findAll(FALSE);

					foreach ($aShop_Orders as $oShop_Order)
					{
						?>
						<tr class="row_table report_height" style="font-size: 120%">
							<td colspan="2"><?php echo sprintf(Core::_('Shop_Item.form_sales_order_orders_number'), htmlspecialchars($oShop_Order->invoice), Core_Date::sql2date($oShop_Order->datetime))?><?php
							if ($oShop_Order->payment_datetime != '0000-00-00 00:00:00')
							{
								$payment_system_string = '';

								if (!is_null(Core_Entity::factory('Shop_Payment_System')->find($oShop_Order->shop_payment_system_id)->id))
								{
									$payment_system_string = ' (' . htmlspecialchars(Core_Entity::factory('Shop_Payment_System', $oShop_Order->shop_payment_system_id)->name) . ')';
								}

								echo sprintf(Core::_('Shop_Item.form_sales_order_date_of_paid'), Core_Date::sql2datetime($oShop_Order->payment_datetime)) . $payment_system_string;
							}
							?></td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td><?php echo htmlspecialchars($oShop_Order->sum())?></td>
							<td><?php echo htmlspecialchars($oShop_Order->Shop_Order_Status->name)?></td>
						</tr>
						<?php
						// Получаем список товаров данного заказа
						$aShopOrderItems = $oShop_Order->Shop_Order_Items->findAll(FALSE);

						foreach ($aShopOrderItems as $oShopOrderItem)
						{
							?>
							<tr class="row_table report_height">
								<td>—<?php echo htmlspecialchars($oShopOrderItem->name)?></td>
								<td><?php echo htmlspecialchars($oShopOrderItem->marking)?></td>
								<td></td>
								<td><?php echo $oShopOrderItem->quantity?></td>
								<td><?php echo $oShopOrderItem->price?></td>
								<td></td>
							</tr>
							<?php
							$iShopOrderItemsCount += $oShopOrderItem->quantity;
							$iShopOrderItemsSum += $oShopOrderItem->price * $oShopOrderItem->quantity;
						}
					}
				}
			}
			else
			{
				$iShopOrderItemsCount += $rowOrdersResult['count_items'];
				$iShopOrderItemsSum += $rowOrdersResult['total_sum'];
			}
		}
		?>
		<tr class="admin_table_filter row_table admin_table_sub_title">
			<td></td>
			<td></td>
			<td>∑</td>
			<td><?php echo $iShopOrderItemsCount?></td>
			<td><?php echo htmlspecialchars($iShopOrderItemsSum)?></td>
			<td></td>
		</tr>
		<?php

		?>
					</tbody>
				</table>
			</div>
		</div>
		<script type="text/javascript">
			$(function(){
				//var shopOrdersReportDiagramData = [];
				var gridbordercolor = "#eee",
				themeprimary = getThemeColorFromCss('themeprimary'),
				themesecondary = getThemeColorFromCss('themesecondary'),
				themethirdcolor = getThemeColorFromCss('themethirdcolor'),

				shopOrdersReportDiagramData = [
					{
						label: '<?php echo Core::_('Shop_Item.form_sales_order_total_summ')?>',
						data: sumOrdersValues,
						color: themeprimary, //'#30C4E8',
						bars: {
							//order: 1,
							show: true,
							borderWidth: 0,
							barWidth: 0.4,
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
						label: '<?php echo Core::_('Shop_Item.form_sales_order_count_items')?>',
						data: countItemsValues,
						lines: {
							show: true,
							fill: false,
							fillColor: {
								colors: [{
									opacity: 0.3
								}, {
									opacity: 0
								}]
							}
						},
						points: {
							show: true
						},
						yaxis: 2
					},
					{
						color: themethirdcolor,
						label: '<?php echo Core::_('Shop_Item.form_sales_order_count_orders')?>',
						data: countOrdersValues,
						lines: {
							show: true,
							fill: false,
							fillColor: {
								colors: [{
									opacity: 0.3
								}, {
									opacity: 0
								}]
							}
						},
						points: {
							show: true
						},
						yaxis: 2
					},
				];

				var options = {
					legend: {
						show: false
					},
					xaxes: [ {
						show: false,
						tickDecimals: 0,
						color: gridbordercolor,
					} ],
					yaxes: [ {
								min: 0,
								color: gridbordercolor,
								tickDecimals: 0
							},
							{
								min: 0,
								color: gridbordercolor,
								tickDecimals: 0,
								position: "right"

					} ],
					grid: {
						hoverable: true,
						clickable: false,
						borderWidth: 0,
						aboveData: false,
						color: '#fbfbfb'
					},
					tooltip: true,
					tooltipOpts: {
						defaultTheme: false,
						//content: "<span>%lx</span>, <b>%s</b> : <span>%y</span>",
						content: function(label, xval, yval, flotItem){
							var labelText = flotItem.seriesIndex == 0 ? yval + ' <?php echo Core_Str::escapeJavascriptVariable(htmlspecialchars($oShop->Shop_Currency->name)) ?>' : yval;

							return '<span>' + timePeriodTitles[xval] + '</span>, <b>' + label + '</b> : <span>' + labelText + '</span>';
						}
					}
				};
				var placeholder = $("#shopOrdersReportDiagram");
				var plot = $.plot(placeholder, shopOrdersReportDiagramData, options);
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
	$oAdmin_View->pageTitle(Core::_('Shop_Item.show_sales_order_link'));

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
	)/*->add(
		Admin_Form_Entity::factory('Code')
			->html("<script>$(function() {
				$('#{$windowId} #sales_order_grouping').buttonset();
			});</script>")
	)*/)
	->add(Admin_Form_Entity::factory('Div')->class('row')->add(
		Admin_Form_Entity::factory('Date')
			->caption(Core::_('Shop_Item.form_sales_order_begin_date'))
			->name('sales_order_begin_date')
			->value(Core_Date::timestamp2sql(strtotime("-2 months")))
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-3'))
	)->add(
		Admin_Form_Entity::factory('Date')
			->caption(Core::_('Shop_Item.form_sales_order_end_date'))
			->name('sales_order_end_date')
			->value(Core_Date::timestamp2sql(time()))
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-3'))
	))->add(Admin_Form_Entity::factory('Div')->class('row')->add(
		Admin_Form_Entity::factory('Checkbox')
			->name('sales_order_show_list_items')
			->caption(Core::_('Shop_Item.form_sales_order_show_list_items'))
			->value(1)
			->divAttr(array('class' => 'form-group col-xs-12'))
	));

	$aPaySystems = array(' … ');
	$aShop_Payment_Systems = $oShop->Shop_Payment_Systems->findAll();
	foreach ($aShop_Payment_Systems as $oShop_Payment_System)
	{
		$aPaySystems[$oShop_Payment_System->id] = $oShop_Payment_System->name;
	}

	$aOrderStatuses = array(' … ');
	$aShop_Order_Statuses = Core_Entity::factory('Shop_Order_Status')->findAll();
	foreach ($aShop_Order_Statuses as $oShop_Order_Status)
	{
		$aOrderStatuses[$oShop_Order_Status->id] = $oShop_Order_Status->name;
	}

	$oMainTab->add(Admin_Form_Entity::factory('Div')->class('row')
		->add(
		Admin_Form_Entity::factory('Checkbox')
			->name('sales_order_show_only_paid_items')
			->caption(Core::_('Shop_Item.form_sales_order_show_paid_items'))
			->value(1)
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-3'))
	))
	->add(Admin_Form_Entity::factory('Div')->class('row')
		->add(
			Admin_Form_Entity::factory('Select')
				->options($aPaySystems)
				->caption(Core::_('Shop_Item.form_sales_order_sop'))
				->name('shop_system_of_pay_id')
				->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'))
		)
		->add(
			Admin_Form_Entity::factory('Select')
				->options($aOrderStatuses)
				->caption(Core::_('Shop_Item.form_sales_order_status'))
				->name('shop_order_status_id')
				->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'))
		)
	);

	$oMainTab->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'));

	if (Core::moduleIsActive('siteuser'))
	{
		$oSiteuser = Core_Entity::factory('Siteuser');

		$oSiteuserSelect = Admin_Form_Entity::factory('Select')
			->caption(Core::_('Shop_Order.siteuser_id'))
			->id('object_siteuser_id')
			->options(array(0))
			->name('siteuser_id')
			->class('siteuser-tag')
			->style('width: 100%')
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'));

		$oMainRow1->add($oSiteuserSelect);

		// Show button
		Siteuser_Controller_Edit::addSiteuserSelect2($oSiteuserSelect, $oSiteuser, $oAdmin_Form_Controller);
	}

	$aSellers = array(' … ');
	$aShop_Sellers = $oShop->Shop_Sellers->findAll();
	foreach ($aShop_Sellers as $oShop_Seller)
	{
		$aSellers[$oShop_Seller->id] = $oShop_Seller->name;
	}

	$oMainRow1->add(
		Admin_Form_Entity::factory('Select')
			->options($aSellers)
			->caption(Core::_('Shop_Item.form_sales_order_sallers'))
			->name('shop_seller_id')
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'))
	);

	$oMainTab->add(Admin_Form_Entity::factory('Div')->class('row')->add(
		Core::factory('Core_Html_Entity_Input')->type('hidden')->name('shop_id')->value(Core_Array::getGet('shop_id'))
	)->add(
		Core::factory('Core_Html_Entity_Input')->type('hidden')->name('shop_group_id')->value(Core_Array::getGet('shop_group_id'))
	));

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
	->title(Core::_('Shop_Item.show_sales_order_link'))
	->execute();