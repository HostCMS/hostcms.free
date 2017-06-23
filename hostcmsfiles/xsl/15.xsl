<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "lang://15">
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>

<xsl:template match="/"><xsl:apply-templates select="/document/comment"/></xsl:template>

<xsl:template match="/document/comment">
<p>&labelTitle;</p>

<p>&labelAuthor; <xsl:value-of select="author" /><br />
&labelEmail; <xsl:value-of select="email" /><br />
&labelSubject; <xsl:value-of select="subject" /><br />
&labelReview; <xsl:value-of select="text" /><br />
&labelShop; <xsl:value-of select="../shop/name" /><br />
&labelShopItem; <xsl:value-of select="../shop_item/name" /><br />
&labelLink; http:<xsl:value-of select="../shop/http" /><xsl:value-of select="../shop/url" /><br />
&labelDate; <xsl:value-of select="datetime" /><br />
&labelIp; <xsl:value-of select="ip" />
</p>

<p>---<br />
&labelHostcms;<br />
&labelHostcmsLink;</p>
</xsl:template>
</xsl:stylesheet>