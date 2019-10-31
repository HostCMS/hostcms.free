<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "lang://226">
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>

	<!-- ЛентаЛичныхСообщений -->
	<xsl:template match="/">
		<script>
			<xsl:comment>
				<xsl:text disable-output-escaping="yes">
					<![CDATA[
					$( function () {
						$('#siteuser_messages').messagesHostCMS();
					})
					]]>
				</xsl:text>
			</xsl:comment>
		</script>

		<div id="siteuser_messages">
			<xsl:apply-templates select="siteuser"/>
		</div>
	</xsl:template>

	<xsl:variable name="current_siteuser_id" select="siteuser/@id" />

	<!-- Пользователи сайта -->
	<xsl:variable name="siteusers" select="//siteuser" />

	<xsl:template match="siteuser">
		<div style="display: none">
			<div id="url"><xsl:value-of select="url" /></div>
			<div id="limit"><xsl:value-of select="limit" /></div>
			<div id="total"><xsl:value-of select="total" /></div>
			<div id="topic_id"><xsl:value-of select="message_topic/@id" /></div>
		</div>

		<h1>
			<xsl:choose>
				<xsl:when test="message_topic/subject != ''"><xsl:value-of select="message_topic/subject" /></xsl:when>
				<xsl:otherwise>No subject</xsl:otherwise>
			</xsl:choose>
		</h1>

		<xsl:for-each select="errors/error">
			<div id="error"><xsl:value-of select="."/></div>
		</xsl:for-each>

		<xsl:for-each select="messages/message">
			<div id="message"><xsl:value-of select="."/></div>
		</xsl:for-each>

		<xsl:if test="message_topic">
			<!-- Показаны не все сообщения -->
			<div id="load_messages" class="right">
				<xsl:if test="total &lt; (page + 1) * limit">
					<xsl:attribute name="style">display: none</xsl:attribute>
				</xsl:if>
				&labelPrevious;
			</div>

			<div id="chat_window">
				<div id="messages">
					<xsl:apply-templates select="message_topic/message" />
				</div>
			</div>
			<div class="comment">
				<form action="{url}{message_topic/@id}/" id="send_message" method="post">
					<div class="row">
						<div class="caption">&labelMessage;</div>
						<div class="field">
							<p>
								<textarea name="text" rows="3" cols="60"></textarea>
							</p>
							<p><input type="submit" class="button" value="&labelReply;" name="add_message" /></p>
						</div>
					</div>
				</form>
			</div>
		</xsl:if>
	</xsl:template>

	<xsl:template match="message">

		<div id="msg_{@id}">
			<xsl:variable name="user_from_id" select="site_users_mail_from_id" />
			<xsl:variable name="user_to_id" select="site_users_mail_to_id" />

			<xsl:attribute name="class">
				<xsl:choose>
					<xsl:when test="$current_siteuser_id = siteuser_id">out </xsl:when>
					<xsl:otherwise>in </xsl:otherwise>
				</xsl:choose>

				<xsl:if test="read = 0">unread </xsl:if>

				<!-- <xsl:if test="site_users_mail_answer_to_mail_id = 0">first_msg </xsl:if> -->
			</xsl:attribute>

			<div class="attr">
				<xsl:variable name="new_user" ><xsl:choose>
						<xsl:when test="preceding::message[1]/siteuser_id != siteuser_id or not(preceding::message[1]/siteuser_id)">1</xsl:when>
						<xsl:otherwise>0</xsl:otherwise>
					</xsl:choose></xsl:variable>

				<!-- Пользователь -->
				<xsl:if test="$new_user = 1">
					<xsl:variable name="siteuser_id" select="siteuser_id" />
					<xsl:variable name="siteuser" select="$siteusers[@id = $siteuser_id]" />
					<b><a href="/users/info/{$siteuser/path}/">
							<xsl:if test="$siteuser/name != ''"><xsl:value-of select="$siteuser/name"/><xsl:text> </xsl:text></xsl:if>
							<xsl:value-of select="$siteuser/surname" />
					</a></b><br/>
				</xsl:if>

				<!-- Дата -->
				<xsl:if test="(substring(preceding::message[1]/datetime, 1, 10) != substring(datetime, 1, 10)	or position() = 1) or $new_user = 1">
					<b><xsl:value-of disable-output-escaping="yes" select="format-number(substring(datetime, 1, 2), '#')"/></b>

					<xsl:variable name="month" select="substring(datetime, 4, 2)" />

					<xsl:choose>
						<xsl:when test="$month = 1"> &labelMonth1; </xsl:when>
						<xsl:when test="$month = 2"> &labelMonth2; </xsl:when>
						<xsl:when test="$month = 3"> &labelMonth3; </xsl:when>
						<xsl:when test="$month = 4"> &labelMonth4; </xsl:when>
						<xsl:when test="$month = 5"> &labelMonth5; </xsl:when>
						<xsl:when test="$month = 6"> &labelMonth6; </xsl:when>
						<xsl:when test="$month = 7"> &labelMonth7; </xsl:when>
						<xsl:when test="$month = 8"> &labelMonth8; </xsl:when>
						<xsl:when test="$month = 9"> &labelMonth9; </xsl:when>
						<xsl:when test="$month = 10"> &labelMonth10; </xsl:when>
						<xsl:when test="$month = 11"> &labelMonth11; </xsl:when>
						<xsl:otherwise> &labelMonth12; </xsl:otherwise>
					</xsl:choose>
					&labelIn;
				</xsl:if>

				<!-- Время -->
				<xsl:value-of select="substring(datetime, 12, 5)" />
			</div>

			<div class="text" >
				<xsl:value-of select="text" disable-output-escaping="yes"/>
			</div>
		</div>

	</xsl:template>
</xsl:stylesheet>