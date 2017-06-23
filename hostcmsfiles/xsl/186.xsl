<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>

	<xsl:template match="/">
		<xsl:apply-templates select="siteuser"/>
	</xsl:template>

	<xsl:template match="siteuser">
		<h1>Структура приглашенных</h1>
		<xsl:apply-templates select="shop" />
	</xsl:template>

	<xsl:template match="shop">
		<xsl:if test="count(affiliate_plan)">
			<h2><xsl:value-of select="name" /></h2>

			<xsl:variable name="shop_id" select="@id" />

			<p>
				Персональный бонус: <b><xsl:value-of select="format-number(sum(/siteuser/transactions/shop_siteuser_transaction[shop_id = $shop_id]/amount_base_currency), '0.##')" /><xsl:text> </xsl:text><xsl:value-of select="/siteuser/shop[@id = $shop_id]/shop_currency/name" /></b>
			</p>

			<xsl:choose>
				<xsl:when test="count(/siteuser/affiliats/siteuser) = 0">
					<p>
						Нет информации о структуре партнерских отношений.
					</p>
				</xsl:when>
				<xsl:otherwise>
					<ul>
						<xsl:apply-templates select="/siteuser/affiliats/siteuser" >
							<xsl:with-param name="shop_id" select="@id"/>
						</xsl:apply-templates>
					</ul>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:if>
	</xsl:template>

	<xsl:template match="affiliats/siteuser">
		<xsl:param name="shop_id" select="shop_id"/>

		<xsl:variable name="siteuser_id" select="@id" />

		<li>
			<b><xsl:value-of select="login" /></b><xsl:text> </xsl:text>
			<xsl:value-of select="date" /><xsl:text> г. Бонусы: </xsl:text>
			<xsl:value-of select="format-number(sum(/siteuser/transactions/shop_siteuser_transaction[shop_id = $shop_id][shop_order/siteuser_id = $siteuser_id]/amount_base_currency), '0.##')" /><xsl:text> </xsl:text>
			<xsl:value-of select="/siteuser/shop[@id = $shop_id]/shop_currency/name" />

			<!-- Рефералы пользователя -->
			<xsl:if test="count(affiliats/siteuser)">
				<ul>
					<xsl:apply-templates select="affiliats/siteuser" >
						<xsl:with-param name="shop_id" select="$shop_id"/>
					</xsl:apply-templates>
				</ul>
			</xsl:if>
		</li>
	</xsl:template>

	<!--
	<xsl:template match="affiliate_items_data">
		<h1>Суммы заказов моей структуры за период</h1>

		<p>
			<form method="POST">
				Период
				<input type="text" name="date_from" class="calendar_field" id="affiliat_date_from" size="8" value="{affiliate_items_date_from}" />
				<xsl:text> </xsl:text>
				<input type="text" name="date_to" class="calendar_field" size="8" value="{affiliate_items_date_to}" /><xsl:text> </xsl:text>
				<input type="submit" name="do_filter" value="Выбрать" />
			</form>
		</p>

		<xsl:choose>
			<xsl:when test="count(affiliate_items_item) = 0">
				Нет данных за указанный период.
			</xsl:when>
			<xsl:otherwise>
				<table>
				<tr>
				<td>Название</td>
				<td>Количество</td>
				<td>На сумму</td>
				</tr>
					<xsl:apply-templates select="affiliate_items_item"/>
				</table>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

	<xsl:template match="affiliate_items_item">
		<tr>
			<td><xsl:value-of select="affiliate_items_name" /></td>
			<td><xsl:value-of select="affiliate_items_quantity" /></td>
			<td><xsl:value-of select="affiliate_items_price" /></td>
		</tr>
	</xsl:template>

	<xsl:template match="transaction">
		<li style="margin-left: 13px;">
			<xsl:variable name="shop_transaction" select="shop_shops_id"/>
			<xsl:variable name="currency_transaction" select="shop_currency_id"/>
			Транз.: <xsl:value-of select="amount_base_currency" /><xsl:text> </xsl:text><xsl:value-of select="//affiliate_plan[@id = $shop_transaction]/all_currency/shop_currency[@id = $currency_transaction]/shop_currency_name" />
		</li>
	</xsl:template>
	-->
</xsl:stylesheet>