<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "lang://30">
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>
	
	<xsl:template match="/siteuser">
		
		<h1>&labelTitle;</h1>
		
		<xsl:if test="message/node()">
			<div id="message">
				<xsl:value-of select="message" disable-output-escaping="yes"/>
			</div>
		</xsl:if>
		
		<xsl:if test="error/node()">
			<div id="error">
				<xsl:value-of select="error" disable-output-escaping="yes"/>
			</div>
		</xsl:if>
		
		<form method="post" action="./">
			<xsl:if test="@id = ''">
				<p>&labelLogin;
					<br /><input name="login" type="text" size="30" class="large" value="{login}" />
				</p>
				<p>&labelEmail;
					<br /><input name="email" type="text" size="30" class="large" value="{email}" />
				</p>
			</xsl:if>
			
			<!-- Выводим список рассылок, на которые можно подписаться -->
			<table class="table maillist">
				<tr>
					<th>&labelMaillist;</th>
					<th>&labelFormat;</th>
					<th>&labelSubscribe;</th>
				</tr>
				<xsl:apply-templates select="maillist"></xsl:apply-templates>
			</table>
			
			<input name="anonymousmaillist" type="submit" value="&labelSubscribe;" class="button" />
		</form>
	</xsl:template>
	
	<xsl:template match="maillist">
		<xsl:variable name="id" select="@id" />
		<xsl:variable name="maillist_siteuser" select="/siteuser/maillist_siteuser[maillist_id = $id]" />
		
		<tr>
			<td>
				<strong><xsl:value-of select="name"/></strong>
				<xsl:if test="description != ''">
					<br /><xsl:value-of select="description"/>
				</xsl:if>
			</td>
			<td align="center">
				<select name="type_{@id}">
					<option value="0">
					<xsl:if test="$maillist_siteuser/node() and $maillist_siteuser/type = 0"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
						&labelText;
					</option>
					<option value="1">
					<xsl:if test="$maillist_siteuser/node() and $maillist_siteuser/type = 1"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
						HTML
					</option>
				</select>
			</td>
			<td align="center">
				<input name="maillist_{@id}" type="checkbox" value="1">
				<xsl:if test="$maillist_siteuser/node() or not(/siteuser/@id)"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if>
				</input>
			</td>
		</tr>
	</xsl:template>
</xsl:stylesheet>