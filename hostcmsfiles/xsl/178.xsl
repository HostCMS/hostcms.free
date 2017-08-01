<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "lang://178">
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>

	<xsl:template match="/">
		<xsl:apply-templates select="/siteuser"/>
	</xsl:template>

	<xsl:template match="/siteuser">
		<h1>&labelTitle;</h1>
		<ul>
			<xsl:apply-templates select="shop"/>
		</ul>
	</xsl:template>

	<!-- Шаблон для магазина -->
	<xsl:template match="shop">
		<li>
			<strong><xsl:value-of select="transaction_amount"/><xsl:text> </xsl:text><xsl:value-of select="shop_currency/name" disable-output-escaping="yes"/></strong><xsl:text> —  &labelShop; "</xsl:text><xsl:value-of select="name"/><xsl:text>". </xsl:text>
			<a href="pay/{@id}/">&labelReplenish;</a>, <a href="shop-{@id}/">&labelHistory;</a>.
		</li>
	</xsl:template>
</xsl:stylesheet>