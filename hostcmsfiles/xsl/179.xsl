<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>

	<xsl:template match="/">
		<xsl:apply-templates select="/siteuser"/>
	</xsl:template>
	
	<xsl:template match="/siteuser">

		<h1>Движение по лицевому счету</h1>

		<table class="table">
			<tr>
				<th width="120px">
					<b>Дата</b>
				</th>
				<th width="120px">
					<b>Сумма</b>
				</th>
				<th width="130px">
					<b>В валюте магазина</b>
				</th>
				<th width="50px">
					<b>Заказ</b>
				</th>
				<th>
					<b>Описание</b>
				</th>
			</tr>
			<xsl:apply-templates select="shop/shop_siteuser_transaction"/>
		</table>

		<p>Остаток: <b><xsl:value-of select="shop/transaction_amount"/><xsl:text> </xsl:text><xsl:value-of select="shop/shop_currency/name"/></b></p>
	</xsl:template>

	<!-- Шаблон для магазина -->
	<xsl:template match="shop_siteuser_transaction">
		<tr>
			<td>
				<xsl:value-of select="datetime"/>
			</td>
			<td>
				<xsl:value-of select="amount"/><xsl:text> </xsl:text><xsl:value-of select="shop_currency/name"/></td>
			<td>
				<xsl:value-of select="amount_base_currency"/><xsl:text> </xsl:text><xsl:value-of select="../shop_currency/name"/></td>
			<td align="center">
				<xsl:choose>
					<xsl:when test="shop_order_id != 0">
						<xsl:value-of select="shop_order/invoice"/>
					</xsl:when>
					<xsl:otherwise>—</xsl:otherwise>
				</xsl:choose>
			</td>
			<td>
				<xsl:value-of select="description"/>
			</td>
		</tr>
	</xsl:template>
</xsl:stylesheet>