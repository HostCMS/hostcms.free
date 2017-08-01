<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "lang://84">
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>

	<!-- СообщенияПользователя -->
	<xsl:template match="/forum">
		<h1>&labelMyMessages;</h1>

		<p>
			<a href="{url}">&labelForumList;</a>
			<xsl:if test="forum_category/node()">
				 <span><xsl:text> → </xsl:text></span> <a href="{url}{forum_category/@id}/">  <xsl:value-of select="forum_category/name" /></a>
			</xsl:if>
			<span><xsl:text> → </xsl:text></span> &labelMyMessages;
		</p>

		<xsl:choose>
			<xsl:when test="count_my_posts > 0">
				<table class="table_forum table_themes">
					<tr class="row_title_themes">
						<td align="center" class="theme_td_title">&labelTopic;</td>
						<td align="center" class="theme_td_author">&labelDate;</td>
					</tr>
					<!-- Posts -->
					<xsl:apply-templates select="forum_topic_post"/>
				</table>

				<p>
					<!-- Pagination -->
					<xsl:call-template name="for">
						<xsl:with-param name="items_on_page" select="limit"/>
						<xsl:with-param name="current_page" select="page"/>
						<xsl:with-param name="count_items" select="count_my_posts"/>
						<xsl:with-param name="visible_pages" select="6"/>
					</xsl:call-template>
					<div style="clear: both"></div>
				</p>

				<p>
					<a href="{url}">&labelForumList;</a>
						<xsl:if test="forum_category/node()">
							<span><xsl:text> → </xsl:text></span><a href="{url}{forum_category/@id}/"><xsl:value-of select="forum_category/name" />	</a>
						</xsl:if>
					<span><xsl:text> → </xsl:text></span>&labelMyMessages;
				</p>
			</xsl:when>
			<xsl:otherwise>
				<p>&labelNoPosts;.</p>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

	<!-- Post Template -->
	<xsl:template match="forum_topic_post">
		<xsl:variable name="forum_topic_id" select="forum_topic_id" />
		<!-- Row Style -->
		<xsl:variable name="color_theme">
			<xsl:choose>
				<xsl:when test="position() mod 2 = 0">color_2_theme</xsl:when>
				<xsl:when test="/forum/topics//forum_topic[@id = $forum_topic_id]/visible=0">color_hidden_theme</xsl:when>
				<xsl:otherwise></xsl:otherwise>
			</xsl:choose>
		</xsl:variable>

		<tr class="{$color_theme}">
			<!-- Topic Subject -->
			<td class="theme_td_title">
				<b><a href="{/forum/url}{/forum/topics//forum_topic[@id = $forum_topic_id]/forum_category_id}/{/forum/topics//forum_topic[@id = $forum_topic_id]/@id}/">
						<xsl:value-of select="/forum/topics//forum_topic[@id = $forum_topic_id]/forum_topic_post/subject"/>
				</a></b>
				<!-- <p><xsl:value-of disable-output-escaping="yes" select="substring(text, 0, 100)" /></p> -->
				<p><xsl:value-of disable-output-escaping="yes" select="short_text" /></p>
			</td>
			<td align="center" class="theme_td_author">
				<xsl:value-of select="datetime"/>
			</td>
		</tr>
	</xsl:template>

	<!-- Pagination -->
	<xsl:template name="for">
		<xsl:param name="i" select="0"/>
		<xsl:param name="items_on_page"/>
		<xsl:param name="current_page"/>
		<xsl:param name="count_items"/>
		<xsl:param name="visible_pages"/>

		<xsl:variable name="n" select="$count_items div $items_on_page"/>

		<xsl:variable name="link"><xsl:value-of select="/forum/url" /><xsl:if test="forum_category/node()"><xsl:value-of select="forum_category/@id" />/</xsl:if>myPosts/</xsl:variable>

		<!-- Links before current -->
		<xsl:variable name="pre_count_page">
			<xsl:choose>
				<xsl:when test="$current_page &gt; ($n - (round($visible_pages div 2) - 1))">
					<xsl:value-of select="$visible_pages - ($n - $current_page)"/>
				</xsl:when>
				<xsl:otherwise>
					<xsl:value-of select="round($visible_pages div 2) - 1"/>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:variable>

		<!-- Links after current -->
		<xsl:variable name="post_count_page">
			<xsl:choose>
				<xsl:when test="0 &gt; $current_page - (round($visible_pages div 2) - 1)">
					<xsl:value-of select="$visible_pages - $current_page - 1"/>
				</xsl:when>
				<xsl:otherwise>
					<xsl:choose>
						<xsl:when test="round($visible_pages div 2) = ($visible_pages div 2)">
							<xsl:value-of select="$visible_pages div 2"/>
						</xsl:when>
						<xsl:otherwise>
							<xsl:value-of select="round($visible_pages div 2) - 1"/>
						</xsl:otherwise>
					</xsl:choose>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:variable>

		<xsl:if test="$count_items &gt; $items_on_page and $n &gt; $i">
			<!-- Pagination item -->
			<xsl:if test="$i != $current_page">
				<!-- Set $link variable -->
				<xsl:variable name="number_link">
					<xsl:choose>
						<xsl:when test="$i != 0">page-<xsl:value-of select="$i + 1"/>/</xsl:when>
						<xsl:otherwise></xsl:otherwise>
					</xsl:choose>
				</xsl:variable>

				<!-- First pagination item -->
				<xsl:if test="$current_page - $pre_count_page &gt; 0 and $i = 0">
					<a href="{$link}" class="page_link" style="text-decoration: none;">←</a>
				</xsl:if>

				<xsl:choose>
					<xsl:when test="$i &gt;= ($current_page - $pre_count_page) and ($current_page + $post_count_page) &gt;= $i">
						<!-- Pagination item -->
						<a href="{$link}{$number_link}" class="page_link">
							<xsl:value-of select="$i + 1"/>
						</a>
					</xsl:when>
					<xsl:otherwise>
					</xsl:otherwise>
				</xsl:choose>

				<!-- Last pagination item -->
				<xsl:if test="$i+1 &gt;= $n and $n &gt; ($current_page + 1 + $post_count_page)">
					<xsl:choose>
						<xsl:when test="$n &gt; round($n)">
							<!-- Last pagination item -->
							<a href="{$link}page-{round($n+1)}/" class="page_link" style="text-decoration: none;">→</a>
						</xsl:when>
						<xsl:otherwise>
							<a href="{$link}page-{round($n)}/" class="page_link" style="text-decoration: none;">→</a>
						</xsl:otherwise>
					</xsl:choose>
				</xsl:if>
			</xsl:if>

			<!-- Current pagination item -->
			<xsl:if test="$i = $current_page">
				<span class="current">
					<xsl:value-of select="$i+1"/>
				</span>
			</xsl:if>

			<!-- Recursive Template -->
			<xsl:call-template name="for">
				<xsl:with-param name="i" select="$i + 1"/>
				<xsl:with-param name="items_on_page" select="$items_on_page"/>
				<xsl:with-param name="current_page" select="$current_page"/>
				<xsl:with-param name="count_items" select="$count_items"/>
				<xsl:with-param name="visible_pages" select="$visible_pages"/>
			</xsl:call-template>
		</xsl:if>
	</xsl:template>
</xsl:stylesheet>