<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>
	
	<!-- КвитанцияПД4 -->
	
	<xsl:template match="/">
		<xsl:apply-templates select="shop"/>
	</xsl:template>
	
	
	<xsl:template match="shop">
		<html xmlns="http://www.w3.org/1999/xhtml">
			<head>
				<meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
				<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />
			</head>
			<body bgcolor="#ffffff" topmargin="0" leftmargin="0" marginwidth="0" marginheight="0">
				<style type="text/css">
					hr { height: 1px; margin: 0px; padding: 0px; color: #000000; background-color: #000000; line-height: 0; }
					* html hr {margin:-7px 0; display: block; padding: 0; border: 0; /* для IE6 */}
					*+html hr {margin:-7px 0; display: block; padding: 0; border: 0; /* для IE7 */}
					hr { border: 0\9 /* для IE8 */ }
					
					.main_div
					{
					margin-left: 0.5em;
					margin-right: 0.5em;
					margin-top: 2em;
					margin-bottom: 1em;
					}
				</style>
				<div class="main_div">
					<!-- Квитанция -->
					<xsl:call-template name="pd4">
						<xsl:with-param name="type">Извещение</xsl:with-param>
					</xsl:call-template>
					<!-- Извещение -->
					<xsl:call-template name="pd4">
						<xsl:with-param name="type">Квитанция</xsl:with-param>
					</xsl:call-template>
				</div>
				<br/>
			</body>
		</html>
	</xsl:template>
	
	<xsl:template name="pd4">
		<xsl:param name="type"/>
		
		<table cellspacing="0" cellpadding="5" border="1" width="680" bordercolor="#000000" rules="all">
			<tr valign="top">
				<td width="190" height="275">
					<table rules="none" border="0" cellspacing="0" cellpadding="0" width="100%">
						<tr>
							<td align="center">
							<font size="2"><b><xsl:value-of select="$type"/></b></font>
							</td>
						</tr>
						<tr>
					<td align="center" valign="bottom" height="240"><font size="2"><b>Кассир</b></font></td>
						</tr>
					</table>
				</td>
				<td>
			<div align="right"><font size="1"><i>Форма №ПД-4</i></font></div>
					<table  rules="none" border="0" cellspacing="0" cellpadding="0" width="100%">
						<tr>
							<td>
							<font size="2"><b><xsl:value-of select="/shop/company/name"/></b></font>
							</td>
						</tr>
						<tr>
							<td height="1"><hr noshade="noshade" size="1" color="#000000"/></td>
						</tr>
						<tr>
							<td align="center" valign="top">
								<font size="1">(наименование получателя платежа)</font>
							</td>
						</tr>
					</table>
					<table rules="none" border="0" cellspacing="0" cellpadding="0" width="100%">
						<tr>
							<!-- ИНН / КПП -->
							<td width="150">
								<font size="2"><xsl:value-of select="/shop/company/tin"/>/<xsl:value-of select="/shop/company/kpp"/></font>
							</td>
							<td rowspan="3" width="20"></td>
							<!-- Счет -->
							<td>
								<font size="2"><xsl:value-of select="/shop/company/current_account"/></font>
							</td>
						</tr>
						<tr>
							<td height="1"><hr noshade="noshade" size="1" color="#000000"/></td>
							<td height="1"><hr noshade="noshade" size="1" color="#000000"/></td>
						</tr>
						<tr>
							<td align="center" valign="top">
								<font size="1">(ИНН/КПП получателя платежа)</font>
							</td>
							<td align="center" valign="top">
								<font size="1">(номер счета получателя платежа)</font>
							</td>
						</tr>
					</table>
					<table rules="none" border="0" cellspacing="0" cellpadding="0" width="100%">
						<tr>
						<td width="5"><font size="2">в</font></td>
							<td width="5"></td>
							<!-- Название/город банка -->
							<td width="320">
							<font size="2"><xsl:value-of select="/shop/company/bank_name"/><xsl:text> </xsl:text><xsl:value-of select="/shop/company/bank_address"/></font>
							</td>
							<td width="10" rowspan="3"></td>
						<td width="20"><font size="2">БИК</font></td>
							<td width="5" rowspan="3"></td>
							<!-- БИК -->
							<td>
								<font size="2"><xsl:value-of select="/shop/company/bic"/></font>
							</td>
						</tr>
						<tr>
							<td></td>
							<td></td>
							<td height="1"><hr noshade="noshade" size="1" color="#000000"/></td>
							<td></td>
							<td height="1"><hr noshade="noshade" size="1" color="#000000"/></td>
						</tr>
						<tr>
							<td align="center" valign="top" colspan="3">
								<font size="1">(наименование банка получателя платежа)</font>
							</td>
							<td align="center" valign="top"></td>
							<td align="center" valign="top"></td>
						</tr>
					</table>
					<table rules="none" border="0" cellspacing="0" cellpadding="0" width="100%">
						<tr>
							<td width="280">
								<font size="2">Номер кор./сч. банка получателя платежа</font>
							</td>
							<td width="10"></td>
							<td>
								<!-- Корр. счет -->
								<font size="2"><xsl:value-of select="/shop/company/correspondent_account"/></font>
							</td>
						</tr>
						<tr>
							<td></td>
							<td></td>
							<td height="1"><hr noshade="noshade" size="1" color="#000000"/></td>
						</tr>
					</table>
					<table rules="none" border="0" cellspacing="0" cellpadding="0" width="100%">
						<tr>
							<td width="225">
								<!-- Назначение платежа -->
								<font size="2">Оплата по счету № <xsl:value-of select="/shop/shop_order/invoice"/></font>
							</td>
							<td width="10"></td>
							<td></td>
						</tr>
						<tr>
							<td height="1"><hr noshade="noshade" size="1" color="#000000"/></td>
							<td></td>
							<td height="1"><hr noshade="noshade" size="1" color="#000000"/></td>
						</tr>
						<tr>
							<td align="center" valign="top">
								<font size="1">(наименование платежа)</font>
							</td>
							<td></td>
							<td align="center" valign="top">
								<font size="1">(номер лицевого счета (код) плательщика)</font>
							</td>
						</tr>
					</table>
					<table rules="none" border="0" cellspacing="0" cellpadding="0" width="100%">
						<tr>
							<td width="120">
							<font size="2">Ф.И.О.<xsl:text> </xsl:text>плательщика</font>
							</td>
							<td rowspan="4" width="10"></td>
							<td>
								<!-- ФИО -->
						<font size="1"><xsl:value-of select="/shop/shop_order/surname"/><xsl:text> </xsl:text><xsl:value-of select="/shop/shop_order/name"/><xsl:text> </xsl:text><xsl:value-of select="/shop/shop_order/patronymic"/></font>
							</td>
						</tr>
						<tr>
							<td></td>
							<td height="1"><hr noshade="noshade" size="1" color="#000000"/></td>
						</tr>
						<tr>
							<td align="left">
								<font size="2">Адрес плательщика</font>
							</td>
							<td>
								<!-- Address -->
								<font size="1">
									<xsl:if test="/shop/shop_order/postcode != ''"><xsl:value-of select="/shop/shop_order/postcode"/>,	</xsl:if>
									
									<xsl:if test="/shop/shop_order/shop_country/name != ''"><xsl:value-of select="/shop/shop_order/shop_country/name"/></xsl:if>
									
									<xsl:if test="/shop/shop_order/shop_country/shop_country_location/name != ''">, <xsl:value-of select="/shop/shop_order/shop_country/shop_country_location/name"/></xsl:if>
									<xsl:if test="/shop/shop_order/shop_country/shop_country_location/shop_country_location_city/name != ''">, <xsl:value-of select="/shop/shop_order/shop_country/shop_country_location/shop_country_location_city/name"/></xsl:if>
									<xsl:if test="/shop/shop_order/shop_country/shop_country_location/shop_country_location_city/shop_country_location_city_area/name != ''">, <xsl:value-of select="/shop/shop_order/shop_country/shop_country_location/shop_country_location_city/shop_country_location_city_area/name"/>&#xA0;район</xsl:if>
									<xsl:if test="/shop/shop_order/address != ''">, <xsl:value-of select="/shop/shop_order/address"/></xsl:if>
								</font>
							</td>
						</tr>
						<tr>
							<td></td>
							<td height="1"><hr noshade="noshade" size="1" color="#000000"/></td>
						</tr>
					</table>
					
					<table rules="none" border="0" cellspacing="0" cellpadding="0" width="100%">
						<tr>
							<td width="70" align="left">
						<font size="1"><bobr><xsl:text>Сумма платежа</xsl:text></bobr></font>
							</td>
							
							<td width="45">
								<!-- Сумма в рублях -->
								<font size="2"><xsl:value-of select="floor(/shop/shop_order/total_amount)"/></font>
							</td>
					<td width="20"><font size="1"><xsl:text> руб. </xsl:text></font></td>
							
							<td width="20" align="center">
								<!-- Сумма в копейках -->
								<font size="2"><xsl:value-of select="floor((/shop/shop_order/total_amount - floor(/shop/shop_order/total_amount)) * 100)"/></font>
							</td>
					<td width="20"><font size="1"><xsl:text> коп.</xsl:text></font></td>
						<td><xsl:text> </xsl:text></td>
							<td width="105">
						<font size="1"><bobr><xsl:text>Сумма платы за услуги </xsl:text></bobr></font>
							</td>
						<td width="45"><xsl:text> </xsl:text></td>
					<td width="20"><font size="1"><xsl:text> руб. </xsl:text></font></td>
							
						<td width="20" align="center" class="bottom_border"><xsl:text> </xsl:text></td>
						<td width="20"><font size="1">&#xA0;коп.</font></td>
						</tr>
						<tr>
							<td></td>
							<td height="1"><hr noshade="noshade" size="1" color="#000000"/></td>
							<td></td>
							<td height="1"><hr noshade="noshade" size="1" color="#000000"/></td>
							<td></td>
							<td></td>
							<td></td>
							<td height="1"><hr noshade="noshade" size="1" color="#000000"/></td>
							<td></td>
							<td height="1"><hr noshade="noshade" size="1" color="#000000"/></td>
							<td></td>
						</tr>
					</table>
					<table rules="none" border="0" cellspacing="0" cellpadding="0" width="100%">
						<tr>
							<td width="30" align="left">
								<font size="1">Итого</font>
							</td>
						<td width="50"><xsl:text> </xsl:text></td>
						<td width="20"><font size="1">&#xA0;руб.&#xA0;</font></td>
						<td width="20" align="center" class="bottom_border"><xsl:text> </xsl:text></td>
						<td><font size="1">&#xA0;коп.</font></td>
							<td align="right">
								<table rules="none" border="0" cellspacing="0" cellpadding="0">
									<tr>
									<td width="2"><font size="1">&#171;</font></td>
										<td width="30"></td>
									<td width="2"><font size="1">&#187;</font></td>
										<td width="90"></td>
									<td width="10"><font size="1">20</font></td>
										<td width="15"></td>
									<td width="2"><font size="1">г.</font></td>
									</tr>
									<tr>
										<td></td>
										<td height="1"><hr noshade="noshade" size="1" color="#000000"/></td>
										<td></td>
										<td height="1"><hr noshade="noshade" size="1" color="#000000"/></td>
										<td></td>
										<td height="1"><hr noshade="noshade" size="1" color="#000000"/></td>
										<td></td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
							<td></td>
							<td height="1"><hr noshade="noshade" size="1" color="#000000"/></td>
							<td></td>
							<td height="1"><hr noshade="noshade" size="1" color="#000000"/></td>
							<td></td>
							<td></td>
						</tr>
					</table>
					
					<font size="1">С условиями приема указанной в платежном документе
						суммы, в т.ч. с суммой взимаемой платы за услуги банка,
					ознакомлен и согласен.</font>
					<table rules="none" border="0" cellspacing="0" cellpadding="0" width="100%">
						<tr>
							<td align="right">
							<font size="1"><b>Подпись плательщика</b></font>
							</td>
							<td width="150"></td>
						</tr>
						<tr>
							<td></td>
							<td width="150"><hr noshade="noshade" size="1" color="#000000"/></td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
		
	</xsl:template>
	
</xsl:stylesheet>