<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "lang://76">
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml" />

	<xsl:decimal-format name="my" decimal-separator="," grouping-separator=" " />

	<!-- ПисьмоАдминистратору -->

	<xsl:template match="/shop">
		<html>
			<head>
				<style type="text/css">html, body, td
					{
					font-family: Arial, Verdana, Tahoma, sans-serif;
					font-size: 10pt;
					background-color: #FFFFFF;
					color: #000000;
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
			<body bgcolor="#FFFFFF" color="#000000">

			<p>&labelTitle1; "<xsl:value-of select="/shop/name" />" &labelTitle2; <b>&labelTitle3; № <xsl:value-of select="shop_order/invoice" /></b>.</p>

				<xsl:apply-templates select="shop_order" />

				<br />

				<xsl:choose>
					<xsl:when test="count(shop_order/shop_order_item)">
						<table cellpadding="3" cellspacing="0" width="100%">
							<tr>
								<td class="td_header">&labelCode;</td>
								<td class="td_header">&labelItemName;</td>
								<td class="td_header">&labelMarking;</td>
								<td class="td_header">&labelQuantity;</td>
								<td class="td_header">&labelPrice;</td>
								<td class="td_header" style="border-right: black 1px solid;">&labelSum;</td>
							</tr>
							<xsl:apply-templates select="shop_order/shop_order_item" />
							<tr class="tr_footer">
							<td colspan="5" align="right"><b>&labelTotal;</b></td>
								<td style="white-space: nowrap">
								<xsl:value-of select="format-number(shop_order/total_amount, '### ##0,00', 'my')" /><xsl:text> </xsl:text><xsl:value-of select="/shop/shop_currency/name" disable-output-escaping="yes" /></td>
							</tr>
						</table>
					</xsl:when>
					<xsl:otherwise>
					<p><b>&labelNone;</b></p>
					</xsl:otherwise>
				</xsl:choose>

				<xsl:if test="count(shop_order/shop_order_item/shop_order_item_digital)">
				<p><b>&labelDigitalItems;</b></p>
					<div style="background-color: #eee; padding: 10px;">
						<xsl:apply-templates select="shop_order/shop_order_item/shop_order_item_digital" />
					</div>
				</xsl:if>

				<p>
					<hr />
					Магазин "<xsl:value-of select="/shop/name"/>".
				</p>
			</body>
		</html>
	</xsl:template>

	<!-- Order Template -->
	<xsl:template match="shop_order">

		<p>
			<xsl:if test="company != ''">
				&labelCompany; <xsl:value-of select="company" /><br />
			</xsl:if>

	&labelName; <xsl:value-of select="surname"/><xsl:text> </xsl:text><xsl:value-of select="name"/><xsl:text> </xsl:text><xsl:value-of select="patronymic"/><br />

			&labelEmail; <xsl:value-of select="email" /><br />

			<xsl:if test="phone != ''">
				&labelPhone; <xsl:value-of select="phone" /><br />
			</xsl:if>
			<xsl:if test="fax != ''">
				&labelFax; <xsl:value-of select="fax" /><br />
			</xsl:if>

			<xsl:if test="shop_order_status/node()">
				&labelStatus; <xsl:value-of select="shop_order_status/name"/>, <xsl:value-of select="status_datetime"/>.<br />
			</xsl:if>

			&labelAddress;
			<xsl:if test="postcode != ''">
				<xsl:value-of select="postcode" /><xsl:text>, </xsl:text>
			</xsl:if>
			<xsl:if test="shop_country/name != ''">
				<xsl:value-of select="shop_country/name" /><xsl:text>, </xsl:text>
			</xsl:if>
			<xsl:if test="shop_country/shop_country_location/name != ''">
				<xsl:value-of select="shop_country/shop_country_location/name" /><xsl:text>, </xsl:text>
			</xsl:if>
			<xsl:if test="shop_country/shop_country_location/shop_country_location_city/name != ''">
				<xsl:value-of select="shop_country/shop_country_location/shop_country_location_city/name" /><xsl:text>, </xsl:text>
			</xsl:if>
			<xsl:if test="shop_country/shop_country_location/shop_country_location_city/shop_country_location_city_area/name != ''">
				<xsl:value-of select="shop_country/shop_country_location/shop_country_location_city/shop_country_location_city_area/name" /><xsl:text>, </xsl:text>
			</xsl:if>
			<xsl:if test="address != ''">
				<xsl:value-of select="address" />
				<xsl:text>, </xsl:text>
			</xsl:if>
			<xsl:if test="house != ''">
				<xsl:value-of select="house" />
				<xsl:text>, </xsl:text>
			</xsl:if>
			<xsl:if test="flat != ''">
				<xsl:value-of select="flat" />
			</xsl:if>

			<br />

			<xsl:if test="shop_delivery/name != ''">
				&labelDelivery;<xsl:value-of select="shop_delivery/name" /><br />
			</xsl:if>

			<xsl:if test="shop_payment_system/name != ''">
				&labelPaymentSystem; <xsl:value-of select="shop_payment_system/name" /><br />
			</xsl:if>

			&labelPaymentStatus; <b><xsl:choose>
					<xsl:when test="paid = '1'">&labelPaid;</xsl:when>
					<xsl:otherwise>&labelNotPaid;</xsl:otherwise>
			</xsl:choose></b>

			<br />

			<xsl:if test="description != ''">
				&labelDescription; <xsl:value-of select="description" disable-output-escaping="yes" /><br />
			</xsl:if>

			<xsl:if test="shop_order_system_information != ''">
				&labelSystemInfo; <xsl:value-of select="shop_order_system_information" /><br />
			</xsl:if>
		</p>
	</xsl:template>

	<!-- Ordered Item Template -->
	<xsl:template match="shop_order/shop_order_item">
		<tr>
			<td class="td_main_2">
				<xsl:value-of select="position()" />
			</td>
			<td class="td_main_2">
				<xsl:value-of select="name" />
			</td>
			<td class="td_main_2">
				<xsl:choose>
					<xsl:when test="marking != ''">
						<xsl:value-of select="marking" />
					</xsl:when>
					<xsl:otherwise>—</xsl:otherwise>
				</xsl:choose>
			</td>
			<td class="td_main_2" style="white-space: nowrap">
				<xsl:value-of select="quantity" /><xsl:text> </xsl:text><xsl:value-of select="shop_item/shop_measure/name" />
			</td>
			<td class="td_main_2" style="white-space: nowrap">
			<xsl:value-of select="format-number(price, '### ##0,00', 'my')" /><xsl:text> </xsl:text><xsl:value-of select="/shop/shop_currency/name" disable-output-escaping="yes" /></td>
			<td class="td_main_2" style="border-right: black 1px solid; white-space: nowrap">
			<xsl:value-of select="format-number(quantity * price, '### ##0,00', 'my')" /><xsl:text> </xsl:text><xsl:value-of select="/shop/shop_currency/name" disable-output-escaping="yes" /></td>
		</tr>
	</xsl:template>

	<!-- Данные об электронных товарах -->
	<xsl:template match="shop_order_item_digital">
		<br />
		<xsl:if test="shop_item_digital/value != ''">
		<i>&labelDigitalItemText;</i><xsl:text> </xsl:text><xsl:value-of select="shop_item_digital/value" /><br />
		</xsl:if>

		<!-- Ссылка на файл электронного товара -->
		<xsl:if test="shop_item_digital/filename != ''">
		<a href="http://{/shop/site/site_alias/name}{/shop/url}?download_file={guid}"><i>&labelDownloadFile;</i></a><br />
		</xsl:if>
	</xsl:template>
</xsl:stylesheet>