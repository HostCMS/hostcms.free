<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop. Backend's Index Pages and Widget.
 *
 * @package HostCMS
 * @subpackage Skin
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Skin_Bootstrap_Module_Shop_Module extends Shop_Module
{
	/**
	 * Widget path
	 * @var string|NULL
	 */
	protected $_path = NULL;

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();

		$this->_adminPages = array(
			1 => array('title' => 'undefined'),
		);
	}

	/**
	 * Show admin widget
	 * @param int $type
	 * @param boolean $ajax
	 * @return self
	 */
	public function adminPage($type = 0, $ajax = FALSE)
	{
		$type = intval($type);

		$oModule = Core_Entity::factory('Module')->getByPath($this->_moduleName);
		$this->_path = "/admin/index.php?ajaxWidgetLoad&moduleId={$oModule->id}&type={$type}";

		switch ($type)
		{
			case 1:
				if ($ajax)
				{
					$this->_ordersContent();
				}
				else
				{
					?><div id="shopOrdersAdminPage">
						<script>
						$.widgetLoad({ path: '<?php echo $this->_path?>', context: $('#shopOrdersAdminPage') });
						</script>
					</div><?php
				}
			break;
		}

		return TRUE;
	}

	protected function _ordersContent()
	{
		$oUser = Core_Auth::getCurrentUser();

		if (is_null($oUser))
		{
			return FALSE;
		}

		$oAdmin_Form = Core_Entity::factory('Admin_Form', 75);

		$aAdmin_Form_Actions = $oAdmin_Form->Admin_Form_Actions->getAllowedActionsForUser($oUser);

		$access = FALSE;

		foreach ($aAdmin_Form_Actions as $oAdmin_Form_Action)
		{
			if ($oAdmin_Form_Action->name == 'edit' && $oAdmin_Form_Action->name == 'showWidget')
			{
				$access = TRUE;
				break;
			}
		}

		if ($oUser->superuser || $access)
		{
			$iShopId = Core_Array::getGet('shop_id');

			$oLast_Shop_Orders = Core_Entity::factory('Shop_Order');
			$oLast_Shop_Orders
				->queryBuilder()
				->straightJoin()
				->join('shops', 'shops.id', '=', 'shop_orders.shop_id')
				->where('shops.site_id', '=', CURRENT_SITE)
				->where('shops.deleted', '=', 0)
				->clearOrderBy()
				->orderBy('datetime', 'DESC')
				->limit(9);

			$iShopId && $oLast_Shop_Orders
				->queryBuilder()
				->where('shops.id', '=', $iShopId);

			!$oUser->superuser && $oUser->only_access_my_own
				&& $oLast_Shop_Orders->queryBuilder()->where('shop_orders.user_id', '=', $oUser->id);

			$aLast_Shop_Orders = $oLast_Shop_Orders->findAll(FALSE);

			if (count($aLast_Shop_Orders) || $iShopId)
			{
			?><div class="col-xs-12 no-padding">
				<div class="col-xs-12 col-md-9">
					<script>
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
					</script>
					<?php
					$iBeginTimestamp = strtotime('-1 month');

					$oDefault_Currency = Core_Entity::factory('Shop_Currency')->getDefault();

					if ($oDefault_Currency)
					{
						$aOrdered = array();

						$sEndTimestamp = date('Y-m-d 23:59:59');
						$iEndTimestamp = Core_Date::date2timestamp($sEndTimestamp);
						for ($iTmp = $iBeginTimestamp; $iTmp <= $iEndTimestamp; $iTmp += 86400)
						{
							$aOrdered[date('Y-m-d', $iTmp)] = 0;
						}

						$aPaidAmount = $aPaid = $aOrderedAmount = $aOrdered;

						$limit = 1000;
						$offset = 0;

						// Ordered
						do {
							$oShop_Orders = Core_Entity::factory('Shop_Order');
							$oShop_Orders
								->queryBuilder()
								->straightJoin()
								->join('shops', 'shops.id', '=', 'shop_orders.shop_id')
								->where('shops.site_id', '=', CURRENT_SITE)
								->where('shops.deleted', '=', 0)
								->where('shop_orders.datetime', '>=', date('Y-m-d 00:00:00', $iBeginTimestamp))
								->where('shop_orders.canceled', '=', 0)
								->offset($offset)
								->limit($limit)
								->clearOrderBy()
								->orderBy('id', 'ASC');

							$iShopId && $oShop_Orders
								->queryBuilder()
								->where('shops.id', '=', $iShopId);

							!$oUser->superuser && $oUser->only_access_my_own
								&& $oShop_Orders->queryBuilder()->where('shop_orders.user_id', '=', $oUser->id);

							$aShop_Orders = $oShop_Orders->findAll(FALSE);

							foreach ($aShop_Orders as $oShop_Order)
							{
								$sDate = date('Y-m-d', Core_Date::sql2timestamp($oShop_Order->datetime));

								isset($aOrdered[$sDate])
									? $aOrdered[$sDate]++
									: $aOrdered[$sDate] = 1;

								$fCurrencyCoefficient = $oShop_Order->Shop_Currency->id > 0 && $oDefault_Currency->id > 0
									? Shop_Controller::instance()->getCurrencyCoefficientInShopCurrency(
										$oShop_Order->Shop_Currency, $oDefault_Currency
									)
									: 0;

								$fAmount = $oShop_Order->getAmount() * $fCurrencyCoefficient;

								isset($aOrderedAmount[$sDate])
									? $aOrderedAmount[$sDate] += $fAmount
									: $aOrderedAmount[$sDate] = $fAmount;
							}

							$offset += $limit;
						}
						while (count($aShop_Orders) == $limit);

						$offset = 0;

						// Paid
						do {
							$oShop_Orders = Core_Entity::factory('Shop_Order');
							$oShop_Orders
								->queryBuilder()
								->join('shops', 'shops.id', '=', 'shop_orders.shop_id')
								->where('shops.site_id', '=', CURRENT_SITE)
								->where('shops.deleted', '=', 0)
								->where('shop_orders.payment_datetime', '>=', date('Y-m-d 00:00:00', $iBeginTimestamp))
								->where('shop_orders.paid', '=', 1)
								->offset($offset)
								->limit($limit)
								->clearOrderBy()
								->orderBy('id', 'ASC');

							$iShopId && $oShop_Orders
								->queryBuilder()
								->where('shops.id', '=', $iShopId);

							!$oUser->superuser && $oUser->only_access_my_own
								&& $oShop_Orders->queryBuilder()->where('shop_orders.user_id', '=', $oUser->id);

							$aShop_Orders = $oShop_Orders->findAll(FALSE);

							foreach ($aShop_Orders as $oShop_Order)
							{
								$sDate = date('Y-m-d', Core_Date::sql2timestamp($oShop_Order->payment_datetime));

								isset($aPaid[$sDate])
									? $aPaid[$sDate]++
									: $aPaid[$sDate] = 1;

								$fCurrencyCoefficient = $oShop_Order->Shop_Currency->id > 0 && $oDefault_Currency->id > 0
									? Shop_Controller::instance()->getCurrencyCoefficientInShopCurrency(
										$oShop_Order->Shop_Currency, $oDefault_Currency
									)
									: 0;

								$fAmount = $oShop_Order->getAmount() * $fCurrencyCoefficient;
								isset($aPaidAmount[$sDate])
									? $aPaidAmount[$sDate] += $fAmount
									: $aPaidAmount[$sDate] = $fAmount;
							}

							$offset += $limit;
						}
						while (count($aShop_Orders) == $limit);

						?><div class="dashboard-box">
							<div class="box-header">
								<div class="deadline">
									<?php echo Core::_('Shop.sales_statistics')?>
									<?php
									$aShops = Core_Entity::factory('Site', CURRENT_SITE)->Shops->findAll(FALSE);

									if (count($aShops))
									{
										//Core_Session::start();
										?>
										<select class="input-xs widget-select-shop" name="select_shop">
											<option value="0"><?php echo Core::_('Shop.all_shops')?></option>

											<?php
											foreach ($aShops as $oShop)
											{
												$selected = $oShop->id == $iShopId
													? 'selected="selected"'
													: '';

												?><option value="<?php echo $oShop->id?>" <?php echo $selected?>><?php echo htmlspecialchars($oShop->name)?></option><?php
											}
											?>
										</select>

										<script>
										$(function() {
											$('select.widget-select-shop').change(function(){
												$.widgetLoad({ path: '<?php echo $this->_path?>&shop_id=' + $(this).val(), context: $('#shopOrdersAdminPage') });
											});
										});
										</script>
									<?php
									}
									?>
								</div>
							</div>

							<div id="sales" class="box-body tab-pane animated fadeInUp no-padding-bottom" style="padding:20px 20px 0 20px;">
								<div class="row">
									<div class="col-xs-6 col-sm-3">
										<div class="databox databox-xlg databox-vertical databox-inverted databox-shadowed">
											<div class="databox-top">
												<div class="databox-sparkline">
													<span data-sparkline="line" data-height="125px" data-width="100%" data-fillcolor="false" data-linecolor="themesecondary"
														 data-spotcolor="#fafafa" data-minspotcolor="#fafafa" data-maxspotcolor="#ffce55"
														 data-highlightspotcolor="#ffce55" data-highlightlinecolor="#ffce55"
														 data-linewidth="1.5" data-spotradius="2">
														<?php echo implode(',', $aOrdered)?>
													</span>
												</div>
											</div>
											<div class="databox-bottom no-padding text-align-center">
												<span class="databox-number lightcarbon no-margin"><?php echo array_sum($aOrdered)?></span>
												<span class="databox-text lightcarbon no-margin"><?php echo Core::_('Shop.ordered')?></span>
											</div>
										</div>
									</div>
									<div class="col-xs-6 col-sm-3">
										<div class="databox databox-xlg databox-vertical databox-inverted databox-shadowed">
											<div class="databox-top">
												<div class="databox-sparkline">
													<span data-sparkline="line" data-height="125px" data-width="100%" data-fillcolor="false" data-linecolor="themefourthcolor"
														 data-spotcolor="#fafafa" data-minspotcolor="#fafafa" data-maxspotcolor="#8cc474"
														 data-highlightspotcolor="#8cc474" data-highlightlinecolor="#8cc474"
														 data-linewidth="1.5" data-spotradius="2">
														 <?php echo implode(',', $aPaid)?>
													</span>
												</div>
											</div>
											<div class="databox-bottom no-padding text-align-center">
												<span class="databox-number lightcarbon no-margin"><?php echo array_sum($aPaid)?></span>
												<span class="databox-text lightcarbon no-margin"><?php echo Core::_('Shop.paid_orders')?></span>
											</div>
										</div>
									</div>

									<div class="col-xs-6 col-sm-3">
										<div class="databox databox-xlg databox-vertical databox-inverted databox-shadowed">
											<div class="databox-top">
												<div class="databox-sparkline">
													<span data-sparkline="line" data-height="125px" data-width="100%" data-fillcolor="false" data-linecolor="themeprimary"
														 data-spotcolor="#fafafa" data-minspotcolor="#fafafa" data-maxspotcolor="#0072C6"
														 data-highlightspotcolor="#0072C6" data-highlightlinecolor="#0072C6	"
														 data-linewidth="1.5" data-spotradius="2">
														 <?php echo implode(',', $aOrderedAmount)?>
													</span>
												</div>
											</div>
											<div class="databox-bottom no-padding text-align-center">
												<span class="databox-number lightcarbon no-margin"><?php echo htmlspecialchars(
													number_format(array_sum($aOrderedAmount), 2, '.', ' ') . ' ' . $oDefault_Currency->name
												)?></span>
												<span class="databox-text lightcarbon no-margin"><?php echo Core::_('Shop.orders_amount')?></span>
											</div>
										</div>
									</div>

									<div class="col-xs-6 col-sm-3">
										<div class="databox databox-xlg databox-vertical databox-inverted databox-shadowed">
											<div class="databox-top">
												<div class="databox-sparkline">
													<span data-sparkline="line" data-height="125px" data-width="100%" data-fillcolor="false" data-linecolor="themethirdcolor"
														 data-spotcolor="#fafafa" data-minspotcolor="#fafafa" data-maxspotcolor="red"
														 data-highlightspotcolor="red" data-highlightlinecolor="red"
														 data-linewidth="1.5" data-spotradius="2">
														 <?php echo implode(',', $aPaidAmount)?>
													</span>
												</div>
											</div>
											<div class="databox-bottom no-padding text-align-center">
												<span class="databox-number lightcarbon no-margin"><?php echo htmlspecialchars(
													number_format(array_sum($aPaidAmount), 2, '.', ' ') . ' ' . $oDefault_Currency->name
												)?></span>
												<span class="databox-text lightcarbon no-margin"><?php echo Core::_('Shop.paid_orders_amount')?></span>
											</div>
										</div>
									</div>
							</div>

							<?php
							$aConfig = Core::$config->get('shop_order_config', array()) + array(
								'indexMostOrderedDays' => 10,
								'indexBrandDays' => 30,
								'Pie3D' => array(
									'E75B8D',
									'FB6E52',
									'FFCE55',
									'A0D468',
									'2DC3E8',
									'6F85BF',
									'CC324B',
									'65B045',
									'5DB2FF',
									'FFF1A8',
									'E46F61',
									'008cd2'
								),
								'cutNames' => 20
							);

							$aColors = Core_Array::get($aConfig, 'Pie3D', array());
							$iCountColors = count($aColors);
							$sWindowId = 'id_content';

							$oMost_Ordered_Shop_Items = Core_Entity::factory('Shop_Order_Item');
							$oMost_Ordered_Shop_Items
								->queryBuilder()
								->select(array(Core_QueryBuilder::expression('SUM(shop_order_items.quantity)'), 'sum'))
								->join('shop_orders', 'shop_orders.id', '=', 'shop_order_items.shop_order_id')
								->join('shops', 'shops.id', '=', 'shop_orders.shop_id')
								->where('shops.site_id', '=', CURRENT_SITE)
								->where('shops.deleted', '=', 0)
								->where('shop_order_items.type', '=', 0)
								->where('shop_order_items.price', '>', 0)
								->where('shop_orders.datetime', '>', date('Y-m-d 00:00:00', strtotime("-{$aConfig['indexMostOrderedDays']} day")))
								->where('shop_orders.deleted', '=', 0)
								->limit(10)
								->groupBy('shop_order_items.shop_item_id')
								->clearOrderBy()
								->orderBy('sum', 'DESC');

							$iShopId && $oMost_Ordered_Shop_Items
								->queryBuilder()
								->where('shops.id', '=', $iShopId);

							$aMost_Ordered_Shop_Items = $oMost_Ordered_Shop_Items->findAll(FALSE);

							$oBrand_Shop_Items = Core_Entity::factory('Shop_Order_Item');
							$oBrand_Shop_Items
								->queryBuilder()
								->select(array(Core_QueryBuilder::expression('SUM(shop_order_items.quantity)'), 'sum'))
								->join('shop_items', 'shop_items.id', '=', 'shop_order_items.shop_item_id')
								->join('shop_orders', 'shop_orders.id', '=', 'shop_order_items.shop_order_id')
								->join('shops', 'shops.id', '=', 'shop_orders.shop_id')
								->where('shops.site_id', '=', CURRENT_SITE)
								->where('shops.deleted', '=', 0)
								->where('shop_order_items.shop_item_id', '!=', 0)
								->where('shop_items.shop_producer_id', '!=', 0)
								->where('shop_orders.datetime', '>', date('Y-m-d 00:00:00', strtotime("-{$aConfig['indexBrandDays']} day")))
								->where('shop_orders.deleted', '=', 0)
								->limit(10)
								->groupBy('shop_items.shop_producer_id')
								->clearOrderBy()
								->orderBy('sum', 'DESC');

							$iShopId && $oBrand_Shop_Items
								->queryBuilder()
								->where('shops.id', '=', $iShopId);

							$aBrand_Shop_Items = $oBrand_Shop_Items->findAll(FALSE);

							if (count($aMost_Ordered_Shop_Items) || count($aBrand_Shop_Items))
							{
								?>
								<div class="row">
									<?php
									if (count($aMost_Ordered_Shop_Items))
									{
										?>
										<div class="col-xs-12 col-md-6">
											<div class="well padding-top-50">
												<div class="header bg-azure"><?php echo Core::_('Shop_Order.most_ordered', $aConfig['indexMostOrderedDays'])?></div>
												<div id="mostOrdered" class="chart"></div>
											</div>

											<script>
											$(function() {
												$.getMultiContent(aScripts, '/modules/skin/bootstrap/js/charts/flot/').done(function() {
													/* Most ordered items */
													var mostOrderedDiagramData = [];

													<?php
													$i = 0;
													foreach ($aMost_Ordered_Shop_Items as $key => $oShop_Order_Item)
													{
														?>
														mostOrderedDiagramData.push({
															label:'<?php echo Core_Str::escapeJavascriptVariable(htmlspecialchars(Core_Str::cut($oShop_Order_Item->name, $aConfig['cutNames'])))?>',
															data:[<?php echo $oShop_Order_Item->sum?>],
															color: '#<?php echo $iCountColors ? $aColors[$key % $iCountColors] : 'E75B8D'?>'
														});
														<?php
														$i++;
													}
													?>

													// all scripts loaded
													setTimeout(function() {
														var placeholderMostOrderedDiagram = $("#<?php echo $sWindowId?> #mostOrdered");

														$.plot(placeholderMostOrderedDiagram, mostOrderedDiagramData, {
															series: {
																pie: {
																	show: true,
																	radius: 1,
																	innerRadius: 0.5,

																	label: {
																			show: true,
																			radius: 0,
																			formatter: function(label, series) {
																				return "<div style='font-size:8pt;' title='" + label + "'>" + label + "</div>";
																			}
																	}
																}
															},
															legend: {
																labelFormatter: function (label, series) {
																	return label + ", " + series.data[0][1];
																}
															},
															grid: {
																hoverable: true,
															}

														});

														placeholderMostOrderedDiagram.bind("plothover", function (event, pos, obj) {
															if (!obj) {
																return;
															}

															$("#<?php echo $sWindowId?> #mostOrdered span[id ^= 'pieLabel']").hide();
															$("#<?php echo $sWindowId?> #mostOrdered span[id = 'pieLabel" + obj.seriesIndex + "']").show();
														});

														placeholderMostOrderedDiagram.resize(function(){$("#<?php echo $sWindowId?> #mostOrdered span[id ^= 'pieLabel']").hide();});

														$("#<?php echo $sWindowId?> #mostOrdered span[id ^= 'pieLabel']").hide();
													}, 200);
												});
											});
											</script>
										</div>
										<?php
									}

									if (count($aBrand_Shop_Items))
									{
										?>
										<div class="col-xs-12 col-md-6">
											<div class="well padding-top-50">
												<div class="header bg-palegreen"><?php echo Core::_('Shop_Order.popular_brands', $aConfig['indexBrandDays'])?></div>
												<div id="countBrands" class="chart"></div>
											</div>

											<script>
											$(function() {
												$.getMultiContent(aScripts, '/modules/skin/bootstrap/js/charts/flot/').done(function() {
													/* Brands shop items */
													var brandsDiagramData = [];

													<?php
													$i = 0;
													foreach ($aBrand_Shop_Items as $key => $oShop_Order_Item)
													{
														?>
														brandsDiagramData.push({
															label:'<?php echo Core_Str::escapeJavascriptVariable(htmlspecialchars(Core_Str::cut($oShop_Order_Item->Shop_Item->Shop_Producer->name, $aConfig['cutNames'])))?>',
															data:[<?php echo $oShop_Order_Item->sum?>],
															color: '#<?php echo $iCountColors ? $aColors[$key % $iCountColors] : 'E75B8D'?>'
														});
														<?php
														$i++;
													}
													?>
													// all scripts loaded
													setTimeout(function() {
														var placeholderBrandsDiagram = $("#<?php echo $sWindowId?> #countBrands");

														$.plot(placeholderBrandsDiagram, brandsDiagramData, {
															series: {
																pie: {
																	show: true,
																	radius: 1,
																	innerRadius: 0.5,

																	label: {
																			show: true,
																			radius: 0,
																			formatter: function(label, series) {
																				return "<div style='font-size:8pt;' title='" + label + "'>" + label + "</div>";
																			}
																	}
																}
															},

															legend: {
																labelFormatter: function (label, series) {
																	return label + ", " + series.data[0][1];
																}
															}
															,
															grid: {
																hoverable: true,
															}

														});

														placeholderBrandsDiagram.bind("plothover", function (event, pos, obj) {
															if (!obj) {
																return;
															}

															$("#<?php echo $sWindowId?> #countBrands span[id ^= 'pieLabel']").hide();
															$("#<?php echo $sWindowId?> #countBrands span[id = 'pieLabel" + obj.seriesIndex + "']").show();
														});

														placeholderBrandsDiagram.resize(function(){$("#<?php echo $sWindowId?> #countBrands span[id ^= 'pieLabel']").hide();});

														$("#<?php echo $sWindowId?> #countBrands span[id ^= 'pieLabel']").hide();
													}, 200);
												});
											});
											</script>
										</div>
										<?php
									}
									?>
								</div>
								<?php
							}
							?>
						</div>
						<?php
						}
						else
						{
							echo Core::_('Shop.undefined_default_currency');
						}
						?>
					</div>

					<script>
					$(function() {
						$.getMultiContent(['jquery.sparkline.js'], '/modules/skin/bootstrap/js/charts/sparkline/').done(function() {
							setTimeout(function() {
								var sparklinelines = $('[data-sparkline=line]');
								$.each(sparklinelines, function () {
									$(this).sparkline('html', {
										type: 'line',
										disableHiddenCheck: true,
										height: $(this).data('height'),
										width: $(this).data('width'),
										fillColor: getcolor($(this).data('fillcolor')),
										lineColor: getcolor($(this).data('linecolor')),
										spotRadius: $(this).data('spotradius'),
										lineWidth: $(this).data('linewidth'),
										spotColor: getcolor($(this).data('spotcolor')),
										minSpotColor: getcolor($(this).data('minspotcolor')),
										maxSpotColor: getcolor($(this).data('maxspotcolor')),
										highlightSpotColor: getcolor($(this).data('highlightspotcolor')),
										highlightLineColor: getcolor($(this).data('highlightlinecolor'))
									});
								});

							}, 300);
						});
					});
					</script>
				</div>

				<div class="col-xs-12 col-md-3">
					<div class="orders-container">
						<div class="orders-header">
							<h6><?php echo Core::_('Shop.recent_orders')?></h6>
						</div>
						<ul class="orders-list">
							<?php

							$iAdmin_Form_Id = 75;
							$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);
							$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form)
								->window('id_content');
							$sShopOrderHref = '/admin/shop/order/index.php';

							foreach ($aLast_Shop_Orders as $oShop_Order)
							{
								$sHref = $oAdmin_Form_Controller->getAdminActionLoadHref($sShopOrderHref, 'edit', NULL, 0, $oShop_Order->id, "shop_id={$oShop_Order->shop_id}");
								$sOnClick = $oAdmin_Form_Controller->getAdminActionLoadAjax($sShopOrderHref, 'edit', NULL, 0, $oShop_Order->id, "shop_id={$oShop_Order->shop_id}");

								?>
								<li class="order-item">
								<div class="row">
									<div class="col-xs-12 item-left">
										<div class="item-booker<?php echo $oShop_Order->canceled ? ' line-through' : ''?>"><?php echo htmlspecialchars($oShop_Order->invoice)?>, <?php echo strlen(trim($oShop_Order->company))
											? htmlspecialchars($oShop_Order->company)
											: htmlspecialchars($oShop_Order->surname . ' ' . $oShop_Order->name . ' ' . $oShop_Order->patronymic)?></div>
									</div>
								</div>
								<div class="row">
									<div class="col-xs-7 item-left">
										<div class="item-time">
											<i class="fa fa-<?php echo $oShop_Order->paid ? 'check' : 'calendar'?>"></i>
											<span><?php echo Core_Date::sql2datetime($oShop_Order->datetime)?></span>
										</div>
									</div>
									<div class="col-xs-5 item-right">
										<div class="item-price">
											<span class="price"><?php echo $oShop_Order->sum()?></span>
										</div>
									</div>
								</div>
								<a class="item-more" href="<?php echo $sHref?>" onclick="<?php echo $sOnClick?>">
									<i></i>
								</a>
								</li>
								<?php
							}
							?>
						</ul>
					</div>
				</div>
			</div>
			<?php
			}
		}
	}
}