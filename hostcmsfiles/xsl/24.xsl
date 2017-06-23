<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>

<xsl:template match="/"><xsl:apply-templates select="/form"/></xsl:template>

<xsl:template match="/form">Заполнена форма "<xsl:value-of select="name"/>" № <xsl:value-of select="form_fill/@id"/>:
<!-- Вывод разделов формы 0-го уровня -->
<xsl:apply-templates select="form_field_dir" />
<!-- Вывод списка полей формы 0-го уровня -->
<xsl:apply-templates select="form_field" />
IP-адрес: <xsl:value-of select="ip"/>
Дата отправки: <xsl:value-of select="datetime"/>

---
Система управления сайтом HostCMS
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