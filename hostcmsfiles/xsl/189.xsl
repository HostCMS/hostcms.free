<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "lang://189">
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

		<!-- Show informationsystem_item -->
		<xsl:if test="informationsystem_item">
			<dl class="news_list">
				<xsl:apply-templates select="informationsystem_item"/>
			</dl>
		</xsl:if>

		<p class="button"><a href="{url}" title="&labelAllNews;">&labelAllNews;</a></p>
	</xsl:template>

	<!-- informationsystem_item template -->
	<xsl:template match="informationsystem_item">
		<!-- Text representation of a date -->
		<dt>
			<xsl:value-of select="substring-before(date, '.')"/>
			<xsl:variable name="month_year" select="substring-after(date, '.')"/>
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
			<xsl:value-of select="substring-after($month_year, '.')"/>
		</dt>

		<dd>
			<a href="{url}" hostcms:id="{@id}" hostcms:field="name" hostcms:entity="informationsystem_item">
				<xsl:value-of select="name"/>
			</a>
		</dd>
	</xsl:template>
</xsl:stylesheet>