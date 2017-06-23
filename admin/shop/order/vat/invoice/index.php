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
$sPageTitle = Core::_("Shop_Order.vat_invoice_title", $oShop_Order->vat_invoice, Core_Date::sql2date($oShop_Order->vat_invoice_datetime));

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
		.base_font{font-size: 9pt; font-family: sans-serif;}
		.small_font{font-size: 0.7em;}
		.big_font{font-size: 1.5em;}
		.bold_font{font-weight: bold;}
		.text_align_right{text-align: right;}
		.text_align_top{vertical-align: top;}
		.text_align_bottom{vertical-align: bottom;}
		.text_nowrap{white-space: nowrap;}
		.disable_indentation{padding:0;margin:0;}
		.border_bottom{border-bottom: 1px solid black;}
		.width_big{width: 20%;}
		.width_normal{width: 10%;}
		.width_small{width: 4%;}
		.text_align_center, table.first tr:first-child + tr td{text-align: center;}

		table{border-collapse: collapse;}
		table.first th{font: inherit;font-size: 0.7em;}
		table.first td,table.first th{border: 1px solid black;}
		table.first{margin-bottom: 10px; width:100%;}
		table.first .border_none{border: none;}
		table.second{width:90%;}
		table.second td{height: 40px; border-right: 5px solid white}
		</style>
	</head>
	<body class="base_font">
	<div class="text_align_right small_font"><?php echo Core::_("Shop_Order.vat_invoice_intro")?></div>
	<div class="big_font bold_font"><?php echo $sPageTitle?></div>
	<div>
	<?php echo Core::_("Shop_Order.vat_invoice_seller") . $oShop_Order->Shop->Shop_Company->name?>
	<br/><?php echo Core::_("Shop_Order.vat_invoice_address") . $oShop_Order->Shop->Shop_Company->address?>
	<br/><?php echo Core::_("Shop_Order.vat_invoice_tin") . $oShop_Order->Shop->Shop_Company->tin . '/' . $oShop_Order->Shop->Shop_Company->kpp?>
	<br/><?php echo Core::_("Shop_Order.vat_invoice_shipper")?>
	<br/><?php echo Core::_("Shop_Order.vat_invoice_consignee")?>
	<br/><?php echo Core::_("Shop_Order.vat_invoice_payment_doc")?>
	<br/><?php echo Core::_("Shop_Order.vat_invoice_buyer") . $oShop_Order->company?>
	<br/><?php echo Core::_("Shop_Order.vat_invoice_buyer_address") . $sFullAddress?>
	<br/><?php echo Core::_("Shop_Order.vat_invoice_buyer_tin") . $oShop_Order->tin . '/' . $oShop_Order->kpp?>
	</div>
	<p class="text_align_right disable_indentation bold_font">
		<?php echo Core::_("Shop_Order.vat_invoice_currency").$oShop_Order->Shop_Currency->name?>
	</p>
	<table class="first">
	<tr>
		<?php echo Core::_("Shop_Order.vat_invoice_table_header")?>
	</tr>
	<tr>
		<td>1</td><td>2</td><td>3</td><td>4</td><td>5</td><td>6</td><td>7</td><td>8</td><td>9</td><td>10</td><td>11</td>
	</tr>
	<?php
	$aShopOrderItems = $oShop_Order->Shop_Order_Items->findAll();
	if(count($aShopOrderItems) > 0)
	{
		$fShopTaxValueSum = $fShopOrderItemSum = 0.0;

		foreach ($aShopOrderItems as $oShopOrderItem)
		{
			$sShopTaxRate = $oShopOrderItem->rate;
			$sShopTaxValue = $sShopTaxRate
				? $oShopOrderItem->getTax() * $oShopOrderItem->quantity
				: 0;

			$sItemAmount = $oShopOrderItem->getAmount();
			$fShopOrderItemSum += $sItemAmount;
			$fShopTaxValueSum += $sShopTaxValue;
			?>
			<tr>
				<td><?php echo $oShopOrderItem->name?></td>
				<td><?php echo $oShopOrderItem->Shop_Item->Shop_Measure->name?></td>
				<td class="text_align_right"><?php echo sprintf('%.3f', $oShopOrderItem->quantity)?></td>
				<td class="text_align_right"><?php echo number_format($oShopOrderItem->price, 2, '-', '')?></td>
				<td class="text_align_right"><?php echo number_format($oShopOrderItem->price * $oShopOrderItem->quantity, 2, '-', '')?></td>
				<td class="text_align_center">—</td>
				<td class="text_align_center"><?php echo $sShopTaxRate ? $sShopTaxRate . '%' : '—'?></td>
				<td class="text_align_right"><?php echo number_format($sShopTaxValue, 2, '-', '')?></td>
				<td class="text_align_right"><?php echo number_format($sItemAmount, 2, '-', '')?></td>
				<td></td>
				<td></td>
			</tr>
			<?php
		}
		?>
		<tr>
			<td colspan="7" class="bold_font"><?php echo Core::_("Shop_Order.vat_invoice_total")?></td>
			<td class="text_align_right"><?php echo  number_format($fShopTaxValueSum, 2, '-', '')?></td>
			<td class="text_align_right"><?php echo  number_format($fShopOrderItemSum, 2, '-', '')?></td>
			<td class="border_none"></td>
			<td class="border_none"></td>
		</tr>
		<?php
	}
	?>
	</table>
	<table class="second">
		<tr>
			<td class="text_align_bottom width_normal text_nowrap"><?php echo Core::_("Shop_Order.vat_invoice_head")?></td>
			<td class="border_bottom width_normal"></td>
			<td class="border_bottom width_big text_align_center text_align_bottom"><?php echo $oShop_Order->Shop->Shop_Company->legal_name?></td>
			<td class="width_small"></td>
			<td class="text_align_bottom width_normal text_nowrap"><?php echo Core::_("Shop_Order.vat_invoice_chief")?></td>
			<td class="width_normal border_bottom"></td>
			<td class="width_big border_bottom text_align_center text_align_bottom"><?php echo $oShop_Order->Shop->Shop_Company->accountant_legal_name?></td>
		</tr>
		<tr>
			<td class="text_align_bottom width_normal text_nowrap"><?php echo Core::_("Shop_Order.vat_invoice_individual")?></td>
			<td class="text_align_top text_align_center small_font width_normal border_bottom"><?php echo Core::_("Shop_Order.vat_invoice_sign")?></td>
			<td class="text_align_top text_align_center small_font width_big border_bottom"><?php echo Core::_("Shop_Order.vat_invoice_fio")?></td>
			<td class="width_small"></td>
			<td class="width_normal"></td>
			<td class="text_align_top text_align_center small_font width_normal"><?php echo Core::_("Shop_Order.vat_invoice_sign")?></td>
			<td class="text_align_top text_align_center small_font width_big"><?php echo Core::_("Shop_Order.vat_invoice_fio")?></td>
		</tr>
		<tr>
			<td class="width_normal"></td>
			<td class="text_align_top text_align_center small_font width_normal"><?php echo Core::_("Shop_Order.vat_invoice_sign")?></td>
			<td class="text_align_top text_align_center small_font width_big"><?php echo Core::_("Shop_Order.vat_invoice_fio")?></td>
			<td class="width_small"></td>
			<td colspan="3" class="text_align_top text_align_center small_font"><?php echo Core::_("Shop_Order.vat_invoice_details")?></td>
		</tr>
	</table>
	<p class="disable_indentation small_font"><?php echo Core::_("Shop_Order.vat_invoice_note")?></p>
	</body>
</html>