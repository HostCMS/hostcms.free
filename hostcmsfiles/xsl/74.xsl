<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="no" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>

	<xsl:decimal-format name="my" decimal-separator="," grouping-separator=" "/>

	<xsl:template match="/">
		<xsl:apply-templates select="shop"/>
	</xsl:template>

	<!-- Шаблон для списка заказов -->
	<xsl:template match="shop">

		<h1>Мои заказы</h1>

		<!-- Выводим ошибку, если она была передана через внешний параметр -->
		<xsl:if test="error/node()">
			<div id="error">
				<xsl:value-of select="error"/>
			</div>
		</xsl:if>

		<xsl:choose>
			<xsl:when test="count(shop_order)">
				<xsl:apply-templates select="shop_order"/>
			</xsl:when>
			<xsl:otherwise>
				<p>Список заказов пуст.</p>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

	<!-- Шаблон для заказа -->
	<xsl:template match="shop_order">

		<h2><xsl:text>Заказ №</xsl:text><xsl:choose>
				<xsl:when test="invoice != ''"><xsl:value-of select="invoice"/></xsl:when>
			<xsl:otherwise><xsl:value-of select="@id"/></xsl:otherwise></xsl:choose><xsl:text> от </xsl:text><xsl:value-of select="date"/><xsl:text> г.</xsl:text>
		</h2>

		<!-- Ссылка на бланк оплаты -->
		<xsl:if test="shop_payment_system/name != ''">
			<p class="orderListPaymentSystem">
				<xsl:text>Распечатать бланк «</xsl:text><a href="{/shop/url}cart/print/{guid}/" target="_blank"><xsl:value-of select="shop_payment_system/name" /></a><xsl:text> </xsl:text><img src="/hostcmsfiles/images/new_window.gif"/><xsl:text>».</xsl:text>
			</p>
		</xsl:if>

		<p class="orderListActions">
			<xsl:choose>
				<xsl:when test="paid != '0'">
					<span class="paid">Заказ оплачен&#xA0;<xsl:value-of select="payment_datetime"/></span>
				</xsl:when>
				<xsl:when test="canceled != 0">
					<span class="canceled">Заказ отменен.</span>
				</xsl:when>
				<xsl:otherwise>
					<span class="notPaid">Заказ не оплачен.</span>
					<xsl:text> </xsl:text>
					<a href="?action=cancel&amp;guid={guid}" onclick="return confirm('Вы действительно хотите отменить заказ?');">Отменить заказ</a>,
					<xsl:text> </xsl:text><a onclick="$('#change_payment_system{@id}').toggle('slow')">изменить форму оплаты</a>,
				</xsl:otherwise>
			</xsl:choose>

			<xsl:text> </xsl:text><a href="{/shop/url}cart/?action=repeat&amp;guid={guid}" onclick="return confirm('Вы действительно хотите повторить заказ?')">повторить заказ</a>.

			<!-- Блок для смены статуса оплаты -->
			<xsl:if test="paid = 0 and canceled = 0">
				<div class="orderListChangePaymentSystem" id="change_payment_system{@id}">
					<form method="post">
						<select name="shop_payment_system_id">
							<xsl:apply-templates select="/shop/shop_payment_systems/shop_payment_system">
								<xsl:with-param name="shop_payment_system_id" select="shop_payment_system_id"/>
							</xsl:apply-templates>
						</select>
						<input type="hidden" name="shop_order_id" value="{@id}"/>
						<input type="submit" name="change_payment_system" style="margin-left: 15px;" value="Изменить форму оплаты"/>
					</form>
				</div>
			</xsl:if>
		</p>

		<xsl:if test="shop_order_status/node()">
			<p>
				Статус:&#xA0;<b><xsl:value-of select="shop_order_status/name"/></b><xsl:if test="status_datetime != '0000-00-00 00:00:00'">, <xsl:value-of select="status_datetime"/></xsl:if>.
			</p>
		</xsl:if>

		<p>
			Адрес:&#xA0;<strong><xsl:if test="postcode != ''">
					<xsl:value-of select="postcode"/>, </xsl:if>

				<xsl:if test="shop_country/name != ''">
					<xsl:value-of select="shop_country/name"/>, </xsl:if>

				<xsl:if test="shop_country/shop_country_location/name != ''">
					<xsl:value-of select="shop_country/shop_country_location/name"/>, </xsl:if>

				<xsl:if test="shop_country/shop_country_location/shop_country_location_city/name != ''">г. <xsl:value-of select="shop_country/shop_country_location/shop_country_location_city/name"/>, </xsl:if>

				<xsl:if test="shop_country/shop_country_location/shop_country_location_city/shop_country_location_city_area/name != ''">
					<xsl:value-of select="shop_country/shop_country_location/shop_country_location_city/shop_country_location_city_area/name"/>, </xsl:if>

				<xsl:value-of select="address"/></strong>

			<xsl:if test="phone != ''">
				<br />Телефон:&#xA0;<strong><xsl:value-of select="phone"/></strong>
			</xsl:if>

			<xsl:if test="shop_delivery/node()">
				<br />Доставка:&#xA0;<strong><xsl:value-of select="shop_delivery/name"/></strong>
			</xsl:if>

			<xsl:if test="delivery_information != ''">
				<br />Информация об отправлении:&#xA0;<strong><xsl:value-of select="delivery_information"/></strong>
			</xsl:if>
		</p>

		<!-- Заказанные товары -->
		<table class="shop_cart">
			<tr>
				<th>Наименование</th>
				<th>Цена</th>
				<th>Количество</th>
				<th></th>
				<th>Стоимость</th>
			</tr>
			<xsl:apply-templates select="shop_order_item"/>
			<tr class="total">
				<td colspan="4" style="text-align: right">
					Итого:
				</td>
				<td style="white-space: nowrap; text-align: right">
					<xsl:value-of select="format-number(amount,'### ##0,00', 'my')"/>&#160;<xsl:value-of select="shop_currency/name" disable-output-escaping="yes"/>
				</td>
			</tr>
		</table>
	</xsl:template>

	<!-- Шаблон для элементов заказа -->
	<xsl:template match="shop_order_item">
		<tr>
			<td>
				<xsl:choose>
					<xsl:when test="shop_item/node()">
						<a href="{shop_item/url}">
							<xsl:value-of select="name"/>
						</a>
					</xsl:when>
					<xsl:otherwise>
						<xsl:value-of select="name"/>
					</xsl:otherwise>
				</xsl:choose>
			</td>
			<td align="right" width="90px" style="white-space: nowrap">
				<xsl:value-of select="format-number(price,'### ##0,00', 'my')"/>
				<!-- Показывать ли валюту -->
				<xsl:if test="../shop_currency/name != ''"><xsl:text> </xsl:text><xsl:value-of select="../shop_currency/name" disable-output-escaping="yes"/></xsl:if>
			</td>
			<td width="60px" style="white-space: nowrap">
				<b>x&#xA0;</b><xsl:text> </xsl:text><xsl:value-of select="quantity"/><xsl:text> </xsl:text><xsl:value-of select="shop_item/shop_measure/name"/></td>
			<td width="15px" align="center">=</td>
			<td width="90px" align="right">
				<xsl:value-of select="format-number(price * quantity,'### ##0,00', 'my')"/><xsl:text> </xsl:text><xsl:value-of select="../shop_currency/name" disable-output-escaping="yes"/>
			</td>
		</tr>
	</xsl:template>

	<!-- Список платежных систем для изменения платежной системы -->
	<xsl:template match="shop_payment_system">
		<xsl:param name="shop_payment_system_id"/>

		<xsl:choose>
			<xsl:when test="$shop_payment_system_id = @id">
				<option value="{@id}" selected="selected"><xsl:value-of select="name"/></option>
			</xsl:when>
			<xsl:otherwise>
				<option value="{@id}"><xsl:value-of select="name"/></option>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
</xsl:stylesheet>