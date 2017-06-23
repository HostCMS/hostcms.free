<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>
	
	<xsl:template match="/site">
		<h1>Карта сайта</h1>
		
		<ul class="map">
			<!-- Выбираем узлы структуры -->
			<xsl:apply-templates select="child::*[show=1]"/>
		</ul>
	</xsl:template>
	
	<xsl:template match="*">
		<li>
			<!-- Запишем в константу ID структуры, данные для которой будут выводиться пользователю -->
			<xsl:variable name="current_structure_id" select="/document/structure/current_structure_id"/>
			
			<!-- Показывать ссылку, или нет -->
			<xsl:if test="show = 1">
				
				<!-- Определяем адрес ссылки -->
				<xsl:variable name="link">
					<xsl:choose>
						<!-- Если внешняя ссылка -->
						<xsl:when test="url != ''">
							<xsl:value-of disable-output-escaping="yes" select="url"/>
						</xsl:when>
						<!-- Иначе если внутренняя ссылка -->
						<xsl:otherwise>
							<xsl:value-of disable-output-escaping="yes" select="link"/>
						</xsl:otherwise>
					</xsl:choose>
				</xsl:variable>
				
				<!-- Определяем стиль вывода ссылки -->
				<xsl:variable name="link_style">
					<xsl:choose>
						<!-- Выделяем текущую страницу жирным (если это текущая страница, либо у нее есть ребенок с ID, равным текущей) -->
						<xsl:when test="$current_structure_id=@id or count(.//structure[@id=$current_structure_id])=1">font-weight: bold</xsl:when>
						<!-- Иначе обычный вывод с пустым стилем -->
						<xsl:otherwise></xsl:otherwise>
					</xsl:choose>
				</xsl:variable>
				
				<a href="{$link}">
					<span style="{$link_style}">
						<xsl:value-of disable-output-escaping="yes" select="name"/>
					</span>
				</a>
			</xsl:if>
			
			<!-- Выбираем подузлы структуры -->
			<xsl:if test="count(child::*[show=1]) &gt; 0">
				<ul>
					<!-- Выбираем узлы структуры -->
					<xsl:apply-templates select="child::*[show=1]"/>
				</ul>
			</xsl:if>
		</li>
	</xsl:template>
</xsl:stylesheet>