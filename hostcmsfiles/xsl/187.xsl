<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "lang://187">
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>

	<xsl:template match="shop">
		<h1 hostcms:id="{@id}" hostcms:field="name" hostcms:entity="shop">
			<xsl:value-of select="name"/>
		</h1>

		<xsl:choose>
			<xsl:when test="shop_item/node()">
				<div class="shop_table board">
					<xsl:apply-templates select="shop_item" />
				</div>
			</xsl:when>
			<xsl:otherwise>
				<h2>&labelNone;</h2>
			</xsl:otherwise>
		</xsl:choose>

		<xsl:if test="show_button_add_advertisement/node() and show_button_add_advertisement = 1">
			<p><a href="{path}">&labelAddItem;</a></p>
		</xsl:if>

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
	</xsl:template>

	<xsl:template match="shop_item">
		<div class="table_row">
			<div class="date" style="display: table-cell">
				<b><xsl:value-of disable-output-escaping="yes" select="format-number(substring-before(datetime, '.'), '#')"/></b>
				<xsl:variable name="month_year" select="substring-after(datetime, '.')"/>
				<xsl:variable name="month" select="substring-before($month_year, '.')"/>
				<xsl:choose>
					<xsl:when test="$month = 1"> &labelMonth1; </xsl:when>
					<xsl:when test="$month = 2"> &labelMonth2; </xsl:when>
					<xsl:when test="$month = 3"> &labelMonth3; </xsl:when>
					<xsl:when test="$month = 4"> &labelMonth4; </xsl:when>
					<xsl:when test="$month = 5"> &labelMonth5; </xsl:when>
					<xsl:when test="$month = 6"> &labelMonth6; </xsl:when>
					<xsl:when test="$month = 7"> &labelMonth7; </xsl:when>
					<xsl:when test="$month = 8"> &labelMonth8; </xsl:when>
					<xsl:when test="$month = 9"> &labelMonth9; </xsl:when>
					<xsl:when test="$month = 10"> &labelMonth10; </xsl:when>
					<xsl:when test="$month = 11"> &labelMonth11; </xsl:when>
					<xsl:otherwise> &labelMonth12; </xsl:otherwise>
				</xsl:choose>
				<br/> &labelIn;
				<!-- Время -->
				<xsl:variable name="full_time" select="substring-after($month_year, ' ')"/>
				<b><xsl:value-of select="substring($full_time, 1, 5)" /><xsl:text> </xsl:text></b>
			</div>
			<div class="image" style="display: table-cell">
				<!-- Изображение для товара, если есть -->
				<a href="{url}">
					<xsl:choose>
						<xsl:when test="image_small != ''">
							<img src="{dir}{image_small}" alt="{name}" title="{name}"/>
						</xsl:when>
						<xsl:otherwise>
							<img src="/images/no-image.png" alt="{name}" title="{name}"/>
						</xsl:otherwise>
					</xsl:choose>
				</a>
			</div>
			<div>
				<a href="{url}" title="{name}" hostcms:id="{@id}" hostcms:field="name" hostcms:entity="shop_item">
					<xsl:value-of select="name"/>
				</a>

				<xsl:variable name="city_name" select="property_value[tag_name='city']/value"/>

				<xsl:if test="$city_name != ''">
					<br/><xsl:value-of select="$city_name" />
				</xsl:if>
			</div>
			<div style="width: 50px; text-decoration: none;">
				<a href="{/shop/structure/link}{@id}/"><img src="/admin/images/edit.gif"/></a>
				<a href="{/shop/structure/link}{@id}/delete/" onclick="return confirm('&labelDeleteAlert;');"><img src="/admin/images/delete.gif"/></a>
			</div>
		</div>
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
			<!-- Set $link variable -->
			<xsl:variable name="number_link"><xsl:if test="$i != 0">page-<xsl:value-of select="$i + 1"/>/</xsl:if></xsl:variable>

			<!-- First pagination item -->
			<xsl:if test="$page - $pre_count_page &gt; 0 and $i = $start_page">
				<a href="{/shop/structure/link}" class="page_link" style="text-decoration: none;">←</a>
			</xsl:if>

			<!-- Pagination item -->
			<xsl:if test="$i != $page">
				<xsl:if test="($page - $pre_count_page) &lt;= $i and $i &lt; $n">
					<!-- Pagination item -->
					<a href="{/shop/structure/link}{$number_link}" class="page_link">
						<xsl:value-of select="$i + 1"/>
					</a>
				</xsl:if>

				<!-- Last pagination item -->
				<xsl:if test="$i+1 &gt;= ($page + $post_count_page + 1) and $n &gt; ($page + 1 + $post_count_page)">
					<!-- Last pagination item -->
					<a href="{/shop/structure/link}page-{$n}/" class="page_link" style="text-decoration: none;">→</a>
				</xsl:if>
			</xsl:if>

			<!-- Ctrl+left link -->
			<xsl:if test="$page != 0 and $i = $page"><xsl:variable name="prev_number_link"><xsl:if test="$page &gt; 1">page-<xsl:value-of select="$i"/>/</xsl:if></xsl:variable><a href="{/shop/structure/link}{$prev_number_link}" id="id_prev"></a></xsl:if>

			<!-- Ctrl+right link -->
			<xsl:if test="($n - 1) > $page and $i = $page">
				<a href="{/shop/structure/link}page-{$page+2}/" id="id_next"></a>
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
</xsl:stylesheet>