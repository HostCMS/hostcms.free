<?php

/* Счет-фактура */
class Shop_Print_Form_Handler1 extends Shop_Print_Form_Handler
{
	/**
	 * Метод, запускающий выполнение обработчика
	 */
	function execute()
	{
		parent::execute();

		$oShop_Order = $this->_Shop_Order;

		$sPageTitle = sprintf("Счет-фактура %s от %s г.", $oShop_Order->vat_invoice, Core_Date::sql2date($oShop_Order->vat_invoice_datetime));
		?>

		<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
		<html xmlns="http://www.w3.org/1999/xhtml">
			<head>
				<title><?php echo htmlspecialchars($sPageTitle)?></title>
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
			<div class="text_align_right small_font">Приложение №1<br/>к Правилам ведения журналов учета полученных и выставленных счетов-фактур,<br/>книг покупок и книг продаж при расчетах по налогу на добавленную стоимость,<br/>утвержденным постановлением Правительства Российской Федерации от 2 декабря 2000 г. N 914<br/>(в редакции постановлений Правительства Российской Федерации от 15 марта 2001 г. N 189,<br/>от 27 июля 2002 г. N 575, от 16 февраля 2004 г. N 84, от 11 мая 2006 г. N 283)</div>
			<div class="big_font bold_font"><?php echo $sPageTitle?></div>
			<div>
			Продавец: <?php echo htmlspecialchars($oShop_Order->Shop->Shop_Company->name)?>
			<br/>Адрес: <?php echo htmlspecialchars($oShop_Order->Shop->Shop_Company->address)?>
			<br/>ИНН/КПП продавца <?php echo htmlspecialchars($oShop_Order->Shop->Shop_Company->tin . '/' . $oShop_Order->Shop->Shop_Company->kpp)?>
			<br/>Грузоотправитель и его адрес: ----
			<br/>Грузополучатель и его адрес: ----
			<br/>К платежно-расчетному документу № ____________ от ____________
			<br/>Покупатель <?php echo htmlspecialchars($oShop_Order->company)?>
			<br/>Адрес: <?php echo $this->_address?>
			<br/>ИНН/КПП покупателя <?php echo htmlspecialchars($oShop_Order->tin . '/' . $oShop_Order->kpp)?>
			</div>
			<p class="text_align_right disable_indentation bold_font">
				Валюта: <?php echo htmlspecialchars($oShop_Order->Shop_Currency->name)?>
			</p>
			<table class="first">
			<tr>
				<th>Наименование товара (описание выполненных работ, оказанных услуг), имущественного права</th>
				<th>Единица измерения</th>
				<th>Количество</th>
				<th>Цена (тариф) за единицу измерения</th>
				<th>Стоимость товаров (работ, услуг), имущественных прав, всего без налога</th>
				<th>В том числе акциз</th>
				<th>Налоговая ставка</th>
				<th>Сумма налога</th>
				<th>Стоимость товаров (работ, услуг), имущественных прав, всего с учетом налога</th>
				<th>Страна происхождения</th>
				<th>Номер таможенной декларации</th>
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
						<td><?php echo htmlspecialchars($oShopOrderItem->name)?></td>
						<td><?php echo htmlspecialchars($oShopOrderItem->Shop_Item->Shop_Measure->name)?></td>
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
					<td colspan="7" class="bold_font">Всего к оплате</td>
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
					<td class="text_align_bottom width_normal text_nowrap">Руководитель организации</td>
					<td class="border_bottom width_normal"></td>
					<td class="border_bottom width_big text_align_center text_align_bottom"><?php echo htmlspecialchars($oShop_Order->Shop->Shop_Company->legal_name)?></td>
					<td class="width_small"></td>
					<td class="text_align_bottom width_normal text_nowrap">Главный бухгалтер</td>
					<td class="width_normal border_bottom"></td>
					<td class="width_big border_bottom text_align_center text_align_bottom"><?php echo htmlspecialchars($oShop_Order->Shop->Shop_Company->accountant_legal_name)?></td>
				</tr>
				<tr>
					<td class="text_align_bottom width_normal text_nowrap">Индивидуальный предприниматель</td>
					<td class="text_align_top text_align_center small_font width_normal border_bottom">(подпись)</td>
					<td class="text_align_top text_align_center small_font width_big border_bottom">(ф.и.о.)</td>
					<td class="width_small"></td>
					<td class="width_normal"></td>
					<td class="text_align_top text_align_center small_font width_normal">(подпись)</td>
					<td class="text_align_top text_align_center small_font width_big">(ф.и.о.)</td>
				</tr>
				<tr>
					<td class="width_normal"></td>
					<td class="text_align_top text_align_center small_font width_normal">(подпись)</td>
					<td class="text_align_top text_align_center small_font width_big">(ф.и.о.)</td>
					<td class="width_small"></td>
					<td colspan="3" class="text_align_top text_align_center small_font">(реквизиты свидетельства о государственной регистрации индивидуального предпринимателя)</td>
				</tr>
			</table>
			<p class="disable_indentation small_font">ПРИМЕЧАНИЕ. Первый экземпляр - покупателю, второй экземпляр - продавцу</p>
			</body>
		</html>

		<?php

		return $this;
	}
}