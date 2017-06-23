<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>
	
	<!-- СписокУслугНаГлавной -->
	
	<xsl:template match="/">
		<div class="info_block">
			<xsl:apply-templates select="/informationsystem/informationsystem_item" />
		</div>
	</xsl:template>
	
	<xsl:template match="informationsystem_item">
		<dl>
			<xsl:attribute name="class">
				<xsl:choose>
					<xsl:when test="position() mod 2 = 0">right</xsl:when>
					<xsl:otherwise>left</xsl:otherwise>
				</xsl:choose>
			</xsl:attribute>
			
		<dt><a href="{url}" hostcms:id="{@id}" hostcms:field="name" hostcms:entity="informationsystem_item"><xsl:value-of select="name"/></a></dt>
			<dd hostcms:id="{@id}" hostcms:field="description" hostcms:entity="informationsystem_item" hostcms:type="wysiwyg">
				<xsl:value-of disable-output-escaping="yes" select="description"/>
			</dd>
		</dl>
	</xsl:template>
</xsl:stylesheet>