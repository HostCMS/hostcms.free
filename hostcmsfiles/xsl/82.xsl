<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "lang://82">
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>

	<!-- БанковскийСчет -->
	<xsl:decimal-format name="my" decimal-separator="," grouping-separator=" "/>

	<xsl:template match="/">
		<xsl:apply-templates select="shop"/>
	</xsl:template>

	
	<xsl:template match="shop">
		<html>
			<head>
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

			<body>
				<div class="main_div">
					<p><img src="http://{/shop/site/site_alias/name}/images/logo.png" border="0" /></p>

					<p>
						<b>&labelProviderName;:</b><xsl:text> </xsl:text><xsl:value-of select="company/name"/>
						<br />
						<b>&labelTinKpp;:</b><xsl:text> </xsl:text><xsl:value-of select="company/tin"/>/<xsl:value-of select="company/kpp"/>
						<br />
						<b>&labelPsrn;:</b><xsl:text> </xsl:text><xsl:value-of select="company/psrn"/>
						<br />
						<b>&labelAddress;:</b><xsl:text> </xsl:text><xsl:value-of select="company/directory_address/full_address"/>
						<br />
						<b>&labelPhone;:</b><xsl:text> </xsl:text><xsl:value-of select="company/directory_phone/value"/>
						<br />
						<b>&labelEmail;:</b><xsl:text> </xsl:text><xsl:value-of select="company/directory_email/value"/>
						<br />
						<b>&labelSite;:</b><xsl:text> </xsl:text><xsl:value-of select="company/directory_website/value"/>
					</p>
					<p>
						<table cellpadding="1" cellspacing="0" width="100%">
							<tr>
								<td colspan="4" align="center">
									<span style="font-weight: bold; font-size: 11pt">&labelSamplePaymentOrder;</span>
								</td>
							</tr>
							<tr>
								<td class="td_main">&labelTin;<xsl:text> </xsl:text><xsl:value-of select="company/tin"/></td>
								<td class="td_main">&labelKpp;<xsl:text> </xsl:text><xsl:value-of select="company/kpp"/></td>
								<td class="td_main" style="border-right: black 1px solid;" rowspan="2" colspan="2"></td>
							</tr>
							<tr>
								<td class="td_main" colspan="2" width="50%">
									<b>&labelRecipient;</b>
								</td>
							</tr>
							<tr>
								<td class="td_main" colspan="2">
									<xsl:value-of select="company/name"/>
								</td>
								<td class="td_main" width="100">
									<b>&labelAccountNumber;</b>
								</td>
								<td class="td_main" style="border-right: black 1px solid;">
									<xsl:value-of select="company/current_account"/>
								</td>
							</tr>
							<tr>
								<td class="td_main" colspan="2">
									<b>&labelPayeesBank;</b>
								</td>
								<td class="td_main">
									<b>&labelBic;</b>
								</td>
								<td class="td_main" style="border-right: black 1px solid;">
									<xsl:value-of select="company/bic"/>
								</td>
							</tr>
							<tr>
								<td class="td_main" style="border-bottom: black 1px solid;" colspan="2">
									<xsl:value-of select="company/bank_name"/><xsl:text> </xsl:text><xsl:value-of select="company/bank_address"/>
								</td>
								<td class="td_main" style="border-bottom: black 1px solid;">
									<b>&labelCorrespondentAccount;</b>
								</td>
								<td class="td_main" style="border-bottom: black 1px solid; border-right: black 1px solid">
									<xsl:value-of select="company/correspondent_account"/>
								</td>
							</tr>
						</table>
					</p>

					<p><b>&labelMessageTitle;</b>
					<br />&labelMessageText;
					</p>

					<p align="center"><span style="font-weight: bold; font-size: 12pt">&labelAccountNumber; <xsl:value-of select="shop_order/invoice"/> &labelDateFrom; <xsl:value-of select="shop_order/date"/></span></p>

					<table cellpadding="2" cellspacing="0" width="100%">
						<tr>
							<td align="right">&labelCustomer;:</td>
							<td>
								<b>
									<xsl:choose>
										<!-- Указана компания-плательщик -->
										<xsl:when test="shop_order/company != ''">
											<xsl:value-of select="shop_order/company"/>
										</xsl:when>
										<!-- ФИО -->
										<xsl:otherwise>
											<xsl:value-of select="shop_order/surname"/><xsl:text> </xsl:text><xsl:value-of select="shop_order/name"/><xsl:text> </xsl:text><xsl:value-of select="shop_order/patronymic"/>
										</xsl:otherwise>
									</xsl:choose>
								</b>
							</td>
						</tr>
						<tr>
							<td align="right">&labelAddress;:</td>
							<td>
								<!-- Address -->
								<xsl:if test="/shop/shop_order/postcode != ''">
									<xsl:value-of select="/shop/shop_order/postcode"/>,
								</xsl:if>
								<xsl:if test="/shop/shop_order/shop_country/name != ''">
									<xsl:value-of select="/shop/shop_order/shop_country/name"/>
								</xsl:if>
								<xsl:if test="/shop/shop_order/shop_country/shop_country_location/name != ''">, <xsl:value-of select="/shop/shop_order/shop_country/shop_country_location/name"/></xsl:if>
								<xsl:if test="/shop/shop_order/shop_country/shop_country_location/shop_country_location_city/name != ''">, <xsl:value-of select="/shop/shop_order/shop_country/shop_country_location/shop_country_location_city/name"/></xsl:if>
								<xsl:if test="/shop/shop_order/shop_country/shop_country_location/shop_country_location_city/shop_country_location_city_area/name != ''">, <xsl:value-of select="/shop/shop_order/shop_country/shop_country_location/shop_country_location_city/shop_country_location_city_area/name"/><xsl:text> </xsl:text>&labelDistrict;</xsl:if>
								<xsl:if test="/shop/shop_order/address != ''">, <xsl:value-of select="/shop/shop_order/address"/></xsl:if>
							</td>
						</tr>
						<xsl:if test="shop_order/phone/node() or shop_order/fax/node()">
						<tr>
							<td align="right">&labelPhoneFax;:</td>
							<td>
								<xsl:value-of select="shop_order/phone"/>
								<xsl:if test="shop_order/fax/node() and shop_order/fax != shop_order/phone"><xsl:text> / </xsl:text><xsl:value-of select="shop_order/fax"/></xsl:if>
							</td>
						</tr>
						</xsl:if>
					</table>

					<br />

					<table cellpadding="3" cellspacing="0" width="100%">
					<tr>
						<td class="td_header">№</td>
						<td class="td_header">&labelOrderItemName;</td>
						<td class="td_header">&labelOrderItemCount;</td>
						<td class="td_header">&labelOrderMeasure;</td>
						<td class="td_header">&labelOrderItemPrice;,<xsl:text> </xsl:text><xsl:value-of disable-output-escaping="yes" select="/shop/shop_order/shop_currency/sign" /></td>
						<td class="td_header">&labelOrderValueAddedTaxRate;</td>
						<td class="td_header">&labelOrderValueAddedTax;,<xsl:text> </xsl:text><xsl:value-of disable-output-escaping="yes" select="/shop/shop_order/shop_currency/sign" /></td>
						<td class="td_header" style="border-right: black 1px solid; border-left: black 1px solid;">&labelOrderAmount;, <xsl:text> </xsl:text><xsl:value-of disable-output-escaping="yes" select="/shop/shop_order/shop_currency/sign" /></td>
					</tr>
					<!-- Ordered Items -->
					<xsl:apply-templates select="shop_order/shop_order_item"/>
					<!-- Total Tax -->
					<tr class="tr_footer">
						<td align="right" colspan="6" style="border-bottom: black 1px solid;">
							<b>&labelOrderIncludingVat;:</b>
						</td>
						<td align="right" colspan="2" style="border-bottom: black 1px solid;">
							<xsl:choose>
							<xsl:when test="shop_order/total_tax != 0">
								<xsl:value-of select="format-number(shop_order/total_tax, '### ##0,00', 'my')"/><xsl:text> </xsl:text><xsl:value-of disable-output-escaping="yes" select="/shop/shop_order/shop_currency/sign" />
							</xsl:when>
							<xsl:otherwise>
								<b>&labelOrderWithoutVat;</b>
							</xsl:otherwise>
							</xsl:choose>

						</td>
					</tr>
					<!-- Total -->
					<tr class="tr_footer">
						<td align="right" colspan="6">
							<b>&labelOrderTotalAmount;:</b>
						</td>
						<td align="right" colspan="2">
							<b>
								<xsl:value-of select="format-number(shop_order/total_amount, '### ##0,00', 'my')"/><xsl:text> </xsl:text><xsl:value-of disable-output-escaping="yes" select="/shop/shop_order/shop_currency/sign" /></b>
						</td>
					</tr>
					</table>

					<br/>
					<br/>
					<br/>

					<table cellpadding="2" cellspacing="0" width="100%">
						<tr>
							<td width="200" height="50">&labelOrderDirector;</td>
							<td style="border-bottom: black 1px solid;"></td>
							<td width="200">
								<xsl:value-of select="company/legal_name"/>
							</td>
						</tr>
						<tr>
							<td width="200" height="50">&labelOrderChiefAccountant;</td>
							<td style="border-bottom: black 1px solid;"> </td>
							<td>
								<xsl:value-of select="company/accountant_legal_name"/>
							</td>
						</tr>
						<tr>
							<td height="50" align="center" colspan="3">
								<b>&labelOrderPlaceStamp;</b>
							</td>
						</tr>
					</table>

					<br />

					<p>
					<ol>
					<li>&labelOrderLine1;</li>
					</ol>
					</p>
				</div>
			</body>
		</html>
	</xsl:template>
	
	<xsl:template match="shop_order/shop_order_item">
		<xsl:variable name="tax_sum_item" select="quantity * price div (100 + rate) * rate" />
		<tr>
			<td class="td_main_2" style="text-align: center">
				<xsl:value-of select="position()"/>
			</td>
			<td class="td_main_2">
				<xsl:value-of select="name"/>
			</td>
			<td class="td_main_2" style="text-align: center">
				<xsl:value-of select="quantity"/>
			</td>
			<td class="td_main_2" style="text-align: center">
				<xsl:value-of select="shop_item/shop_measure/name"/>
			</td>
			<td class="td_main_2" style="white-space: nowrap">
				<xsl:choose>
					<xsl:when test="$tax_sum_item != 0">
						<xsl:value-of select="format-number(price - $tax_sum_item div quantity, '### ##0,00', 'my')"/>
					</xsl:when>
					<xsl:otherwise>
						<xsl:value-of select="format-number(price, '### ##0,00', 'my')"/>
					</xsl:otherwise>
				</xsl:choose>
			</td>
			<td class="td_main_2" align="center">
				<xsl:choose>
					<xsl:when test="rate != 0">
						<xsl:value-of select="rate"/>%</xsl:when>
					<xsl:otherwise>—</xsl:otherwise>
				</xsl:choose>
			</td>
			<td class="td_main_2" align="center">
				<xsl:choose>
					<xsl:when test="$tax_sum_item != 0">
						<xsl:value-of select="format-number($tax_sum_item, '### ##0,00', 'my')"/>
					</xsl:when>
					<xsl:otherwise>—</xsl:otherwise>
				</xsl:choose>
			</td>
			<td class="td_main_2" style="white-space: nowrap; border-right: black 1px solid;">
				<xsl:if test="price * quantity != 0">
					<xsl:value-of select="format-number(price * quantity, '### ##0,00', 'my')"/>
				</xsl:if>
			</td>
		</tr>
	</xsl:template>
</xsl:stylesheet>