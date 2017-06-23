<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>

	<xsl:template match="/">
		<xsl:apply-templates select="/document"/>
	</xsl:template>

	<xsl:template match="/document">

		<!-- Получаем ID родительской группы и записываем в переменную $parent_group_id -->
		<xsl:variable name="parent_group_id" select="blocks/parent_group_id"/>

		<!-- Если в находимся корне - выводим название информационной системы -->
		<xsl:if test="blocks/parent_group_id=0">

			<h1>
				<xsl:value-of disable-output-escaping="yes" select="blocks/name"/>
			</h1>
			<span class="bodytext">
				<xsl:value-of disable-output-escaping="yes" select="blocks/description"/>
			</span>
		</xsl:if>

		<!-- Если в находимся в группе - выводим название группы -->
		<xsl:if test="blocks/parent_group_id!=0">
			<h1>
				<xsl:value-of disable-output-escaping="yes" select=".//group[@id=$parent_group_id]/name"/>
			</h1>

			<!-- Путь к группе -->
			<p>
				<xsl:apply-templates select=".//group[@id=$parent_group_id]" mode="goup_path"/>
			</p>
		</xsl:if>

		<!-- Отображение подгрупп данной группы -->
		<ul>
			<xsl:apply-templates select=".//group[@parent_id=$parent_group_id]" mode="groups"/>
		</ul>

		<!-- Отображение записи информационной системы -->
		<xsl:apply-templates select="blocks/items/item[item_status=1]"/>

		<p>
			<!-- Строка ссылок на другие страницы информационной системы -->
			<xsl:if test="ОтображатьСсылкиНаСледующиеСтраницы=1">
				<xsl:call-template name="for">
					<xsl:with-param name="items_on_page" select="blocks/items/items_on_page"/>
					<xsl:with-param name="current_page" select="blocks/items/current_page"/>
					<xsl:with-param name="count_items" select="blocks/items/count_items"/>
				</xsl:call-template>
			</xsl:if>
		</p>

		<div style="clear: both"></div>

		<div style="margin-right:10px">
			<table border="0" width="100%" cellpadding="0" cellspacing="0" style="padding-left: 10px;">
				<tr>
					<!-- Ссылка на архив -->
					<xsl:if test="ОтображатьСсылкуНаАрхив=1">
						<td>
							<a href="{blocks/url}">Архив "<xsl:value-of disable-output-escaping="yes" select="blocks/name"/>"</a>
						</td>
					</xsl:if>
					<td align="right">
						<a href="{blocks/url}rss/">
							<img src="/images/rss/rss_2.0.gif"/>
						</a>
					</td>
				</tr>
			</table>
		</div>
	</xsl:template>

	<!-- ======================================================== -->
	<!-- Шаблон выводит рекурсивно ссылки на группы инф. элемента -->
	<!-- ======================================================== -->

	<xsl:template match="group" mode="goup_path">
		<xsl:variable name="parent_id" select="@parent_id"/>

		<xsl:apply-templates select="//group[@id=$parent_id]" mode="goup_path"/>

		<xsl:if test="@parent_id=0">
			<a href="{/document/blocks/url}">
				<xsl:value-of disable-output-escaping="yes" select="/document/blocks/name"/>
			</a>
		</xsl:if>

		<span><xsl:text> → </xsl:text></span>

		<a href="{/document/blocks/url}{fullpath}">
			<xsl:value-of disable-output-escaping="yes" select="name"/>
		</a>
	</xsl:template>

	<!-- ======================================================== -->
	<!-- Шаблон выводит ссылки подгруппы информационного элемента -->
	<!-- ======================================================== -->
	<xsl:template match="group" mode="groups">
		<li>
			<xsl:if test="small_image!=''">
				<a href="{/document/blocks/url}{fullpath}" target="_blank">
					<img src="{small_image}"/>
				</a><xsl:text> </xsl:text></xsl:if>
			<a href="{/document/blocks/url}{fullpath}">
				<b>
					<xsl:value-of disable-output-escaping="yes" select="name"/>
				</b>
			</a><xsl:text> </xsl:text><span style="color: #dadada">(<xsl:value-of select="count_all_items"/>)</span></li>
	</xsl:template>

	<!-- ======================== -->
	<!-- Данные об инф. элементах -->
	<!-- ======================== -->
	<xsl:template match="blocks/items/item">


		<p>

			<!-- Дата время -->

			<span class="news_date">
				<xsl:value-of disable-output-escaping="yes" select="item_datetime"/>
			</span>
			<br/>
			<!-- Название -->
			<a href="{item_path}">
				<xsl:value-of disable-output-escaping="yes" select="item_name"/>
			</a>
			<br/>

			<!-- Изображение для информационного элемента (если есть) -->
			<xsl:if test="item_small_image!=''">
				<a href="{item_path}" class="news_title">
					<img src="{item_small_image}" class="partner_img" alt="" style="margin: 3px 5px 3px 0px" align="left"/>
				</a>
			</xsl:if>

			<span class="bodytext">
				<xsl:value-of disable-output-escaping="yes" select="item_description"/>
			</span>

			<div style="clear: both;"></div>
		</p>
	</xsl:template>

	<!-- Цикл для вывода строк ссылок -->
	<xsl:template name="for">
		<xsl:param name="i" select="0"/>
		<xsl:param name="items_on_page"/>
		<xsl:param name="current_page"/>
		<xsl:param name="count_items"/>

		<xsl:variable name="n" select="$count_items div $items_on_page"/>

		<xsl:if test="$count_items &gt; $items_on_page and $n &gt; $i">

			<!-- Разделитель ссылок -->
			<!--
		<xsl:if test="$i != 0 and $n > $current_page"> - </xsl:if>
		-->

			<!-- Ставим ссылку на страницу-->
			<xsl:if test="$i != $current_page">

				<!-- Заносим в переменную $parent_group_id идентификатор текущей группы -->
				<xsl:variable name="parent_group_id" select="/document/blocks/parent_group_id"/>

				<!-- Определяем группу для формирования адреса ссылки -->
				<xsl:variable name="group_link">
					<xsl:choose>
						<!-- Если группа не корневая (!=0) -->
						<xsl:when test="$parent_group_id != 0">
							<xsl:value-of select="/document/blocks//group[@id = $parent_group_id]/fullpath"/>
						</xsl:when>
						<!-- Иначе если нулевой уровень - просто ссылка на страницу со списком элементов -->
						 <xsl:otherwise><xsl:value-of select="/informationsystem/url"/></xsl:otherwise> 
					</xsl:choose>
				</xsl:variable>

				<!-- Определяем адрес ссылки -->
				<xsl:variable name="number_link">
					<xsl:choose>
						<!-- Если не нулевой уровень -->
						<xsl:when test="$i != 0">page-<xsl:value-of select="$i + 1"/>/</xsl:when>
						<!-- Иначе если нулевой уровень - просто ссылка на страницу со списком элементов -->
						<xsl:otherwise></xsl:otherwise>
					</xsl:choose>
				</xsl:variable>

				<a href="{/document/blocks/url}{$group_link}{$number_link}" class="page_link">
					<xsl:value-of select="$i + 1"/>
				</a>
			</xsl:if>

			<!-- Не ставим ссылку на страницу-->
			<xsl:if test="$i = $current_page">
				<span class="current">
					<xsl:value-of select="$i+1"/>
				</span>
			</xsl:if>

			<!-- Рекурсивный вызов шаблона. НЕОБХОДИМО ПЕРЕДАВАТЬ ВСЕ НЕОБХОДИМЫЕ ПАРАМЕТРЫ! -->
			<xsl:call-template name="for">
				<xsl:with-param name="i" select="$i + 1"/>
				<xsl:with-param name="items_on_page" select="$items_on_page"/>
				<xsl:with-param name="current_page" select="$current_page"/>
				<xsl:with-param name="count_items" select="$count_items"/>
			</xsl:call-template>
		</xsl:if>
	</xsl:template>
</xsl:stylesheet>