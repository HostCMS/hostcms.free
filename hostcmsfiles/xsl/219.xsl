<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "lang://219">
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>

	<!-- МагазинПроизводитель -->

	<xsl:template match="/">
		<xsl:apply-templates select="shop/shop_producer"/>
	</xsl:template>

	<xsl:template match="shop/shop_producer">
		<h1>&labelProducer; <xsl:value-of disable-output-escaping="no" select="name"/></h1>

		<xsl:if test="image_large != ''">
			<p><img src="{dir}{image_large}" vspace="5" border="0"/></p>
		</xsl:if>

		<xsl:if test="description != ''">
			<p><xsl:value-of disable-output-escaping="yes" select="description"/></p>
		</xsl:if>

		<xsl:if test="address != ''">
			<p><b>&labelAddress;</b><xsl:text> </xsl:text><xsl:value-of select="address"/></p>
		</xsl:if>

		<xsl:if test="phone != ''">
			<p><b>&labelPhone;</b><xsl:text> </xsl:text><xsl:value-of select="phone"/></p>
		</xsl:if>

		<xsl:if test="fax != ''">
			<p><b>&labelFax;</b><xsl:text> </xsl:text><xsl:value-of select="fax"/></p>
		</xsl:if>

		<xsl:if test="site != ''">
			<p><b>&labelSite;</b><xsl:text> </xsl:text><xsl:value-of select="site"/></p>
		</xsl:if>

		<xsl:if test="email != ''">
			<p><b>&labelEmail;</b><xsl:text> </xsl:text><a href="mailto:{email}"><xsl:value-of select="email"/></a></p>
		</xsl:if>

		<xsl:if test="tin != ''">
			<p><b>&labelINN;</b><xsl:text> </xsl:text><xsl:value-of select="tin"/></p>
		</xsl:if>

		<p class="button">
			<a href="{/shop/url}producer-{@id}/">&labelAllItems; <xsl:value-of select="name"/></a>
		</p>
	</xsl:template>

</xsl:stylesheet>