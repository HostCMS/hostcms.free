<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>
	
	<!-- Шаблон для платежных систем -->
	<xsl:template match="/shop">
		
		<!-- Строка шага заказа -->
		<ul class="shop_navigation">
			<li><span>Адрес доставки</span>→</li>
			<li><span>Способ доставки</span>→</li>
			<li class="shop_navigation_current"><span>Форма оплаты</span>→</li>
			<li><span>Данные доставки</span></li>
		</ul>
		
		<h1>Выбор формы оплаты</h1>
		
		<form method="post">
			<xsl:choose>
				<xsl:when test="count(shop_payment_system) = 0">
					<p><b>В данный момент нет доступных платежных систем!</b></p>
					<p>Оформление заказа невозможно, свяжитесь с администрацией Интернет-магазина.</p>
				</xsl:when>
				<xsl:otherwise>
					<table class="shop_cart">
						<tr class="total">
							<th>Форма оплаты</th>
							<th>Описание</th>
						</tr>
						<xsl:apply-templates select="shop_payment_system"/>
					</table>
					
					<input name="step" value="4" type="hidden" />
					<input value="Далее →" type="submit" class="button" />
				</xsl:otherwise>
			</xsl:choose>
		</form>
	</xsl:template>
	
	<xsl:template match="shop_payment_system">
		<tr>
			<td width="40%">
				<label>
				<input type="radio" name="shop_payment_system_id" value="{@id}">
					<xsl:if test="position() = 1">
						<xsl:attribute name="checked">checked</xsl:attribute>
					</xsl:if>
				</input>
				<xsl:text> </xsl:text><span class="caption"><xsl:value-of select="name"/></span>
				</label>
			</td>
			<td width="60%">
				<xsl:value-of disable-output-escaping="yes" select="description"/>
			</td>
		</tr>
	</xsl:template>
</xsl:stylesheet>