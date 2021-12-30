<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "lang://134">
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>

	<!-- ПополнениеЛицевогоСчетаРеквизиты -->

	<xsl:template match="/shop">

		<form method="POST">
			<h1>&labelPaymentDetails;</h1>

			<div class="comment shop_address">

				<div class="row">
					<div class="caption">&labelAmount;</div>
					<div class="field">
						<input type="text" size="15" class="width1" name="amount" />
						<xsl:text> </xsl:text>
						<xsl:value-of disable-output-escaping="yes" select="shop_currency/sign" />
						<span class="redSup"> *</span>
					</div>
				</div>

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
						</select>
						<span class="redSup"> *</span>
					</div>
				</div>
				<div class="row">
					<div class="caption">&labelCity;</div>
					<div class="field">
						<select name="shop_country_location_city_id" id="shop_country_location_city_id" onchange="$.loadCityAreas('{/shop/url}cart/', $(this).val())">
							<option value="0">…</option>
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
				<div class="row">
					<div class="caption">&labelReview;</div>
					<div class="field">
						<textarea rows="3" name="description" class="width2"></textarea>
					</div>
				</div>
			</div>

			<!-- List of payment systems -->
			<xsl:choose>
			<xsl:when test="count(shop_payment_system) = 0">
				<p><b>&labelShopPaymentSystemLine1;</b></p>
				<p>&labelShopPaymentSystemLine2;</p>
			</xsl:when>
			<xsl:otherwise>
				<table class="shop_cart">
					<tr class="total">
						<th>&labelPayForm;</th>
						<th>&labelDescription;</th>
					</tr>
					<xsl:apply-templates select="shop_payment_system"/>
				</table>

				<!-- Button -->
				<input name="apply" value="apply" type="hidden" />
				<input value="&labelNext;" type="submit" class="button" />
			</xsl:otherwise>
			</xsl:choose>
		</form>

		<SCRIPT type="text/javascript">
		$(function() {
			$.loadLocations('<xsl:value-of select="/shop/url" />cart/', $('#shop_country_id').val());
		});
		</SCRIPT>

	</xsl:template>

	<xsl:template match="shop_country">
		<option value="{@id}">
			<xsl:if test="/shop/shop_country_id = @id">
				<xsl:attribute name="selected">selected</xsl:attribute>
			</xsl:if>
			<xsl:value-of select="name" />
		</option>
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