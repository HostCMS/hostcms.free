<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>

	<!-- Устаревшие - Фоурм - Анкетные данные пользователя -->
	<xsl:template match="/document">

		<h1>
			<xsl:value-of select="site_user_fio"/>
		</h1>

		<xsl:if test="error != ''">
			<div id="error">
				<xsl:value-of disable-output-escaping="yes" select="error"/>
			</div>
		</xsl:if>

		<p>
			<a href="{forums_path}">Список форумов</a>
			<xsl:if test="forums_id != 0 and forums_name != ''">
				<span><xsl:text> → </xsl:text></span>
				<a href="{forums_path}{forums_id}/">
					<xsl:value-of select="forums_name"/>
				</a>
				<xsl:if test="theme_id != 0 and theme_title != ''">
					<span><xsl:text> → </xsl:text></span>
					<a href="{forums_path}{forums_id}/{theme_id}/">
						<xsl:value-of select="theme_title"/>
					</a>
				</xsl:if>
			</xsl:if>
		</p>

		<table class="table_user_info">
			<tr class="row_user_parameters">
				<td height="20" align="center">Аватар</td>
				<td width="350" align="center">Анкетные данные</td>
			</tr>
			<tr>
				<td rowspan="1" valign="middle" align="center" style="padding: 10px;">
					<xsl:choose>
						<xsl:when test="extra_property[extra_property_name = 'avatar']/extra_property_value != ''">
							<!-- Отображаем картинку-аватарку -->
							<img src="{extra_property[extra_property_name = 'avatar']/extra_property_value}" alt="" border="0" style="padding: 10px;"/>
							<br/>
						</xsl:when>
						<xsl:otherwise>
							<!-- Отображаем картинку, символизирующую пустую аватарку -->
							<img src="/hostcmsfiles/forum/avatar.gif" alt="" border="0" style="padding: 10px;"/>
							<br/>
						</xsl:otherwise>
					</xsl:choose>
					<span class="author_status">
						<xsl:value-of select="site_user_status"/>
					</span>
				</td>
				<td valign="middle">
					<!-- Анкетные данные -->
					<table border="0" class="table_extra_properties">

						<!-- Проверяем, указано ли имя -->
						<xsl:if test="site_users_name != ''">
						<!-- Имя -->
						<tr>
							<td><xsl:text>Имя: </xsl:text></td>
							<td>
								<b>
									<xsl:value-of select="site_users_name"/>
								</b>
							</td>
						</tr>
						</xsl:if>

						<!-- E-mail -->

						<!-- Проверяем, нужно ли выводить электронный адрес пользователя -->
						<xsl:if test="extra_property[extra_property_name = 'public_email']/extra_property_value != 0">
							<tr>
								<td><xsl:text>E-mail: </xsl:text></td>
								<td>
									<b>
										<a href="mailto:{site_users_email}">
											<xsl:value-of select="site_users_email"/>
										</a>
									</b>
								</td>
							</tr>
						</xsl:if>

						<!-- Зарегистрирован -->
						<tr>
							<td><xsl:text>Зарегистрирован: </xsl:text></td>
							<td>
								<b>
									<!--Если дата регистрации есть в ОСНОВНОМ свойстве пользователя -->
									<xsl:if test="site_users_date_registration != '00.00.0000 00:00:00'">
										<xsl:value-of select="site_users_date_registration"/>
									</xsl:if>
									<!--Если дата регистрации есть в ДОПОЛНИТЕЛЬНОМ свойстве пользователя -->
									<xsl:if test="site_users_date_registration = '00.00.0000 00:00:00'">
										<xsl:value-of select="extra_property[extra_property_id = '32']/extra_property_value"/>
									</xsl:if>
								</b>
							</td>
						</tr>
						<!-- ICQ (из доп. свойства) -->
						<xsl:apply-templates select="extra_property[extra_property_name = 'icq_number']"/>
					</table>
				</td>
			</tr>
		</table>
	</xsl:template>


	<!-- Шаблон вывода дополнительных параметров пользователя -->
	<xsl:template match="extra_property">
		<tr>
			<td valign="middle" align="left">
				<xsl:value-of select="extra_property_title"/><xsl:text>: </xsl:text></td>
			<td valign="middle" align="left">
				<b>
					<xsl:value-of select="extra_property_value"/>
				</b>
			</td>
		</tr>
	</xsl:template>
</xsl:stylesheet>