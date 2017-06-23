<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "lang://43">
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>
	
	<!-- ПисьмоОДобавленииСообщения -->
	<xsl:template match="/forum">&labelLine1; "<xsl:value-of select="forum_category/name"/>"!
<xsl:variable name="topic_link">http://<xsl:value-of select="site/site_alias/alias_name_without_mask"/><xsl:value-of select="url"/><xsl:value-of select="forum_category/@id"/>/<xsl:value-of select="forum_category/forum_topic/@id"/>/</xsl:variable>
<xsl:variable name="post_author_id" select="forum_category/forum_topic/new/forum_topic_post/siteuser_id" />
<xsl:variable name="moderator"><xsl:choose><xsl:when test="forum_category/moderators//siteuser[@id = $post_author_id]/node()">1</xsl:when><xsl:otherwise>0</xsl:otherwise></xsl:choose></xsl:variable>
&labelLine2; "<xsl:value-of select="forum_category/forum_topic/forum_topic_post/subject"/>" ( <xsl:value-of select="$topic_link"/> ) <xsl:value-of select="forum_category/forum_topic/new/forum_topic_post/datetime"/> &labelLine3; "<xsl:value-of select="forum_category/forum_topic/new/forum_topic_post/subject"/>" &labelLine4;
		
-------------------------------------------------
<xsl:value-of select="forum_category/forum_topic/new/forum_topic_post/original_text" disable-output-escaping="yes"/>
-------------------------------------------------
		
&labelAuthor; <xsl:if test="$moderator = 1">&labelModerator;<xsl:text> </xsl:text></xsl:if><xsl:value-of select="forum_category/forum_topic/new/forum_topic_post/siteuser/login"/>
E-mail: <xsl:value-of select="forum_category/forum_topic/new/forum_topic_post/siteuser/email"/>
IP: <xsl:value-of select="forum_category/forum_topic/new/forum_topic_post/ip"/>

---
&labelHostcms;
</xsl:template>
</xsl:stylesheet>