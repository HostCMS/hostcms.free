<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>
	
	<!-- ПисьмоОДобавленииТемы -->
	<xsl:template match="/forum">
<xsl:variable name="topic_link">http://<xsl:value-of select="site/site_alias/alias_name_without_mask"/><xsl:value-of select="url"/><xsl:value-of select="forum_category/@id"/>/<xsl:value-of select="forum_category/forum_topic/@id"/>/</xsl:variable>
<xsl:variable name="post_author_id" select="forum_category/forum_topic/forum_topic_post/siteuser_id" />
<xsl:variable name="moderator"><xsl:choose><xsl:when test = "/forum/forum_category/moderators/siteuser/node()">
<xsl:choose><xsl:when test="/forum/forum_category/moderators//siteuser[@id = $post_author_id]/node()">1</xsl:when><xsl:otherwise>0</xsl:otherwise></xsl:choose></xsl:when><xsl:otherwise>0</xsl:otherwise></xsl:choose></xsl:variable>Здравствуйте, куратор форума "<xsl:value-of select="forum_category/name"/>"!

На <xsl:if test="forum_category/closed = 1">закрытом</xsl:if> форуме <xsl:value-of select="forum_category/forum_topic/forum_topic_post/datetime" /> была создана новая <xsl:choose>
<xsl:when test="forum_category/forum_topic/closed = 0">открытая<xsl:text> </xsl:text></xsl:when>
<xsl:otherwise>закрытая<xsl:text> </xsl:text></xsl:otherwise></xsl:choose>
<xsl:choose><xsl:when test="forum_category/forum_topic/announcement = 1">прилепленная<xsl:text> </xsl:text></xsl:when></xsl:choose>
<xsl:choose><xsl:when test="forum_category/forum_topic/visible = 0">невидимая<xsl:text> </xsl:text></xsl:when>
<xsl:otherwise>видимая<xsl:text> </xsl:text></xsl:otherwise></xsl:choose>тема: "<xsl:value-of select="forum_category/forum_topic/forum_topic_post/subject"/>" ( <xsl:value-of select="$topic_link"/> )

-------------------------------------------------
<xsl:value-of select="forum_category/forum_topic/forum_topic_post/original_text" disable-output-escaping="yes" />
-------------------------------------------------

Автор темы: <xsl:if test="$moderator=1">модератор<xsl:text> </xsl:text></xsl:if><xsl:value-of select="forum_category/forum_topic/forum_topic_post/siteuser/login" />
E-mail: <xsl:value-of select="forum_category/forum_topic/forum_topic_post/siteuser/email"/>
IP: <xsl:value-of select="forum_category/forum_topic/forum_topic_post/siteuser/ip"/>

---
Система управления сайтом HostCMS
</xsl:template>
</xsl:stylesheet>