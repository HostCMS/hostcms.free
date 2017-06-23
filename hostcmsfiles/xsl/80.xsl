<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>

	<!-- ОплатаПоФормеПД4 -->

	<xsl:decimal-format name="my" decimal-separator="." grouping-separator=" "/>

	<xsl:template match="/shop">
		
		<ul class="shop_navigation">
			<li><span>Адрес доставки</span>→</li>
			<li><span>Способ доставки</span>→</li>
			<li><span>Форма оплаты</span>→</li>
			<li class="shop_navigation_current"><span>Данные доставки</span></li>
		</ul>

		<h1>Ваш заказ оформлен</h1>

		<p>Распечатайте <a href="{/shop/url}cart/print/{shop_order/guid}/" target="_blank"><b>бланк квитанции по форме ПД-4</b></a><xsl:text> </xsl:text><img src="/hostcmsfiles/images/new_window.gif"/>, вырежьте его и оплатите в отделении Сбербанка или другого коммерческого банка.</p>

		<!-- <p>Через некоторое время с Вами свяжется наш менеджер, чтобы согласовать заказанный товар и время доставки.</p> -->

		<xsl:apply-templates select="shop_order"/>

		<xsl:choose>
			<xsl:when test="count(shop_order/shop_order_item)">
				<h2>Заказанные товары</h2>

				<table class="shop_cart">
					<tr>
						<th>Наименование</th>
						<th>Артикул</th>
						<th>Количество</th>
						<th>Цена</th>
						<th>Сумма</th>
					</tr>
					<xsl:apply-templates select="shop_order/shop_order_item"/>
					<tr class="total">
						<td colspan="3"></td>
						<td>Итого:</td>
						<td><xsl:value-of select="format-number(shop_order/total_amount,'### ##0.00', 'my')"/><xsl:text> </xsl:text><xsl:value-of select="/shop/shop_order/shop_currency/name"/></td>
					</tr>
				</table>
			</xsl:when>
			<xsl:otherwise>
				<p><b>Заказанных товаров нет.</b></p>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

	<!-- Order Template -->
	<xsl:template match="shop_order">

		<h2>Данные доставки</h2>

		<p>
			<b>ФИО:</b><xsl:text> </xsl:text><xsl:value-of select="surname"/><xsl:text> </xsl:text><xsl:value-of select="name"/><xsl:text> </xsl:text><xsl:value-of select="patronymic"/>

			<br /><b>E-mail:</b><xsl:text> </xsl:text><xsl:value-of select="email"/>

			<xsl:if test="phone != ''">
				<br /><b>Телефон:</b><xsl:text> </xsl:text><xsl:value-of select="phone"/>
			</xsl:if>

			<xsl:if test="fax != ''">
				<br /><b>Факс:</b><xsl:text> </xsl:text><xsl:value-of select="fax"/>
			</xsl:if>

			<xsl:variable name="location">, <xsl:value-of select="shop_country/shop_country_location/name"/></xsl:variable>
			<xsl:variable name="city">, <xsl:value-of select="shop_country/shop_country_location/shop_country_location_city/name"/></xsl:variable>
			<xsl:variable name="city_area">, <xsl:value-of select="shop_country/shop_country_location/shop_country_location_city/shop_country_location_city_area/name"/></xsl:variable>
			<xsl:variable name="adres">, <xsl:value-of select="address"/></xsl:variable>

			<br /><b>Адрес доставки:</b><xsl:text> </xsl:text><xsl:if test="postcode != ''"><xsl:value-of select="postcode"/>, </xsl:if>
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
				<xsl:value-of select="$city_area"/><xsl:text> </xsl:text>район</xsl:if>
			<xsl:if test="$adres != ', '">
				<xsl:value-of select="$adres"/>
			</xsl:if>

			<xsl:if test="shop_delivery/name != ''">
				<br /><b>Тип доставки:</b><xsl:text> </xsl:text><xsl:value-of select="shop_delivery/name"/>
			</xsl:if>

			<xsl:if test="shop_payment_system/name != ''">
				<br /><b>Способ оплаты:</b><xsl:text> </xsl:text><xsl:value-of select="shop_payment_system/name"/>
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