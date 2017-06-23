<?php
/**
 * Online shop.
 *
 * @package HostCMS
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
require_once('../../../../../bootstrap.php');

Core_Auth::authorization('shop');

$sAdminFormAction = '/admin/shop/order/card/index.php';

$oShop_Order = Core_Entity::factory('Shop_Order', Core_Array::getGet('shop_order_id', 0));
$sPageTitle = Core::_("Shop_Order.acceptance_report_akt", $oShop_Order->acceptance_report, Core_Date::sql2date($oShop_Order->acceptance_report_datetime)) . Core::_("Shop_Order.acceptance_report_year");
$sShopCurrency = $oShop_Order->Shop_Currency->name ? ', ' . $oShop_Order->Shop_Currency->name : '';

$aFullAddress = array(
	trim($oShop_Order->postcode),
	$oShop_Order->Shop_Country->name,
	$oShop_Order->Shop_Country_Location->name,
	$oShop_Order->Shop_Country_Location_City->name,
	$oShop_Order->Shop_Country_Location_City_Area->name,
	trim($oShop_Order->address)
);

$aFullAddress = array_filter($aFullAddress);
$sFullAddress = implode(', ', $aFullAddress);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title><?php echo $sPageTitle?></title>
		<meta http-equiv="Content-Language" content="ru" />
		<meta content="text/html; charset=UTF-8" http-equiv="Content-Type" />
		<style type="text/css">
		.disable_indentation{padding:0;margin:0;}
		.text_align_right{text-align: right;}
		.text_align_center{text-align: center;}
		.border_bottom{border-bottom: 1px solid black;}
		.width_small{width: 5%;}
		.width_normal{width: 10%;}
		.width_big{width: 40%;}
		.font_weight_bold{font-weight: bold;}
		.text_underline{text-decoration: underline;}
		.base_font{font-size: 10pt; font-family: sans-serif;}
		.big_font{font-size: 1.5em;}
		.small_font{font-size: 0.7em;}
		.font_style_italic{font-style: italic;}
		
		table{border-collapse: collapse;}
		table.first{margin: 0 0 2em;}
		table.second{margin: 2em 0 0;}
		table.first td{border: 1px solid black; white-space: nowrap;}
		table.first td.border_none{border: none;}
		</style>
	</head>
<body class="font_sans_serif base_font">
		<p class="font_weight_bold text_underline disable_indentation"><?php echo $oShop_Order->Shop->Shop_Company->name?></p>
		<p class="font_weight_bold disable_indentation"><?php echo Core::_("Shop_Order.acceptance_report_address") . $oShop_Order->Shop->Shop_Company->address . ', '.  Core::_("Shop_Order.acceptance_report_phone") . ' ' . $oShop_Order->Shop->Shop_Company->phone?></p>
		<h1 class="text_align_center center big_font"><?php echo $sPageTitle?></h1>
		<p><?php echo Core::_("Shop_Order.acceptance_report_customer"). ': ' . $oShop_Order->company?>
		<br/><?php echo Core::_("Shop_Order.acceptance_report_address") . $sFullAddress . ', ' . Core::_("Shop_Order.acceptance_report_phone") . ' ' . $oShop_Order->phone?></p>
		<table cellspacing="0" cellpadding="3" border="0" width="100%" class="first">
		<tr>
			<td class="width_small text_align_center">
				<?php echo "№"?>
			</td>
			<td class="text_align_center">
				<?php echo Core::_("Shop_Order.acceptance_report_work_name")?>
			</td>
			<td class="width_small text_align_center">
				<?php echo Core::_("Shop_Order.acceptance_report_measure")?>
			</td>
			<td class="width_small text_align_center">
				<?php echo Core::_("Shop_Order.acceptance_report_count")?>
			</td>
			<td class="width_small text_align_center">
				<?php echo Core::_("Shop_Order.acceptance_report_price") . $sShopCurrency?>
			</td>
			<td class="width_normal text_align_center">
				<?php echo Core::_("Shop_Order.acceptance_report_sum") . $sShopCurrency?>
			</td>
		</tr>
		<?php
		$i = 1;
		
		$oShop_Controller = Shop_Controller::instance();
		$aShopOrderItems = $oShop_Order->Shop_Order_Items->findAll();

		if (count($aShopOrderItems))
		{
			$fShopTaxValueSum = $fShopOrderItemSum = 0.0;
		
			foreach ($aShopOrderItems as $oShopOrderItem)
			{
				$sShopTaxRate = $oShopOrderItem->rate;

				$sShopTaxValue = $sShopTaxRate
					? $oShop_Controller->round($oShopOrderItem->getTax()) * $oShopOrderItem->quantity
					: 0;

				$fItemAmount = $oShop_Controller->round($oShopOrderItem->getAmount());

				$fShopTaxValueSum += $sShopTaxValue;
				$fShopOrderItemSum += $fItemAmount;
				?>
				<tr>
					<td class="text_align_right">
					<?php echo $i++?>
					</td>
					<td>
					<?php echo $oShopOrderItem->name?>
					</td>
					<td class="text_align_center">
					<?php echo $oShopOrderItem->Shop_Item->Shop_Measure->name?>
					</td>
					<td class="text_align_center">
					<?php echo $oShopOrderItem->quantity?>
					</td>
					<td class="text_align_right">
					<?php echo number_format($oShop_Controller->round($oShopOrderItem->price), 2, '.', '')?>
					</td>
					<td class="text_align_right">
					<?php echo number_format($fItemAmount, 2, '.', '')?>
					</td>
				</tr>
				<?php
			}
			?>
			<tr>
				<td colspan="5" class="border_none text_align_right font_weight_bold">
				<?php echo Core::_("Shop_Order.acceptance_report_summary")?>
				</td>
				<td class="text_align_right font_weight_bold">
				<?php echo number_format($fShopOrderItemSum, 2, '.', '')?>
				</td>
			</tr>
			<tr>
				<td colspan="5" class="border_none text_align_right font_weight_bold">
				<?php echo Core::_("Shop_Order.acceptance_report_include_vat")?>
				</td>
				<td class="text_align_right font_weight_bold">
				<?php echo number_format($fShopTaxValueSum, 2, '.', '')?>
				</td>
			</tr>
			<?php
		}
		?>
		</table>
		<p class="font_style_italic disable_indentation">
			<?php echo Core::_("Shop_Order.acceptance_report_resume_1", number_format($fShopOrderItemSum, 2, '.', '') . ' ' . $oShop_Order->Shop_Currency->name, number_format($fShopTaxValueSum, 2, '.', '') .' ' . $oShop_Order->Shop_Currency->name)?>
		</p>
		<p class="disable_indentation">
			<?php echo Core::_("Shop_Order.acceptance_report_resume_2")?>
		</p>
		<table cellspacing="0" cellpadding="3" width="100%" class="second">
			<tr>
				<td>
					<?php echo Core::_("Shop_Order.acceptance_report_performer")?>
				</td>
				<td>
				</td>
				<td>
					<?php echo Core::_("Shop_Order.acceptance_report_customer")?>
				</td>
			</tr>
			<tr>
				<td class="border_bottom width_big">
				</td>
				<td>
				</td>
				<td class="border_bottom width_big">
				</td>
			</tr>
			<tr>
				<td class="text_align_center small_font">
					<?php echo Core::_("Shop_Order.acceptance_report_signature")?>
				</td>
				<td>
				</td>
				<td class="text_align_center small_font">
					<?php echo Core::_("Shop_Order.acceptance_report_signature")?>
				</td>
			</tr>
		</table>
	</body>
</html>