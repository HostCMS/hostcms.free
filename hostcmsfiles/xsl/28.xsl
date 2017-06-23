<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>

	<xsl:template match="/siteuser">

		<!-- Быстрая регистрация в магазине -->
		<xsl:if test="fastRegistration/node()">
			<div id="fastRegistrationDescription" style="float: left">
				<p class="h1">Быстрая регистрация</p>
				<b>Какие преимущества дает регистрация на сайте?</b>

				<ul class="ul1">
					<li>Вы получаете возможность оформлять заказы прямо на сайте.</li>
					<li>Вы будете получать информацию о специальных акциях магазина, доступных только зарегистрированным пользователям.</li>
				</ul>

				<p>
					<a href="/users/registration/" onclick="$('#fastRegistrationDescription').hide('slow'); $('#fastRegistration').show('slow'); return false">Заполнить форму регистрации →</a>
				</p>
			</div>
		</xsl:if>

		<div>
			<xsl:if test="fastRegistration/node()">
				<xsl:attribute name="id">fastRegistration</xsl:attribute>
			</xsl:if>

			<h1><xsl:choose>
					<xsl:when test="@id > 0">Анкетные данные</xsl:when>
					<xsl:otherwise>Регистрация нового пользователя</xsl:otherwise>
			</xsl:choose></h1>

			<!-- Выводим ошибку, если она была передана через внешний параметр -->
			<xsl:if test="error/node()">
				<div id="error">
					<xsl:value-of select="error"/>
				</div>
			</xsl:if>

			<p>Обратите внимание, введенные контактные данные будут доступны на странице пользователя
				неограниченному кругу лиц.
				<br />Обязательные поля отмечены *.</p>

			<div class="comment">
				<form action="/users/registration/" method="post" enctype="multipart/form-data">
					<div class="row">
						<div class="caption">Логин</div>
						<div class="field">
							<input name="login" type="text" value="{login}" size="40"/> *
						</div>
					</div>

					<div class="row">
						<div class="caption">Пароль</div>
						<div class="field">
							<input name="password" type="password" value="" size="40"/>

							<!-- Для авторизированного пользователя заполнять пароль при редактирвоании данных необязательно -->
							<xsl:if test="@id = ''"> *</xsl:if>
						</div>
					</div>
					<div class="row">
						<div class="caption">Повтор пароля</div>
						<div class="field">
							<input name="password2" type="password" value="" size="40"/>

							<!-- Для авторизированного пользователя заполнять пароль при редактирвоании данных необязательно -->
							<xsl:if test="@id = ''"> *</xsl:if>
						</div>
					</div>
					<div class="row">
						<div class="caption">E-mail</div>
						<div class="field">
							<input name="email" type="text" value="{email}" size="40"/> *</div>
					</div>
					<div class="row">
						<div class="caption">Имя</div>
						<div class="field">
							<input name="name" type="text" value="{name}" size="40"/>
						</div>
					</div>
					<div class="row">
						<div class="caption">Фамилия</div>
						<div class="field">
							<input name="surname" type="text" value="{surname}" size="40"/>
						</div>
					</div>
					<div class="row">
						<div class="caption">Отчество</div>
						<div class="field">
							<input name="patronymic" type="text" value="{patronymic}" size="40"/>
						</div>
					</div>
					<div class="row">
						<div class="caption">Компания</div>
						<div class="field">
							<input name="company" type="text" value="{company}" size="40"/>
						</div>
					</div>
					<div class="row">
						<div class="caption">Телефон</div>
						<div class="field">
							<input name="phone" type="text" value="{phone}" size="40"/>
						</div>
					</div>
					<div class="row">
						<div class="caption">Факс</div>
						<div class="field">
							<input name="fax" type="text" value="{fax}" size="40"/>
						</div>
					</div>
					<div class="row">
						<div class="caption">Сайт</div>
						<div class="field">
							<input name="website" type="text" value="{website}" size="40"/>
						</div>
					</div>
					<div class="row">
						<div class="caption">ICQ</div>
						<div class="field">
							<input name="icq" type="text" value="{icq}" size="40"/>
						</div>
					</div>
					<div class="row">
						<div class="caption">Страна</div>
						<div class="field">
							<input name="country" type="text" value="{country}" size="40"/>
						</div>
					</div>
					<div class="row">
						<div class="caption">Почтовый индекс</div>
						<div class="field">
							<input name="postcode" type="text" value="{postcode}" size="40"/>
						</div>
					</div>
					<div class="row">
						<div class="caption">Город</div>
						<div class="field">
							<input name="city" type="text" value="{city}" size="40"/>
						</div>
					</div>
					<div class="row">
						<div class="caption">Адрес</div>
						<div class="field">
							<input name="address" type="text" value="{address}" size="40"/>
						</div>
					</div>

					<!-- Внешние параметры -->
					<xsl:if test="count(properties/property)">
						<xsl:apply-templates select="properties/property"/>
					</xsl:if>

					<xsl:if test="@id > 0 and count(maillist) > 0">
						<div class="row">
						<div class="caption" style="vertical-align: top; padding-top: 27px"><strong>Почтовые рассылки</strong></div>
							<div class="field">
								<xsl:apply-templates select="maillist"></xsl:apply-templates>
							</div>
						</div>
					</xsl:if>

					<xsl:if test="not(/siteuser/@id > 0)">
						<div class="row">
							<div class="caption"></div>
							<div class="field">
								<img id="registerUser" class="captcha" src="/captcha.php?id={/siteuser/captcha_id}&amp;height=30&amp;width=100" title="Контрольное число" name="captcha"/>

								<div class="captcha">
									<img src="/images/refresh.png" /> <span onclick="$('#registerUser').updateCaptcha('{/siteuser/captcha_id}', 30); return false">Показать другое число</span>
								</div>
							</div>
						</div>

						<div class="row">
							<div class="caption">
						Контрольное число<sup><font color="red">*</font></sup>
							</div>
							<div class="field">
								<input type="hidden" name="captcha_id" value="{/siteuser/captcha_id}"/>
								<input type="text" name="captcha" size="15"/>
							</div>
						</div>
					</xsl:if>

					<!-- Страница редиректа после авторизации -->
					<xsl:if test="location/node()">
						<input name="location" type="hidden" value="{location}" />
					</xsl:if>

					<!-- Определяем имя кнопки -->
					<xsl:variable name="buttonName"><xsl:choose>
							<xsl:when test="@id > 0">Изменить</xsl:when>
							<xsl:otherwise>Зарегистрироваться</xsl:otherwise>
					</xsl:choose></xsl:variable>

					<div class="row">
						<div class="caption"></div>
						<div class="field">
							<input name="apply" type="submit" value="{$buttonName}" class="button" />
						</div>
					</div>

				</form>
			</div>
		</div>
	</xsl:template>

	<xsl:template match="maillist">
		<xsl:variable name="id" select="@id" />
		<xsl:variable name="maillist_siteuser" select="/siteuser/maillist_siteuser[maillist_id = $id]" />

		<fieldset class="maillist_fieldset">
			<legend><xsl:value-of select="name"/></legend>
			<select name="type_{@id}" >

				<option value="0">
				<xsl:if test="$maillist_siteuser/node() and $maillist_siteuser/type = 0"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
					Текст
				</option>

				<option value="1">
				<xsl:if test="$maillist_siteuser/node() and $maillist_siteuser/type = 1"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
					HTML
				</option>
			</select>

	<input name="maillist_{@id}" type="checkbox" value="1" id="maillist_{@id}"><xsl:if test="$maillist_siteuser/node()" ><xsl:attribute name="checked">checked</xsl:attribute></xsl:if></input>

			<label for="maillist_{@id}">подписаться</label>
		</fieldset>
	</xsl:template>

	<!-- Внешние свойства -->
	<xsl:template match="properties/property">

		<xsl:if test="type != 10">

			<xsl:variable name="id" select="@id" />
			<xsl:variable name="property_value" select="/siteuser/property_value[property_id=$id]" />

			<div class="row">
				<div class="caption"><xsl:value-of select="name" /></div>
				<div class="field">

					<xsl:choose>
						<!-- Отображаем поле ввода -->
						<xsl:when test="type = 0 or type=1">
							<br/>
							<input type="text" name="property_{@id}" value="$property_value/value" size="40" />
						</xsl:when>
						<!-- Отображаем файл -->
						<xsl:when test="type = 2">
							<br/>
							<input type="file" name="property_{@id}" size="35" />

							<xsl:if test="$property_value/file != ''">
								<xsl:text> </xsl:text>
						<a href="{/siteuser/dir}{$property_value/file}" target="_blank"><img src="/hostcmsfiles/images/preview.gif" class="img"/></a><xsl:text> </xsl:text><a href="?delete_property={$property_value/property_id}" onclick="return confirm('Вы уверены, что хотите удалить?')"><img src="/hostcmsfiles/images/delete.gif" class="img" /></a>
							</xsl:if>

						</xsl:when>
						<!-- Отображаем список -->
						<xsl:when test="type = 3">
							<br/>
							<select name="property_{@id}">
								<option value="0">...</option>
								<xsl:apply-templates select="list/list_item"/>
							</select>
						</xsl:when>
						<!-- Большое текстовое поле, Визуальный редактор -->
						<xsl:when test="type = 4 or type = 6">
							<br/>
							<textarea name="property_{@id}" size="40"><xsl:value-of select="$property_value/value" /></textarea>
						</xsl:when>
						<!-- Флажок -->
						<xsl:when test="type = 7">
							<br/>
							<input type="checkbox" name="property_{@id}">
							<xsl:if test="$property_value/value = 1"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if>
							</input>
						</xsl:when>
					</xsl:choose>
				</div>
			</div>
		</xsl:if>

	</xsl:template>

	<xsl:template match="list/list_item">
		<!-- Отображаем список -->
		<xsl:variable name="id" select="../../@id" />
		<option value="{@id}">
		<xsl:if test="/siteuser/property_value[property_id=$id]/value = value"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
			<xsl:value-of disable-output-escaping="yes" select="value"/>
		</option>
	</xsl:template>

</xsl:stylesheet>