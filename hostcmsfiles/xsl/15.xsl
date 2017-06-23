<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>

<xsl:template match="/"><xsl:apply-templates select="/document/comment"/></xsl:template>

<xsl:template match="/document/comment">
<p>На сайт был добавлен комментарий:</p>

<p>Автор: <xsl:value-of select="author" /><br />
E-mail: <xsl:value-of select="email" /><br />
Тема: <xsl:value-of select="subject" /><br />
Комментарий: <xsl:value-of select="text" /><br />
Магазин: <xsl:value-of select="../shop/name" /><br />
Дата: <xsl:value-of select="datetime" /><br />
IP-адрес: <xsl:value-of select="ip" />
</p>

<p>---<br />
Система управления сайтом HostCMS<br />
http://www.hostcms.ru</p>
</xsl:template>
</xsl:stylesheet>