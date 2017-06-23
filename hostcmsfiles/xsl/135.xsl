<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>
	
	<!-- ПополнениеЛицевогоСчетаПлатежныеСистемы -->
	
	<xsl:template match="/list">
		
		<xsl:choose>
			<xsl:when test="count_system_of_pay=0">
				<p>
					<b>В данный момент нет доступных платежных систем!</b>
				</p>
				<p>Оформление заказа невозможно, свяжитесь с администрацией Интернет-магазина.</p>
			</xsl:when>
			<xsl:otherwise>
				<table cellspacing="0" cellpadding="0" border="0" class="shop_cart_table">
					<tr class="shop_cart_table_title">
						<td>Форма оплаты</td>
						<td>Описание</td>
					</tr>
					<xsl:apply-templates select="system_of_pay"/>
				</table>
				<div style="margin: 10px 0px; float: left" class="shop_button_block red_button_block">
					<input name="apply" value="Отправить заказ" type="submit"/>
				</div>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	
	<xsl:template match="system_of_pay">
		<tr>
			<td width="50%">
				
				<table border="0" cellpadding="0" cellspacing="0" width="100%">
					<tr>
						<td width="20" style="border-bottom: 0;">
							<input type="radio" class="input_radio" name="system_of_pay_id" value="{@id}" id="system_of_pay_{@id}" style="border: 0px">
								<xsl:if test="position() = 1">
									<xsl:attribute name="checked"></xsl:attribute>
								</xsl:if>
							</input>
						</td>
						<td style="border-bottom: 0;">
							<label for="system_of_pay_{@id}">
								<b>
									<xsl:value-of select="name"/>
								</b>
							</label>
						</td>
					</tr>
				</table>
			</td>
			<td width="60%">
				<!-- Описание платежной системы -->
				<xsl:value-of disable-output-escaping="yes" select="description"/>
			</td>
		</tr>
	</xsl:template>
</xsl:stylesheet>