<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "lang://68">
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>
	
	<!-- МагазинПлатежнаяСистема -->
	<xsl:template match="/shop">
		
		<ul class="shop_navigation">
		<li><span>&labelAddress;</span>→</li>
		<li><span>&labelShipmentMethod;</span>→</li>
		<li class="shop_navigation_current"><span>&labelPaymentMethod;</span>→</li>
		<li><span>&labelOrderConfirmation;</span></li>
		</ul>
		
		<h1>&labelForm;</h1>
		
		<form method="post">
			<xsl:choose>
				<xsl:when test="count(shop_payment_system) = 0">
				<p><b>&labelLine1;</b></p>
					<p>&labelLine2;</p>
				</xsl:when>
				<xsl:otherwise>
					<table class="shop_cart">
						<tr class="total">
							<th>&labelForm;</th>
							<th>&labelDescription;</th>
						</tr>
						<xsl:apply-templates select="shop_payment_system"/>
					</table>
					
					<!-- Частичная оплата с лицевого счета -->
					<xsl:if test="siteuser/transaction_amount/node() and siteuser/transaction_amount &gt; 0">
						<p>
					<label><input type="checkbox" name="partial_payment_by_personal_account" /> &labelPartiallyPay; <strong><xsl:value-of select="siteuser/transaction_amount" /><xsl:text> </xsl:text><xsl:value-of select="shop_currency/name" /></strong></label>
						</p>
					</xsl:if>
					
					<input name="step" value="4" type="hidden" />
					<input value="&labelNext;" type="submit" class="button" />
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