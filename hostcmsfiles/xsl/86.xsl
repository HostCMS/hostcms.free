<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>

	<!-- ПисьмоРедактированияСообщенияПользователю -->
	<xsl:template match="/forum">Здравствуйте!
<xsl:variable name="topic_link"><xsl:value-of select="http"/><xsl:value-of select="url"/><xsl:value-of select="forum_category/@id"/>/<xsl:value-of select="forum_category/forum_topic/@id"/>/</xsl:variable>
<xsl:variable name="post_author_id" select="forum_category/forum_topic/new/forum_topic_post/siteuser_id" />
<xsl:variable name="post_editor_id" select="siteuser/@id" />

<xsl:variable name="author_moderator"><xsl:choose><xsl:when test="forum_category/moderators//siteuser[@id = $post_author_id]/node()">1</xsl:when><xsl:otherwise>0</xsl:otherwise></xsl:choose></xsl:variable>
<xsl:variable name="editor_moderator"><xsl:choose><xsl:when test="forum_category/moderators//siteuser[@id = $post_editor_id]/node()">1</xsl:when><xsl:otherwise>0</xsl:otherwise></xsl:choose></xsl:variable>
В теме "<xsl:value-of select="forum_category/forum_topic/forum_topic_post/subject"/>" ( <xsl:value-of select="$topic_link"/> ) было заменено сообщение c заголовком "<xsl:value-of select="forum_category/forum_topic/old/forum_topic_post/subject"/>" следующего содержания:

-------------------------------------------------
<xsl:value-of select="forum_category/forum_topic/old/forum_topic_post/original_text" disable-output-escaping="yes" />
-------------------------------------------------

Автор сообщения: <xsl:if test="$author_moderator = 1">модератор<xsl:text> </xsl:text></xsl:if><xsl:value-of select="forum_category/forum_topic/new/forum_topic_post/siteuser/login"/>
<!--
E-mail: <xsl:value-of select="theme_author/site_users_email"/>
IP: <xsl:value-of select="theme_author/theme_author_ip"/>-->

на сообщение с заголовком "<xsl:value-of select="forum_category/forum_topic/new/forum_topic_post/subject"/>" следующего содержания:

-------------------------------------------------
<xsl:value-of select="forum_category/forum_topic/new/forum_topic_post/original_text" disable-output-escaping="yes" />
-------------------------------------------------
Редактор сообщения: <xsl:if test="$editor_moderator = 1">модератор<xsl:text> </xsl:text></xsl:if><xsl:value-of select="siteuser/login"/>

---
Система управления сайтом HostCMS
</xsl:template>
</xsl:stylesheet>