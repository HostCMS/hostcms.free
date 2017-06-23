<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN"
		encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml" />

	<!-- Шаблон "МагазинПрайс" -->
	<xsl:template match="/shop">
		<h1>Прайс-лист</h1>

		<table class="table">
			<tr>
			<th>Наименование</th>
			<th>Цена</th>
			</tr>
			<xsl:apply-templates select="/shop/shop_item[shop_group_id = 0]"/>
			<xsl:apply-templates select="//shop_group">
				<xsl:sort select="@id" data-type="number" order="ascending"/>
			</xsl:apply-templates>
		</table>

		<xsl:if test="total &gt; 0 and limit &gt; 0">

			<xsl:variable name="count_pages" select="ceiling(total div limit)"/>

			<xsl:variable name="visible_pages" select="5"/>

			<xsl:variable name="real_visible_pages"><xsl:choose>
					<xsl:when test="$count_pages &lt; $visible_pages"><xsl:value-of select="$count_pages"/></xsl:when>
					<xsl:otherwise><xsl:value-of select="$visible_pages"/></xsl:otherwise>
			</xsl:choose></xsl:variable>

			<!-- Считаем количество выводимых ссылок перед текущим элементом -->
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

			<!-- Считаем количество выводимых ссылок после текущего элемента -->
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

	<!-- Группы товаров -->
	<xsl:template match="shop_group">
		<xsl:variable name="id"><xsl:value-of select="@id"/></xsl:variable>
		<xsl:if test="count(/shop/shop_item[shop_group_id=$id])">
			<tr class="total">
				<td colspan="2">
					<xsl:value-of select="name"/>
				</td>
			</tr>
			<xsl:apply-templates select="/shop/shop_item[shop_group_id = $id]"/>
		</xsl:if>
	</xsl:template>

	<!-- Товары -->
	<xsl:template match="shop_item">
		<tr>
		<td>
			<a href="{url}"><xsl:value-of select="name"/></a>
		</td>
		<td>
			<xsl:value-of select="price"/><xsl:text> </xsl:text><xsl:value-of select="currency"/>
		</td>
		</tr>
	</xsl:template>

	<!-- Цикл для вывода строк ссылок -->
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

		<!-- Передаем фильтр -->
		<xsl:variable name="filter"><xsl:if test="/shop/filter/node()">?filter=1&amp;sorting=<xsl:value-of select="/shop/sorting"/>&amp;price_from=<xsl:value-of select="/shop/price_from"/>&amp;price_to=<xsl:value-of select="/shop/price_to"/><xsl:for-each select="/shop/*"><xsl:if test="starts-with(name(), 'property_')">&amp;<xsl:value-of select="name()"/>=<xsl:value-of select="."/></xsl:if></xsl:for-each></xsl:if></xsl:variable>

		<xsl:if test="$items_count &gt; $limit and ($page + $post_count_page + 1) &gt; $i">
			<!-- Заносим в переменную $group идентификатор текущей группы -->
			<xsl:variable name="group" select="/shop/group"/>

			<!-- Путь для тэга -->
			<xsl:variable name="tag_path"><xsl:if test="count(/shop/tag) != 0">tag/<xsl:value-of select="/shop/tag/urlencode"/>/</xsl:if></xsl:variable>

			<!-- Путь для сравнения товара -->
			<xsl:variable name="shop_producer_path"><xsl:if test="count(/shop/shop_producer)">producer-<xsl:value-of select="/shop/shop_producer/@id"/>/</xsl:if></xsl:variable>

			<!-- Определяем группу для формирования адреса ссылки -->
			<xsl:variable name="group_link"><xsl:choose><xsl:when test="$group != 0"><xsl:value-of select="/shop//shop_group[@id=$group]/url"/></xsl:when><xsl:otherwise><xsl:value-of select="/shop/url"/>price/</xsl:otherwise></xsl:choose></xsl:variable>

			<!-- Определяем адрес ссылки -->
			<xsl:variable name="number_link"><xsl:if test="$i != 0">page-<xsl:value-of select="$i + 1"/>/</xsl:if></xsl:variable>

			<!-- Выводим ссылку на первую страницу -->
			<xsl:if test="$page - $pre_count_page &gt; 0 and $i = $start_page">
				<a href="{$group_link}{$tag_path}{$shop_producer_path}{$filter}" class="page_link" style="text-decoration: none;">←</a>
			</xsl:if>

			<!-- Ставим ссылку на страницу-->
			<xsl:if test="$i != $page">
				<xsl:if test="($page - $pre_count_page) &lt;= $i and $i &lt; $n">
					<!-- Выводим ссылки на видимые страницы -->
					<a href="{$group_link}{$number_link}{$tag_path}{$shop_producer_path}{$filter}" class="page_link">
						<xsl:value-of select="$i + 1"/>
					</a>
				</xsl:if>

				<!-- Выводим ссылку на последнюю страницу -->
				<xsl:if test="$i+1 &gt;= ($page + $post_count_page + 1) and $n &gt; ($page + 1 + $post_count_page)">
					<!-- Выводим ссылку на последнюю страницу -->
					<a href="{$group_link}page-{$n}/{$tag_path}{$shop_producer_path}{$filter}" class="page_link" style="text-decoration: none;">→</a>
				</xsl:if>
			</xsl:if>

			<!-- Ссылка на предыдущую страницу для Ctrl + влево -->
			<xsl:if test="$page != 0 and $i = $page"><xsl:variable name="prev_number_link"><xsl:if test="$page &gt; 1">page-<xsl:value-of select="$i"/>/</xsl:if></xsl:variable><a href="{$group_link}{$prev_number_link}{$tag_path}{$shop_producer_path}{$filter}" id="id_prev"></a></xsl:if>

			<!-- Ссылка на следующую страницу для Ctrl + вправо -->
			<xsl:if test="($n - 1) > $page and $i = $page">
				<a href="{$group_link}page-{$page+2}/{$tag_path}{$shop_producer_path}{$filter}" id="id_next"></a>
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