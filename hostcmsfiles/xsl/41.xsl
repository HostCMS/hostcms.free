<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "lang://41">
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>

	<!-- РедактированиеСообщения -->
	<xsl:template match="/forum">

		<!-- Форма редактирования сообщения -->
		<script type="text/javascript">
			<xsl:comment>
				<xsl:text disable-output-escaping="yes">
					<![CDATA[
					$(document).ready(function() {
						$("#post_text").bbedit({lang: 'ru'});
					});
					]]>
				</xsl:text>
			</xsl:comment>
		</script>

		<h1>&labelEditMessage;</h1>

		<xsl:if test="error != ''">
			<div id="error">
				<xsl:value-of disable-output-escaping="yes" select="error" />
			</div>
		</xsl:if>

		<!-- Хлебные крошки -->
		<p>
			<a href="{url}">&labelForumsList;</a>
			<span><xsl:text> → </xsl:text></span>
			<a href="{url}{forum_category/@id}/">
				<xsl:value-of select="forum_category/name"/>
			</a>
			<span><xsl:text> → </xsl:text></span>
			<a href="{url}{forum_category/@id}/{forum_topic/@id}/page-{current_page + 1}/">
				<xsl:value-of select="forum_topic/forum_topic_post/subject"/>
			</a>
		</p>

		<form name="mainform" action="./" method="post">
			<xsl:variable name="post_title"><xsl:choose><xsl:when test="error_post_title/node()"><xsl:value-of select="error_post_title"/></xsl:when><xsl:otherwise><xsl:value-of select="forum_topic_post/subject"/></xsl:otherwise></xsl:choose></xsl:variable>

			<xsl:variable name="post_text"><xsl:choose><xsl:when test="error_post_text/node()"><xsl:value-of select="error_post_text"/></xsl:when><xsl:otherwise><xsl:value-of select="forum_topic_post/original_text"/></xsl:otherwise></xsl:choose></xsl:variable>

			<div class="add_message_table">
				<div class="add_row">
					<!-- Заголовок темы-->
					<div style="float: left; width: 50px; margin-top: 2px">&labelSubject;</div>
					<div style="margin-left: 50px; padding-right: 10px"><input id="post_title" style="width: 97%;" type="text" name="post_title" value="{$post_title}"/></div>
				</div>

				<div class="add_row" style="padding-right: 10px">
					<textarea id="post_text" rows="9" name="post_text" style="width: 97%">
						<xsl:value-of select="$post_text"/>
					</textarea>
				</div>

				<div class="add_row">
					<input border="0" name="add_post" type="submit" class="button" value="&labelAdd;"/>
				</div>
			</div>

			<input name="edit_post_id" type="hidden" value="{forum_topic_post/@id}"/>
			<input type="hidden" name="current_page" value="{current_page + 1}"/>
		</form>

		<!-- Хлебные крошки -->
		<p>
			<a href="{url}">&labelForumsList;</a>
			<span><xsl:text> → </xsl:text></span>
			<a href="{url}{forum_category/@id}/">
				<xsl:value-of select="forum_category/name"/>
			</a>
			<span><xsl:text> → </xsl:text></span>
			<a href="{url}{forum_category/@id}/{forum_topic/@id}/page-{current_page + 1}/">
				<xsl:value-of select="forum_topic/forum_topic_post/subject"/>
			</a>

		</p>
	</xsl:template>
</xsl:stylesheet>