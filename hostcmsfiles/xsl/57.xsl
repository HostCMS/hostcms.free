<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "lang://57">
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:exsl="http://exslt.org/common" extension-element-prefixes="exsl">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>

	<xsl:decimal-format name="my" decimal-separator="," grouping-separator=" "/>

	<!-- Шаблон для корзины -->
	<xsl:template match="/shop">
		<xsl:choose>
			<xsl:when test="count(shop_cart) = 0">
				<h1>&labelEmptyCart;</h1>
				<p>&labelChooseProduct;</p>
			</xsl:when>
			<xsl:otherwise>
				<h1>&labelTitle;</h1>
				<p>&labelOrder;</p>

				<form action="{/shop/url}cart/" method="post">
					<!-- Если есть товары -->
					<xsl:if test="count(shop_cart[postpone = 0]) > 0">
						<table class="shop_cart">
							<xsl:call-template name="tableHeader"/>
							<xsl:apply-templates select="shop_cart[postpone = 0]"/>
							<xsl:call-template name="tableFooter">
								<xsl:with-param name="nodes" select="shop_cart[postpone = 0]"/>
							</xsl:call-template>

							<!-- Скидки -->
							<xsl:if test="count(shop_purchase_discount)">
								<xsl:apply-templates select="shop_purchase_discount"/>
								<tr class="total">
									<td>&labelTotal;</td>
									<td></td>
									<td></td>
									<td>
										<xsl:value-of select="format-number(total_amount, '### ##0,00', 'my')"/><xsl:text> </xsl:text><xsl:value-of disable-output-escaping="yes" select="/shop/shop_currency/name"/>
									</td>
									<td></td>
									<xsl:if test="count(/shop/shop_warehouse)">
										<td></td>
									</xsl:if>
									<td></td>
									<td></td>
								</tr>
							</xsl:if>
						</table>
					</xsl:if>

					<!-- Купон -->
					<div class="shop_coupon">
						&labelCoupon; <input name="coupon_text" type="text" value="{coupon_text}"/>
					</div>

					<!-- Если есть отложенные товары -->
					<xsl:if test="count(shop_cart[postpone = 1]) > 0">
						<div class="transparent">
							<h2>&labelPostponeItems;</h2>
							<table class="shop_cart">
								<xsl:call-template name="tableHeader"/>
								<xsl:apply-templates select="shop_cart[postpone = 1]"/>
								<xsl:call-template name="tableFooter">
									<xsl:with-param name="nodes" select="shop_cart[postpone = 1]"/>
								</xsl:call-template>
							</table>
						</div>
					</xsl:if>

					<!-- Кнопки -->
					<input name="recount" value="&labelRecount;" type="submit" class="button" />

					<!-- Пользователь авторизован или модуль пользователей сайта отсутствует -->
					<xsl:if test="count(shop_cart[postpone = 0]) and (siteuser_id > 0 or siteuser_exists = 0)">
						<input name="step" value="1" type="hidden" />
						<input value="&labelCheckout;" type="submit" class="button"/>
					</xsl:if>
				</form>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

	<!-- Заголовок таблицы -->
	<xsl:template name="tableHeader">
		<tr>
			<th>&labelItem;</th>
			<th width="70">&labelQuantity;</th>
			<th width="110">&labelUnitPrice;</th>
			<th width="150">&labelAmount;</th>
			<xsl:if test="count(/shop/shop_warehouse)">
				<th width="100">&labelWarehouse;</th>
			</xsl:if>
			<th>&labelPostpone;</th>
			<th>&labelActions;</th>
		</tr>
	</xsl:template>

	<!-- Итоговая строка таблицы -->
	<xsl:template name="tableFooter">
		<xsl:param name="nodes"/>

		<tr class="total">
			<td>&labelTotal2;</td>
			<td><xsl:value-of select="sum($nodes/quantity)"/></td>
		<td><xsl:text> </xsl:text></td>
			<td>
				<xsl:variable name="subTotals">
					<xsl:for-each select="$nodes">
						<sum><xsl:value-of select="shop_item/price * quantity"/></sum>
					</xsl:for-each>
				</xsl:variable>

				<xsl:value-of select="format-number(sum(exsl:node-set($subTotals)/sum), '### ##0,00', 'my')"/><xsl:text> </xsl:text><xsl:value-of disable-output-escaping="yes" select="/shop/shop_currency/name"/>
			</td>
			<xsl:if test="count(/shop/shop_warehouse)">
			<td><xsl:text> </xsl:text></td>
			</xsl:if>
		<td><xsl:text> </xsl:text></td>
		<td><xsl:text> </xsl:text></td>
		</tr>
	</xsl:template>

	<!-- Шаблон для товара в корзине -->
	<xsl:template match="shop_cart">
		<tr>
			<td>
				<a href="{shop_item/url}">
					<xsl:value-of disable-output-escaping="yes" select="shop_item/name"/>
				</a>

				<!-- Комплекты -->
				<xsl:if test="shop_item/type = 3">
					<xsl:for-each select="shop_item/set/shop_item">
						<div>
							<span><xsl:text> — </xsl:text></span>
							<a href="{url}">
								<xsl:value-of disable-output-escaping="yes" select="name"/>
							</a>
						</div>
					</xsl:for-each>
				</xsl:if>
			</td>
			<td>
				<input type="text" size="3" name="quantity_{shop_item/@id}" id="quantity_{shop_item/@id}" value="{quantity}"/>
			</td>
			<td>
				<!-- Цена -->
				<xsl:value-of select="format-number(shop_item/price, '### ##0,00', 'my')"/><xsl:text> </xsl:text><xsl:value-of select="shop_item/currency" disable-output-escaping="yes"/>
			</td>
			<td>
				<!-- Amount -->
				<xsl:value-of disable-output-escaping="yes" select="format-number(shop_item/price * quantity, '### ##0,00', 'my')"/><xsl:text> </xsl:text><xsl:value-of disable-output-escaping="yes" select="shop_item/currency"/>
			</td>
			<xsl:if test="count(/shop/shop_warehouse)">
				<td>
					<xsl:choose>
						<xsl:when test="sum(shop_item/shop_warehouse_item/count) > 0">
							<select name="warehouse_{shop_item/@id}">
								<xsl:apply-templates select="shop_item/shop_warehouse_item"/>
							</select>
						</xsl:when>
						<xsl:otherwise>—</xsl:otherwise>
					</xsl:choose>
				</td>
			</xsl:if>
			<td align="center">
				<!-- Postpone -->
				<input type="checkbox" name="postpone_{shop_item/@id}">
					<xsl:if test="postpone = 1">
						<xsl:attribute name="checked">checked</xsl:attribute>
					</xsl:if>
				</input>
			</td>
		<td align="center"><a href="?delete={shop_item/@id}" onclick="return confirm('&labelDeleteAlert;')" title="&labelDelete2;" alt="&labelDelete2;">&labelDelete;</a></td>
		</tr>
	</xsl:template>

	<xsl:template match="shop_purchase_discount">
		<tr>
			<td>
				<xsl:value-of select="name"/>
			</td>
			<td></td>
			<td></td>
			<td>
				<!-- Amount -->
				<xsl:value-of select="format-number(discount_amount * -1, '### ##0,00', 'my')"/><xsl:text> </xsl:text><xsl:value-of select="/shop/shop_currency/name" disable-output-escaping="yes"/>
			</td>
			<xsl:if test="count(/shop/shop_warehouse)">
				<td></td>
			</xsl:if>
			<td></td>
			<td></td>
		</tr>
	</xsl:template>

	<!-- Warehouse option -->
	<xsl:template match="shop_warehouse_item">
		<xsl:if test="count != 0">
			<xsl:variable name="shop_warehouse_id" select="shop_warehouse_id" />
			<option value="{$shop_warehouse_id}">
				<xsl:if test="../../shop_warehouse_id = $shop_warehouse_id">
					<xsl:attribute name="selected">selected</xsl:attribute>
				</xsl:if>
				<xsl:value-of select="/shop/shop_warehouse[@id=$shop_warehouse_id]/name"/> (<xsl:value-of select="count - reserved"/>)
			</option>
		</xsl:if>
	</xsl:template>

</xsl:stylesheet>