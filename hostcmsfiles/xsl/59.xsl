<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>

	<!-- ПисьмоВосстановлениеПароля -->

	<xsl:template match="/siteuser">Здравствуйте, <xsl:value-of select="login"/>!

Кто-то, возможно Вы, изменил пароль на сайте: http://<xsl:value-of select="/siteuser/site/site_alias/alias_name_without_mask"/>

Новые регистрационные данные:
Логин: <xsl:value-of select="login"/>
Пароль: <xsl:value-of select="new_password"/>

---
Администрация сайта <xsl:value-of select="/siteuser/site/site_alias/alias_name_without_mask"/>
</xsl:template>
</xsl:stylesheet>