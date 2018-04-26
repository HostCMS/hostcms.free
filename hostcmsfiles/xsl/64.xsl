<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "lang://64">
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>
	
	<!-- ОтображениеОпросаБезРезультатов -->
	
	<xsl:template match="/poll_group">
		
		<xsl:apply-templates select="poll"/>
		
		<xsl:if test="ОтображатьСсылкиНаСледующиеСтраницы/node() and ОтображатьСсылкиНаСледующиеСтраницы = 1">
			<!-- Рекурсивный вызов шаблона. -->
			<xsl:call-template name="for">
				<xsl:with-param name="link" select="url"/>
				<xsl:with-param name="limit" select="limit"/>
				<xsl:with-param name="page" select="page"/>
				<xsl:with-param name="total" select="total"/>
				<xsl:with-param name="visible_pages">5</xsl:with-param>
			</xsl:call-template>
		</xsl:if>
	</xsl:template>
	
	<xsl:template match="poll">
		<div class="survey_block">
			<p class="h1 red" hostcms:id="{@id}" hostcms:field="name" hostcms:entity="poll">
				<xsl:value-of select="name" />
			</p>
			
			<form action="{/poll_group/url}poll-{@id}/" method="post" class="poll">
				<!-- Выводим варианты ответа -->
				<xsl:apply-templates select="poll_response" />
				
				<!-- <xsl:if test="show_results=1">
					<p>
						<a href="{/poll_group/url}poll-{@id}/">Результаты голосования →</a>
					</p>
				</xsl:if> -->
				
				<p><input class="button" type="submit" name="vote" value="&labelAnswer;"/></p>
				
				<div>
					<input type="hidden" name="polls_id" value="{@id}"/>
					<input type="hidden" name="show_results_vote" value="{show_results}"/>
				</div>
			</form>
		</div>
	</xsl:template>
	
	<xsl:template match="poll_response">
		<p>
			<label>
				<xsl:choose>
					<xsl:when test="../type = 0">
						<input type="radio" name="poll_response" id="poll_response_{@id}" value="{@id}">
						<xsl:if test="position() = 1"><xsl:attribute name="checked"></xsl:attribute></xsl:if>
						</input>
					</xsl:when>
					<xsl:otherwise>
						<input type="checkbox" name="poll_response_{@id}" id="poll_response_{@id}" value="{@id}"></input>
					</xsl:otherwise>
				</xsl:choose>
				
				<xsl:text> </xsl:text><span hostcms:id="{@id}" hostcms:field="name" hostcms:entity="poll_response"><xsl:value-of select="name"/></span>
			</label>
		</p>
	</xsl:template>
	
	<!-- Pagination -->
	<xsl:template name="for">
		<xsl:param name="i" select="0"/>
		<xsl:param name="prefix">page</xsl:param>
		<xsl:param name="link"/>
		<xsl:param name="limit"/>
		<xsl:param name="page"/>
		<xsl:param name="total"/>
		<xsl:param name="visible_pages"/>
		
		<xsl:variable name="n" select="$total div $limit"/>
		
		<!-- Links before current -->
		<xsl:variable name="pre_count_page">
			<xsl:choose>
				<xsl:when test="$page &gt; ($n - (round($visible_pages div 2) - 1))">
					<xsl:value-of select="$visible_pages - ($n - $page)"/>
				</xsl:when>
				<xsl:otherwise>
					<xsl:value-of select="round($visible_pages div 2) - 1"/>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:variable>
		
		<!-- Links after current -->
		<xsl:variable name="post_count_page">
			<xsl:choose>
				<xsl:when test="0 &gt; $page - (round($visible_pages div 2) - 1)">
					<xsl:value-of select="$visible_pages - $page"/>
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
		
		<xsl:if test="$i = 0 and $page != 0">
			<span class="ctrl">
				← Ctrl
			</span>
		</xsl:if>
		
		<xsl:if test="$i >= $n and ($n - 1) > $page">
			<span class="ctrl">
				Ctrl →
			</span>
		</xsl:if>
		
		<xsl:if test="$total &gt; $limit and $n &gt; $i">
			
			<!-- Определяем адрес тэга -->
			<xsl:variable name="tag_link">
				<xsl:choose>
					
					<xsl:when test="count(/document/tag) != 0">tag/<xsl:value-of select="/document/tag/urlencode"/>/</xsl:when>
					
					<xsl:otherwise></xsl:otherwise>
				</xsl:choose>
			</xsl:variable>
			
			<!-- Set $link variable -->
			<xsl:variable name="number_link">
				<xsl:choose>
					
					<xsl:when test="$i != 0">
						<xsl:value-of select="$prefix"/>-<xsl:value-of select="$i + 1"/>/</xsl:when>
					
					<xsl:otherwise></xsl:otherwise>
				</xsl:choose>
			</xsl:variable>
			
			<!-- Pagination item -->
			<xsl:if test="$i != $page">
				<!-- First pagination item -->
				<xsl:if test="$page - $pre_count_page &gt; 0 and $i = 0">
					<a href="{$link}" class="page_link" style="text-decoration: none;">←</a>
				</xsl:if>
				
				<xsl:choose>
					<xsl:when test="$i &gt;= ($page - $pre_count_page) and ($page + $post_count_page) &gt;= $i">
						<!-- Pagination item -->
						<a href="{$link}{$tag_link}{$number_link}" class="page_link">
							<xsl:value-of select="$i + 1"/>
						</a>
					</xsl:when>
					<xsl:otherwise></xsl:otherwise>
				</xsl:choose>
				
				<!-- Last pagination item -->
				<xsl:if test="$i+1 &gt;= $n and $n &gt; ($page + 1 + $post_count_page)">
					<xsl:choose>
						<xsl:when test="$n &gt; round($n)">
							<!-- Last pagination item -->
							<a href="{$link}{$prefix}-{round($n+1)}/" class="page_link" style="text-decoration: none;">→</a>
						</xsl:when>
						<xsl:otherwise>
							<a href="{$link}{$prefix}-{round($n)}/" class="page_link" style="text-decoration: none;">→</a>
						</xsl:otherwise>
					</xsl:choose>
				</xsl:if>
			</xsl:if>
			
			<!-- Ctrl+left link -->
			<xsl:if test="$page != 0 and $i = $page">
				<xsl:variable name="prev_number_link">
					<xsl:choose>
						
						<xsl:when test="$page != 0">page-<xsl:value-of select="$i"/>/</xsl:when>
						
						<xsl:otherwise></xsl:otherwise>
					</xsl:choose>
				</xsl:variable>
				
				<a href="{$link}{$tag_link}{$prev_number_link}" id="id_prev"></a>
			</xsl:if>
			
			<!-- Ctrl+right link -->
			<xsl:if test="($n - 1) > $page and $i = $page">
				<a href="{$link}{$tag_link}page-{$page+2}/" id="id_next"></a>
			</xsl:if>
			
			<!-- Current pagination item -->
			<xsl:if test="$i = $page">
				<span class="current">
					<xsl:value-of select="$i+1"/>
				</span>
			</xsl:if>
			
			<!-- Recursive Template -->
			<xsl:call-template name="for">
				<xsl:with-param name="i" select="$i + 1"/>
				<xsl:with-param name="prefix" select="$prefix"/>
				<xsl:with-param name="link" select="$link"/>
				<xsl:with-param name="limit" select="$limit"/>
				<xsl:with-param name="page" select="$page"/>
				<xsl:with-param name="total" select="$total"/>
				<xsl:with-param name="visible_pages" select="$visible_pages"/>
			</xsl:call-template>
		</xsl:if>
	</xsl:template>
</xsl:stylesheet>