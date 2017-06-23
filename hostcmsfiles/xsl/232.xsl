<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>
	
	<xsl:template match="/forum">
		
		<style>
			.posts_list dt
			{
			background: url("/images/li.gif") no-repeat scroll 0 8px transparent;
			color: #606060;
			font-weight: bold;
			margin: 0 0 7px;
			padding-left: 15px;
			}
			
			.posts_list dd
			{
			margin: 0;
			}
			
			.user {
			color: #AAAAAA;
			font-size: 9pt;
			margin: 5px 0 0;
			}
			
			.user span
			{
			padding-right: 15px;
			}
		</style>
		
		<p class="h1">Последние сообщения</p>
		<dl class="posts_list">
			<!-- Выбираем посты -->
			<xsl:apply-templates select="forum_topic_post" />
		</dl>
	</xsl:template>
	
	<xsl:template match="forum_topic_post">
		<dt>
			<!-- Дата и время -->
			<xsl:value-of disable-output-escaping="yes" select="datetime"/>
		</dt>
		<dd>
			<!-- Текст -->
			<xsl:value-of disable-output-escaping="yes" select="text"/>
		</dd>
		<p class="user">
			<!-- Логин -->
			<img src="/images/user.png" />
			<span><xsl:value-of disable-output-escaping="yes" select="siteuser/login"/></span>
		</p>
	</xsl:template>
</xsl:stylesheet>