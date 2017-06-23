<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>

	<!-- СписокНовостейНаГлавной -->

	<xsl:template match="/">
		<xsl:apply-templates select="/informationsystem"/>
	</xsl:template>

	<xsl:template match="/informationsystem">
		<!-- Выводим название информационной системы -->
		<p class="h1" hostcms:id="{@id}" hostcms:field="name" hostcms:entity="informationsystem">
			<xsl:value-of select="name"/>
		</p>

		<!-- Отображение записи информационной системы -->
		<xsl:if test="informationsystem_item">
			<dl class="news_list">
				<xsl:apply-templates select="informationsystem_item"/>
			</dl>
		</xsl:if>

		<p class="button"><a href="{url}" title="Все новости">Все новости</a></p>
	</xsl:template>

	<!-- Шаблон вывода информационного элемента -->
	<xsl:template match="informationsystem_item">
		<!-- Дата время -->
		<dt>
			<xsl:value-of select="substring-before(date, '.')"/>
			<xsl:variable name="month_year" select="substring-after(date, '.')"/>
			<xsl:variable name="month" select="substring-before($month_year, '.')"/>
			<xsl:choose>
				<xsl:when test="$month = 1"> января </xsl:when>
				<xsl:when test="$month = 2"> февраля </xsl:when>
				<xsl:when test="$month = 3"> марта </xsl:when>
				<xsl:when test="$month = 4"> апреля </xsl:when>
				<xsl:when test="$month = 5"> мая </xsl:when>
				<xsl:when test="$month = 6"> июня </xsl:when>
				<xsl:when test="$month = 7"> июля </xsl:when>
				<xsl:when test="$month = 8"> августа </xsl:when>
				<xsl:when test="$month = 9"> сентября </xsl:when>
				<xsl:when test="$month = 10"> октября </xsl:when>
				<xsl:when test="$month = 11"> ноября </xsl:when>
				<xsl:otherwise> декабря </xsl:otherwise>
			</xsl:choose>
			<xsl:value-of select="substring-after($month_year, '.')"/><xsl:text> г.</xsl:text>
		</dt>

		<dd>
			<a href="{url}" hostcms:id="{@id}" hostcms:field="name" hostcms:entity="informationsystem_item">
				<xsl:value-of select="name"/>
			</a>
		</dd>
	</xsl:template>
</xsl:stylesheet>