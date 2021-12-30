<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "lang://202">
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>

	<xsl:template match="/">
		<xsl:apply-templates select="siteuser"/>
	</xsl:template>

	<xsl:template match="siteuser">
		<h1>&labelBonuses;</h1>

		<form method="POST" action="./">
			&labelPeriod; <input type="text" name="date_from" size="8" value="{date_from}" />
			<xsl:text> </xsl:text>
			<input type="text" name="date_to" size="8" value="{date_to}" />
			<xsl:text> </xsl:text>
			<input type="submit" name="submit" value="&labelCheck;" />
		</form>

		<xsl:apply-templates select="shop" />

	</xsl:template>

	<xsl:template match="shop">
		<xsl:if test="count(affiliate_plan)">
			<h2><xsl:value-of select="name" /></h2>

			<xsl:variable name="shop_id" select="@id" />

			<p>
				&labelBonus; <b><xsl:value-of select="format-number(sum(/siteuser/transactions/shop_siteuser_transaction[shop_id = $shop_id]/amount_base_currency), '0.##')" /><xsl:text> </xsl:text><xsl:value-of select="/siteuser/shop[@id = $shop_id]/shop_currency/sign" /></b>
			</p>

			<xsl:choose>
				<xsl:when test="count(/siteuser/affiliats/siteuser) = 0">
					<p>
						&labelNone;
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
			<xsl:value-of select="date" /><xsl:text> &labelBonuses;: </xsl:text>
			<xsl:value-of select="format-number(sum(/siteuser/transactions/shop_siteuser_transaction[shop_id = $shop_id][shop_order/siteuser_id = $siteuser_id]/amount_base_currency), '0.##')" /><xsl:text> </xsl:text>
			<xsl:value-of select="/siteuser/shop[@id = $shop_id]/shop_currency/sign" />

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
</xsl:stylesheet>