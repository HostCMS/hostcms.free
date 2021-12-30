<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "lang://221">
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>

	<xsl:template match="/">
		<xsl:apply-templates select="siteuser"/>
	</xsl:template>

	<xsl:template match="siteuser">
		<h1>&labelTitle;</h1>
		<xsl:choose>
			<xsl:when test="count(shop/affiliate_plan)">
				<xsl:apply-templates select="shop"/>
			</xsl:when>
			<xsl:otherwise>
				<p>&labelNone;</p>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

	<xsl:template match="shop">
		<xsl:if test="count(affiliate_plan)">
			<h1>&labelShop; "<xsl:value-of select="name"/>"</h1>

			<xsl:for-each select="affiliate_plan">
				<h2>&labelAffiliatePlan; "<xsl:value-of select="name"/>"</h2>
				<p>
					&labelMinQuantity; <b><xsl:value-of select="min_count_of_items"/></b>
					<br/>
					&labelMinAmount; <b><xsl:value-of select="min_amount_of_items"/><xsl:text> </xsl:text><xsl:value-of select="../shop_currency/sign"/></b>
				</p>

				<xsl:if test="count(affiliate_plan_level)">
					<h2>&labelMinLevels;</h2>
					<ul>
						<xsl:for-each select="affiliate_plan_level">
							<li>&labelLevel; <xsl:value-of select="level"/><xsl:text> – </xsl:text>
							<b><xsl:choose>
								<!-- Процент -->
								<xsl:when test="type = 0"><xsl:value-of select="percent"/>%</xsl:when>
								<!-- Amount -->
								<xsl:otherwise><xsl:value-of select="value"/><xsl:text> </xsl:text><xsl:value-of select="../../shop_currency/sign"/></xsl:otherwise>
							</xsl:choose></b>
							</li>
						</xsl:for-each>
					</ul>
				</xsl:if>

				<p>&labelLink;<br />
				<textarea rows="1" cols="60">http://<xsl:value-of select="/siteuser/site/site_alias/name" /><xsl:value-of select="../url" />user-<xsl:value-of select="/siteuser/login" />/</textarea>
				</p>
			</xsl:for-each>
		</xsl:if>
	</xsl:template>
</xsl:stylesheet>