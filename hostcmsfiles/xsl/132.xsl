<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "lang://132">
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>
	
	<!-- Хлебные крошки -->
	
	<xsl:template match="/site">
		<xsl:if test="count(*[@id]) &gt; 0">
			<a href="/">&labelMainPage;</a>
			<xsl:apply-templates select="*[@id]" />
		</xsl:if>
	</xsl:template>
	
	<xsl:template match="*">
		
		<!-- Set $link variable -->
		<xsl:variable name="link">
			<xsl:choose>
				<!-- Если внутренняя ссылка -->
				<xsl:when test="link != ''">
					<xsl:value-of disable-output-escaping="yes" select="link"/>
				</xsl:when>
				<!-- External link -->
				<xsl:otherwise>
					<xsl:value-of disable-output-escaping="yes" select="url"/>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:variable>
		
		<span><xsl:text> → </xsl:text></span>
		
		<xsl:variable name="child" select="*[@id][link/node() or url/node()][name(.) != 'shop_discount']" />
		
		<!-- Показывать ссылку? -->
		<xsl:choose>
			<xsl:when test="(show = 1 or active/node() and active = 1) and count($child) &gt; 0">
				<a href="{$link}">
					<xsl:value-of select="name"/>
				</a>
			</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="name"/>
			</xsl:otherwise>
		</xsl:choose>

		<xsl:apply-templates select="$child" />
		
	</xsl:template>
</xsl:stylesheet>