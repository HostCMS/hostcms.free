<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "lang://180">
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>

	<!-- ОблакоТэговМагазин -->

	<xsl:template match="/">
		<xsl:apply-templates select="/shop"/>
	</xsl:template>

	<xsl:template match="/shop">

		<xsl:if test="count(tag) != 0">
			<!-- <p class="h1 red">Метки</p>-->

			<xsl:variable name="max_tag_count" select="(/shop/tag/count[not(. &lt; /shop/tag/count)])[1] - 1"/>

			<xsl:variable name="max_size" select="8"/>
			<xsl:variable name="min_size" select="8"/>

			<xsl:variable name="coeff_size">
				<xsl:choose>
					<xsl:when test="$max_tag_count &gt; 0">
						<xsl:value-of select="($max_size - $min_size) div $max_tag_count"/>
					</xsl:when>
					<xsl:otherwise>0</xsl:otherwise>
				</xsl:choose>
			</xsl:variable>
			<div class="TagsCloud">
				<xsl:apply-templates select="tag">
					<xsl:with-param name="min_size" select="$min_size"/>
					<xsl:with-param name="coeff_size" select="$coeff_size"/>
				</xsl:apply-templates>
			</div>
		</xsl:if>

	</xsl:template>

	<!-- Облако из групп -->
	<xsl:template match="tag">

		<xsl:param name="min_size"/>
		<xsl:param name="coeff_size" select="10"/>

		<!-- Нужный размер шрифта вычисляется по формуле $min_size + количество * $coeff_size -->
		<xsl:variable name="size" select="round($min_size + ((count - 1) * $coeff_size))"/>

	<xsl:variable name="group_path"><xsl:if test="/shop/ПутьКГруппе/node()"><xsl:value-of select="/shop/ПутьКГруппе" /></xsl:if></xsl:variable>
		<a href="{/shop/url}{$group_path}tag/{urlencode}/" style="font-size: {$size}pt" hostcms:id="{@id}" hostcms:field="name" hostcms:entity="tag">
			<xsl:value-of select="name"/>
	</a><xsl:text> </xsl:text>

	</xsl:template>
</xsl:stylesheet>