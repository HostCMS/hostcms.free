<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "lang://21">
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>
	
	<xsl:template match="/">
		<xsl:apply-templates select="/site"/>
	</xsl:template>
	
	<xsl:template match="/site">
		<h1>&labelSearch;</h1>
		
		<form method="get" action="/search/" class="search">
			<input id="search" type="text" size="50" name="text" value="{query}" maxlength="200" class="input_buttom_search" placeholder="&labelSearchQuery;"/><xsl:text> </xsl:text>
			<input type="submit" class="button" value="&labelSearch;"/>
		</form>
		
		<xsl:if test="query!=''">
			<p><strong>&labelFound; <xsl:value-of select="total"/><xsl:text> </xsl:text><xsl:call-template name="declension">
				<xsl:with-param name="number" select="total"/></xsl:call-template>
			</strong>.</p>
			
			<xsl:if test="total!=0">
				<ol start="{page * limit + 1}" class="search">
					<xsl:apply-templates select="search_page"></xsl:apply-templates>
				</ol>
				
				<!-- Pagination -->
				<p>
					<xsl:variable name="count_pages" select="ceiling(total div limit)"/>
					
					<xsl:variable name="visible_pages" select="5"/>
					
					<xsl:variable name="real_visible_pages"><xsl:choose>
							<xsl:when test="$count_pages &lt; $visible_pages"><xsl:value-of select="$count_pages"/></xsl:when>
							<xsl:otherwise><xsl:value-of select="$visible_pages"/></xsl:otherwise>
					</xsl:choose></xsl:variable>
					
					<!-- Links before current -->
					<xsl:variable name="pre_count_page"><xsl:choose>
						<xsl:when test="(page) - (floor($real_visible_pages div 2)) &lt; 0">
							<xsl:value-of select="page"/>
						</xsl:when>
						<xsl:when test="($count_pages  - (page) - 1) &lt; floor($real_visible_pages div 2)">
							<xsl:value-of select="$real_visible_pages - ($count_pages  - (page) - 1) - 1"/>
						</xsl:when>
						<xsl:otherwise>
							<xsl:choose>
								<xsl:when test="round($real_visible_pages div 2) = $real_visible_pages div 2">
									<xsl:value-of select="floor($real_visible_pages div 2) - 1"/>
								</xsl:when>
								<xsl:otherwise>
									<xsl:value-of select="floor($real_visible_pages div 2)"/>
								</xsl:otherwise>
							</xsl:choose>
						</xsl:otherwise>
					</xsl:choose></xsl:variable>
					
					<!-- Links after current -->
					<xsl:variable name="post_count_page"><xsl:choose>
						<xsl:when test="0 &gt; (page) - (floor($real_visible_pages div 2) - 1)">
							<xsl:value-of select="$real_visible_pages - (page) - 1"/>
						</xsl:when>
						<xsl:when test="($count_pages  - (page) - 1) &lt; floor($real_visible_pages div 2)">
							<xsl:value-of select="$real_visible_pages - $pre_count_page - 1"/>
						</xsl:when>
						<xsl:otherwise>
							<xsl:value-of select="$real_visible_pages - $pre_count_page - 1"/>
						</xsl:otherwise>
					</xsl:choose></xsl:variable>
					
					<xsl:variable name="i"><xsl:choose>
						<xsl:when test="(page) + 1 = $count_pages"><xsl:value-of select="(page) - $real_visible_pages + 1"/></xsl:when>
						<xsl:when test="(page) - $pre_count_page &gt; 0"><xsl:value-of select="(page) - $pre_count_page"/></xsl:when>
						<xsl:otherwise>0</xsl:otherwise>
					</xsl:choose></xsl:variable>
					
					<xsl:call-template name="for">
						<xsl:with-param name="limit" select="limit"/>
						<xsl:with-param name="page" select="page"/>
						<xsl:with-param name="items_count" select="total"/>
						<xsl:with-param name="i" select="$i"/>
						<xsl:with-param name="post_count_page" select="$post_count_page"/>
						<xsl:with-param name="pre_count_page" select="$pre_count_page"/>
						<xsl:with-param name="visible_pages" select="$real_visible_pages"/>
					</xsl:call-template>
					
					<div style="clear: both"></div>
				</p>
			</xsl:if>
		</xsl:if>
		
		<xsl:if test="query = ''">
			<p>&labelSearchQuery;</p>
		</xsl:if>
	</xsl:template>
	
	<xsl:template match="search_page">
		<li>
			<a href="{url}">
				<xsl:value-of select="title"/>
			</a>
			<br />
		<span class="description"><xsl:apply-templates select="url"/><xsl:text> · </xsl:text><xsl:value-of select="date"/></span>
		</li>
	</xsl:template>
	
	<xsl:template name="url" match="text()">
		<xsl:param name="str" select="."/>
		
		<xsl:param name="max">50</xsl:param>
		<xsl:param name="hvost">10</xsl:param>
		
		<xsl:param name="begin">
			<xsl:choose>
				<xsl:when test="string-length($str) &gt; $max">
					<xsl:value-of select="substring($str, 1, $max - $hvost)"/>
				</xsl:when>
				
				<xsl:otherwise>
					<xsl:value-of select="substring($str, 1)"/>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:param>
		
		<xsl:param name="end">
			<xsl:choose>
				<xsl:when test="string-length($str) &gt; $max">
					<xsl:value-of select="substring($str, string-length($str) - $hvost + 1, $hvost)"/>
				</xsl:when>
				
				<xsl:otherwise>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:param>
		
		
		<xsl:param name="result">
			<xsl:choose>
				<xsl:when test="$end != ''">
					<xsl:value-of select="concat($begin, '…', $end)"/>
				</xsl:when>
				<xsl:otherwise>
					<xsl:value-of select="$begin"/>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:param>
		
		<xsl:value-of disable-output-escaping="yes" select="$result"/>
	</xsl:template>
	
	<!-- Pagination -->
	<xsl:template name="for">
		<xsl:param name="limit"/>
		<xsl:param name="page"/>
		<xsl:param name="pre_count_page"/>
		<xsl:param name="post_count_page"/>
		<xsl:param name="i" select="0"/>
		<xsl:param name="items_count"/>
		<xsl:param name="visible_pages"/>
		
		<xsl:variable name="n" select="ceiling($items_count div $limit)"/>
		
		<xsl:variable name="start_page"><xsl:choose>
				<xsl:when test="$page + 1 = $n"><xsl:value-of select="$page - $visible_pages + 1"/></xsl:when>
				<xsl:when test="$page - $pre_count_page &gt; 0"><xsl:value-of select="$page - $pre_count_page"/></xsl:when>
				<xsl:otherwise>0</xsl:otherwise>
		</xsl:choose></xsl:variable>
		
		<!-- <xsl:if test="$i = $start_page and $page != 0">
			<span class="ctrl">
				← Ctrl
			</span>
		</xsl:if>
		
		<xsl:if test="$i = ($page + $post_count_page + 1) and $n != ($page+1)">
			<span class="ctrl">
				Ctrl →
			</span>
		</xsl:if> -->
		
		<xsl:if test="$items_count &gt; $limit and ($page + $post_count_page + 1) &gt; $i">
			
			<!-- Ссылка на текущий узел структуры -->
			<xsl:variable name="link" select="/site/url" />
			
			<!-- Текст поискового запроса -->
			<xsl:variable name="queryencode">?text=<xsl:value-of select="/site/queryencode"/></xsl:variable>
			
			<!-- Set $link variable -->
			<xsl:variable name="number_link">
				<xsl:choose>
					
					<xsl:when test="$i != 0">page-<xsl:value-of select="$i + 1"/>/</xsl:when>
					
					<xsl:otherwise></xsl:otherwise>
				</xsl:choose>
			</xsl:variable>
			
			<!-- First pagination item -->
			<xsl:if test="$page - $pre_count_page &gt; 0 and $i = $start_page">
				<a href="{$link}{$queryencode}" class="page_link" style="text-decoration: none;">←</a>
			</xsl:if>
			
			<!-- Pagination item -->
			<xsl:if test="$i != $page">
				<xsl:if test="($page - $pre_count_page) &lt;= $i and $i &lt; $n">
					<!-- Pagination item -->
					<a href="{$link}{$number_link}{$queryencode}" class="page_link">
						<xsl:value-of select="$i + 1"/>
					</a>
				</xsl:if>
				
				<!-- Last pagination item -->
				<xsl:if test="$i+1 &gt;= ($page + $post_count_page + 1) and $n &gt; ($page + 1 + $post_count_page)">
					<!-- Last pagination item -->
					<a href="{$link}page-{$n}/{$queryencode}" class="page_link" style="text-decoration: none;">→</a>
				</xsl:if>
			</xsl:if>
			
			<!-- Ctrl+left link -->
			<xsl:if test="$page != 0 and $i = $page">
				<xsl:variable name="prev_number_link">
					<xsl:choose>
						
						<xsl:when test="$page &gt; 1">page-<xsl:value-of select="$i"/>/</xsl:when>
						
						<xsl:otherwise></xsl:otherwise>
					</xsl:choose>
				</xsl:variable>
				
				<a href="{$link}{$prev_number_link}{$queryencode}" id="id_prev"></a>
			</xsl:if>
			
			<!-- Ctrl+right link -->
			<xsl:if test="($n - 1) > $page and $i = $page">
				<a href="{$link}page-{$page+2}/{$queryencode}" id="id_next"></a>
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
				<xsl:with-param name="limit" select="$limit"/>
				<xsl:with-param name="page" select="$page"/>
				<xsl:with-param name="items_count" select="$items_count"/>
				<xsl:with-param name="pre_count_page" select="$pre_count_page"/>
				<xsl:with-param name="post_count_page" select="$post_count_page"/>
				<xsl:with-param name="visible_pages" select="$visible_pages"/>
			</xsl:call-template>
		</xsl:if>
	</xsl:template>
	
	<!-- Declension of the numerals -->
	<xsl:template name="declension">
		
		<xsl:param name="number" select="number"/>
		
		<!-- Nominative case / Именительный падеж -->
		<xsl:variable name="nominative">
			<xsl:text>&labelPage;</xsl:text>
		</xsl:variable>
		
		<!-- Genitive singular / Родительный падеж, единственное число -->
		<xsl:variable name="genitive_singular">
			<xsl:text>&labelPages;</xsl:text>
		</xsl:variable>
		
		<xsl:variable name="genitive_plural">
			<xsl:text>&labelPages1;</xsl:text>
		</xsl:variable>
		
		<xsl:variable name="last_digit">
			<xsl:value-of select="$number mod 10"/>
		</xsl:variable>
		
		<xsl:variable name="last_two_digits">
			<xsl:value-of select="$number mod 100"/>
		</xsl:variable>
		
		<xsl:choose>
			<xsl:when test="$last_digit = 1 and $last_two_digits != 11">
				<xsl:value-of select="$nominative"/>
			</xsl:when>
			<xsl:when test="$last_digit = 2 and $last_two_digits != 12     or     $last_digit = 3 and $last_two_digits != 13     or     $last_digit = 4 and $last_two_digits != 14">
				<xsl:value-of select="$genitive_singular"/>
			</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="$genitive_plural"/>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
</xsl:stylesheet>