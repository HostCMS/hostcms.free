<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "lang://224">
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>
	
	<!-- НижнееМеню -->
	
	<xsl:template match="/site">
		<div class="footer_menu">
			<ul>
				<xsl:apply-templates select="structure[show=1]" />
			</ul>
		</div>
	</xsl:template>
	
	<xsl:variable name="count" select="count(/site/structure[show=1])"/>
	
	<!-- Запишем в константу ID структуры, данные для которой будут выводиться пользователю -->
	<xsl:variable name="floor" select="floor($count div 5)"/>
	
	<!-- Не распределенные элементы -->
	<xsl:template match="structure">
		<li>
			<!-- Set $link variable -->
			<xsl:variable name="link">
				<xsl:choose>
					<!-- External link -->
					<xsl:when test="type = 3 and url != ''">
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

		<xsl:variable name="position">
			<xsl:choose>
				<xsl:when test="$count div position() &lt;= 1">4</xsl:when>
				<xsl:otherwise>3</xsl:otherwise>
			</xsl:choose>
		</xsl:variable>

		<xsl:if test="position() mod $floor = 0 and position() != last()">
			<xsl:text disable-output-escaping="yes">
				&lt;/ul&gt;
				&lt;ul&gt;
			</xsl:text>
		</xsl:if>
	</xsl:template>
</xsl:stylesheet>