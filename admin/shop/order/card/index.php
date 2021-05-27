<?php
/**
 * Online shop.
 *
 * @package HostCMS
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
require_once('../../../../bootstrap.php');

Core_Auth::authorization('shop');

$sAdminFormAction = '/admin/shop/order/card/index.php';

$shop_order_id = intval(Core_Array::getGet('shop_order_id'));

$oShop_Order = Core_Entity::factory('Shop_Order')->getById($shop_order_id);

if (is_null($oShop_Order))
{
	throw new Core_Exception('Shop_Order does not exist');
}

$oShop = $oShop_Order->Shop;

$oCompany = $oShop_Order->company_id
	? $oShop_Order->Shop_Company
	: $oShop->Shop_Company;

$sFullAddress = $oShop_Order->getFullAddress();

ob_start();
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<title><?php echo htmlspecialchars(Core::_("Shop_Order.order_card_dt", $oShop_Order->invoice, Core_Date::sql2datetime($oShop_Order->datetime)))?></title>
<meta content="text/html; charset=UTF-8" http-equiv=Content-Type>

<style type="text/css">html, body, td
	{
		font-family: Arial, Verdana, Tahoma, sans-serif;
		font-size: 9pt;
		background-color: #FFFFFF;
		color: #000000;
	}

	.main_div
	{
		margin-left: 0.5em;
		margin-right: 0.5em;
		margin-top: 2em;
		margin-bottom: 1em;
	}

	.td_main
	{
		border-top: black 1px solid;
		border-left: black 1px solid;
	}

	.td_header
	{
		border-left: black 1px solid;
		border-top: black 1px solid;
		border-bottom: black 1px solid;
		text-align: center;
		font-weight: bold;
	}

	.td_main_2
	{
		border-left: black 1px solid;
		border-bottom: black 1px solid;
	}

	.tr_footer td
	{
		font-size: 11pt;
		font-weight: bold;
		white-space: nowrap;
	}

	table, td
	{
		empty-cells: show;
	}
</style>
</head>
<body style="margin: 3.5em">

<?php
if (defined('SHOP_ORDER_CARD_XSL'))
{
	$oXsl = Core_Entity::factory('Xsl')->getByName(SHOP_ORDER_CARD_XSL);

	if (!is_null($oXsl))
	{
		$aShopOrderItems = $oShop_Order->Shop_Order_Items->findAll();

		$fShopTaxValueSum = $fShopOrderItemSum = 0.0;

		foreach ($aShopOrderItems as $oShop_Order_Item)
		{
			$sShopTaxRate = $oShop_Order_Item->rate;

			$fShopTaxValue = $sShopTaxRate
				? $oShop_Order_Item->getTax() * $oShop_Order_Item->quantity
				: 0;

			$sItemAmount = $oShop_Order_Item->getAmount();

			$fShopTaxValueSum += $fShopTaxValue;
			$fShopOrderItemSum += $sItemAmount;
		}

		$oShop
			// ->addEntity($oCompany->clearEntities())
			->addEntity(
				$oShop->Site->clearEntities()->showXmlAlias()
			)
			->addEntity(
				$oShop_Order->clearEntities()
					->showXmlCurrency(TRUE)
					->showXmlCountry(TRUE)
					->showXmlItems(TRUE)
					->showXmlDelivery(TRUE)
					->showXmlPaymentSystem(TRUE)
					->showXmlSiteuser(TRUE)
					->showXmlOrderStatus(TRUE)
					->showXmlDelivery(TRUE)
					->showXmlProperties(TRUE)
					->addEntity(
						Core::factory('Core_Xml_Entity')
							->name('shop_tax_value_sum')
							->value($fShopTaxValueSum)
					)
					->addEntity(
						Core::factory('Core_Xml_Entity')
							->name('shop_order_item_sum')
							->value($fShopOrderItemSum)
					)
			)
			->addEntity(
				Core::factory('Core_Xml_Entity')
					->name('full_address')
					->value(strval($sFullAddress))
			);

		$sXml = $oShop->getXml();

		Core::setLng($oShop->Site->lng);

		$return = Xsl_Processor::instance()
				->xml($sXml)
				->xsl($oXsl)
				->process();

		echo $return;
	}
	else
	{
		throw new Core_Exception('XSL template %name does not exist.', array(
			'%name' => SHOP_ORDER_CARD_XSL
		));
	}
}
else
{
	?>
	<p style="margin-bottom: 40px"><img src="/admin/images/logo.gif" alt="(^) HostCMS" title="HostCMS"></p>

	<table cellpadding="2" cellspacing="2" border="0" width="100%">
		<tr>
			<td valign="top" width="17%">
				<?php echo Core::_("Shop_Order.order_card_supplier") . ":"?>
			</td>
			<td valign="top">
				<b>
					<?php echo htmlspecialchars($oCompany->name)?>
				</b>
			</td>
		</tr>
		<tr>
			<td valign="top">
				<?php echo Core::_("Shop_Order.order_card_inn_kpp") . ":"?>
			</td>
			<td valign="top">
				<b>
					<?php echo htmlspecialchars($oCompany->tin . "/" . $oCompany->kpp)?>
				</b>
			</td>
		</tr>
		<tr>
			<td valign="top">
				<?php echo Core::_("Shop_Order.order_card_ogrn") . ":"?>
			</td>
			<td valign="top">
				<b>
					<?php echo htmlspecialchars($oCompany->psrn)?>
				</b>
			</td>
		</tr>
		<tr>
			<td valign="top">
				<?php echo Core::_("Shop_Order.order_card_address") . ":"?>
			</td>
			<td valign="top">
				<b>
					<?php
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

						echo htmlspecialchars($sFullCompanyAddress);
					}
					?>
				</b>
			</td>
		</tr>
		<tr>
			<td valign="top">
				<?php echo Core::_("Shop_Order.order_card_phone") . ":"?>
			</td>
			<td valign="top">
				<b>
					<?php
					$aDirectory_Phones = $oCompany->Directory_Phones->findAll();
					if (isset($aDirectory_Phones[0]))
					{
						echo htmlspecialchars($aDirectory_Phones[0]->value);
					}
					?>
				</b>
			</td>
		</tr>
		<tr>
			<td valign="top">
				<?php echo Core::_("Shop_Order.order_card_email") . ":"?>
			</td>
			<td valign="top">
				<b>
					<?php
					$aDirectory_Emails = $oCompany->Directory_Emails->findAll(FALSE);
					if (isset($aDirectory_Emails[0]))
					{
						echo htmlspecialchars($aDirectory_Emails[0]->value);
					}
					?>
				</b>
			</td>
		</tr>
		<tr>
			<td valign="top">
				<?php echo Core::_("Shop_Order.order_card_site") . ":"?>
			</td>
			<td valign="top">
				<b>
					<?php
					$aDirectory_Websites = $oCompany->Directory_Websites->findAll(FALSE);
					if (isset($aDirectory_Websites[0]))
					{
						echo htmlspecialchars($aDirectory_Websites[0]->value);
					}
					?>
				</b>
			</td>
		</tr>
	</table>

	<h2 align="center"><?php echo htmlspecialchars(Core::_("Shop_Order.order_card_dt", $oShop_Order->invoice, Core_Date::sql2date($oShop_Order->datetime)))?></h2>

	<table cellpadding="2" cellspacing="2" border="0" width="100%">
		<tr>
			<td valign="top" width="17%">
				<?php echo Core::_("Shop_Order.payer") . ":"?>
			</td>
			<td valign="top">
				<b>
					<?php echo htmlspecialchars($oShop_Order->company)?>
				</b>
			</td>
		</tr>
		<tr>
			<td valign="top">
				<?php echo Core::_("Shop_Order.order_card_contact_person") . ":"?>
			</td>
			<td valign="top">
				<b>
					<?php echo htmlspecialchars($oShop_Order->surname . " " . $oShop_Order->name . " " . $oShop_Order->patronymic)?>
				</b>
			</td>
		</tr>
		<tr>
			<td valign="top">
				<?php echo Core::_("Shop_Order.order_card_address") . ":"?>
			</td>
			<td valign="top">
				<b>
					<?php echo htmlspecialchars($sFullAddress)?>
				</b>
			</td>
		</tr>
		<?php
		if (Core::moduleIsActive('siteuser'))
		{
		?>
		<tr>
			<td valign="top">
				<?php echo Core::_("Shop_Order.order_card_site_user") . ":"?>
			</td>
			<td valign="top">
				<b>
					<?php echo htmlspecialchars($oShop_Order->Siteuser->login) . " (" . Core::_("Shop_Order.order_card_site_user_id") . " " . $oShop_Order->Siteuser->id . ")"?>
				</b>
			</td>
		</tr>

		<?php
		}
		?>
		<tr>
			<td valign="top">
				<?php echo Core::_("Shop_Order.order_card_phone") . ":"?>
			</td>
			<td valign="top">
				<b>
					<?php echo htmlspecialchars($oShop_Order->phone)?>
				</b>
			</td>
		</tr>
		<tr>
			<td valign="top">
				<?php echo Core::_("Shop_Order.order_card_fax") . ":"?>
			</td>
			<td valign="top">
				<b>
					<?php echo htmlspecialchars($oShop_Order->fax)?>
				</b>
			</td>
		</tr>
		<tr>
			<td valign="top">
				<?php echo Core::_("Shop_Order.order_card_email") . ":"?>
			</td>
			<td valign="top">
				<b>
					<?php echo htmlspecialchars($oShop_Order->email)?>
				</b>
			</td>
		</tr>
	</table>
	<br>
	<table cellspacing="0" cellpadding="3" width="100%">
	<tr>
		<td class="td_header">
			<?php echo "№"?>
		</td>
		<td class="td_header">
			<?php echo Core::_("Shop_Order.table_description")?>
		</td>
		<td class="td_header">
			<?php echo Core::_("Shop_Order.table_item_status")?>
		</td>
		<td class="td_header">
			<?php echo Core::_("Shop_Order.table_mark")?>
		</td>
		<td class="td_header">
			<?php echo Core::_("Shop_Order.table_mesures")?>
		</td>
		<td class="td_header">
			<?php echo Core::_("Shop_Order.table_warehouse")?>
		</td>
		<td class="td_header">
			<?php echo Core::_("Shop_Order.table_price") . ", <br/>" . htmlspecialchars($oShop->Shop_Currency->name)?>
		</td>
		<td class="td_header">
			<?php echo Core::_("Shop_Order.table_amount")?>
		</td>
		<td class="td_header">
			<?php echo Core::_("Shop_Order.table_nds_tax")?>
		</td>
		<td class="td_header">
			<?php echo Core::_("Shop_Order.table_nds_value") . ", <br/>" . htmlspecialchars($oShop->Shop_Currency->name)?>
		</td>
		<td class="td_header" style="border-right: 1px solid black; white-space: nowrap">
			<?php echo Core::_("Shop_Order.table_amount_value") . ", <br/>" . htmlspecialchars($oShop->Shop_Currency->name)?>
		</td>
	</tr>
	<?php
	$i = 1;

	$aShopOrderItems = $oShop_Order->Shop_Order_Items->findAll();

	$fShopTaxValueSum = $fShopOrderItemSum = 0.0;

	if (count($aShopOrderItems))
	{
		foreach ($aShopOrderItems as $oShop_Order_Item)
		{
			$sShopTaxRate = $oShop_Order_Item->rate;

			$fShopTaxValue = $sShopTaxRate
				? $oShop_Order_Item->getTax() * $oShop_Order_Item->quantity
				: 0;

			// Не установлен статус у товара или статус НЕ отмененный
			$bNotCanceled = !$oShop_Order_Item->shop_order_item_status_id || !$oShop_Order_Item->Shop_Order_Item_Status->canceled;

			$fItemAmount = $bNotCanceled
				? $oShop_Order_Item->getAmount()
				: 0;

			$fShopTaxValueSum += $fShopTaxValue;
			$fShopOrderItemSum += $fItemAmount;

			?>
			<tr>
			<td style="text-align: center" class="td_main_2" >
			<?php echo $i++?>
			</td>
			<td class="td_main_2">
			<?php echo htmlspecialchars($oShop_Order_Item->name)?>
			</td>
			<td class="td_main_2">
				<?php
				if ($oShop_Order_Item->shop_order_item_status_id)
				{
					echo htmlspecialchars($oShop_Order_Item->Shop_Order_Item_Status->name);
				}
				?>
			</td>
			<td class="td_main_2">
			<?php echo htmlspecialchars($oShop_Order_Item->marking)?>
			</td>
			<td class="td_main_2">
			<?php echo htmlspecialchars($oShop_Order_Item->Shop_Item->Shop_Measure->name)?>
			</td>
			<td style="text-align: center"  class="td_main_2">
			<?php echo htmlspecialchars($oShop_Order_Item->Shop_Warehouse->name)?><br/><?php echo htmlspecialchars($oShop_Order_Item->getCellName())?>
			</td>
			<td class="td_main_2">
			<?php echo number_format(Shop_Controller::instance()->round($oShop_Order_Item->price), 2, '.', '')?>
			</td>
			<td style="text-align: center" class="td_main_2">
			<?php echo $oShop_Order_Item->quantity?>
			</td>
			<td style="text-align: center" class="td_main_2">
			<?php echo $sShopTaxRate != 0 ? "{$sShopTaxRate}%" : '-'?>
			</td>
			<td style="text-align: center" class="td_main_2">
			<?php echo $fShopTaxValue != 0 ? $fShopTaxValue : '-'?>
			</td>
			<td class="td_main_2" style="border-right: 1px solid black; white-space: nowrap">
			<?php echo $bNotCanceled ? number_format($fItemAmount, 2, '.', '') : '-'?>
			</td>
			</tr><?php
		}
	}
	?>
	</table>
	<table width="100%" cellspacing="0" cellpadding="3">
	<tr class="tr_footer">
		<td width="80%" align="right" style="border-bottom: 1px solid black" colspan="6">
			<?php echo Core::_("Shop_Order.table_nds")?>
		</td>
		<td width="80%" align="right"  style="border-bottom: 1px solid black" colspan="2">
			<?php echo sprintf("%.2f", $fShopTaxValueSum) . " " . htmlspecialchars($oShop->Shop_Currency->name)?>
		</td>
	</tr>
	<tr class="tr_footer">
		<td align="right" colspan="6">
			<?php echo Core::_("Shop_Order.table_all_to_pay")?>
		</td>
		<td align="right" colspan="2">
			<?php echo sprintf("%.2f", $fShopOrderItemSum) . " " . htmlspecialchars($oShop->Shop_Currency->name)?>
		</td>
	</tr>
	</table>

	<table cellpadding="2" cellspacing="2" border="0"  width="100%">
	<tr>
		<td valign="top" width="30%">
			<?php echo Core::_("Shop_Order.order_card_system_of_pay") . ": "?>
		</td>
		<td valign="top">
			<b><?php echo htmlspecialchars($oShop_Order->Shop_Payment_System->name)?></b>
		</td>
	</tr>
	<tr>
		<td valign="top">
			<?php echo Core::_("Shop_Order.order_card_status_of_pay") . ": "?>
		</td>
		<td valign="top">
			<?php
			if ($oShop_Order->paid)
			{
				echo "<b>" . Core::_("Admin_Form.yes") . "</b>";
			}
			else
			{
				echo Core::_("Admin_Form.no");
			}
			?>
		</td>
	</tr>
	<tr>
		<td valign="top">
			<?php echo Core::_("Shop_Order.order_card_cancel") . ": "?>
		</td>
		<td valign="top">
			<?php
			if ($oShop_Order->canceled)
			{
				echo "<b>" . Core::_("Admin_Form.yes") . "</b>";
			}
			else
			{
				echo Core::_("Admin_Form.no");
			}

			?>
		</td>
	</tr>
	<?php
	if ($oShop_Order->shop_order_status_id)
	{
		?>
		<tr>
			<td valign="top">
				<?php echo Core::_("Shop_Order.order_card_order_status") . ": "?>
			</td>
			<td valign="top">
				<b><?php echo htmlspecialchars($oShop_Order->Shop_Order_Status->name) . ' (' . Core_Date::sql2datetime($oShop_Order->status_datetime) . ')'?></b>
			</td>
		</tr>
		<?php
	}
	if ($oShop_Order->shop_delivery_condition_id)
	{
		?>
		<tr>
			<td valign="top">
				<?php echo Core::_("Shop_Order.order_card_type_of_delivery") . ": "?>
			</td>
			<td valign="top">
				<b><?php echo htmlspecialchars($oShop_Order->Shop_Delivery_Condition->Shop_Delivery->name) . " (" . htmlspecialchars($oShop_Order->Shop_Delivery_Condition->name) . ")"?></b>
			</td>
		</tr>
		<?php
	}
	if ($oShop_Order->description)
	{
		?>
		<tr>
			<td valign="top">
				<?php echo Core::_("Shop_Order.order_card_description") . ": "?>
			</td>
			<td>
				<?php echo nl2br(htmlspecialchars($oShop_Order->description))?>
			</td>
		</tr>
		<?php
	}
	if ($oShop_Order->system_information)
	{
		?>
		<tr>
			<td valign="top">
				<?php echo Core::_("Shop_Order.order_card_system_info") . ": "?>
			</td>
			<td>
				<?php echo nl2br(htmlspecialchars($oShop_Order->system_information))?>
			</td>
		</tr>
		<?php
	}

	if (defined('SHOP_ORDER_CARD_PROPERTY'))
	{
		$aProperty_Values = $oShop_Order->getPropertyValues();

		if (count($aProperty_Values))
		{
			?><tr><td colspan="2"></td></tr><?php
			foreach ($aProperty_Values as $oProperty_Value)
			{
				if ($oProperty_Value->Property->type != 2)
				{
					?><tr>
						<td><?php echo htmlspecialchars($oProperty_Value->Property->name)?>:</td>
						<td><?php echo htmlspecialchars($oProperty_Value->value)?></td>
					</tr><?php
				}
			}
		}
	}

	if ($oShop_Order->source_id)
	{
		?><tr><td colspan="2"></td></tr><?php

		$oSource = $oShop_Order->Source;

		$aSourceFields = array('service', 'campaign', 'ad', 'source', 'medium', 'content', 'term');

		foreach ($aSourceFields as $sFieldName)
		{
			if ($oSource->$sFieldName != '')
			{
				?><tr>
					<td><?php echo Core::_('Source.' . $sFieldName)?>:</td>
					<td><?php echo htmlspecialchars($oSource->$sFieldName)?></td>
				</tr><?php
			}
		}
	}
	?>
	</table>
	<?php
}
?>
</body>
</html>
<?php
echo ob_get_clean();
?>