<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "lang://23">
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>
	
	<xsl:template match="/">
		<xsl:apply-templates select="/document"/>
	</xsl:template>
	
	<xsl:template match="/document">
		
		<!-- Разрешена отправка формы -->
		<xsl:if test="confirm_get_form=1">
			
			<!-- Название формы -->
			<h1>
				<xsl:value-of select="forms_name"/>
			</h1>
			
			<p>&labelSuccess;</p>
		</xsl:if>
	</xsl:template>
</xsl:stylesheet>