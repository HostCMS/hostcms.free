<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>

	<!-- ВыводУслуги -->

	<xsl:template match="/">
		<xsl:apply-templates select="/informationsystem/informationsystem_item"/>
	</xsl:template>

	<xsl:template match="/informationsystem/informationsystem_item">

		<h1 hostcms:id="{@id}" hostcms:field="name" hostcms:entity="informationsystem_item"><xsl:value-of select="name"/></h1>

		<!-- Выводим сообщение -->
		<xsl:if test="/informationsystem/message/node()">
			<xsl:value-of disable-output-escaping="yes" select="/informationsystem/message"/>
		</xsl:if>

		<!-- Фотогафия к информационному элементу -->
		<xsl:if test="image_small!=''">
			<!-- Проверяем задан ли путь к файлу большого изображения -->
			<xsl:choose>
				<xsl:when test="image_large!=''">
					<div id="gallery">
						<a href="{dir}{image_large}" target="_blank">
							<img src="{dir}{image_small}" class="news_img"/>
						</a>
					</div>
				</xsl:when>
				<xsl:otherwise>
					<img src="{dir}{image_small}" class="news_img"/>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:if>

		<!-- Текст информационного элемента -->
		<xsl:choose>
		<xsl:when test="parts_count > 1">
			<xsl:value-of disable-output-escaping="yes" select="text"/>
		</xsl:when>
		<xsl:otherwise>
			<div hostcms:id="{@id}" hostcms:field="text" hostcms:entity="informationsystem_item" hostcms:type="wysiwyg">
				<xsl:value-of disable-output-escaping="yes" select="text"/>
			</div>
		</xsl:otherwise>
		</xsl:choose>

		<!-- Ссылка 1-2-3 на части документа -->
		<xsl:if test="parts_count &gt; 1">
			<div class="read_more">Читать дальше:</div>

			<xsl:call-template name="for">
				<xsl:with-param name="limit">1</xsl:with-param>
				<xsl:with-param name="page" select="/informationsystem/part"/>
				<xsl:with-param name="link" select="/informationsystem/informationsystem_item/url"/>
				<xsl:with-param name="items_count" select="parts_count"/>
				<xsl:with-param name="visible_pages">6</xsl:with-param>
				<xsl:with-param name="prefix">part</xsl:with-param>
			</xsl:call-template>

			<div style="clear: both"></div>
		</xsl:if>
	</xsl:template>

	<!-- Цикл для вывода строк ссылок -->
	<xsl:template name="for">
		<xsl:param name="i" select="0"/>
		<xsl:param name="prefix">page</xsl:param>
		<xsl:param name="link"/>
		<xsl:param name="limit"/>
		<xsl:param name="page"/>
		<xsl:param name="items_count"/>
		<xsl:param name="visible_pages"/>

		<xsl:variable name="n" select="$items_count div $limit"/>

		<!-- Заносим в переменную $group идентификатор текущей группы -->
		<xsl:variable name="group" select="/informationsystem/group"/>


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

		<xsl:if test="$items_count &gt; $limit and $n &gt; $i">
			<!-- Ставим ссылку на страницу-->
			<xsl:if test="$i != $page">
				<!-- Определяем адрес тэга -->
				<xsl:variable name="tag_link">
					<xsl:choose>
						<!-- Если не нулевой уровень -->
						<xsl:when test="count(/informationsystem/tag) != 0">tag/<xsl:value-of select="/informationsystem/tag/urlencode"/>/</xsl:when>
						<!-- Иначе если нулевой уровень - просто ссылка на страницу со списком элементов -->
						<xsl:otherwise></xsl:otherwise>
					</xsl:choose>
				</xsl:variable>

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

				<!-- Выводим ссылку на первую страницу -->
				<xsl:if test="$page - $pre_count_page &gt; 0 and $i = 0">
					<a href="{$link}" class="page_link" style="text-decoration: none;">←</a>
				</xsl:if>

				<xsl:choose>
					<xsl:when test="$i &gt;= ($page - $pre_count_page) and ($page + $post_count_page) &gt;= $i">

						<!-- Выводим ссылки на видимые страницы -->
						<a href="{$link}{$tag_link}{$number_link}" class="page_link">
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
				<xsl:with-param name="items_count" select="$items_count"/>
				<xsl:with-param name="visible_pages" select="$visible_pages"/>
			</xsl:call-template>
		</xsl:if>
	</xsl:template>

	<!-- Склонение после числительных -->
	<xsl:template name="declension">

		<xsl:param name="number" select="number"/>

		<!-- Именительный падеж -->
	<xsl:variable name="nominative"><xsl:text>просмотр</xsl:text></xsl:variable>

		<!-- Родительный падеж, единственное число -->
	<xsl:variable name="genitive_singular"><xsl:text>просмотра</xsl:text></xsl:variable>

	<xsl:variable name="genitive_plural"><xsl:text>просмотров</xsl:text></xsl:variable>
		<xsl:variable name="last_digit"><xsl:value-of select="$number mod 10"/></xsl:variable>
		<xsl:variable name="last_two_digits"><xsl:value-of select="$number mod 100"/></xsl:variable>

		<xsl:choose>
			<xsl:when test="$last_digit = 1 and $last_two_digits != 11">
				<xsl:value-of select="$nominative"/>
			</xsl:when>
			<xsl:when test="$last_digit = 2 and $last_two_digits != 12
				or $last_digit = 3 and $last_two_digits != 13
				or $last_digit = 4 and $last_two_digits != 14">
				<xsl:value-of select="$genitive_singular"/>
			</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="$genitive_plural"/>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
</xsl:stylesheet>