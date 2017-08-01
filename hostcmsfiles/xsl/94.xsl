<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "lang://94">
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>

	<!-- ОтображениеБаннера -->

	<xsl:template match="/">
		<xsl:choose>
			<xsl:when test="advertisement_group/node()">
				<xsl:apply-templates select="advertisement_group/advertisement" />
			</xsl:when>
			<xsl:otherwise>
				<xsl:apply-templates select="advertisement" />
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

	<xsl:template match="advertisement">
		<!-- Check banner's height -->
	<xsl:variable name="height"><xsl:if test="height != 0">height: <xsl:value-of select="height"/>px;</xsl:if></xsl:variable>

		<!-- Check banner's width -->
	<xsl:variable name="width"><xsl:if test="width != 0">width: <xsl:value-of select="width"/>px;</xsl:if></xsl:variable>

		<!-- Check banner's type -->
		<xsl:choose>
			<!-- Image -->
			<xsl:when test="type = 0">
				<div>
					<xsl:if test="$height != '' or $width != ''">
						<xsl:attribute name="style"><xsl:value-of select="$height" /><xsl:value-of select="$width" /></xsl:attribute>
					</xsl:if>

					<xsl:choose>
						<!-- Link -->
						<xsl:when test="href != '' ">
							<a href="/showbanner/?id={advertisement_show/@id}">
								<img src="{dir}{source}" alt="" />
							</a>
						</xsl:when>
						<!-- Just image -->
						<xsl:otherwise>
							<img src="{dir}{source}" alt="" />
						</xsl:otherwise>
					</xsl:choose>
				</div>
			</xsl:when>

			<!-- Text -->
			<xsl:when test="type = 1">
				<div>
					<xsl:if test="$height != '' or $width != ''">
						<xsl:attribute name="style"><xsl:value-of select="$height" /><xsl:value-of select="$width" /></xsl:attribute>
					</xsl:if>
					<xsl:value-of select="html" disable-output-escaping="yes"/>
				</div>
			</xsl:when>

			<!-- Popup -->
			<xsl:when test="type = 2">
				<SCRIPT language = "JavaScript">
					<xsl:comment>
						<xsl:text disable-output-escaping="yes">
							<![CDATA[
							var popUp = 0;
						var popURL = "/]]></xsl:text><xsl:value-of select = "url" /><xsl:text disable-output-escaping="yes"><![CDATA[";
						var popWidth = ]]></xsl:text><xsl:value-of select = "width" /><xsl:text disable-output-escaping="yes"><![CDATA[;
						var popHeight = ]]></xsl:text><xsl:value-of select = "height" /><xsl:text disable-output-escaping="yes"><![CDATA[;
							popUp =	window.open(popURL, "popup", "width="+popWidth+", height="+popHeight+", status=yes, scrollbars=yes, location=no, menubar=no, directories=no, resizable=no, titlebar=yes");
							]]>
						</xsl:text>
					</xsl:comment>
				</SCRIPT>
			</xsl:when>
			<!-- Flash -->
			<xsl:when test="type = 3">
				<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,29,0" width="{width}" height="{height}">
					<param name="movie" value="{dir}{source}"/>
					<param name="quality" value="high"/>
					<param name="href" value="/showbanner/?list_id={list_id}"/>
					<embed src="{dir}{source}" quality="high" pluginspage="http://www.macromedia.com/shockwave/download/index.cgi?P1_Prod_Version=ShockwaveFlash" type="application/x-shockwave-flash" width="{width}" height="{height}"></embed>
				</object>
			</xsl:when>
			<xsl:otherwise></xsl:otherwise>
		</xsl:choose>
	</xsl:template>
</xsl:stylesheet>