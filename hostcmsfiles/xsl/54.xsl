<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "lang://54">
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>

	<xsl:template match="/">
		<xsl:apply-templates select="/document/comment"/>
	</xsl:template>

	<xsl:template match="/document/comment">

		<h1>&labelTitle;</h1>

		<!-- Определяем текст сообщения -->
		<xsl:variable name="message">
			<xsl:choose>
				<!-- Ошибка. С момента добавления последнего комментария прошло слишком мало времени -->
				<xsl:when test="/document/is_error_time = 1">
					<div id="error">&labelError1;</div>
				</xsl:when>
				<!-- Ошибка. Неверный код подтверждения -->
				<xsl:when test="/document/is_error_capthca = 1">
					<div id="error">&labelError2;</div>
				</xsl:when>
				<!-- Без ошибок -->
				<xsl:otherwise>
					<div id="message">
						&labelThankYou; <xsl:value-of disable-output-escaping="yes" select="comment_autor"/>!
						<br/>&labelLine1;
					</div>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:variable>

		<xsl:choose>
			<xsl:when test="$message = ''"></xsl:when>
			<xsl:otherwise>
				<div id="error">
					<xsl:value-of disable-output-escaping="yes" select="$message"/>
				</div>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
</xsl:stylesheet>