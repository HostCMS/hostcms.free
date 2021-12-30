<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "lang://77">
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml" />

	<xsl:decimal-format name="my" decimal-separator="," grouping-separator=" " />

	<!-- ПисьмоПользователю -->

	<xsl:template match="/shop">
		<xsl:variable name="schema">
			<xsl:choose>
				<xsl:when test="/shop/site/https = 1">https</xsl:when>
				<xsl:otherwise>http</xsl:otherwise>
			</xsl:choose>
		</xsl:variable>

		<html>
			<head>
				<style>
					html { -webkit-text-size-adjust: none; -ms-text-size-adjust: none;}
					body { 	padding: 0; margin: 0; font-family: Arial, Helvetica, sans-serif; font-size: 16px; }
					.es-wrapper { background-color: #f5f5f5; }
					.es-container { width:100%; height:100%; }
					.es-preheader { display:none !important; visibility:hidden; opacity:0; color:transparent; height:0; width:0; max-height:0; overflow:hidden; }
					.table744 { width: 744px; }

					@media only screen and (min-device-width: 744px) {
						.table744 {
							width: 744px !important;
						}
					}
					@media only screen and (max-device-width: 744px), only screen and (max-width: 744px) {
						.table744 {
							width: 100% !important;
						}
					}
					@media only screen and (max-width: 744px) {
						.table744 {
							width: 100% !important;
						}
					}
				</style>
			</head>
			<body>
				<span class="es-preheader" style="display:block !important;font-size:0px;color:#ffffff;">Данное письмо содержит подробную информацию о вашем заказе</span>
				<div class="es-wrapper">
					<table class="es-container" style="background-position: center top" width="100%" cellspacing="0" cellpadding="0">
						<tr>
							<td align="center" bgcolor="#F6F6F6">
								<!--[if (gte mso 9)|(IE)]>
								<table width="744" border="0" cellspacing="0" cellpadding="0">
									<tr>
										<td>
								<![endif]-->
								<div style="max-width:744px;overflow:hidden;">
									<table border="0" cellspacing="0" cellpadding="0" class="table744" width="100%" style="max-width: 744px;min-width:300px;">
										<tr>
											<td align="center">
												<!-- padding -->
												<div style="height: 25px; line-height:50px; font-size:48px;"></div>

												<!-- Header beginning -->
												<!-- <table width="94%" border="0" cellspacing="0" cellpadding="0" style="min-width: 450px;margin:0;padding:0;border:0;border-collapse:collapse">
													<tr>
														<td align="center" valign="top" bgcolor="#ff1962">
															<table style="width: 100%; background-color: #ff1962;" border="0" cellspacing="0" cellpadding="0">
																<tr>
																	<td align="center" style="width:100%;height: 50px">
																		<span style="color:#ffffff;">Магазин "<xsl:value-of select="/shop/name"/>"</span>
																	</td>
																</tr>
															</table>
														</td>
													</tr>
												</table>-->
												<!-- Header ending -->
												<!-- Body beginning -->
												<table width="94%" border="0" cellspacing="0" cellpadding="0">
													<tr>
														<td align="center" valign="top" bgcolor="#ffffff" style="-webkit-box-shadow: 0px 2px 10px 4px rgba(218,218,218,0.56);
															-moz-box-shadow: 0px 2px 10px 4px rgba(218,218,218,0.56);
															box-shadow: 0px 2px 10px 4px rgba(218,218,218,0.56);">
															<!-- padding -->
															<div style="height: 50px; line-height:50px; font-size:48px;"></div>

															<table width="90%" cellspacing="0" cellpadding="0" border="0">
																<tbody>
																	<tr>
																		<td width="61" valign="top" align="left">
																			<img src="data:image/png;base64,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" alt="" style="display: block;" width="61" height="61" border="0"/>
																		</td>
																		<td width="25" valign="middle" align="left"></td>
																		<td valign="top" align="left">
																			<!-- padding -->
																			<div style="height: 5px; line-height:5px; font-size:3px;"></div>
																			<div>
																				<span style="font-family: Arial, Helvetica, sans-serif; font-size: 16px;line-height:22px; color:#808285;">
																					<xsl:variable name="name">
																						<xsl:choose>
																							<xsl:when test="shop_order/name != '' or shop_order/surname !=''">
																								<xsl:value-of select="shop_order/name"/><xsl:text> </xsl:text><xsl:value-of select="shop_order/surname"/>
																							</xsl:when>
																							<xsl:when test="shop_order/company != ''">
																								<xsl:value-of select="shop_order/company"/>
																							</xsl:when>
																							<xsl:otherwise>Покупатель</xsl:otherwise>
																						</xsl:choose>
																					</xsl:variable>

																				<xsl:value-of select="$name"/>,</span>
																			</div>
																			<div>
																				<span style="font-family: Arial, Helvetica, sans-serif; font-size: 20px;line-height:24px; color:#606163;"><b>Вами был оформлен заказ № <xsl:value-of select="shop_order/invoice" /></b></span>
																			</div>

																			<!-- padding -->
																			<div style="height: 36px; line-height:36px; font-size:34px;"></div>
																		</td>
																	</tr>
																</tbody>
															</table>

															<table width="90%" style="line-height:0;border-collapse:collapse" cellspacing="0" cellpadding="0" border="0">
																<tbody>
																<tr>
																	<td>
																		<table style="border-collapse: collapse;" width="100%" cellspacing="0" cellpadding="0" border="0">
																			<tbody>
																				<xsl:apply-templates select="shop_order" />
																			</tbody>
																		</table>
																	</td>
																</tr>
																<tr>
																	<td>
																		<table style="min-width: 450px;" width="100%" cellspacing="0" cellpadding="0" border="0">
																			<tbody><tr>
																				<td valign="top">
																					<!-- padding -->
																					<div style="height: 14px; line-height:14px; font-size:12px;"></div>

																					<span style="font-family: Arial, Helvetica, sans-serif; font-size: 20px;line-height:24px; color:#606163;"><b>Состав заказа</b></span>
																					<!-- padding -->
																					<div style="height: 14px; line-height:14px; font-size:12px;"></div>

																					<xsl:choose>
																						<xsl:when test="count(shop_order/shop_order_item)">
																							<table width="100%" cellspacing="0" cellpadding="0" border="0">
																								<tbody><tr>
																									<td valign="top" bgcolor="#f7f7f7" align="center">
																										<!-- padding -->
																										<div style="height: 15px; line-height:15px; font-size:13px;"></div>

																										<table style="border-collapse:collapse;" width="94%" cellspacing="0" cellpadding="0" border="0">
																											<tbody><tr>
																												<td width="60" valign="middle" align="center"></td>
																												<td valign="middle" align="center">
																													<div>
																														<span style="font-family: Arial, Helvetica, sans-serif; font-size: 14px;line-height:16px; color:#333;">
																															Наименование
																														</span>
																													</div>
																												</td>
																												<td width="68" valign="middle" align="center">
																													<div>
																														<span style="font-family: Arial, Helvetica, sans-serif; font-size: 12px;line-height:14px; color:#333;">
																															Цена, <xsl:text> </xsl:text><xsl:value-of select="/shop/shop_currency/sign" disable-output-escaping="yes" />
																														</span>
																													</div>
																												</td>
																												<td width="68" valign="middle" align="center">
																													<div>
																														<span style="font-family: Arial, Helvetica, sans-serif; font-size: 12px;line-height:14px; color:#333;">
																															Кол-во
																														</span>
																													</div>
																												</td>
																												<td width="68" valign="middle" align="center">
																													<div>
																														<span style="font-family: Arial, Helvetica, sans-serif; font-size: 12px;line-height:14px; color:#333;">
																															Сумма, <xsl:text> </xsl:text><xsl:value-of select="/shop/shop_currency/sign" disable-output-escaping="yes" />
																														</span>
																													</div>
																												</td>
																											</tr>
																										</tbody></table>
																										<!-- padding -->
																										<div style="height: 15px; line-height:15px; font-size:13px;"></div>
																									</td>
																								</tr>
																							</tbody></table>

																							<table width="100%" cellspacing="0" cellpadding="0" border="0">
																								<tbody>
																									<xsl:apply-templates select="shop_order/shop_order_item" />
																								</tbody>
																							</table>
																						</xsl:when>
																						<xsl:otherwise>
																							<p><b>Заказанных товаров нет</b></p>
																						</xsl:otherwise>
																					</xsl:choose>

																					<table width="100%" cellspacing="0" cellpadding="0" border="0">
																						<tbody><tr>
																							<td valign="top" bgcolor="#f7f7f7" align="center">
																								<!-- padding -->
																								<div style="height: 10px; line-height:10px; font-size:8px;"></div>
																								<table width="94%" cellspacing="0" cellpadding="0" border="0">
																									<tbody><tr>
																										<td valign="middle" align="left">
																											<!-- padding -->
																											<!-- <div style="height: 10px; line-height:10px; font-size:8px;"></div> -->
																											<div>
																												<span style="font-family: Arial, Helvetica, sans-serif; font-size: 14px;line-height:16px; color:#333;"><b>Итого к оплате:</b></span>
																											</div>
																										</td>
																										<td style="min-width: 100px;" align="right">
																											<div>
																												<span style="font-family: Arial, Helvetica, sans-serif; font-size: 20px;line-height:24px; color:#333;"><b><xsl:value-of select="format-number(shop_order/total_amount, '### ##0,00', 'my')" /><xsl:text> </xsl:text><xsl:value-of select="/shop/shop_currency/sign" disable-output-escaping="yes" /></b></span>
																											</div>
																										</td>
																									</tr>
																								</tbody></table>
																								<!-- padding -->
																								<div style="height: 10px; line-height:5px; font-size:3px;"></div>
																							</td>
																						</tr>
																					</tbody></table>
																					<!-- padding -->
																					<div style="height: 18px; line-height:18px; font-size:16px;"></div>
																				</td>
																			</tr>
																		</tbody></table>
																	</td>
																</tr>

																<xsl:if test="count(shop_order/shop_order_item/shop_order_item_digital)">
																	<tr>
																		<td>
																			<table style="min-width: 450px;" width="100%" cellspacing="0" cellpadding="0" border="0">
																				<tbody><tr>
																					<td valign="top">
																						<!-- padding -->
																						<div style="height: 14px; line-height:14px; font-size:12px;"></div>

																						<span style="font-family: Arial, Helvetica, sans-serif; font-size: 20px;line-height:24px; color:#606163;"><b>Электронные товары</b></span>
																						<!-- padding -->
																						<div style="height: 14px; line-height:14px; font-size:12px;"></div>
																					</td>
																				</tr>
																			</tbody></table>
																		</td>
																	</tr>
																	<tr>
																		<td>
																			<table style="border-collapse: collapse;" width="100%" cellspacing="0" cellpadding="0" border="0">
																				<tbody>
																					<xsl:apply-templates select="shop_order/shop_order_item/shop_order_item_digital" />
																				</tbody>
																			</table>
																		</td>
																	</tr>
																</xsl:if>
															</tbody></table>

															<!-- padding -->
															<div style="height: 30px; line-height:30px; font-size:28px;"></div>

															<!-- Footer beginning -->
															<table width="100%" border="0" cellspacing="0" cellpadding="0" style="min-width: 450px;">
																<tr>
																	<td align="center" valign="center" bgcolor="#ff1962" style="height: 50px">
																		<span style="color:#ffffff;">Благодарим за ваш заказ!</span>
																	</td>
																</tr>
															</table>
															<!-- Footer ending -->
														</td>
													</tr>
												</table>
												<!-- Body ending -->

												<!-- padding -->
												<div style="height: 25px; line-height:25px; font-size:23px;"></div>
												<table width="76%" border="0" cellspacing="0" cellpadding="0">
													<tr>
														<td align="center" valign="top">
															<div>
																<span style="font-family: Tahoma, Arial, Helvetica, sans-serif; font-size: 11px;line-height:15px; color:#666;">
																	<xsl:value-of select="/shop/name"/>
																</span>
															</div>
														</td>
													</tr>
													<tr>
														<td align="center" valign="top">
															<div>
																<span style="font-family: Tahoma, Arial, Helvetica, sans-serif; font-size: 11px;line-height:15px; color:#666;">
																	<a href="{$schema}://{/shop/site/site_alias/name}"><xsl:value-of select="/shop/site/site_alias/name"/></a>
																</span>
															</div>
														</td>
													</tr>
													<tr>
														<td align="center" valign="top">
															<div>
																<span style="font-family: Tahoma, Arial, Helvetica, sans-serif; font-size: 11px;line-height:15px; color:#aaa;">
																	Работает на HostCMS
																</span>
															</div>
															<!-- padding -->
															<div style="height: 25px; line-height:25px; font-size:23px;"></div>
														</td>
													</tr>
												</table>
											</td>
										</tr>
									</table>
								</div>
							</td>
						</tr>
					</table>
				</div>
			</body>
		</html>
	</xsl:template>

	<!-- Order Template -->
	<xsl:template match="shop_order">
		<xsl:if test="company != ''">
			<tr valign="top">
				<td style=" color: #808285; line-height: 26px;  font-size: 13px;">
					<!--[if mso]><span style="font-family: sans-serif, Arial; font-size: 13px;"><![endif]-->
						Компания:
					<!--[if mso]></span><![endif]-->
				</td>
				<td style=" color: #333333; line-height: 26px;  font-size: 13px;">
					<!--[if mso]><span style="font-family: sans-serif, Arial; font-size: 13px;"><![endif]-->
						<xsl:value-of select="company" />
					<!--[if mso]></span><![endif]-->
				</td>
			</tr>
		</xsl:if>

		<xsl:if test="surname != '' or name !=''">
			<tr valign="top">
				<td style=" color: #808285; line-height: 26px;  font-size: 13px;">
					<!--[if mso]><span style="font-family: sans-serif, Arial; font-size: 13px;"><![endif]-->
						ФИО:
					<!--[if mso]></span><![endif]-->
				</td>
				<td style=" color: #333333; line-height: 26px;  font-size: 13px;">
					<!--[if mso]><span style="font-family: sans-serif, Arial; font-size: 13px;"><![endif]-->
						<xsl:value-of select="surname"/><xsl:text> </xsl:text><xsl:value-of select="name"/><xsl:text> </xsl:text><xsl:value-of select="patronymic"/>
					<!--[if mso]></span><![endif]-->
				</td>
			</tr>
		</xsl:if>

		<tr valign="top">
			<td style=" color: #808285; line-height: 26px;  font-size: 13px;">
				<!--[if mso]><span style="font-family: sans-serif, Arial; font-size: 13px;"><![endif]-->
					E-mail:
				<!--[if mso]></span><![endif]-->
			</td>
			<td style=" color: #333333; line-height: 26px;  font-size: 13px;">
				<!--[if mso]><span style="font-family: sans-serif, Arial; font-size: 13px;"><![endif]-->
					<xsl:value-of select="email" />
				<!--[if mso]></span><![endif]-->
			</td>
		</tr>

		<xsl:if test="phone != ''">
			<tr valign="top">
				<td style=" color: #808285; line-height: 26px;  font-size: 13px;">
					<!--[if mso]><span style="font-family: sans-serif, Arial; font-size: 13px;"><![endif]-->
						Телефон:
					<!--[if mso]></span><![endif]-->
				</td>
				<td style=" color: #333333; line-height: 26px;  font-size: 13px;">
					<!--[if mso]><span style="font-family: sans-serif, Arial; font-size: 13px;"><![endif]-->
						<xsl:value-of select="phone" />
					<!--[if mso]></span><![endif]-->
				</td>
			</tr>
		</xsl:if>

		<xsl:if test="shop_order_status/node()">
			<tr valign="top">
				<td style=" color: #808285; line-height: 26px;  font-size: 13px;">
					<!--[if mso]><span style="font-family: sans-serif, Arial; font-size: 13px;"><![endif]-->
						Статус:
					<!--[if mso]></span><![endif]-->
				</td>
				<td style=" color: #333333; line-height: 26px;  font-size: 13px;">
					<!--[if mso]><span style="font-family: sans-serif, Arial; font-size: 13px;"><![endif]-->
						<span style="color: {shop_order_status/color}; font-weight: bold;"><xsl:value-of select="shop_order_status/name"/></span><xsl:text> </xsl:text><small><xsl:value-of select="status_datetime"/></small>
					<!--[if mso]></span><![endif]-->
				</td>
			</tr>
		</xsl:if>

		<tr valign="top">
			<td style=" color: #808285; line-height: 26px;  font-size: 13px;">
				<!--[if mso]><span style="font-family: sans-serif, Arial; font-size: 13px;"><![endif]-->
					Адрес:
				<!--[if mso]></span><![endif]-->
			</td>
			<td style=" color: #333333; line-height: 26px;  font-size: 13px;">
				<!--[if mso]><span style="font-family: sans-serif, Arial; font-size: 13px;"><![endif]-->
				<xsl:if test="postcode != ''">
					<xsl:value-of select="postcode" /><xsl:text>, </xsl:text>
				</xsl:if>
				<xsl:if test="shop_country/name != ''">
					<xsl:value-of select="shop_country/name" /><xsl:text>, </xsl:text>
				</xsl:if>
				<xsl:if test="shop_country/shop_country_location/name != ''">
					<xsl:value-of select="shop_country/shop_country_location/name" /><xsl:text>, </xsl:text>
				</xsl:if>
				<xsl:if test="shop_country/shop_country_location/shop_country_location_city/name != ''">
					<xsl:value-of select="shop_country/shop_country_location/shop_country_location_city/name" /><xsl:text>, </xsl:text>
				</xsl:if>
				<xsl:if test="shop_country/shop_country_location/shop_country_location_city/shop_country_location_city_area/name != ''">
					<xsl:value-of select="shop_country/shop_country_location/shop_country_location_city/shop_country_location_city_area/name" /><xsl:text>, </xsl:text>
				</xsl:if>
				<xsl:if test="address != ''">
					<xsl:value-of select="address" />
					<xsl:text>, </xsl:text>
				</xsl:if>
				<xsl:if test="house != ''">
					<xsl:value-of select="house" />
					<xsl:text>, </xsl:text>
				</xsl:if>
				<xsl:if test="flat != ''">
					<xsl:value-of select="flat" />
				</xsl:if>
				<!--[if mso]></span><![endif]-->
			</td>
		</tr>

		<xsl:if test="shop_delivery/name != ''">
			<tr valign="top">
				<td style=" color: #808285; line-height: 26px;  font-size: 13px;">
					<!--[if mso]><span style="font-family: sans-serif, Arial; font-size: 13px;"><![endif]-->
						Тип доставки:
					<!--[if mso]></span><![endif]-->
				</td>
				<td style=" color: #333333; line-height: 26px;  font-size: 13px;">
					<!--[if mso]><span style="font-family: sans-serif, Arial; font-size: 13px;"><![endif]-->
						<xsl:value-of select="shop_delivery/name" />
					<!--[if mso]></span><![endif]-->
				</td>
			</tr>
		</xsl:if>

		<xsl:if test="shop_payment_system/name != ''">
			<tr valign="top">
				<td style=" color: #808285; line-height: 26px;  font-size: 13px;">
					<!--[if mso]><span style="font-family: sans-serif, Arial; font-size: 13px;"><![endif]-->
						Способ оплаты:
					<!--[if mso]></span><![endif]-->
				</td>
				<td style=" color: #333333; line-height: 26px;  font-size: 13px;">
					<!--[if mso]><span style="font-family: sans-serif, Arial; font-size: 13px;"><![endif]-->
						<xsl:value-of select="shop_payment_system/name" />
					<!--[if mso]></span><![endif]-->
				</td>
			</tr>
		</xsl:if>

		<tr valign="top">
			<td style=" color: #808285; line-height: 26px;  font-size: 13px;">
				<!--[if mso]><span style="font-family: sans-serif, Arial; font-size: 13px;"><![endif]-->
					Статус оплаты:
				<!--[if mso]></span><![endif]-->
			</td>
			<td style=" color: #333333; line-height: 26px;  font-size: 13px;">
				<!--[if mso]><span style="font-family: sans-serif, Arial; font-size: 13px;"><![endif]-->
					<b>
						<xsl:choose>
							<xsl:when test="paid = '1'"><span style="color: #16c60c">✓ оплачено</span></xsl:when>
							<xsl:otherwise><span style="color: #e81224">не оплачено</span></xsl:otherwise>
						</xsl:choose>
					</b>
				<!--[if mso]></span><![endif]-->
			</td>
		</tr>

		<xsl:if test="description != ''">
			<tr valign="top">
				<td style=" color: #808285; line-height: 26px;  font-size: 13px;">
					<!--[if mso]><span style="font-family: sans-serif, Arial; font-size: 13px;"><![endif]-->
						Описание заказа:
					<!--[if mso]></span><![endif]-->
				</td>
				<td style=" color: #333333; line-height: 26px;  font-size: 13px;">
					<!--[if mso]><span style="font-family: sans-serif, Arial; font-size: 13px;"><![endif]-->
						<xsl:value-of select="description" disable-output-escaping="yes" />
					<!--[if mso]></span><![endif]-->
				</td>
			</tr>
		</xsl:if>
	</xsl:template>

	<!-- Ordered Item Template -->
	<xsl:template match="shop_order/shop_order_item">
		<xsl:variable name="schema">
			<xsl:choose>
				<xsl:when test="/shop/site/https = 1">https</xsl:when>
				<xsl:otherwise>http</xsl:otherwise>
			</xsl:choose>
		</xsl:variable>

		<tr>
			<xsl:if test="position() mod 2 = 0">
				<xsl:attribute name="bgcolor">#f7feff</xsl:attribute>
			</xsl:if>

			<td valign="middle" align="left">
				<!-- padding -->
				<div style="height: 10px; line-height:10px; font-size:8px;"></div>
				<table width="100%" cellspacing="0" cellpadding="0" border="0">
					<tbody><tr>
						<td width="100" valign="middle" align="center">
							<!-- Item -->
							<div style="display: inline-block;vertical-align:top; width:100px;">
								<table style="border-collapse:collapse;" width="100" cellspacing="0" cellpadding="0" border="0">
									<tbody><tr>
										<td valign="middle" align="center">
											<table width="100%" cellspacing="0" cellpadding="0" border="0">
												<tbody><tr>
													<td valign="top" align="center">
														<xsl:if test="shop_item/image_large != ''">
															<a href="{$schema}://{/shop/site/site_alias/name}{shop_item/url}" target="_blank" style="color: #FFF; font-family: Arial, Helvetica, sans-serif; font-size: 16px;">
																<img width="64" height="64" src="{$schema}://{/shop/site/site_alias/name}{shop_item/dir}{shop_item/image_large}" alt="" style="display: block; max-height: 64px; max-width: 64px;" border="0"/>
															</a>
														</xsl:if>
													</td>
												</tr>
											</tbody></table>
										</td>
									</tr>
								</tbody></table>
							</div>
						</td>
						<td style="line-height: 0px; font-size: 0px;" valign="top" align="center">
							<!--[if (gte mso 9)|(IE)]>
							</td>
							<td valign="top">
							<![endif]-->
							<!-- Item -->
							<div style="display: inline-block;vertical-align:top; width:280px;">
								<table style="border-collapse:collapse;" width="280" cellspacing="0" cellpadding="0" border="0">
									<tbody><tr>
										<td valign="middle" align="left">
											<table width="100%" cellspacing="0" cellpadding="0" border="0">
												<tbody>
												<tr>
													<td valign="middle" height="40" align="left">
														<a href="{$schema}://{/shop/site/site_alias/name}{shop_item/url}" target="_blank" style="text-decoration: none; color: #5C2D91; font-family: Arial, Helvetica, sans-serif; font-size: 14px;line-height: 16px;"><xsl:value-of select="name" /></a>
													</td>
												</tr>
												<xsl:if test="marking != ''">
													<tr>
														<td valign="middle" height="15" align="left">
															<span style="color: #ccc; font-size: 12px;">Артикул: <xsl:value-of select="marking" /></span>
														</td>
													</tr>
												</xsl:if>
											</tbody></table>
										</td>
									</tr>
								</tbody></table>
							</div>
							<!-- Item END-->
						</td>
					</tr>
				</tbody></table>
				<!-- padding -->
				<div style="height: 10px; line-height:10px; font-size:8px;"></div>
			</td>
			<td width="210" valign="middle" align="left">
				<table width="100%" cellspacing="0" cellpadding="0" border="0">
					<tbody><tr>
						<td width="70" valign="middle" align="left">
							<div>
								<span style="font-family: Arial, Helvetica, sans-serif; font-size: 12px;line-height:14px; color:#58595B;"><xsl:value-of select="format-number(price, '### ##0,00', 'my')" /></span>
							</div>
						</td>
						<td width="40" valign="middle" align="left">
							<div>
								<span style="font-family: Arial, Helvetica, sans-serif; font-size: 12px;line-height:14px; color:#58595B;"><xsl:value-of select="quantity" /><xsl:text> </xsl:text><xsl:value-of select="shop_item/shop_measure/name" /></span>
							</div>
						</td>
						<td width="80" valign="middle" align="center">
							<div>
								<span style="font-family: Arial, Helvetica, sans-serif; font-size: 12px;line-height:16px; color:#333;"><b><xsl:value-of select="format-number(quantity * price, '### ##0,00', 'my')" /></b></span>
							</div>

						</td>
					</tr>
				</tbody></table>
			</td>
		</tr>
	</xsl:template>

	<!-- Данные об электронных товарах -->
	<xsl:template match="shop_order_item_digital">
		<xsl:variable name="schema">
			<xsl:choose>
				<xsl:when test="/shop/site/https = 1">https</xsl:when>
				<xsl:otherwise>http</xsl:otherwise>
			</xsl:choose>
		</xsl:variable>

		<xsl:if test="shop_item_digital/value != ''">
			<tr valign="top">
				<td style=" color: #808285; line-height: 26px;  font-size: 13px;">
					<!--[if mso]><span style="font-family: sans-serif, Arial; font-size: 13px;"><![endif]-->
						Текст электронного товара:
					<!--[if mso]></span><![endif]-->
				</td>
				<td style=" color: #333333; line-height: 26px;  font-size: 13px;">
					<!--[if mso]><span style="font-family: sans-serif, Arial; font-size: 13px;"><![endif]-->
						<xsl:value-of select="shop_item_digital/value" />
					<!--[if mso]></span><![endif]-->
				</td>
			</tr>
		</xsl:if>
		<xsl:if test="shop_item_digital/filename != ''">
			<tr valign="top">
				<td style=" color: #808285; line-height: 26px;  font-size: 13px;">
					<!--[if mso]><span style="font-family: sans-serif, Arial; font-size: 13px;"><![endif]-->
						Файл электронного товара:
					<!--[if mso]></span><![endif]-->
				</td>
				<td style=" color: #333333; line-height: 26px;  font-size: 13px;">
					<!--[if mso]><span style="font-family: sans-serif, Arial; font-size: 13px;"><![endif]-->
						<a href="{$schema}://{/shop/site/site_alias/name}{/shop/url}?download_file={guid}"><i>Скачать файл</i></a><br />
					<!--[if mso]></span><![endif]-->
				</td>
			</tr>
		</xsl:if>
	</xsl:template>
</xsl:stylesheet>