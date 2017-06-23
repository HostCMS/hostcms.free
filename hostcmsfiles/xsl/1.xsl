<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "lang://1">
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">

	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>

	<!-- ВтороеВерхнееМеню -->

	<xsl:template match="/site">
		<xsl:if test="count(structure[show=1])">

			<xsl:if test="parent_id != 0">
				<hr />
			</xsl:if>

			<ul class="submenu">
				<xsl:apply-templates select="structure[show=1]"/>
			</ul>
		</xsl:if>
	</xsl:template>

	<xsl:template match="structure">
		<li>
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
			<a href="{$link}" title="{name}" hostcms:id="{@id}" hostcms:field="name" hostcms:entity="structure">
					<!--Highlight current item if current item or has child with id=current -->
					<xsl:variable name="current_structure_id" select="/site/current_structure_id"/>
					<xsl:if test="$current_structure_id = @id or count(.//structure[@id=$current_structure_id]) = 1">
						<xsl:attribute name="style">font-weight: bold</xsl:attribute>
					</xsl:if>
					<xsl:value-of select="name"/>
				</a>
			</li>
		</xsl:template>
	</xsl:stylesheet>