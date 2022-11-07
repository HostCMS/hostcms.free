<?php

/* Invoice */
class Shop_Print_Form_Handler4 extends Shop_Print_Form_Handler
{
	/**
	 * Execute print form
	 */
	function execute()
	{
		parent::execute();

		$oShop_Order = $this->_Shop_Order;
		$oShop = $this->_Shop_Order->Shop;
		$sShopCurrency = $oShop_Order->Shop_Currency->code ? $oShop_Order->Shop_Currency->code : '';

		$sPageTitle = "Invoice # " . htmlspecialchars($oShop_Order->acceptance_report);

		$oCompany = $oShop_Order->company_id
			? $oShop_Order->Shop_Company
			: $oShop->Shop_Company;

		$sFullCompanyAddress = $sCompanyPhone = $sCompanyEmail = $sCompanySite = '';

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

		$aDirectory_Emails = $oCompany->Directory_Emails->findAll();
		if (isset($aDirectory_Emails[0]))
		{
			$sCompanyEmail = $aDirectory_Emails[0]->value;
		}

		$aDirectory_Websites = $oCompany->Directory_Websites->findAll();
		if (isset($aDirectory_Websites[0]))
		{
			$sCompanySite = $aDirectory_Websites[0]->value;
		}
		?>

		<!doctype html>
		<html>
		<head>
			<meta charset="utf-8">
			<title><?php echo $sPageTitle?></title>

			<link type="text/css" href="/modules/skin/bootstrap/css/bootstrap.min.css" rel="stylesheet" />
			<link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,700" rel="stylesheet" />

			<script src="/modules/skin/bootstrap/js/jquery-2.0.3.min.js" type="text/javascript"></script>
			<script src="/modules/skin/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>

			<style>
				.invoice-box {
					max-width: 800px;
					/*margin: auto;*/
					padding: 30px;
					border: 1px solid #eee;
					box-shadow: 0 0 10px rgba(0, 0, 0, .15);
					font-size: 12px;
					line-height: 24px;
					ont-family: 'Open Sans', sans-serif;
					color: #000;
					margin: 10px auto;
				}

				.invoice-box img {
					max-width: 300px;
				}

				.invoice-box .invoice-title {
					margin: 30px 0;
					font-weight: 600;
					font-size: 32px;
					color: #000;
				}
				.invoice-box .invoice-title span:first-child {
					color: #999;
					font-weight: 400;
					font-size: 16px;
					margin-left: 10px;
				}
				.invoice-box .invoice-title .invoice-date {
					font-size: 12px;
				}
				.invoice-box .table-responsive {
					margin-top: 30px;
				}
				.invoice-box .sub-table {
					color: #999;
					margin-left: 10px;
					font-style: italic;
				}
				.invoice-box .invoice-total span {
					font-weight: 600;
					color: #000;
					text-transform: uppercase;
					margin-right: 10px;
				}
				.invoice-box .thanks {
					text-align: center;
					margin: 30px 0;
					text-transform: uppercase;
					color: #fff;
					font-size: 16px;
					height: 50px;
					background-color: #d7d7d7;
				}
				.invoice-box .thanks > div {
					padding-top: 15px;
				}
				.invoice-box .bank-details {
					border: 1px dashed #ddd;
					min-height: 150px;
					width: 100%;
					padding: 10px;
				}
				.invoice-box .company {
					text-align: right;
					color: #999;
					font-size:10px;
				}
				.invoice-box .company-name {
					font-size: 40px;
					font-weight: 600;
					margin-bottom: 10px;
					color: #000;
				}
				.invoice-box .company-contacts {
					line-height: 1.4;
				}
				.invoice-box .company-contacts i {
					font-size: 6px;
					margin: 0 5px;
				}

				.table { margin-bottom: 0; }
				.table > thead > tr, .table > tbody > tr:last-child {
					border-bottom: 1px solid #ddd;
				}
				.table > thead > tr > th, .table > tbody > tr > th, .table > tfoot > tr > th, .table > thead > tr > td, .table > tbody > tr > td, .table > tfoot > tr > td	{ border: none; }

				@media only screen and (max-width: 768px)
				{
					.invoice-box { margin-top: 0 }
					.invoice-box .logo, .invoice-box .invoice-title, .invoice-box .company { text-align: center; }
					.invoice-box .company-name { margin-top: 20px; }
					.invoice-box .company { margin-top: 40px; }
				}

			</style>
		</head>

		<body>
			<div class="invoice-box">
				<div class="row">
					<div class="col-xs-12 col-md-6">
						<div class="logo">
							<img src="/admin/images/logo.gif" alt="(^) HostCMS" title="HostCMS">
						</div>
					</div>
					<!-- Company details -->
					<div class="col-xs-12 col-md-6">
						<div class="company">
							<div class="company-name"><?php echo htmlspecialchars($oCompany->name)?></div>
							<div class="company-contacts"><?php echo htmlspecialchars($sFullCompanyAddress)?><br/><?php echo htmlspecialchars($sCompanyPhone)?><br/><?php echo htmlspecialchars($sCompanySite)?><br/><?php echo htmlspecialchars($sCompanyEmail)?></div>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-xs-12">
						<div class="invoice-title">
							Invoice<span># <?php echo htmlspecialchars($oShop_Order->acceptance_report)?></span><br/>
							<span class="invoice-date">Issue Date: <?php echo Core_Date::sql2date($oShop_Order->datetime)?></span>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-xs-12 col-md-6">
						<div class="row">
							<div class="col-xs-12">
								Bill to:
							</div>
							<div class="col-xs-12">
								<div class=""><?php echo htmlspecialchars($oShop_Order->company)?></div>
							</div>
							<div class="col-xs-12">
								<div class=""><?php echo $this->_address?></div>
							</div>
							<div class="col-xs-12">
								<div class=""><?php echo htmlspecialchars($oShop_Order->phone)?></div>
							</div>
							<div class="col-xs-12">
								<div class=""><?php echo htmlspecialchars($oShop_Order->email)?></div>
							</div>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-xs-12">
						<div class="table-responsive">
							<table class="table">
								<thead>
									<tr>
										<th>#</th>
										<th>Item</th>
										<th>Product ID</th>
										<th>Quantity</th>
										<th>Price</th>
										<th>Tax</th>
										<th>Total</th>
									</tr>
								</thead>
								<tbody>
								<?php
								$fShopTaxValueSum = $fShopOrderItemSum = 0.0;

								$aShopOrderItems = $oShop_Order->Shop_Order_Items->findAll();

								if(count($aShopOrderItems))
								{
									foreach ($aShopOrderItems as $key => $oShopOrderItem)
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
											<th scope="row"><?php echo $key + 1?></th>
											<td><?php echo htmlspecialchars((string) $oShopOrderItem->name)?></td>
											<td><?php echo htmlspecialchars((string) $oShopOrderItem->marking)?></td>
											<td><?php echo sprintf('%.0f', $oShopOrderItem->quantity)?></td>
											<td><?php echo number_format(Shop_Controller::instance()->round($oShopOrderItem->price), 2, '.', '')?></td>
											<td><?php echo $sShopTaxRate != 0 ? "{$sShopTaxRate}%" : '-'?></td>
											<td><?php echo number_format($sItemAmount, 2, '.', '')?></td>
										</tr>
										<?php
									}
								}
								?>
								</tbody>
							</table>
						</div>
						<div class="sub-table">* Prices are in <?php echo htmlspecialchars($sShopCurrency)?></div>
					</div>
					<div class="col-xs-12">
						<div class="invoice-total pull-right">
							<span>Total:</span> <?php echo sprintf("%.2f", $fShopOrderItemSum) . " " . htmlspecialchars($oShop->Shop_Currency->name)?>
						</div>
					</div>
					<!-- Bank details -->
					<div class="col-xs-12 col-md-6">
						<div class="bank-details">
							<div class="row">
								<div class="col-xs-12">
									Payment to:
								</div>
								<div class="col-xs-12">
									Please make payment to the following account
								</div>
								<div class="col-xs-12">
									Bank: <?php echo htmlspecialchars($oCompany->bank_name)?>
								</div>
								<div class="col-xs-12">
									IBAN: <?php echo htmlspecialchars($oCompany->current_account)?>
								</div>
								<div class="col-xs-12">
									Swift Code: <?php echo htmlspecialchars($oCompany->bic)?>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-xs-12">
						<div class="thanks">
							<div>Thank you for your business</div>
						</div>
					</div>
				</div>
			</div>
		</body>
		</html>

		<?php
		return $this;
	}
}