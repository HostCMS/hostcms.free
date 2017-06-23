<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>

	<xsl:template match="/siteuser">Здравствуйте, <xsl:value-of select="name"/>!

Информация о регистрации на сайте: http://<xsl:value-of select="site/site_alias/alias_name_without_mask"/>
Логин: <xsl:value-of select="login"/>
Пароль: Тот, который вы указали при регистрации.

Для подтверждения регистрации перейдите по ссылке:
http://<xsl:value-of select="site/site_alias/alias_name_without_mask"/>/users/?accept=<xsl:value-of select="guid"/>

Для отмены регистрации перейдите по ссылке:
http://<xsl:value-of select="site/site_alias/alias_name_without_mask"/>/users/?cancel=<xsl:value-of select="guid"/>

С уважением, администрация сайта.</xsl:template>
</xsl:stylesheet>