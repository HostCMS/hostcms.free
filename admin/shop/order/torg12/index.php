<?php
/**
 * Online shop.
 *
 * @package HostCMS
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
require_once('../../../../bootstrap.php');

Core_Auth::authorization('shop');

function num_propis($num){
 $m = array(
  array('ноль'),
  array('-','один','два','три','четыре','пять','шесть','семь','восемь','девять'),
  array('десять','одиннадцать','двенадцать','тринадцать','четырнадцать','пятнадцать','шестнадцать','семнадцать','восемнадцать','девятнадцать'),
  array('-','-','двадцать','тридцать','сорок','пятьдесят','шестьдесят','семьдесят','восемьдесят','девяносто'),
  array('-','сто','двести','триста','четыреста','пятьсот','шестьсот','семьсот','восемьсот','девятьсот'),
  array('-','одна','две')
 );

 $r=array(
  array('...ллион','','а','ов'),
  array('тысяч','а','и',''),
  array('миллион','','а','ов'),
  array('миллиард','','а','ов')
 );
 if($num==0)return$m[0][0];
 $o=array();

 foreach(array_reverse(str_split(str_pad($num,ceil(strlen($num)/3)*3,'0',STR_PAD_LEFT),3))as$k=>$p){
  $o[$k]=array();

  foreach($n=str_split($p)as$kk=>$pp)
  if(!$pp)continue;else
   switch($kk){
    case 0:$o[$k][]=$m[4][$pp];break;
    case 1:if($pp==1){$o[$k][]=$m[2][$n[2]];break 2;}else$o[$k][]=$m[3][$pp];break;
    case 2:if(($k==1)&&($pp<=2))$o[$k][]=$m[5][$pp];else$o[$k][]=$m[1][$pp];break;
   }$p*=1;if(!$r[$k])$r[$k]=reset($r);

  if($p&&$k)switch(true){
   case preg_match("/^[1]$|^\d*[0,2-9][1]$/",$p):$o[$k][]=$r[$k][0].$r[$k][1];break;
   case preg_match("/^[2-4]$|\d*[0,2-9][2-4]$/",$p):$o[$k][]=$r[$k][0].$r[$k][2];break;
   default:$o[$k][]=$r[$k][0].$r[$k][3];break;
  }$o[$k]=implode(' ',$o[$k]);
 }

 return implode(' ',array_reverse($o));
}

$oShop_Order = Core_Entity::factory('Shop_Order', Core_Array::getGet('shop_order_id', 0));

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
		<title><?php echo Core::_("Shop_Order.torg12_title")?></title>
		<meta http-equiv="Content-Language" content="ru" />
		<meta content="text/html; charset=UTF-8" http-equiv="Content-Type" />
		<style type="text/css">
			.base_font{font-size: 7pt; font-family: sans-serif;}
			.small_font{font-size: 0.7em;}
			.bold_font{font-weight: bold;}
			.align_right{text-align: right;}
			.align_bottom{vertical-align: bottom;}
			.align_center{text-align: center;}
			.align_top{vertical-align: top;}
			.small_width{width: 10%;}
			.tiny_width{width: 1%;}
			.border_all{border: 1px solid black;}
			.border_bottom{border-bottom: 1px solid black;}
			.border_left{border-left: 1px solid black;}
			.border_right{border-right: 10px solid white;}
			.text_nowrap{white-space: nowrap;}
			.text_uppercase{text-transform: uppercase;}

			table{border-collapse: collapse;}
			tr.content th{font: inherit; border: 1px solid black;}
			tr.content td{border: 1px solid black;}
			tr.content td.border_none{border-style: none;}
			td{height: 7px;}
		</style>
	</head>
	<body class="base_font">
	<div class="small_font align_right"><?php echo Core::_("Shop_Order.torg12_intro")?></div>
	<table>
		<tr><td></td><td colspan="12" rowspan="3" class="bold_font align_bottom border_bottom"><?php echo sprintf("%s, ИНН %s, %s, р/с %s в %s, БИК %s, корр/с %s", $oShop_Order->Shop->Shop_Company->name, $oShop_Order->Shop->Shop_Company->tin, $oShop_Order->Shop->Shop_Company->address, $oShop_Order->Shop->Shop_Company->current_account, $oShop_Order->Shop->Shop_Company->bank_name, $oShop_Order->Shop->Shop_Company->bic, $oShop_Order->Shop->Shop_Company->correspondent_account)?></td><td colspan="2"></td><td class="align_center border_all"><?php echo Core::_("Shop_Order.torg12_codes")?></td></tr>
		<tr><td></td><td colspan="2" class="align_right"><?php echo Core::_("Shop_Order.torg12_okud")?></td><td class="align_center border_all">0330212</td></tr>
		<tr><td></td><td colspan="2" class="align_right"><?php echo Core::_("Shop_Order.torg12_okpo")?></td><td class="align_center border_all"><?php echo $oShop_Order->Shop->Shop_Company->okpo?></td></tr>
		<tr><td></td><td class="border_bottom"></td><td colspan="11" class="small_font align_center align_top border_bottom"><?php echo Core::_("Shop_Order.torg12_gruz")?></td><td colspan="2" class="border_bottom"></td><td class="align_center border_all"></td></tr>
		<tr><td></td><td></td><td colspan="13" class="align_center align_top"><span class="small_font"><?php echo Core::_("Shop_Order.torg12_struct")?></span><div class="align_right" style="float: right;"><?php echo Core::_("Shop_Order.torg12_okdp")?></div></td><td class="align_center border_all"></td></tr>
		<tr><td></td><td class="align_right small_width"><?php echo Core::_("Shop_Order.torg12_gruzopol")?></td><td colspan="11" class="border_bottom"><?php echo $oShop_Order->company . ', ' . $sFullAddress?></td><td colspan="2" class="align_right"><?php echo Core::_("Shop_Order.torg12_okpo")?></td><td class="align_center border_all"></td></tr>
		<tr><td></td><td></td><td colspan="11" class="small_font align_center align_top"><?php echo Core::_("Shop_Order.torg12_organiz")?></td><td colspan="2" rowspan="2" class="align_right"><?php echo Core::_("Shop_Order.torg12_okpo")?></td><td rowspan="2" class="align_center border_all"><?php echo $oShop_Order->Shop->Shop_Company->okpo?></td></tr>
		<tr><td></td><td class="align_right"><?php echo Core::_("Shop_Order.torg12_postav")?></td><td colspan= "11" class="border_bottom"><?php echo sprintf("%s, ИНН %s, %s", $oShop_Order->Shop->Shop_Company->name, $oShop_Order->Shop->Shop_Company->tin, $oShop_Order->Shop->Shop_Company->address)?></td></tr>
		<tr><td></td><td></td><td colspan="11" class="small_font align_center align_top"><?php echo Core::_("Shop_Order.torg12_organiz")?></td><td colspan="2" rowspan="2" class="align_right border_bottom"><?php echo Core::_("Shop_Order.torg12_okpo")?></td><td rowspan="2" class="align_center border_all"></td></tr>
		<tr><td></td><td class="align_right"><?php echo Core::_("Shop_Order.torg12_plat")?></td><td colspan="11" class="border_bottom"><?php echo $oShop_Order->company . ', ' . $sFullAddress?></td></tr>
		<tr><td></td><td></td><td colspan="11" class="small_font align_center align_top"><?php echo Core::_("Shop_Order.torg12_organiz")?></td><td rowspan="2" class="border_bottom"></td><td rowspan="2" class="align_right border_all"><?php echo Core::_("Shop_Order.torg12_num")?></td><td rowspan="2" class="align_center border_all"></td></tr>
		<tr><td></td><td class="align_right"><?php echo Core::_("Shop_Order.torg12_osn")?></td><td colspan="11" class="border_bottom"><?php echo sprintf("Счет № %s от %s", $oShop_Order->invoice, Core_Date::sql2date($oShop_Order->acceptance_report_datetime))?></td></tr>
		<tr><td></td><td></td><td colspan="11" class="small_font align_center align_top"><?php echo Core::_("Shop_Order.torg12_naim")?></td><td></td><td class="align_right border_all"><?php echo Core::_("Shop_Order.torg12_date")?></td><td class="align_center border_all"></td></tr>
		<tr><td></td><td></td><td colspan="4"></td><td colspan="2" class="small_font align_center border_all"><?php echo Core::_("Shop_Order.torg12_nom")?></td><td colspan="2" class="small_font align_center border_all"><?php echo Core::_("Shop_Order.torg12_dat_sost")?></td><td colspan="4" class="align_right"><?php echo Core::_("Shop_Order.torg12_transp")?></td><td class="align_right border_all"><?php echo Core::_("Shop_Order.torg12_num")?></td><td class="align_center border_all"></td></tr>
		<tr><td></td><td></td><td colspan="4" class="bold_font align_right text_uppercase"><?php echo Core::_("Shop_Order.torg12_tovarn")?></td><td colspan="2" class="bold_font align_center border_all"><?php echo $oShop_Order->acceptance_report?></td><td colspan="2" class="bold_font align_center border_all"><?php echo Core_Date::sql2date($oShop_Order->acceptance_report_datetime)?></td><td colspan="4"></td><td class="align_right border_all"><?php echo Core::_("Shop_Order.torg12_date")?></td><td class="align_center border_all"></td></tr>
		<tr><td></td><td></td><td colspan="13" class="align_right"><?php echo Core::_("Shop_Order.torg12_vid")?></td><td class="align_center border_all"></td></tr>
		<tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
		<!-- table header -->
		<tr class="content"><th rowspan="2"><?php echo Core::_("Shop_Order.torg12_nomer")?></th><th colspan="3"><?php echo Core::_("Shop_Order.torg12_tovar")?></th><th colspan="2"><?php echo Core::_("Shop_Order.torg12_ed")?></th><th rowspan="2"><?php echo Core::_("Shop_Order.torg12_vid_up")?></th><th colspan="2"><?php echo Core::_("Shop_Order.torg12_col")?></th><th rowspan="2"><?php echo Core::_("Shop_Order.torg12_mas")?></th><th rowspan="2"><?php echo Core::_("Shop_Order.torg12_kol_mas")?></th><th rowspan="2"><?php echo Core::_("Shop_Order.torg12_cen")?></th><th rowspan="2"><?php echo Core::_("Shop_Order.torg12_sum_b")?></th><th colspan="2"><?php echo Core::_("Shop_Order.torg12_nds")?></th><th rowspan="2"><?php echo Core::_("Shop_Order.torg12_sum_nds")?></th></tr>
		<tr class="content"><th colspan="2"><?php echo Core::_("Shop_Order.torg12_naim_hor")?></th><th><?php echo Core::_("Shop_Order.torg12_code")?></th><th><?php echo Core::_("Shop_Order.torg12_naimen")?></th><th><?php echo Core::_("Shop_Order.torg12_okei")?></th><th><?php echo Core::_("Shop_Order.torg12_mest")?></th><th><?php echo Core::_("Shop_Order.torg12_mest_sht")?></th><th><?php echo Core::_("Shop_Order.torg12_stav")?></th><th><?php echo Core::_("Shop_Order.torg12_sum_rub")?></th></tr>
		<tr class="content"><th>1</th><th colspan="2">2</th><th>3</th><th>4</th><th>5</th><th>6</th><th>7</th><th>8</th><th>9</th><th>10</th><th>11</th><th>12</th><th>13</th><th>14</th><th>15</th></tr>
		<!-- table contents -->
		<?php
		$iCounter = 0;
		$aShop_Order_Items = $oShop_Order->Shop_Order_Items->findAll();
		$fShopTaxValueSum = 0.0;
		$fShopOrderItemSum = 0.0;
		$fShopOrderItemSumWithoutTax = 0.0;
		$iShopOrderItemCount = 0;
		$fShopOrderItemTaxSum = 0;
		foreach($aShop_Order_Items as $oShop_Order_Item)
		{
			$sShopTaxRate = $oShop_Order_Item->rate;

			$sShopTaxValue = $sShopTaxRate
				? $oShop_Order_Item->getTax() * $oShop_Order_Item->quantity
				: 0;

			$sItemAmount = $oShop_Order_Item->getAmount();
			$fShopOrderItemSum += $sItemAmount;
			$iShopOrderItemCount += $oShop_Order_Item->quantity;
			$fShopTaxValueSum += $sShopTaxValue * $oShop_Order_Item->quantity;
			$fShopOrderItemSumWithoutTax += $oShop_Order_Item->price * $oShop_Order_Item->quantity;
			$fShopOrderItemTaxSum += $sShopTaxValue;
			?>
			<tr class="content"><td class="align_right align_top"><?php echo ++$iCounter?></td><td colspan="2" class="align_top"><?php echo $oShop_Order_Item->name?></td><td class="align_top"></td><td class="align_top align_center"><?php echo $oShop_Order_Item->Shop_Item->Shop_Measure->name?></td><td class="align_top align_center"><?php echo $oShop_Order_Item->Shop_Item->Shop_Measure->okei?></td><td class="align_top align_center"></td><td class="align_top align_center"></td><td class="align_top align_center"></td><td class="align_top align_center"></td><td class="align_top align_right"><?php echo sprintf('%.3f', $oShop_Order_Item->quantity)?></td><td class="align_top align_right"><?php echo number_format($oShop_Order_Item->price, 2, '-', '')?></td><td class="align_top align_right"><?php echo number_format($oShop_Order_Item->price * $oShop_Order_Item->quantity, 2, '-', '')?></td><td class="align_top align_center text_nowrap"><?php echo $sShopTaxRate ? $sShopTaxRate : 'Без НДС'?></td><td class="align_top align_right"><?php echo number_format($sShopTaxValue, 2, '-', '')?></td><td class="align_top align_right"><?php echo number_format($sItemAmount, 2, '-', '')?></td></tr>
			<?php
		}
		?>
		<tr class="content"><td colspan="8" class="align_right border_none"><?php echo Core::_("Shop_Order.torg12_itog")?></td><td></td><td></td><td class="align_right"><?php echo sprintf('%.3f', $iShopOrderItemCount)?></td><td class="align_center">X</td><td class="align_right"><?php echo number_format($fShopOrderItemSumWithoutTax, 2, '-', '')?></td><td class="align_center">X</td><td class="align_right"><?php echo number_format($fShopOrderItemTaxSum, 2, '-', '')?></td><td class="align_right"><?php echo number_format($fShopOrderItemSum, 2, '-', '')?></td></tr>
		<tr class="content"><td colspan="8" class="align_right border_none"><?php echo Core::_("Shop_Order.torg12_vseg")?></td><td></td><td></td><td class="align_right"><?php echo sprintf('%.3f', $iShopOrderItemCount)?></td><td class="align_center">X</td><td class="align_right"><?php echo number_format($fShopOrderItemSumWithoutTax, 2, '-', '')?></td><td class="align_center">X</td><td class="align_right"><?php echo number_format($fShopOrderItemTaxSum, 2, '-', '')?></td><td class="align_right"><?php echo number_format($fShopOrderItemSum, 2, '-', '')?></td></tr>
		<tr><td></td><td></td><td colspan="4"><?php echo Core::_("Shop_Order.torg12_tov_nakl")?></td><td colspan="6" class="border_bottom"></td><td colspan="4"><?php echo Core::_("Shop_Order.torg12_list")?></td></tr>
		<tr><td colspan="6"></td><td colspan="6" class="align_center align_top small_font"><?php echo Core::_("Shop_Order.torg12_prop")?></td><td colspan="4"></td></tr>
		<tr><td></td><td></td><td><?php echo Core::_("Shop_Order.torg12_cont")?></td><td colspan="9" class="border_bottom align_center"><?php echo Core_Str::ucfirst(num_propis($iCounter))?></td><td colspan="4"><?php echo Core::_("Shop_Order.torg12_por")?></td></tr>
		<tr><td></td><td></td><td></td><td colspan="9" class="align_center align_top small_font"><?php echo Core::_("Shop_Order.torg12_prop")?></td><td colspan="3"></td><td rowspan="2" class="border_all"></td></tr>
		<tr><td colspan="6"></td><td colspan="3"><?php echo Core::_("Shop_Order.torg12_mass_gr")?></td><td colspan="6" class="border_bottom"></td></tr>
		<tr><td colspan="6"></td><td colspan="3"></td><td colspan="6" class="align_center align_top small_font"><?php echo Core::_("Shop_Order.torg12_prop")?></td><td rowspan="2" class="border_all"></td></tr>
		<tr><td colspan="2"></td><td><?php echo Core::_("Shop_Order.torg12_vseg_mes")?></td><td colspan="3" class="border_bottom"></td><td colspan="3"><?php echo Core::_("Shop_Order.torg12_mass_gr_n")?></td><td colspan="6" class="border_bottom"></td></tr>
		<tr><td colspan="3"><?php echo Core::_("Shop_Order.torg12_pril")?></td><td colspan="3" class="border_bottom small_font align_center align_top"><?php echo Core::_("Shop_Order.torg12_prop")?></td><td colspan="3"><?php echo Core::_("Shop_Order.torg12_list")?></td><td colspan="6" class="small_font align_center align_top"><?php echo Core::_("Shop_Order.torg12_prop")?></td><td></td></tr>
		<tr><td colspan="3"></td><td colspan="3" class="small_font align_center align_top"><?php echo Core::_("Shop_Order.torg12_prop")?></td><td></td><td></td><td></td><td class="border_left"></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
		<tr><td colspan="2"><?php echo Core::_("Shop_Order.torg12_vseg_otp")?></td><td colspan="7"><?php echo sprintf("%s %s %s %s", Core_Str::ucfirst(num_propis((int)$fShopOrderItemSum)), Core_Str::declension((int)$fShopOrderItemSum, 'руб', array('лей', 'ль', 'ля', 'ля', 'ля', 'лей', 'лей', 'лей', 'лей', 'лей')), sprintf('%02d', ($fShopOrderItemSum - (int)$fShopOrderItemSum) * 100), Core_Str::declension(($fShopOrderItemSum - (int)$fShopOrderItemSum) * 100, 'копе', array('ек', 'йка', 'йки', 'йки', 'йки', 'ек', 'ек', 'ек', 'ек', 'ек')))?></td><td colspan="2" class="border_left"><?php echo Core::_("Shop_Order.torg12_po_dov")?></td><td class="border_bottom"></td><td><?php echo Core::_("Shop_Order.torg12_from")?></td><td colspan="2" class="border_bottom"></td><td><?php echo Core::_("Shop_Order.torg12_from_y")?></td></tr>
		<tr><td colspan="9"></td><td class="border_left"><?php echo Core::_("Shop_Order.torg12_vyd")?></td><td colspan="6" class="border_bottom"></td></tr>
		<tr><td colspan="2"><?php echo Core::_("Shop_Order.torg12_otp")?></td><td class="border_bottom border_right"></td><td colspan="3" class="border_bottom border_right"></td><td colspan="2" class="border_bottom"></td><td class="tiny_width"></td><td class="border_left"></td><td colspan="6" class="border_bottom align_center align_top small_font"><?php echo Core::_("Shop_Order.torg12_kem")?></td></tr>
		<tr><td colspan="2"></td><td class="align_center align_top small_font"><?php echo Core::_("Shop_Order.torg12_dolj")?></td><td colspan="3" class="align_center align_top small_font"><?php echo Core::_("Shop_Order.torg12_podp")?></td><td colspan="2" class="align_center align_top small_font"><?php echo Core::_("Shop_Order.torg12_ras")?></td><td></td><td class="border_left">Груз принял</td><td colspan="2" class="border_bottom"></td><td colspan="2" class="border_bottom"></td><td colspan="2" class="border_bottom"></td></tr>
		<tr><td colspan="3" class="border_right"><?php echo Core::_("Shop_Order.torg12_glav")?></td><td colspan="3" class="border_bottom border_right"></td><td colspan="2" class="border_bottom"><?php echo $oShop_Order->Shop->Shop_Company->accountant_legal_name?></td><td></td><td class="border_left"></td><td colspan="2" class="border_right align_center align_top small_font"><?php echo Core::_("Shop_Order.torg12_dolj")?></td><td colspan="2" class="border_right align_center align_top small_font"><?php echo Core::_("Shop_Order.torg12_podp")?></td><td colspan="2" class="border_right align_center align_top small_font"><?php echo Core::_("Shop_Order.torg12_ras")?></td></tr>
		<tr><td colspan="3"></td><td colspan="3" class="align_center align_top small_font"><?php echo Core::_("Shop_Order.torg12_podp")?></td><td colspan="2" class="align_center align_top small_font"><?php echo Core::_("Shop_Order.torg12_ras")?></td><td></td><td colspan="7" class="border_left"></td></tr>
		<tr><td colspan="2"><?php echo Core::_("Shop_Order.torg12_otp_pr")?></td><td class="border_bottom border_right"></td><td colspan="3" class="border_bottom border_right"></td><td colspan="2" class="border_bottom"></td><td></td><td class="border_left"><?php echo Core::_("Shop_Order.torg12_gr_poluch")?></td><td colspan="2" class="border_bottom border_right"></td><td colspan="2" class="border_bottom border_right"></td><td colspan="2" class="border_bottom border_right"></td></tr>
		<tr><td colspan="2"></td><td class="align_center align_top small_font"><?php echo Core::_("Shop_Order.torg12_dolj")?></td><td colspan="3" class="align_center align_top small_font"><?php echo Core::_("Shop_Order.torg12_podp")?></td><td colspan="2" class="align_center align_top small_font"><?php echo Core::_("Shop_Order.torg12_ras")?></td><td></td><td class="border_left"><?php echo Core::_("Shop_Order.torg12_gruzopol_s")?></td><td colspan="2" class="border_right align_center align_top small_font"><?php echo Core::_("Shop_Order.torg12_dolj")?></td><td colspan="2" class="border_right align_center align_top small_font"><?php echo Core::_("Shop_Order.torg12_podp")?></td><td colspan="2" class="border_right align_center align_top small_font"><?php echo Core::_("Shop_Order.torg12_ras")?></td></tr>
		<tr><td colspan="9" class="align_center"><?php echo Core::_("Shop_Order.torg12_mp")?></td><td colspan="7"  class="border_left align_center"><?php echo Core::_("Shop_Order.torg12_mp")?></td></tr>
	</table>
	</body>
</html>

