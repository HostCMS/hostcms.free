<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>
	
	<!-- ОтображениеРезультатовОпроса -->
	
	<xsl:template match="/">
		<xsl:apply-templates select="/poll_group"/>
	</xsl:template>
	
	<xsl:template match="poll_group">
		
		<h1 hostcms:id="{poll/@id}" hostcms:field="poll/name" hostcms:entity="poll">
			<xsl:value-of select="poll/name"/>
		</h1>
		
		<xsl:if test="ПользовательИмеетПравоОтвечать=1">
			<xsl:if test="ОтображатьСообщениеПользователю=1">
				<p id="message">Спасибо Ваш ответ принят!</p>
			</xsl:if>
		</xsl:if>
		
		<xsl:if test="poll/show_results != 1 and ОтображатьСообщениеПользователю != 1">
			<p id="error">Запрещено отображение результатов!</p>
		</xsl:if>
		
		<!--<xsl:if test="НеВыбранВариантОтвета=1 and poll/show_results != 1">
			<p>Выберите вариант ответа!</p>
		</xsl:if>-->
		
		<xsl:if test="ПользовательИмеетПравоОтвечать=0">
			<p id="error">Вы уже голосовали по данному опросу!</p>
		</xsl:if>
		
		<xsl:if test="not(НеВыбранВариантОтвета=1 and poll/show_results != 1)">
			
			<xsl:if test="poll/show_results = 1 or ПоказатьРезультытыБезГолосования = 1">
				<!-- Отображаем результаты голосования -->
				<table border="0" cellspacing="4" cellpadding="0">
					<xsl:apply-templates select="poll/poll_response"></xsl:apply-templates>
				</table>
				
			<p>Всего голосов: <strong><xsl:value-of select="poll/voted"/></strong></p>
			</xsl:if>
		</xsl:if>
	</xsl:template>
	
	<!-- Отображение результатов голосования -->
	<xsl:template match="poll/poll_response">
		
		<xsl:variable name="color_number">
			<xsl:choose>
				<xsl:when test="position() &lt;= 4 and position() mod 4 != 0">
					<xsl:value-of select="position()"/>
				</xsl:when>
				<xsl:when test="position() mod 4 = 0">4</xsl:when>
				<xsl:otherwise>
					<xsl:value-of select="position() mod 4"/>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:variable>
		
		<xsl:variable name="percent"><xsl:choose>
				<xsl:when test="../voted = 0">0</xsl:when>
				<xsl:otherwise><xsl:value-of select="round(voted div ../voted * 100)" /></xsl:otherwise>
		</xsl:choose></xsl:variable>
		
		<tr>
			<td>
			<xsl:value-of select="name"/><xsl:text> </xsl:text></td>
			<td width="200px" align="left" valign="middle">
				<xsl:value-of select="number"/>
			<span style="color: #38b4c6;"><xsl:text> </xsl:text>(<xsl:value-of select="$percent"/> %)</span>
				<br/>
				<div class="polls">
					<img src="/hostcmsfiles/polls/bg_vote_{$color_number}.gif" alt="" height="4" width="{$percent}%"/>
				</div>
			</td>
		</tr>
	</xsl:template>
</xsl:stylesheet>