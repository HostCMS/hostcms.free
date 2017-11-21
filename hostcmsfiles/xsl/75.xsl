<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "lang://75">
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>
	
	<xsl:decimal-format name="my" decimal-separator="." grouping-separator=" "/>
	
	<!-- ОплатаПриПолучении -->
	
	<xsl:template match="/shop">
		
		<ul class="shop_navigation">
		<li><span>&labelAddress;</span>→</li>
		<li><span>&labelShipmentMethod;</span>→</li>
		<li><span>&labelPaymentMethod;</span>→</li>
		<li class="shop_navigation_current"><span>&labelOrderConfirmation;</span></li>
		</ul>
		
		<h1>&labelTitle;</h1>
		
		<p>&labelLine1;</p>
		
		<xsl:apply-templates select="shop_order"/>
		
		<xsl:choose>
			<xsl:when test="count(shop_order/shop_order_item)">
				<h2>&labelOrderedItems;</h2>
				
				<table class="shop_cart">
					<tr>
						<th>&labelItemName;</th>
						<th>&labelMarking;</th>
						<th>&labelQuantity;</th>
						<th>&labelPrice;</th>
						<th>&labelSum;</th>
					</tr>
					<xsl:apply-templates select="shop_order/shop_order_item"/>
					<tr class="total">
						<td colspan="3"></td>
						<td>&labelTotal;</td>
					<td><xsl:value-of select="format-number(shop_order/total_amount,'### ##0.00', 'my')"/><xsl:text> </xsl:text><xsl:value-of select="/shop/shop_order/shop_currency/name"/></td>
					</tr>
				</table>
			</xsl:when>
			<xsl:otherwise>
			<p><b>&labelNone;</b></p>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	
	<!-- Order Template -->
	<xsl:template match="shop_order">
		
		<h2>&labelData;</h2>
		
		<p>
<b>&labelName;</b><xsl:text> </xsl:text><xsl:value-of select="surname"/><xsl:text> </xsl:text><xsl:value-of select="name"/><xsl:text> </xsl:text><xsl:value-of select="patronymic"/>
			
		<br /><b>&labelEmail;</b><xsl:text> </xsl:text><xsl:value-of select="email"/>
			
			<xsl:if test="phone != ''">
			<br /><b>&labelPhone;</b><xsl:text> </xsl:text><xsl:value-of select="phone"/>
			</xsl:if>
			
			<xsl:if test="fax != ''">
			<br /><b>&labelFax;</b><xsl:text> </xsl:text><xsl:value-of select="fax"/>
			</xsl:if>
			
			<xsl:variable name="location">, <xsl:value-of select="shop_country/shop_country_location/name"/></xsl:variable>
			<xsl:variable name="city">, <xsl:value-of select="shop_country/shop_country_location/shop_country_location_city/name"/></xsl:variable>
			<xsl:variable name="city_area">, <xsl:value-of select="shop_country/shop_country_location/shop_country_location_city/shop_country_location_city_area/name"/></xsl:variable>
			<xsl:variable name="adres">, <xsl:value-of select="address"/></xsl:variable>
			
	<br /><b>&labelDeliveryAddress;</b><xsl:text> </xsl:text><xsl:if test="postcode != ''"><xsl:value-of select="postcode"/>, </xsl:if>
			<xsl:if test="shop_country/name != ''">
				<xsl:value-of select="shop_country/name"/>
			</xsl:if>
			<xsl:if test="$location != ', '">
				<xsl:value-of select="$location"/>
			</xsl:if>
			<xsl:if test="$city != ', '">
				<xsl:value-of select="$city"/>
			</xsl:if>
			<xsl:if test="$city_area != ', '">
				<xsl:value-of select="$city_area"/>&#xA0;&labelDistrict;</xsl:if>
			<xsl:if test="$adres != ', '">
				<xsl:value-of select="$adres"/>
			</xsl:if>
			
			<xsl:if test="shop_delivery/name != ''">
			<br /><b>&labelDelivery;</b><xsl:text> </xsl:text><xsl:value-of select="shop_delivery/name"/>
			</xsl:if>
			
			<xsl:if test="shop_payment_system/name != ''">
			<br /><b>&labelPaymentSystem;</b><xsl:text> </xsl:text><xsl:value-of select="shop_payment_system/name"/>
			</xsl:if>
		</p>
	</xsl:template>
	
	<!-- Ordered Item Template -->
	<xsl:template match="shop_order/shop_order_item">
		<tr>
			<td>
				<xsl:choose>
					<xsl:when test="shop_item/url != ''">
						<a href="http://{/shop/site/site_alias/name}{shop_item/url}">
							<xsl:value-of select="name"/>
						</a>
					</xsl:when>
					<xsl:otherwise>
						<xsl:value-of select="name"/>
					</xsl:otherwise>
				</xsl:choose>
			</td>
			<td>
				<xsl:value-of select="marking"/>
			</td>
			<td>
				<xsl:value-of select="quantity"/><xsl:text> </xsl:text><xsl:value-of select="shop_item/shop_measure/name"/>
			</td>
			<td style="white-space: nowrap">
				<xsl:value-of select="format-number(price,'### ##0.00', 'my')"/><xsl:text> </xsl:text><xsl:value-of select="/shop/shop_order/shop_currency/name" disable-output-escaping="yes" />
			</td>
			<td style="white-space: nowrap">
				<xsl:value-of select="format-number(quantity * price,'### ##0.00', 'my')"/><xsl:text> </xsl:text><xsl:value-of select="/shop/shop_order/shop_currency/name" disable-output-escaping="yes" />
			</td>
		</tr>
	</xsl:template>
</xsl:stylesheet>