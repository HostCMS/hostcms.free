<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>

	<xsl:template match="/document">

		<h1>
			<xsl:value-of select="page_title"/>
		</h1>

		<xsl:if test="error != ''">
			<!-- Определяем текст ошибки по ее коду -->
			<xsl:variable name="error_text">
				<xsl:choose>
					<xsl:when test="error = -1">Введен некорректный электронный адрес</xsl:when>
					<xsl:when test="error = -2">Пользователь с указанным электронным адресом зарегистрирован ранее</xsl:when>
					<xsl:when test="error = -3">Пользователь с указанным логином зарегистрирован ранее</xsl:when>
					<xsl:when test="error = -4">Заполните, пожалуйста, все обязательные параметры</xsl:when>
					<xsl:when test="error = -5">Неправильно введен код подтверждения</xsl:when>
					<xsl:otherwise></xsl:otherwise>
				</xsl:choose>
			</xsl:variable>

			<div id="error">
				<xsl:value-of disable-output-escaping="yes" select="$error_text"/>
			</div>
		</xsl:if>

		<p>
			<a href="/users/">Кабинет пользователя</a>
		</p>

		<form action="/users/registration/" method="post" enctype="multipart/form-data">
			<input type="hidden" name="MAX_FILE_SIZE" value="1000000000"/>
			<table border="0" cellspacing="0" cellpadding="2">
				<tr>
					<td width="150px">Логин</td>
					<td>
						<input name="site_users_login" type="text" value="{site_users_login}" size="40"/>*</td>
				</tr>
				<tr>
					<td>Пароль</td>
					<td>
						<input name="site_users_password" type="password" value="" size="40"/>

						<!-- Для авторизированного пользователя заполнять пароль при редактирвоании данных необязательно -->
						<xsl:if test="site_users_id = 0">*</xsl:if>
					</td>
				</tr>
				<tr>
					<td>E-mail</td>
					<td>
						<input name="site_users_email" type="text" value="{site_users_email}" size="40"/>*</td>
				</tr>
				<tr>
					<td>Фамилия</td>
					<td>
						<input name="site_users_surname" type="text" value="{site_users_surname}" size="40"/>
					</td>
				</tr>
				<tr>
					<td>Имя</td>
					<td>
						<input name="site_users_name" type="text" value="{site_users_name}" size="40"/>
					</td>
				</tr>
				<tr>
					<td>Отчество</td>
					<td>
						<input name="site_users_patronymic" type="text" value="{site_users_patronymic}" size="40"/>
					</td>
				</tr>
				<tr>
					<td>Компания</td>
					<td>
						<input name="site_users_company" type="text" value="{site_users_company}" size="40"/>
					</td>
				</tr>
				<tr>
					<td>Телефон</td>
					<td>
						<input name="site_users_phone" type="text" value="{site_users_phone}" size="40"/>
					</td>
				</tr>
				<tr>
					<td>Факс</td>
					<td>
						<input name="site_users_fax" type="text" value="{site_users_fax}" size="40"/>
					</td>
				</tr>
				<tr>
					<td>Сайт</td>
					<td>
						<input name="site_users_site" type="text" value="{site_users_site}" size="40"/>
					</td>
				</tr>
				<tr>
					<td>ICQ</td>
					<td>
						<input name="site_users_icq" type="text" value="{site_users_icq}" size="40"/>
					</td>
				</tr>
				<tr>
					<td>Страна</td>
					<td>
						<input name="site_users_country" type="text" value="{site_users_country}" size="40"/>
					</td>
				</tr>
				<tr>
					<td>Почтовый индекс</td>
					<td>
						<input name="site_users_postcode" type="text" value="{site_users_postcode}" size="40"/>
					</td>
				</tr>
				<tr>
					<td>Город</td>
					<td>
						<input name="site_users_city" type="text" value="{site_users_city}" size="40"/>
					</td>
				</tr>
				<tr>
					<td>Адрес</td>
					<td>
						<input name="site_users_address" type="text" value="{site_users_address}" size="40"/>
					</td>
				</tr>

				<!-- Внешние параметры -->
				<xsl:if test="count(extra_property)!=0">
					<xsl:apply-templates select="extra_property"/>
				</xsl:if>

				<!-- Код подтверждения выводится только при регистрации -->
				<xsl:if test="//captcha_key/node()">
					<tr>
						<td>Код подтверждения</td>
						<td>

							<div style="float: left">
								<input type="hidden" name="captcha_key" value="{//captcha_key}"/>
								<input type="text" name="captcha_keystring" size="5"/>
							</div>

							<div style="float: left; margin-left: 10px">
								<img style="border: 1px solid #000000" src="/captcha.php?get_captcha={//captcha_key}&amp;height=18" title="Введите число изображённое на картинке"/>
							</div>
						</td>
					</tr>
				</xsl:if>
			</table>

			<!-- Определяем имя кнопки -->
			<xsl:variable name="button_name">
				<xsl:choose>
					<xsl:when test="site_users_id = 0">Зарегистрироваться</xsl:when>
					<xsl:otherwise>Изменить</xsl:otherwise>
				</xsl:choose>
			</xsl:variable>

			<p>
				<input name="add_user" type="submit" value="{$button_name}"/>
			</p>
		</form>
	</xsl:template>

	<!-- Внешнее свойство типа "список" -->
	<xsl:template match="extra_property_value/item">
		<xsl:if test="not(selected='true')">
			<option value="{item_id}">
				<xsl:value-of select="item_name"/>
			</option>
		</xsl:if>
		<xsl:if test="selected='true'">
			<option value="{item_id}" selected="">
				<xsl:value-of select="item_name"/>
			</option>
		</xsl:if>
	</xsl:template>

	<!-- Внешнее свойство типа "радио группа" -->
	<xsl:template match="extra_property_value/radio">
		<xsl:param name="property_name"/>
		<xsl:if test="not(selected='true')">
			<input style="border:0px" type="radio" id="{radio_id}" name="{$property_name}" value="{radio_id}"/>
			<label for="{radio_id}">
				<xsl:value-of select="radio_name"/>
			</label>
		</xsl:if>
		<xsl:if test="selected='true'">
			<input style="border:0px" type="radio" id="{radio_id}" name="{$property_name}" value="{radio_id}" checked=""/>
			<label for="{radio_id}">
				<xsl:value-of select="radio_name"/>
			</label>
		</xsl:if>
	</xsl:template>

	<!-- Внешние свойства -->
	<xsl:template match="extra_property">
		<tr>
			<xsl:if test="extra_property_type!='hidden'">
				<td>
					<xsl:value-of select="extra_property_title"/>
				</td>
				<td>
					<xsl:if test="extra_property_type='select'">
						<select name="{extra_property_name}" title="{extra_property_comment}">
							<xsl:apply-templates select="extra_property_value/item"/>
						</select>
					</xsl:if>

					<xsl:if test="extra_property_type='radio'">
						<xsl:apply-templates select="extra_property_value/radio">
							<xsl:with-param name="property_name" select="extra_property_name"/>
						</xsl:apply-templates>
					</xsl:if>

					<xsl:if test="(extra_property_type='checkbox') and not(extra_property_value=1)">
						<input style="border:0px" name="{extra_property_name}" type="{extra_property_type}" title="{extra_property_comment}" value="1"/>
					</xsl:if>

					<xsl:if test="(extra_property_type='checkbox') and (extra_property_value=1)">
						<input style="border:0px" name="{extra_property_name}" type="{extra_property_type}" title="{extra_property_comment}" value="1" checked=""/>
					</xsl:if>

					<xsl:if test="extra_property_type='text' or extra_property_type='password'">
						<input size="{extra_property_size}" name="{extra_property_name}" type="{extra_property_type}" title="{extra_property_comment}" value="{extra_property_value}"/>
					</xsl:if>

					<xsl:if test="extra_property_type='file'">
						<input size="{extra_property_size}" name="{extra_property_name}" type="{extra_property_type}" title="{extra_property_comment}"/>
						<xsl:if test="not(extra_property_value='')"><xsl:text> </xsl:text>
							<a href="/upload/users/{/document/site_users_id}/{extra_property_value}" target="_blank">
								<img src="/hostcmsfiles/images/preview.gif" style="margin-bottom: -2px"/>
							</a><xsl:text> </xsl:text><a href="?delete_value_property={extra_property_id}" onclick="return confirm('Вы уверены, что хотите удалить?')"><img src="/hostcmsfiles/images/delete.gif" style="margin-bottom: -2px"/></a></xsl:if>
					</xsl:if>

					<xsl:if test="extra_property_type='textarea'">
						<textarea name="{extra_property_name}" title="{extra_property_comment}" cols="{extra_property_cols}" rows="{extra_property_rows}">
							<xsl:value-of select="extra_property_value"/>
						</textarea>
					</xsl:if>
				</td>
			</xsl:if>

			<!--
	<xsl:if test="extra_property_type='hidden'">
		<input name="{extra_property_name}" type="hidden" value="{extra_property_value}" />
	</xsl:if>
	-->
		</tr>
	</xsl:template>
</xsl:stylesheet>