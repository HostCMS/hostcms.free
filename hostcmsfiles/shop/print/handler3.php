<?php

/* Акт */
class Shop_Print_Form_Handler3 extends Shop_Print_Form_Handler
{
	/**
	 * Метод, запускающий выполнение обработчика
	 */
	function execute()
	{
		parent::execute();

		$oShop_Order = $this->_Shop_Order;
		$oShop = $oShop_Order->Shop;

		$sPageTitle = sprintf("Акт № %s от %s", $oShop_Order->acceptance_report, Core_Date::sql2date($oShop_Order->acceptance_report_datetime)) . " г.";
		$sShopCurrency = $oShop_Order->Shop_Currency->name ? ', ' . $oShop_Order->Shop_Currency->name : '';

		$oCompany = $oShop_Order->company_id
			? $oShop_Order->Shop_Company
			: $oShop->Shop_Company;

		$sFullCompanyAddress = $sCompanyPhone = '';

		$aDirectory_Addresses = $oCompany->Directory_Addresses->findAll();
		if (isset($aDirectory_Addresses[0]))
		{
			$aCompanyAddress = array(
				$aDirectory_Addresses[0]->postcode,
				$aDirectory_Addresses[0]->country,
				$aDirectory_Addresses[0]->city,
				$aDirectory_Addresses[0]->value
			);

			$aCompanyAddress = array_filter($aCompanyAddress, 'strlen');
			$sFullCompanyAddress = implode(', ', $aCompanyAddress);
		}

		$aDirectory_Phones = $oCompany->Directory_Phones->findAll();
		if (isset($aDirectory_Phones[0]))
		{
			$sCompanyPhone = $aDirectory_Phones[0]->value;
		}
		?>
		<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
		<html xmlns="http://www.w3.org/1999/xhtml">
			<head>
				<title><?php echo htmlspecialchars($sPageTitle)?></title>
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
					table.first th, table.first td{border: 1px solid black;}
					table.first th{ white-space: nowrap; font-size: 90% }
					table.first td.border_none{border: none;}
				</style>
			</head>
			<body class="font_sans_serif base_font">
				<p class="font_weight_bold text_underline disable_indentation"><?php echo htmlspecialchars($oCompany->name)?></p>
				<p class="font_weight_bold disable_indentation"><?php echo "Адрес: " . htmlspecialchars($sFullCompanyAddress) . ", тел.: " . htmlspecialchars($sCompanyPhone)?></p>
				<h1 class="text_align_center center big_font"><?php echo htmlspecialchars($sPageTitle)?></h1>
				<p>
				Заказчик: <?php echo htmlspecialchars($oShop_Order->company)?>
				<br/>Адрес: <?php echo $this->_address?>
				<?php
				if (strlen($oShop_Order->phone))
				{
					?><br/>Телефон: <?php echo htmlspecialchars($oShop_Order->phone)?><?php
				}
				?>
				</p>
				<table cellspacing="0" cellpadding="3" border="0" width="100%" class="first">
				<tr>
					<th class="width_small text_align_center">
						№
					</th>
					<th class="text_align_center">
						Наименование работы (услуги)
					</th>
					<th class="width_small text_align_center">
						Ед. изм.
					</th>
					<th class="width_small text_align_center">
						Количество
					</th>
					<th class="width_small text_align_center">
						Цена<?php echo htmlspecialchars($sShopCurrency)?>
					</th>
					<th class="width_normal text_align_center">
						Сумма<?php echo htmlspecialchars($sShopCurrency)?>
					</th>
				</tr>
				<?php
				$i = 1;

				$aShopOrderItems = $oShop_Order->Shop_Order_Items->findAll();

				$fShopTaxValueSum = $fShopOrderItemSum = 0.0;

				if (count($aShopOrderItems))
				{
					foreach ($aShopOrderItems as $oShopOrderItem)
					{
						$sShopTaxRate = $oShopOrderItem->rate;

						$sShopTaxValue = $sShopTaxRate
							? $oShopOrderItem->getTax() * $oShopOrderItem->quantity
							: 0;

						$fItemAmount = $oShopOrderItem->getAmount();

						$fShopTaxValueSum += $sShopTaxValue;
						$fShopOrderItemSum += $fItemAmount;
						?>
						<tr>
							<td class="text_align_right">
							<?php echo $i++?>
							</td>
							<td>
							<?php echo htmlspecialchars($oShopOrderItem->name)?>
							</td>
							<td class="text_align_center">
							<?php echo htmlspecialchars($oShopOrderItem->Shop_Item->Shop_Measure->name)?>
							</td>
							<td class="text_align_center">
							<?php echo $oShopOrderItem->quantity?>
							</td>
							<td class="text_align_right">
							<?php echo number_format(Shop_Controller::instance()->round($oShopOrderItem->price), 2, '.', '')?>
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
							Итого:
						</td>
						<td class="text_align_right font_weight_bold">
							<?php echo number_format($fShopOrderItemSum, 2, '.', '')?>
						</td>
					</tr>
					<tr>
						<td colspan="5" class="border_none text_align_right font_weight_bold">
							В том числе НДС:
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
					<?php echo sprintf("Всего оказано услуг на сумму %s, в т.ч.: НДС - %s", number_format($fShopOrderItemSum, 2, '.', '') . ' '.htmlspecialchars($oShop_Order->Shop_Currency->name), number_format($fShopTaxValueSum, 2, '.', '') .' ' . htmlspecialchars($oShop_Order->Shop_Currency->name))?>
				</p>
				<p class="disable_indentation">
					Вышеперечисленные услуги выполнены полностью и в срок. Заказчик претензий по объему, качеству и срокам оказания услуг не имеет.
				</p>
				<table cellspacing="0" cellpadding="3" width="100%" class="second">
					<tr>
						<td>
							Исполнитель
						</td>
						<td>
						</td>
						<td>
							Заказчик
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
							подпись
						</td>
						<td>
						</td>
						<td class="text_align_center small_font">
							подпись
						</td>
					</tr>
				</table>
			</body>
		</html>
		<?php

		return $this;
	}
}