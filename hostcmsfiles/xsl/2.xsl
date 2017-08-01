<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "lang://2">
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>

	<!-- ВерхнееМеню -->

	<xsl:template match="/site">
		<ul class="top_menu">
			<!-- Выбираем узлы структуры первого уровня -->
			<xsl:apply-templates select="structure[show=1]" />
		</ul>
	</xsl:template>

	<!-- Запишем в константу ID структуры, данные для которой будут выводиться пользователю -->
	<xsl:variable name="current_structure_id" select="/site/current_structure_id"/>

	<xsl:template match="structure">
		<li>
			<!--
			Выделяем текущую страницу добавлением к li класса current,
			если это текущая страница, либо у нее есть ребенок с атрибутом id, равным текущей группе.
			-->
			<xsl:if test="$current_structure_id = @id or count(.//structure[@id=$current_structure_id]) = 1">
				<xsl:attribute name="class">current</xsl:attribute>
			</xsl:if>

			<xsl:if test="position() = last()">
				<xsl:attribute name="style">background-image: none</xsl:attribute>
			</xsl:if>

			<!-- Set $link variable -->
			<xsl:variable name="link">
				<xsl:choose>
					<!-- External link -->
					<xsl:when test="url != ''">
						<xsl:value-of disable-output-escaping="yes" select="url"/>
					</xsl:when>
					<!-- Internal link -->
					<xsl:otherwise>
						<xsl:value-of disable-output-escaping="yes" select="link"/>
					</xsl:otherwise>
				</xsl:choose>
			</xsl:variable>

			<!-- Menu Node -->
			<a href="{$link}" title="{name}" hostcms:id="{@id}" hostcms:field="name" hostcms:entity="structure"><xsl:value-of select="name"/></a>
		</li>
	</xsl:template>
</xsl:stylesheet>