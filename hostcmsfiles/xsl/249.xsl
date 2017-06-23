<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>
	
	<!-- СписокЭлементовИнфосистемы -->
	
	<xsl:template match="/">
		<xsl:apply-templates select="/informationsystem"/>
	</xsl:template>
	
	<xsl:template match="/informationsystem">
		<!-- Если в находимся корне - выводим название информационной системы -->
		<hgroup>
			<h3  hostcms:id="{@id}" hostcms:field="name" hostcms:entity="informationsystem"><xsl:value-of disable-output-escaping="yes" select="name"/></h3>
		</hgroup>
		
		<ul class="date-list">
			<!-- Отображение записи информационной системы -->
			<xsl:apply-templates select="informationsystem_item[active=1]"/>
		</ul>
	<a href="{url}"><b>Все новости</b></a>
	</xsl:template>
	
	<!-- Шаблон вывода информационного элемента -->
	<xsl:template match="informationsystem_item">
		<!-- День -->
		<xsl:variable name="day" select="substring-before(date, '.')" />
		<!-- Месяц -->
		<xsl:variable name="month" select="substring(date, 4, 2)" />
		
		<!-- Название месяца -->
		<xsl:variable name="month_name"><xsl:choose><xsl:when test="$month='01'">янв</xsl:when>
				<xsl:when test="$month='02'">фев</xsl:when>
				<xsl:when test="$month='03'">мар</xsl:when>
				<xsl:when test="$month='04'">апр</xsl:when>
				<xsl:when test="$month='05'">май</xsl:when>
				<xsl:when test="$month='06'">июн</xsl:when>
				<xsl:when test="$month='07'">июл</xsl:when>
				<xsl:when test="$month='08'">авг</xsl:when>
				<xsl:when test="$month='09'">сен</xsl:when>
				<xsl:when test="$month='10'">окт</xsl:when>
				<xsl:when test="$month='11'">ноя</xsl:when>
		<xsl:when test="$month='12'">дек</xsl:when></xsl:choose></xsl:variable>
		<li>
		<p class="date"><span><xsl:value-of select="$day" /></span><xsl:value-of select="$month_name" /></p>
		<h6><a href="{url}" hostcms:id="{@id}" hostcms:field="name" hostcms:entity="informationsystem_item"><xsl:value-of disable-output-escaping="yes" select="name"/></a></h6>
			
			<xsl:if test="description != ''">
				<p hostcms:id="{@id}" hostcms:field="description" hostcms:entity="informationsystem_item" hostcms:type="wysiwyg"><xsl:value-of disable-output-escaping="yes" select="description"/></p>
			</xsl:if>
			
		<div class="alignright"><a href="{url}" class="button">Подробнее</a></div>
		</li>
	</xsl:template>
</xsl:stylesheet>