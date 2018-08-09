<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "lang://11">
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>
	
	<!-- СписокКартинок -->
	
	<xsl:template match="/">
		<xsl:apply-templates select="/informationsystem"/>
	</xsl:template>
	
	<xsl:template match="/informationsystem">
		
		<!-- Store parent id in a variable -->
		<xsl:variable name="group" select="group"/>
		
		<xsl:if test="group = 0">
			<h1>
				<xsl:value-of select="name"/>
			</h1>
			
			<!-- Description displays if there is no filtering by tags -->
			<xsl:if test="count(tag) = 0">
				<xsl:value-of disable-output-escaping="yes" select="description"/>
			</xsl:if>
		</xsl:if>
		
		<!-- Show group name inside group -->
		<xsl:if test="group != 0">
			<h1>
				<xsl:value-of select=".//informationsystem_group[@id=$group]/name"/>
			</h1>
			
			<!-- Description displayed only in the first page -->
			<xsl:if test="page = 0">
				<xsl:value-of disable-output-escaping="yes" select=".//informationsystem_group[@id=$group]/description"/>
			</xsl:if>
		</xsl:if>
		
		<!-- Processing of the selected tag -->
		<xsl:if test="count(tag) = 1">
		<p class="h2">&labelTagName; — <strong><xsl:value-of select="tag/name"/></strong>.</p>
		</xsl:if>
		
		<!-- Show subgroups if there are subgroups and not processing of the selected tag -->
		<xsl:if test="count(tag) = 0 and count(.//informationsystem_group[parent_id=$group]) &gt; 0">
			<ul class="polaroids">
				<xsl:apply-templates select=".//informationsystem_group[parent_id=$group]" mode="groups"/>
			</ul>
		</xsl:if>
		
		<xsl:if test="count(informationsystem_item)">
			<!-- jQuery lightbox -->
			<div id="gallery">
				<ul class="polaroids">
					<xsl:apply-templates select="informationsystem_item" />
				</ul>
			</div>
		</xsl:if>
		
		<br class="clearing" />
		
		<!-- Pagination -->
		<xsl:if test="ОтображатьСсылкиНаСледующиеСтраницы=1">
			<div>
				<!-- Current page link -->
				<xsl:variable name="link">
					<xsl:value-of select="/informationsystem/url"/>
					<xsl:if test="$group != 0">
						<xsl:value-of select="/informationsystem//informationsystem_group[@id = $group]/url"/>
					</xsl:if>
				</xsl:variable>
				
				<xsl:if test="total &gt; 0 and limit &gt; 0">
					
					<xsl:variable name="count_pages" select="ceiling(total div limit)"/>
					
					<xsl:variable name="visible_pages" select="5"/>
					
					<xsl:variable name="real_visible_pages"><xsl:choose>
							<xsl:when test="$count_pages &lt; $visible_pages"><xsl:value-of select="$count_pages"/></xsl:when>
							<xsl:otherwise><xsl:value-of select="$visible_pages"/></xsl:otherwise>
					</xsl:choose></xsl:variable>
					
					<!-- Links before current -->
					<xsl:variable name="pre_count_page"><xsl:choose>
							<xsl:when test="page - (floor($real_visible_pages div 2)) &lt; 0">
								<xsl:value-of select="page"/>
							</xsl:when>
							<xsl:when test="($count_pages - page - 1) &lt; floor($real_visible_pages div 2)">
								<xsl:value-of select="$real_visible_pages - ($count_pages - page - 1) - 1"/>
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
							<xsl:when test="0 &gt; page - (floor($real_visible_pages div 2) - 1)">
								<xsl:value-of select="$real_visible_pages - page - 1"/>
							</xsl:when>
							<xsl:when test="($count_pages - page - 1) &lt; floor($real_visible_pages div 2)">
								<xsl:value-of select="$real_visible_pages - $pre_count_page - 1"/>
							</xsl:when>
							<xsl:otherwise>
								<xsl:value-of select="$real_visible_pages - $pre_count_page - 1"/>
							</xsl:otherwise>
					</xsl:choose></xsl:variable>
					
					<xsl:variable name="i"><xsl:choose>
							<xsl:when test="page + 1 = $count_pages"><xsl:value-of select="page - $real_visible_pages + 1"/></xsl:when>
							<xsl:when test="page - $pre_count_page &gt; 0"><xsl:value-of select="page - $pre_count_page"/></xsl:when>
							<xsl:otherwise>0</xsl:otherwise>
					</xsl:choose></xsl:variable>
					
					<p>
						<xsl:call-template name="for">
							<xsl:with-param name="limit" select="limit"/>
							<xsl:with-param name="page" select="page"/>
							<xsl:with-param name="items_count" select="total"/>
							<xsl:with-param name="i" select="$i"/>
							<xsl:with-param name="post_count_page" select="$post_count_page"/>
							<xsl:with-param name="pre_count_page" select="$pre_count_page"/>
							<xsl:with-param name="visible_pages" select="$real_visible_pages"/>
						</xsl:call-template>
					</p>
					<div style="clear: both"></div>
				</xsl:if>
			</div>
		</xsl:if>
		
		<div style="clear: both"></div>
		
	</xsl:template>
	
	<!-- Breadcrumb -->
	<xsl:template match="informationsystem_group" mode="breadCrumbs">
		<xsl:variable name="parent_id" select="parent_id"/>
		
		<xsl:apply-templates select="//informationsystem_group[@id=$parent_id]" mode="breadCrumbs"/>
		
		<xsl:if test="parent_id=0">
			<a href="{/informationsystem/url}">
				<xsl:value-of select="/informationsystem/name"/>
			</a>
		</xsl:if>
		
	<span><xsl:text> → </xsl:text></span>
		
		<a href="{url}">
			<xsl:value-of select="name"/>
		</a>
	</xsl:template>
	
	<!-- Subgroups Template -->
	<xsl:template match="informationsystem_group" mode="groups">
		<li>
			<xsl:choose>
				<xsl:when test="image_small != ''">
					<a href="{url}" title="{name}"><img src="{dir}{image_small}" alt="{name}" /></a>
				</xsl:when>
				<xsl:otherwise>
					<a href="{url}" title="{name}"><img src="/images/no-image.png" alt="{name}" /></a>
				</xsl:otherwise>
			</xsl:choose>
		</li>
	</xsl:template>
	
	<xsl:template match="informationsystem_item">
		<li>
			<xsl:choose>
				<xsl:when test="image_large != ''">
					<a href="{dir}{image_large}" title="{name}" target="_blank"><img src="{dir}{image_small}" alt="{name}" /></a>
				</xsl:when>
				<xsl:when test="image_small != ''">
					<img src="{dir}{image_small}" alt="{name}" />
				</xsl:when>
				<xsl:otherwise>
					<img src="/images/no-image.png" alt="{name}" />
				</xsl:otherwise>
			</xsl:choose>
		</li>
	</xsl:template>
	
	<!-- Tags Template -->
	<xsl:template match="tag">
		<a href="{/informationsystem/url}tag/{urlencode}/" class="tag">
			<xsl:value-of select="name"/>
		</a>
<xsl:if test="position() != last()"><xsl:text>, </xsl:text></xsl:if></xsl:template>
	
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
		
		<xsl:if test="$i = $start_page and $page != 0">
			<span class="ctrl">
				← Ctrl
			</span>
		</xsl:if>
		
		<xsl:if test="$i = ($page + $post_count_page + 1) and $n != ($page+1)">
			<span class="ctrl">
				Ctrl →
			</span>
		</xsl:if>
		
		<xsl:if test="$items_count &gt; $limit and ($page + $post_count_page + 1) &gt; $i">
			<!-- Store in the variable $group ID of the current group -->
			<xsl:variable name="group" select="/informationsystem/group"/>
			
			<!-- Tag Path -->
			<xsl:variable name="tag_path">
				<xsl:choose>
					
					<xsl:when test="count(/informationsystem/tag)">tag/<xsl:value-of select="/informationsystem/tag/urlencode"/>/</xsl:when>
					
					<xsl:otherwise></xsl:otherwise>
				</xsl:choose>
			</xsl:variable>
			
			<!-- Choose Group Path -->
			<xsl:variable name="group_link">
				<xsl:choose>
					<!-- If the group is not root -->
					<xsl:when test="$group != 0">
						<xsl:value-of select="/informationsystem//informationsystem_group[@id=$group]/url"/>
					</xsl:when>
					
					<xsl:otherwise><xsl:value-of select="/informationsystem/url"/></xsl:otherwise>
				</xsl:choose>
			</xsl:variable>
			
			<!-- Set $link variable -->
			<xsl:variable name="number_link">
				<xsl:choose>
					
					<xsl:when test="$i != 0">page-<xsl:value-of select="$i + 1"/>/</xsl:when>
					
					<xsl:otherwise></xsl:otherwise>
				</xsl:choose>
			</xsl:variable>
			
			<!-- First pagination item -->
			<xsl:if test="$page - $pre_count_page &gt; 0 and $i = $start_page">
				<a href="{$group_link}{$tag_path}" class="page_link" style="text-decoration: none;">←</a>
			</xsl:if>
			
			<!-- Pagination item -->
			<xsl:if test="$i != $page">
				<xsl:if test="($page - $pre_count_page) &lt;= $i and $i &lt; $n">
					<!-- Pagination item -->
					<a href="{$group_link}{$number_link}{$tag_path}" class="page_link">
						<xsl:value-of select="$i + 1"/>
					</a>
				</xsl:if>
				
				<!-- Last pagination item -->
				<xsl:if test="$i+1 &gt;= ($page + $post_count_page + 1) and $n &gt; ($page + 1 + $post_count_page)">
					<!-- Last pagination item -->
					<a href="{$group_link}page-{$n}/{$tag_path}" class="page_link" style="text-decoration: none;">→</a>
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
				
				<a href="{$group_link}{$prev_number_link}{$tag_path}" id="id_prev"></a>
			</xsl:if>
			
			<!-- Ctrl+right link -->
			<xsl:if test="($n - 1) > $page and $i = $page">
				<a href="{$group_link}page-{$page+2}/{$tag_path}" id="id_next"></a>
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
			<xsl:text>&labelNominative;</xsl:text>
		</xsl:variable>
		
		<!-- Genitive singular / Родительный падеж, единственное число -->
		<xsl:variable name="genitive_singular">
			<xsl:text>&labelGenitiveSingular;</xsl:text>
		</xsl:variable>
		
		<xsl:variable name="genitive_plural">
			<xsl:text>&labelGenitivePlural;</xsl:text>
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
			<xsl:when test="$last_digit = 2 and $last_two_digits != 12 or $last_digit = 3 and $last_two_digits != 13 or $last_digit = 4 and $last_two_digits != 14">
				<xsl:value-of select="$genitive_singular"/>
			</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="$genitive_plural"/>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
</xsl:stylesheet>