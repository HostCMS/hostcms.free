<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>

<xsl:template match="/"><xsl:apply-templates select="/document/comment"/></xsl:template>

<xsl:template match="/document/comment">На сайт был добавлен комментарий:

Автор: <xsl:value-of select="author" />
E-mail: <xsl:value-of select="email" />
Тема: <xsl:value-of select="subject" />
Комментарий: <xsl:value-of select="text" />
Магазин: <xsl:value-of select="../shop/name" />
Дата: <xsl:value-of select="datetime" />
IP-адрес: <xsl:value-of select="ip" />

---
Система управления сайтом HostCMS
http://www.hostcms.ru
</xsl:template>
</xsl:stylesheet>