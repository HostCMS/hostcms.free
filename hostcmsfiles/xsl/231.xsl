<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "lang://231">
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>

	<!-- МагазинПоследнийЗаказ -->

	<xsl:decimal-format name="my" decimal-separator="," grouping-separator=" "/>

	<xsl:template match="/shop">
		<p class="h1 red">&labelTitle;</p>

		<div class="lastOrder">
			<p class="h2">&labelOrder; <xsl:value-of disable-output-escaping="yes" select="substring(shop_order/payment_datetime, 1, 10)"/></p>
			<xsl:apply-templates select="shop_item[position() &lt; 3]"/>
		</div>
	</xsl:template>

	<xsl:template match="shop_item">
		<!-- Изображение для товара, если есть -->
		<div style="height:{image_small_height + 10}px;">
			<xsl:choose>
				<xsl:when test="image_small != ''">
					<div style = "float:left; margin: 0 15px 10px 0; width:100px">
						<a href="{url}" target="_blank"><img src="{dir}{image_small}" /></a>
					</div>
				</xsl:when>
				<xsl:otherwise>
					<div style = "float:left;">
						<img src="/images/no-image.png" />
					</div>
				</xsl:otherwise>
			</xsl:choose>

			<div style="margin-top: 15px">
				<a href="{url}"><xsl:value-of select="name"/></a>

				<br/><br/>

				<span class="category_name">
					<xsl:value-of select="format-number(price, '### ##0,00', 'my')"/><xsl:text> </xsl:text><xsl:value-of select="currency"/><xsl:text> </xsl:text>
				</span>
			</div>
		</div>

		<xsl:if test="position() != last()">
			<hr/>
		</xsl:if>

		<div style="clear:both"></div>
	</xsl:template>

</xsl:stylesheet>