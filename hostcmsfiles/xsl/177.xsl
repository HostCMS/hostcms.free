<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "lang://177">
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	targetNamespace="http://www.sitemaps.org/schemas/sitemap/0.9"
	xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
	<xsl:output encoding="UTF-8" method="xml" indent="yes" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>
	
	<!-- GoogleSiteMap -->
	
	<xsl:template match="/site">
		<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
			<!-- Выбираем узлы структуры -->
			<xsl:apply-templates select="child::*[show=1]"/>
		</urlset>
	</xsl:template>
	
	<xsl:template match="*">
		
		<!-- Показывать ссылку и ссылка не внешняя -->
		<xsl:if test="show = 1">
			<url>
				<loc>http://<xsl:value-of select="/site/site_alias[current = 1]/name"/><xsl:value-of select="link"/></loc>
				<changefreq>
					<xsl:choose>
						<xsl:when test="changefreq = 0">always</xsl:when>
						<xsl:when test="changefreq = 1">hourly</xsl:when>
						<xsl:when test="changefreq = 2">daily</xsl:when>
						<xsl:when test="changefreq = 3">weekly</xsl:when>
						<xsl:when test="changefreq = 4">monthly</xsl:when>
						<xsl:when test="changefreq = 5">yearly</xsl:when>
						<xsl:when test="changefreq = 6">never</xsl:when>
						<xsl:otherwise>daily</xsl:otherwise>
					</xsl:choose>
				</changefreq>
				<priority>
					<xsl:choose>
						<xsl:when test="priority/node()"><xsl:value-of select="priority"/></xsl:when>
						<xsl:otherwise>0</xsl:otherwise>
					</xsl:choose>
				</priority>
			</url>
			
			<!-- Выбираем подузлы структуры -->
			<xsl:apply-templates select="child::*[show=1]"/>
			
		</xsl:if>
	</xsl:template>
</xsl:stylesheet>