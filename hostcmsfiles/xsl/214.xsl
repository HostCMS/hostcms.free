<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>

	<!-- МагазинСписокПроизводителей -->

	<xsl:template match="/">
		<xsl:apply-templates select="shop"/>
	</xsl:template>

	<xsl:template match="shop">
		<h1>Производители</h1>
		
		<xsl:if test="count(shop_producer) = 0">
			<div id="error">Производители не найдены!</div>
		</xsl:if>

		<table cellspacing="0" cellpadding="0" border="0" width="100%">
			<tr>
				<xsl:apply-templates select="shop_producer"/>
			</tr>
		</table>

		<!-- Ссылка, для которой дописываются суффиксы page-XX/ -->
		<xsl:variable name="link"><xsl:value-of select="/shop/url"/>producers/</xsl:variable>

		<xsl:call-template name="for">
			<xsl:with-param name="link" select="$link"/>
			<xsl:with-param name="limit" select="/shop/limit"/>
			<xsl:with-param name="page" select="/shop/page"/>
			<xsl:with-param name="total" select="/shop/total"/>
			<xsl:with-param name="visible_pages">5</xsl:with-param>
		</xsl:call-template>

	</xsl:template>

	<xsl:template match="shop_producer">
		<td width="33%" align="center" valign="top">
			<xsl:if test="image_small != ''">
				<p><a href="{/shop/url}producers/{path}/"><img src="{dir}{image_small}" class="image" /></a></p>
			</xsl:if>
			<p><a href="{/shop/url}producers/{path}/"><xsl:value-of disable-output-escaping="no" select="name"/></a></p>
		</td>
		<xsl:if test="position() mod 3 = 0 and position() != last()">
			<xsl:text disable-output-escaping="yes">
				&lt;/tr&gt;
				&lt;tr valign="top"&gt;
			</xsl:text>
		</xsl:if>
	</xsl:template>

	<!-- Цикл для вывода строк ссылок -->
	<xsl:template name="for">
		<xsl:param name="i" select="0"/>
		<xsl:param name="prefix">page</xsl:param>
		<xsl:param name="link"/>
		<xsl:param name="limit"/>
		<xsl:param name="page"/>
		<xsl:param name="total"/>
		<xsl:param name="visible_pages"/>

		<xsl:variable name="n" select="$total div $limit"/>

		<!-- Заносим в переменную $parent_group_id идентификатор текущей группы -->
		<xsl:variable name="parent_group_id" select="/document/parent_group_id"/>

		<!-- Считаем количество выводимых ссылок перед текущим элементом -->
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

		<!-- Считаем количество выводимых ссылок после текущего элемента -->
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
			<span class="ctrl">←
			Ctrl</span>
		</xsl:if>

		<xsl:if test="$i &gt;= $n and ($n - 1) &gt; $page">
			<span class="ctrl">Ctrl →</span>
		</xsl:if>

		<xsl:if test="$total &gt; $limit and $n &gt; $i">

			<!-- Определяем адрес ссылки -->
			<xsl:variable name="number_link">
				<xsl:choose>
					<!-- Если не нулевой уровень -->
					<xsl:when test="$i != 0">
						<xsl:value-of select="$prefix"/>-<xsl:value-of select="$i + 1"/>/</xsl:when>
					<!-- Иначе если нулевой уровень - просто ссылка на страницу со списком элементов -->
					<xsl:otherwise></xsl:otherwise>
				</xsl:choose>
			</xsl:variable>

			<!-- Ставим ссылку на страницу-->
			<xsl:if test="$i != $page">
				<!-- Выводим ссылку на первую страницу -->
				<xsl:if test="$page - $pre_count_page &gt; 0 and $i = 0">
					<a href="{$link}" class="page_link" style="text-decoration: none;">←</a>
				</xsl:if>

				<xsl:choose>
					<xsl:when test="$i &gt;= ($page - $pre_count_page) and ($page + $post_count_page) &gt;= $i">
						<!-- Выводим ссылки на видимые страницы -->
						<a href="{$link}{$number_link}" class="page_link">
							<xsl:value-of select="$i + 1"/>
						</a>
					</xsl:when>
					<xsl:otherwise></xsl:otherwise>
				</xsl:choose>

				<!-- Выводим ссылку на последнюю страницу -->
				<xsl:if test="$i+1 &gt;= $n and $n &gt; ($page + 1 + $post_count_page)">
					<xsl:choose>
						<xsl:when test="$n &gt; round($n)">
							<!-- Выводим ссылку на последнюю страницу -->
							<a href="{$link}{$prefix}-{round($n+1)}/" class="page_link" style="text-decoration: none;">→</a>
						</xsl:when>
						<xsl:otherwise>
							<a href="{$link}{$prefix}-{round($n)}/" class="page_link" style="text-decoration: none;">→</a>
						</xsl:otherwise>
					</xsl:choose>
				</xsl:if>
			</xsl:if>

			<!-- Ссылка на предыдущую страницу для Ctrl + влево -->
			<xsl:if test="$page != 0 and $i = $page">
				<xsl:variable name="prev_number_link">
					<xsl:choose>
						<!-- Если не нулевой уровень -->
						<xsl:when test="$page &gt; 1">page-<xsl:value-of select="$i"/>/</xsl:when>
						<!-- Иначе если нулевой уровень - просто ссылка на страницу со списком элементов -->
						<xsl:otherwise></xsl:otherwise>
					</xsl:choose>
				</xsl:variable>

				<a href="{$link}{$prev_number_link}" id="id_prev"></a>
			</xsl:if>

			<!-- Ссылка на следующую страницу для Ctrl + вправо -->
			<xsl:if test="($n - 1) &gt; $page and $i = $page">
				<a href="{$link}page-{$page+2}/" id="id_next"></a>
			</xsl:if>

			<!-- Не ставим ссылку на страницу-->
			<xsl:if test="$i = $page">
				<span class="current">
					<xsl:value-of select="$i+1"/>
				</span>
			</xsl:if>

			<!-- Рекурсивный вызов шаблона. НЕОБХОДИМО ПЕРЕДАВАТЬ ВСЕ НЕОБХОДИМЫЕ ПАРАМЕТРЫ! -->
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