<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "lang://24">
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>

<xsl:template match="/"><xsl:apply-templates select="/form"/></xsl:template>

<xsl:template match="/form">&labelTitle; "<xsl:value-of select="name"/>" № <xsl:value-of select="form_fill/@id"/>:
<!-- Вывод разделов формы 0-го уровня -->
<xsl:apply-templates select="form_field_dir" />
<!-- Вывод списка полей формы 0-го уровня -->
<xsl:apply-templates select="form_field" />
&labelIp; <xsl:value-of select="ip"/>
&labelDate; <xsl:value-of select="datetime"/>
<xsl:if test="source/node()">
&labelService; <xsl:value-of select="source/service" />
&labelCampaign; <xsl:value-of select="source/campaign" />
&labelAd; <xsl:value-of select="source/ad" />
&labelSource; <xsl:value-of select="source/source" />
&labelMedium; <xsl:value-of select="source/medium" />
&labelContent; <xsl:value-of select="source/content" />
&labelTerm; <xsl:value-of select="source/term" />
</xsl:if>
---
&labelHostcms;
http://www.hostcms.ru
</xsl:template>

<xsl:template match="form_field_dir">
- <xsl:value-of select="name" />
<!-- Вывод списка полей формы -->
<xsl:apply-templates select="form_field" />
<!-- Вывод разделов формы -->
<xsl:apply-templates select="form_field_dir" />
<xsl:text>
</xsl:text>
</xsl:template>

<xsl:template match="form_field">
<xsl:value-of select="caption"/>:<xsl:text> </xsl:text><xsl:choose><xsl:when test="values/node()"><xsl:apply-templates select="values/value"/></xsl:when><xsl:otherwise><xsl:value-of select="value" /></xsl:otherwise></xsl:choose><xsl:text>
</xsl:text>
</xsl:template>

<xsl:template match="values/value"><xsl:variable name="currentValue" select="." /><xsl:value-of select="../../list/list_item[@id=$currentValue]/value"/><xsl:if test="position() != last()"><xsl:text>, </xsl:text></xsl:if></xsl:template>

</xsl:stylesheet>