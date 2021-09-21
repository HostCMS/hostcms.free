<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "lang://61">
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml" />
	
	<!-- МагазинАдресДоставки -->
	
	<xsl:template match="/shop">
		<ul class="shop_navigation">
		<li class="shop_navigation_current"><span>&labelAddress;</span>→</li>
		<li><span>&labelShipmentMethod;</span>→</li>
		<li><span>&labelPaymentMethod;</span>→</li>
		<li><span>&labelOrderConfirmation;</span></li>
		</ul>
		
		<h1>&labelAddress;</h1>
		
		<!-- Выводим ошибку (error), если она была передана через внешний параметр -->
		<xsl:if test="error != ''">
			<div class="alert alert-danger alert-cart" role="alert">
				<xsl:value-of disable-output-escaping="yes" select="error" />
			</div>
		</xsl:if>
		
		<form method="post" enctype="multipart/form-data">
			<div class="comment shop_address">
				<div class="row">
					<div class="caption">&labelCountry;</div>
					<div class="field">
						<select id="shop_country_id" name="shop_country_id" onchange="$.loadLocations('{/shop/url}cart/', $(this).val())">
							<option value="0">…</option>
							<xsl:apply-templates select="shop_country" />
						</select>
						<span class="redSup"> *</span>
					</div>
				</div>
				
				<div class="row">
					<div class="caption">&labelRegion;</div>
					<div class="field">
						<select name="shop_country_location_id" id="shop_country_location_id" onchange="$.loadCities('{/shop/url}cart/', $(this).val())">
							<option value="0">…</option>
							<xsl:apply-templates select="shop_country_location" />
						</select>
						<span class="redSup"> *</span>
					</div>
				</div>
				<div class="row">
					<div class="caption">&labelCity;</div>
					<div class="field">
						<select name="shop_country_location_city_id" id="shop_country_location_city_id" onchange="$.loadCityAreas('{/shop/url}cart/', $(this).val())">
							<option value="0">…</option>
							<xsl:apply-templates select="shop_country_location_city" />
						</select>
					</div>
				</div>
				<div class="row">
					<div class="caption">&labelDistrict;</div>
					<div class="field">
						<select name="shop_country_location_city_area_id" id="shop_country_location_city_area_id">
							<option value="0">…</option>
						</select>
					</div>
				</div>
				<div class="row">
					<div class="caption">&labelPostcode;</div>
					<div class="field">
						<input type="text" size="15" class="width1" name="postcode" value="{/shop/siteuser/postcode}" />
					</div>
				</div>
				<div class="row">
					<div class="caption">&labelAddressLine1;<br/>
					&labelAddressLine2;</div>
					<div class="field">
						<input type="text" size="30" name="address" value="{/shop/siteuser/address}" class="width2" />
					</div>
				</div>
				<div class="row">
					<div class="caption">&labelName;</div>
					<div class="field">
						<input type="text" size="15" class="width1" name="surname" value="{/shop/siteuser/surname}" />
						<input type="text" size="15" class="width1" name="name" value="{/shop/siteuser/name}" />
						<input type="text" size="15" class="width1" name="patronymic" value="{/shop/siteuser/patronymic}" />
					</div>
				</div>
				<div class="row">
					<div class="caption">&labelCompany;</div>
					<div class="field">
						<input type="text" size="30" name="company" value="{/shop/siteuser/company}" class="width2" />
					</div>
				</div>
				<div class="row">
					<div class="caption">&labelPhone;</div>
					<div class="field">
						<input type="text" size="30" name="phone" value="{/shop/siteuser/phone}" class="width2" />
					</div>
				</div>
				<div class="row">
					<div class="caption">&labelFax;</div>
					<div class="field">
						<input type="text" size="30" name="fax" value="{/shop/siteuser/fax}" class="width2" />
					</div>
				</div>
				<div class="row">
					<div class="caption">&labelEmail;</div>
					<div class="field">
						<input type="text" size="30" name="email" value="{/shop/siteuser/email}" class="width2" />
					</div>
				</div>
				
				<!-- Дополнительные свойства заказа -->
				<xsl:if test="count(shop_order_properties//property[display != 0 and (type != 2 )])">
					<xsl:apply-templates select="shop_order_properties//property[display != 0]" mode="propertyList"/>
				</xsl:if>
				
				<div class="row">
					<div class="caption">&labelReview;</div>
					<div class="field">
						<textarea rows="3" name="description" class="width2"></textarea>
					</div>
				</div>
				<div class="row">
					<div class="caption"></div>
					<div class="field">
						<input name="step" value="2" type="hidden" />
						<input value="&labelNext;" type="submit" class="button" />
					</div>
				</div>
			</div>
		</form>
		
		<xsl:if test="not(/shop/current_shop_country_location_id/node())">
			<SCRIPT type="text/javascript">
				$(function() {
				$.loadLocations('<xsl:value-of select="/shop/url" />cart/', $('#shop_country_id').val());
				});
			</SCRIPT>
		</xsl:if>
	</xsl:template>
	
	<!-- Шаблон для фильтра дополнительных свойств заказа -->
	<xsl:template match="property" mode="propertyList">
		<xsl:variable name="nodename">property_<xsl:value-of select="@id"/></xsl:variable>
		
		<div class="row">
			<div class="caption">
				<xsl:if test="display != 5">
					<xsl:value-of select="name"/>:
				</xsl:if>
			</div>
			<div class="field">
				<xsl:choose>
					<!-- Отображаем файл -->
					<xsl:when test="type = 2">
						<input type="file" size="30" name="property_{@id}" class="width2"></input>
					</xsl:when>
					<!-- Отображаем поле ввода -->
					<xsl:when test="display = 1">
						<input type="text" size="30" name="property_{@id}" class="width2">
							<xsl:choose>
								<xsl:when test="/shop/*[name()=$nodename] != ''">
									<xsl:attribute name="value"><xsl:value-of select="/shop/*[name()=$nodename]"/></xsl:attribute>
								</xsl:when>
							<xsl:otherwise><xsl:attribute name="value"><xsl:value-of select="default_value"/></xsl:attribute></xsl:otherwise>
							</xsl:choose>
						</input>
					</xsl:when>
					<!-- Отображаем список -->
					<xsl:when test="display = 2">
						<select name="property_{@id}">
							<option value="0">...</option>
							<xsl:apply-templates select="list/list_item"/>
						</select>
					</xsl:when>
					<!-- Отображаем переключатели -->
					<xsl:when test="display = 3">
						<div class="propertyInput">
							<input type="radio" name="property_{@id}" value="0" id="id_prop_radio_{@id}_0"></input>
							<label for="id_prop_radio_{@id}_0">&labelAnyOption;</label>
							<xsl:apply-templates select="list/list_item"/>
						</div>
					</xsl:when>
					<!-- Отображаем флажки -->
					<xsl:when test="display = 4">
						<div class="propertyInput">
							<xsl:apply-templates select="list/list_item"/>
						</div>
					</xsl:when>
					<!-- Отображаем флажок -->
					<xsl:when test="display = 5">
						<input type="checkbox" name="property_{@id}" id="property_{@id}" style="padding-top:4px">
							<xsl:if test="/shop/*[name()=$nodename] != ''">
								<xsl:attribute name="checked"><xsl:value-of select="/shop/*[name()=$nodename]"/></xsl:attribute>
							</xsl:if>
						</input>
						<label for="property_{@id}">
							<xsl:value-of select="name"/><xsl:text> </xsl:text>
						</label>
					</xsl:when>
					<!-- Отображаем список с множественным выбором-->
					<xsl:when test="display = 7">
						<select name="property_{@id}[]" multiple="multiple">
							<xsl:apply-templates select="list/list_item"/>
						</select>
					</xsl:when>
					<!-- Отображаем большое текстовое поле -->
					<xsl:when test="display = 8">
						<textarea type="text" size="30" rows="5" name="property_{@id}" class="width2">
							<xsl:choose>
								<xsl:when test="/shop/*[name()=$nodename] != ''">
									<xsl:value-of select="/shop/*[name()=$nodename]"/>
								</xsl:when>
								<xsl:otherwise><xsl:value-of select="default_value"/></xsl:otherwise>
							</xsl:choose>
						</textarea>
					</xsl:when>
					
				</xsl:choose>
			</div>
		</div>
	</xsl:template>
	
	<xsl:template match="list/list_item">
		<xsl:if test="../../display = 2">
			<!-- Отображаем список -->
			<xsl:variable name="nodename">property_<xsl:value-of select="../../@id"/></xsl:variable>
			<option value="{@id}">
			<xsl:if test="/shop/*[name()=$nodename] = @id"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
				<xsl:value-of disable-output-escaping="yes" select="value"/>
			</option>
		</xsl:if>
		<xsl:if test="../../display = 3">
			<!-- Отображаем переключатели -->
			<xsl:variable name="nodename">property_<xsl:value-of select="../../@id"/></xsl:variable>
			<br/>
			<input type="radio" name="property_{../../@id}" value="{@id}" id="id_property_{../../@id}_{@id}">
				<xsl:if test="/shop/*[name()=$nodename] = @id">
					<xsl:attribute name="checked">checked</xsl:attribute>
				</xsl:if>
				<label for="id_property_{../../@id}_{@id}">
					<xsl:value-of disable-output-escaping="yes" select="value"/>
				</label>
			</input>
		</xsl:if>
		<xsl:if test="../../display = 4">
			<!-- Отображаем флажки -->
			<xsl:variable name="nodename">property_<xsl:value-of select="../../@id"/></xsl:variable>
			<br/>
			<input type="checkbox" value="{@id}" name="property_{../../@id}[]" id="property_{../../@id}_{@id}">
				<xsl:if test="/shop/*[name()=$nodename] = @id">
					<xsl:attribute name="checked">checked</xsl:attribute>
				</xsl:if>
				<label for="property_{../../@id}_{@id}">
					<xsl:value-of disable-output-escaping="yes" select="value"/>
				</label>
			</input>
		</xsl:if>
		<xsl:if test="../../display = 7">
			<!-- Отображаем список -->
			<xsl:variable name="nodename">property_<xsl:value-of select="../../@id"/></xsl:variable>
			<option value="{@id}">
				<xsl:if test="/shop/*[name()=$nodename] = @id">
					<xsl:attribute name="selected">
					</xsl:attribute>
				</xsl:if>
				<xsl:value-of disable-output-escaping="yes" select="value"/>
			</option>
		</xsl:if>
	</xsl:template>
	
	<xsl:template match="shop_country">
		<option value="{@id}">
			<xsl:if test="/shop/current_shop_country_id = @id or not(/shop/current_shop_country_id/node()) and /shop/shop_country_id = @id and /shop/shop_country_id/@id != 1">
				<xsl:attribute name="selected">selected</xsl:attribute>
			</xsl:if>
			<xsl:value-of select="name" />
		</option>
	</xsl:template>
	
	<xsl:template match="shop_country_location">
		<option value="{@id}">
			<xsl:if test="/shop/current_shop_country_location_id = @id">
				<xsl:attribute name="selected">selected</xsl:attribute>
			</xsl:if>
			<xsl:value-of select="name" />
		</option>
	</xsl:template>
	
	<xsl:template match="shop_country_location_city">
		<option value="{@id}">
			<xsl:if test="/shop/current_shop_country_location_city_id = @id">
				<xsl:attribute name="selected">selected</xsl:attribute>
			</xsl:if>
			<xsl:value-of select="name" />
		</option>
	</xsl:template>
</xsl:stylesheet>