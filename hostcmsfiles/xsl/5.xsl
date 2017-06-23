<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN"
encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml" />

<xsl:template match="/">
    <xsl:apply-templates select="/document"/>
</xsl:template>

<xsl:template match="/document">

	<!-- Определяем текст сообщения -->
	<xsl:choose>
	
		<!-- Ошибка. С момента добавления последнего комментария прошло слишком мало времени -->
		<xsl:when test="/document/error_time = 1">
			<div id="error">Ошибка! С момента добавления Вами последнего комментария/ответа прошло слишком мало времени!</div>
		</xsl:when>
		<!-- Ошибка. Неверный код подтверждения -->
		<xsl:when test="/document/error_captcha = 1">
			<div id="error">Ошибка! Вы неверно ввели число подтверждения отправки комментария!</div>
		</xsl:when>
		<!-- Без ошибок -->
		<xsl:otherwise>
			<div id="message">
				Благодарим Вас<xsl:if test="comment/author != ''">, <xsl:value-of select="comment/author"/></xsl:if>!
				<br />

				<!-- Проверка на тип добавления, сразу после публикации или после проверки -->
				<xsl:choose>
				<xsl:when test="comment/active = 1">
					Комментарий опубликован.
				</xsl:when>
				<xsl:otherwise>
					После проверки администратором Ваш комментарий станет доступным.
				</xsl:otherwise>
				</xsl:choose>
			</div >
		</xsl:otherwise>
	</xsl:choose>
</xsl:template>

</xsl:stylesheet>