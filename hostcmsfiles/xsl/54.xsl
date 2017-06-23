<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>

	<xsl:template match="/">
		<xsl:apply-templates select="/document/comment"/>
	</xsl:template>

	<xsl:template match="/document/comment">

		<h1>Добавление ответа</h1>

		<!-- Определяем текст сообщения -->
		<xsl:variable name="message">
			<xsl:choose>
				<!-- Ошибка. С момента добавления последнего комментария прошло слишком мало времени -->
				<xsl:when test="/document/is_error_time = 1">
					<div id="error">Ошибка! С момента добавления Вами последнего ответа прошло слишком мало времени!</div>
				</xsl:when>
				<!-- Ошибка. Неверный код подтверждения -->
				<xsl:when test="/document/is_error_capthca = 1">
					<div id="error">Ошибка! Вы неверно ввели число подтверждения отправки ответа!</div>
				</xsl:when>
				<!-- Без ошибок -->
				<xsl:otherwise>
					<div id="message">
						Благодарим Вас, <xsl:value-of disable-output-escaping="yes" select="comment_autor"/>!
						<br/>После проверки Администратором Ваш ответ станет доступным!
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