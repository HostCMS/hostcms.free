<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "lang://62">
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>
	
	<xsl:decimal-format name="my" decimal-separator="," grouping-separator=" "/>
	
	<!-- Шаблон для типов доставки -->
	<xsl:template match="/shop">
		
		<ul class="shop_navigation">
		<li><span>&labelAddress;</span>→</li>
		<li class="shop_navigation_current"><span>&labelShipmentMethod;</span>→</li>
		<li><span>&labelPaymentMethod;</span>→</li>
		<li><span>&labelOrderConfirmation;</span></li>
		</ul>
		
		<h1>&labelType;</h1>
		
		<form method="post">
			<!-- Проверяем количество способов доставки -->
			<xsl:choose>
				<xsl:when test="count(shop_delivery) = 0">
					<p>&labelLine1;</p>
					<p>&labelLine2;</p>
					<input type="hidden" name="shop_delivery_condition_id" value="0"/>
				</xsl:when>
				<xsl:otherwise>
					<table class="shop_cart">
						<tr class="total">
							<th width="20%">&labelType;</th>
							<th>&labelDescription;</th>
							<th width="15%">&labelDeliveryPrice;</th>
							<th width="15%">&labelItemsAmount;</th>
							<th width="15%">&labelTotal;</th>
						</tr>
						<xsl:apply-templates select="shop_delivery"/>
					</table>
				</xsl:otherwise>
			</xsl:choose>
			
			<input name="step" value="3" type="hidden" />
			<input value="&labelNext;" type="submit" class="button" />
		</form>
	</xsl:template>
	
	<xsl:template match="shop_delivery">
		<tr>
			<td>
				<label>
					<input type="radio" value="{shop_delivery_condition/@id}" name="shop_delivery_condition_id">
						<xsl:if test="position() = 1">
							<xsl:attribute name="checked">checked</xsl:attribute>
						</xsl:if>
			</input><xsl:text> </xsl:text><span class="caption"><xsl:value-of select="name"/></span>
				</label>
			</td>
			<td>
				
				<xsl:value-of disable-output-escaping="yes" select="description"/>
				
				<xsl:if test="normalize-space(shop_delivery_condition/description) != ''">
					<div>
						<xsl:value-of disable-output-escaping="yes" select="shop_delivery_condition/description"/>
					</div>
				</xsl:if>
			</td>
			<td>
				<xsl:if test="shop_delivery_condition/price != ''">
					<xsl:value-of select="format-number(shop_delivery_condition/price, '### ##0,00', 'my')"/><xsl:text> </xsl:text><xsl:value-of select="/shop/shop_currency/sign"/>
				</xsl:if>
			</td>
			<td>
				<xsl:value-of select="format-number(/shop/total_amount, '### ##0,00', 'my')"/><xsl:text> </xsl:text><xsl:value-of select="/shop/shop_currency/sign"/>
			</td>
			<td class="total">
				<xsl:if test="shop_delivery_condition/price != ''">
					<xsl:value-of select="format-number(/shop/total_amount + shop_delivery_condition/price, '### ##0,00', 'my')"/><xsl:text> </xsl:text><xsl:value-of select="/shop/shop_currency/sign"/>
				</xsl:if>
			</td>
		</tr>
	</xsl:template>
</xsl:stylesheet>