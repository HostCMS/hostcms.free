<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "lang://88">
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>

	<!-- ПисьмоРедактированияСообщенияКуратору -->

	<xsl:template match="/forum">&labelGreetingCurator; "<xsl:value-of select="forum_category/name"/>"!
<xsl:variable name="topic_link">http://<xsl:value-of select="site/site_alias/alias_name_without_mask"/><xsl:value-of select="url"/><xsl:value-of select="forum_category/@id"/>/<xsl:value-of select="forum_category/forum_topic/@id"/>/</xsl:variable>
<xsl:variable name="post_author_id" select="forum_category/forum_topic/new/forum_topic_post/siteuser_id" />
<xsl:variable name="post_editor_id" select="siteuser/@id" />
<xsl:variable name="author_moderator"><xsl:choose><xsl:when test="forum_category/moderators//siteuser[@id = $post_author_id]/node()">1</xsl:when><xsl:otherwise>0</xsl:otherwise></xsl:choose></xsl:variable>
<xsl:variable name="editor_moderator"><xsl:choose><xsl:when test="forum_category/moderators//siteuser[@id = $post_editor_id]/node()">1</xsl:when><xsl:otherwise>0</xsl:otherwise></xsl:choose></xsl:variable>
&labelIntoForum; <xsl:value-of select="forum_category/forum_topic/new/forum_topic_post/datetime"/> &labelIntoForumTopic; "<xsl:value-of select="forum_category/forum_topic/forum_topic_post/subject"/>" ( <xsl:value-of select="$topic_link"/> )
&labelMessageTitleAnnouncement; "<xsl:value-of select="forum_category/forum_topic/old/forum_topic_post/subject"/>" &labelMessageTextAnnouncement;:

-------------------------------------------------
<xsl:value-of select="forum_category/forum_topic/old/forum_topic_post/original_text" disable-output-escaping="yes" />
-------------------------------------------------

&labelMessageAuthor;: <xsl:value-of select="forum_category/forum_topic/new/forum_topic_post/siteuser/login"/><xsl:if test="$author_moderator = 1"><xsl:text> </xsl:text>(&labelForumModerator;)</xsl:if>
&labelSiteuserEmail;: <xsl:value-of select="forum_category/forum_topic/new/forum_topic_post/siteuser/email"/>
&labelSiteuserIp;: <xsl:value-of select="forum_category/forum_topic/new/forum_topic_post/siteuser/ip"/>

на сообщение с заголовком "<xsl:value-of select="forum_category/forum_topic/new/forum_topic_post/subject"/>" &labelMessageTextAnnouncement;:

-------------------------------------------------
<xsl:value-of select="forum_category/forum_topic/new/forum_topic_post/original_text" disable-output-escaping="yes" />
-------------------------------------------------

<xsl:if test="forum_category/forum_topic/visible != forum_category/forum_topic/original_topic/forum_topic/visible or forum_category/forum_topic/announcement != forum_category/forum_topic/original_topic/forum_topic/announcement or forum_category/forum_topic/closed != forum_category/forum_topic/original_topic/forum_topic/closed">
Оригинальная<xsl:text> </xsl:text><xsl:choose><xsl:when test="forum_category/forum_topic/original_topic/forum_topic/closed = 0">открытая</xsl:when><xsl:otherwise>закрытая</xsl:otherwise></xsl:choose>
<xsl:choose><xsl:when test="forum_category/forum_topic/original_topic/forum_topic/announcement = 1"><xsl:text> </xsl:text>прилепленная</xsl:when></xsl:choose><xsl:choose><xsl:when test="forum_category/forum_topic/original_topic/forum_topic/visible = 0"><xsl:text> </xsl:text>невидимая</xsl:when><xsl:otherwise><xsl:text> </xsl:text>видимая</xsl:otherwise>
</xsl:choose><xsl:text> </xsl:text>тема была заменена на<xsl:choose>
<xsl:when test="forum_category/forum_topic/closed = 0"><xsl:text> </xsl:text>открытую</xsl:when>
<xsl:otherwise><xsl:text> </xsl:text>закрытую</xsl:otherwise></xsl:choose><xsl:choose><xsl:when test="forum_category/forum_topic/announcement = 1"><xsl:text> </xsl:text>прилепленную</xsl:when></xsl:choose><xsl:choose><xsl:when test="forum_category/forum_topic/visible = 0"><xsl:text> </xsl:text>невидимую</xsl:when><xsl:otherwise><xsl:text> </xsl:text>видимую</xsl:otherwise></xsl:choose><xsl:text> </xsl:text>тему.</xsl:if>
&labelEditor;: <xsl:value-of select="siteuser/login"/><xsl:if test="$editor_moderator = 1"><xsl:text> </xsl:text>(модератор)</xsl:if>
&labelSiteuserEmail;: <xsl:value-of select="siteuser/email"/>
&labelSiteuserIp;: <xsl:value-of select="siteuser/ip"/>

---
&labelHostcms;
</xsl:template>
</xsl:stylesheet>