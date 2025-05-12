<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "lang://44">
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>
	
	<!-- ПисьмоОДобавленииТемы -->
	<xsl:template match="/forum">
<xsl:variable name="topic_link"><xsl:value-of select="http"/><xsl:value-of select="url"/><xsl:value-of select="forum_category/@id"/>/<xsl:value-of select="forum_category/forum_topic/@id"/>/</xsl:variable>
<xsl:variable name="post_author_id" select="forum_category/forum_topic/forum_topic_post/siteuser_id" />
<xsl:variable name="moderator"><xsl:choose><xsl:when test = "/forum/forum_category/moderators/siteuser/node()">
<xsl:choose><xsl:when test="/forum/forum_category/moderators//siteuser[@id = $post_author_id]/node()">1</xsl:when><xsl:otherwise>0</xsl:otherwise></xsl:choose></xsl:when><xsl:otherwise>0</xsl:otherwise></xsl:choose></xsl:variable>&labelLine1; "<xsl:value-of select="forum_category/name"/>"!

&labelLine2-1; <xsl:if test="forum_category/closed = 1">&labelLine2-2;</xsl:if> &labelLine2-3; <xsl:value-of select="forum_category/forum_topic/forum_topic_post/datetime" /> &labelLine3; <xsl:choose>
<xsl:when test="forum_category/forum_topic/closed = 0">&labelOpened;<xsl:text> </xsl:text></xsl:when>
<xsl:otherwise>&labelClosed;<xsl:text> </xsl:text></xsl:otherwise></xsl:choose>
<xsl:choose><xsl:when test="forum_category/forum_topic/announcement = 1">&labelAnnouncement;<xsl:text> </xsl:text></xsl:when></xsl:choose>
<xsl:choose><xsl:when test="forum_category/forum_topic/visible = 0">&labelInvisible;<xsl:text> </xsl:text></xsl:when>
<xsl:otherwise>&labelVisible;<xsl:text> </xsl:text></xsl:otherwise></xsl:choose>&labelTopic; "<xsl:value-of select="forum_category/forum_topic/forum_topic_post/subject"/>" ( <xsl:value-of select="$topic_link"/> )

-------------------------------------------------
<xsl:value-of select="forum_category/forum_topic/forum_topic_post/original_text" disable-output-escaping="yes" />
-------------------------------------------------

&labelAuthor; <xsl:if test="$moderator=1">&labelModerator;<xsl:text> </xsl:text></xsl:if><xsl:value-of select="forum_category/forum_topic/forum_topic_post/siteuser/login" />
E-mail: <xsl:value-of select="forum_category/forum_topic/forum_topic_post/siteuser/email"/>
IP: <xsl:value-of select="forum_category/forum_topic/forum_topic_post/siteuser/ip"/>

---
&labelHostcms;
</xsl:template>
</xsl:stylesheet>