<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>

	<!-- БанковскийСчет -->

	<xsl:decimal-format name="my" decimal-separator="," grouping-separator=" "/>

	<xsl:template match="/">
		<xsl:apply-templates select="shop"/>
	</xsl:template>

	<!-- Выводим данные -->
	<xsl:template match="shop">
		<html>
			<head>
				<style type="text/css">html, body, td
					{
						font-family: Arial, Verdana, Tahoma, sans-serif;
						font-size: 9pt;
						background-color: #FFFFFF;
						color: #000000;
					}

					.main_div
					{
						margin-left: 0.5em;
						margin-right: 0.5em;
						margin-top: 2em;
						margin-bottom: 1em;
					}

					.td_main
					{
						border-top: black 1px solid;
						border-left: black 1px solid;
					}

					.td_header
					{
						border-left: black 1px solid;
						border-top: black 1px solid;
						border-bottom: black 1px solid;
						text-align: center;
						font-weight: bold;
					}

					.td_main_2
					{
						border-left: black 1px solid;
						border-bottom: black 1px solid;
					}

					.tr_footer td
					{
						font-size: 11pt;
						font-weight: bold;
						white-space: nowrap;
					}

					table, td
					{
						empty-cells: show;
					}
				</style>
			</head>

			<body>
				<div class="main_div">
					<p><img src="http://{/shop/site/site_alias/name}/images/logo.png" border="0" /></p>

					<p>
						<b>Поставщик:</b><xsl:text> </xsl:text><xsl:value-of select="shop_company/name"/>
						<br />
						<b>ИНН/КПП:</b><xsl:text> </xsl:text><xsl:value-of select="shop_company/tin"/>/<xsl:value-of select="/shop/shop_company/kpp"/>
						<br />
						<b>ОГРН:</b><xsl:text> </xsl:text><xsl:value-of select="shop_company/psrn"/>
						<br />
						<b>Адрес:</b><xsl:text> </xsl:text><xsl:value-of select="shop_company/address"/>
						<br />
						<b>Телефон:</b><xsl:text> </xsl:text><xsl:value-of select="shop_company/phone"/>
						<br />
						<b>Факс:</b><xsl:text> </xsl:text><xsl:value-of select="shop_company/fax"/>
						<br />
						<b>E-mail:</b><xsl:text> </xsl:text><xsl:value-of select="shop_company/email"/>
						<br />
						<b>Сайт:</b><xsl:text> </xsl:text><xsl:value-of select="shop_company/site"/>
					</p>
					<p>
						<table cellpadding="1" cellspacing="0" width="100%">
							<tr>
								<td colspan="4" align="center">
									<span style="font-weight: bold; font-size: 11pt">Образец заполнения платежного поручения</span>
								</td>
							</tr>
							<tr>
								<td class="td_main">ИНН<xsl:text> </xsl:text><xsl:value-of select="shop_company/tin"/></td>
								<td class="td_main">КПП<xsl:text> </xsl:text><xsl:value-of select="shop_company/kpp"/></td>
								<td class="td_main" style="border-right: black 1px solid;" rowspan="2" colspan="2"></td>
							</tr>
							<tr>
								<td class="td_main" colspan="2" width="50%">
									<b>Получатель</b>
								</td>
							</tr>
							<tr>
								<td class="td_main" colspan="2">
									<xsl:value-of select="shop_company/name"/>
								</td>
								<td class="td_main" width="100">
									<b>Сч. №</b>
								</td>
								<td class="td_main" style="border-right: black 1px solid;">
									<xsl:value-of select="shop_company/current_account"/>
								</td>
							</tr>
							<tr>
								<td class="td_main" colspan="2">
									<b>Банк получателя</b>
								</td>
								<td class="td_main">
									<b>БИК</b>
								</td>
								<td class="td_main" style="border-right: black 1px solid;">
									<xsl:value-of select="shop_company/bic"/>
								</td>
							</tr>
							<tr>
								<td class="td_main" style="border-bottom: black 1px solid;" colspan="2">
									<xsl:value-of select="shop_company/bank_name"/><xsl:text> </xsl:text><xsl:value-of select="shop_company/bank_address"/>
								</td>
								<td class="td_main" style="border-bottom: black 1px solid;">
									<b>Кор/Сч. №</b>
								</td>
								<td class="td_main" style="border-bottom: black 1px solid; border-right: black 1px solid">
									<xsl:value-of select="/shop/shop_company/correspondent_account"/>
								</td>
							</tr>
						</table>
					</p>

					<p><b>Уважаемый Клиент!</b>
					<br />Просим оплатить этот счет в течение 5 дней с даты выставления.
					Вы можете сделать это в любом банковском учреждении РФ.
					Не забудьте указать номер счета в платежном документе.
					</p>

					<p align="center"><span style="font-weight: bold; font-size: 12pt">Счет № <xsl:value-of select="shop_order/invoice"/> от <xsl:value-of select="shop_order/date"/> г.</span></p>

					<table cellpadding="2" cellspacing="0" width="100%">
						<tr>
							<td align="right">Покупатель:</td>
							<td>
								<b>
									<xsl:choose>
										<!-- Указана компания-плательщик -->
										<xsl:when test="shop_order/company != ''">
											<xsl:value-of select="shop_order/company"/>
										</xsl:when>
										<!-- ФИО -->
										<xsl:otherwise>
											<xsl:value-of select="shop_order/surname"/><xsl:text> </xsl:text><xsl:value-of select="shop_order/name"/><xsl:text> </xsl:text><xsl:value-of select="shop_order/patronymic"/>
										</xsl:otherwise>
									</xsl:choose>
								</b>
							</td>
						</tr>
						<tr>
							<td align="right">Адрес:</td>
							<td>
								<!-- Адрес -->
								<xsl:if test="/shop/shop_order/postcode != ''">
									<xsl:value-of select="/shop/shop_order/postcode"/>,
								</xsl:if>
								<xsl:if test="/shop/shop_order/shop_country/name != ''">
									<xsl:value-of select="/shop/shop_order/shop_country/name"/>
								</xsl:if>
								<xsl:if test="/shop/shop_order/shop_country/shop_country_location/name != ''">, <xsl:value-of select="/shop/shop_order/shop_country/shop_country_location/name"/></xsl:if>
								<xsl:if test="/shop/shop_order/shop_country/shop_country_location/shop_country_location_city/name != ''">, г. <xsl:value-of select="/shop/shop_order/shop_country/shop_country_location/shop_country_location_city/name"/></xsl:if>
								<xsl:if test="/shop/shop_order/shop_country/shop_country_location/shop_country_location_city/shop_country_location_city_area/name != ''">, <xsl:value-of select="/shop/shop_order/shop_country/shop_country_location/shop_country_location_city/shop_country_location_city_area/name"/><xsl:text> </xsl:text>район</xsl:if>
								<xsl:if test="/shop/shop_order/address != ''">, <xsl:value-of select="/shop/shop_order/address"/></xsl:if>
							</td>
						</tr>
						<xsl:if test="shop_order/phone/node() or shop_order/fax/node()">
						<tr>
							<td align="right">Тел./факс:</td>
							<td>
								<xsl:value-of select="shop_order/phone"/>
								<xsl:if test="shop_order/fax/node() and shop_order/fax != shop_order/phone"><xsl:text> / </xsl:text><xsl:value-of select="shop_order/fax"/></xsl:if>
							</td>
						</tr>
						</xsl:if>
					</table>

					<br />

					<table cellpadding="3" cellspacing="0" width="100%">
					<tr>
						<td class="td_header">№</td>
						<td class="td_header">Наименование</td>
						<td class="td_header">Кол-во</td>
						<td class="td_header">Ед. изм.</td>
						<td class="td_header">Цена,<xsl:text> </xsl:text><xsl:value-of disable-output-escaping="yes" select="/shop/shop_order/shop_currency/name" /></td>
						<td class="td_header">Ставка НДС</td>
						<td class="td_header">НДС,<xsl:text> </xsl:text><xsl:value-of disable-output-escaping="yes" select="/shop/shop_order/shop_currency/name" /></td>
						<td class="td_header" style="border-right: black 1px solid; border-left: black 1px solid;">Сумма, <xsl:text> </xsl:text><xsl:value-of disable-output-escaping="yes" select="/shop/shop_order/shop_currency/name" /></td>
					</tr>
					<!-- Элементы заказа -->
					<xsl:apply-templates select="shop_order/shop_order_item"/>
					<!-- Всего НДС -->
					<tr class="tr_footer">
						<td align="right" colspan="6" style="border-bottom: black 1px solid;">
							<b>В том числе НДС:</b>
						</td>
						<td align="right" colspan="2" style="border-bottom: black 1px solid;">
							<xsl:choose>
							<xsl:when test="shop_order/total_tax != 0">
								<xsl:value-of select="format-number(shop_order/total_tax, '### ##0,00', 'my')"/><xsl:text> </xsl:text><xsl:value-of disable-output-escaping="yes" select="/shop/shop_order/shop_currency/name" />
							</xsl:when>
							<xsl:otherwise>
								<b>Без НДС</b>
							</xsl:otherwise>
							</xsl:choose>

						</td>
					</tr>
					<!-- Итого -->
					<tr class="tr_footer">
						<td align="right" colspan="6">
							<b>Всего к оплате:</b>
						</td>
						<td align="right" colspan="2">
							<b>
								<xsl:value-of select="format-number(shop_order/total_amount, '### ##0,00', 'my')"/><xsl:text> </xsl:text><xsl:value-of disable-output-escaping="yes" select="/shop/shop_order/shop_currency/name" /></b>
						</td>
					</tr>
					</table>

					<br/>
					<br/>
					<br/>

					<table cellpadding="2" cellspacing="0" width="100%">
						<tr>
							<td width="200" height="50">Руководитель предприятия</td>
							<td style="border-bottom: black 1px solid;"></td>
							<td width="200">
								<xsl:value-of select="shop_company/legal_name"/>
							</td>
						</tr>
						<tr>
							<td width="200" height="50">Главный бухгалтер</td>
							<td style="border-bottom: black 1px solid;"> </td>
							<td>
								<xsl:value-of select="shop_company/accountant_legal_name"/>
							</td>
						</tr>
						<tr>
							<td height="50" align="center" colspan="3">
								<b>М.П.</b>
							</td>
						</tr>
					</table>

					<br />

					<p>
					<ol>
					<li>Счет действителен в течение 5 банковских дней.</li>
					</ol>
					</p>
				</div>
			</body>
		</html>
	</xsl:template>

	<!-- Выводим элементы заказа -->
	<xsl:template match="shop_order/shop_order_item">
		<xsl:variable name="tax_sum_item" select="quantity * price div (100 + rate) * rate" />
		<tr>
			<td class="td_main_2" style="text-align: center">
				<xsl:value-of select="position()"/>
			</td>
			<td class="td_main_2">
				<xsl:value-of select="name"/>
			</td>
			<td class="td_main_2" style="text-align: center">
				<xsl:value-of select="quantity"/>
			</td>
			<td class="td_main_2" style="text-align: center">
				<xsl:value-of select="shop_item/shop_measure/name"/>
			</td>
			<td class="td_main_2" style="white-space: nowrap">
				<xsl:choose>
					<xsl:when test="$tax_sum_item != 0">
						<xsl:value-of select="format-number(price - $tax_sum_item div quantity, '### ##0,00', 'my')"/>
					</xsl:when>
					<xsl:otherwise>
						<xsl:value-of select="format-number(price, '### ##0,00', 'my')"/>
					</xsl:otherwise>
				</xsl:choose>
			</td>
			<td class="td_main_2" align="center">
				<xsl:choose>
					<xsl:when test="rate != 0">
						<xsl:value-of select="rate"/>%</xsl:when>
					<xsl:otherwise>—</xsl:otherwise>
				</xsl:choose>
			</td>
			<td class="td_main_2" align="center">
				<xsl:choose>
					<xsl:when test="$tax_sum_item != 0">
						<xsl:value-of select="format-number($tax_sum_item, '### ##0,00', 'my')"/>
					</xsl:when>
					<xsl:otherwise>—</xsl:otherwise>
				</xsl:choose>
			</td>
			<td class="td_main_2" style="white-space: nowrap; border-right: black 1px solid;">
				<xsl:if test="price * quantity != 0">
					<xsl:value-of select="format-number(price * quantity, '### ##0,00', 'my')"/>
				</xsl:if>
			</td>
		</tr>
	</xsl:template>
</xsl:stylesheet>