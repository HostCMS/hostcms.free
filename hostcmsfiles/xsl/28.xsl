<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "lang://28">
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml" />

	<xsl:template match="/">
		<xsl:choose>
			<!-- Вывод редиректа о регистрации или изменении данных клиента -->
			<xsl:when test="siteuser/success_code/node()">
				<xsl:choose>
					<xsl:when test="siteuser/success_code = 'successfulRegistration'">&labelSuccessfulRegistration;</xsl:when>
					<xsl:when test="siteuser/success_code = 'successfulUpdate'">&labelSuccessfulUpdate;</xsl:when>
					<xsl:otherwise>Unknown Error</xsl:otherwise>
				</xsl:choose>
			</xsl:when>
			<xsl:otherwise>
				<xsl:apply-templates select="siteuser" />
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

	<xsl:template match="siteuser">

		<!-- Быстрая регистрация в магазине -->
		<xsl:if test="fastRegistration/node()">
			<div id="fastRegistrationDescription" style="float: left">
				<p class="h1">&labelLine1;</p>
				<b>&labelLine2;</b>

				<ul class="ul1">
					<li>&labelLine3;</li>
					<li>&labelLine4;</li>
				</ul>

				<p>
					<a href="/users/registration/" onclick="$('#fastRegistrationDescription').hide('slow'); $('#fastRegistration').show('slow'); return false">&labelForm; →</a>
				</p>
			</div>
		</xsl:if>

		<div>
			<xsl:if test="fastRegistration/node()">
				<xsl:attribute name="id">fastRegistration</xsl:attribute>
			</xsl:if>

			<h1><xsl:choose>
				<xsl:when test="@id > 0">&labelData;</xsl:when>
				<xsl:otherwise>&labelNewUser;</xsl:otherwise>
			</xsl:choose></h1>

			<!-- Show Error code -->
			<xsl:if test="error_code/node()">
				<div id="error">
					<xsl:choose>
						<xsl:when test="error_code = 'wrongCaptcha'">&labelMessageWrongCaptcha;</xsl:when>
						<xsl:when test="error_code = 'wrongEmail'">&labelMessageWrongEmail;</xsl:when>
						<xsl:when test="error_code = 'antispam'">&labelMessageAntispam;</xsl:when>
						<xsl:when test="error_code = 'repeatPasswordIncorrect'">&labelMessageRepeatPasswordIncorrect;</xsl:when>
						<xsl:when test="error_code = 'userWithEmailAlreadyExists'">&labelMessageUserWithEmailAlreadyExistst;</xsl:when>
						<xsl:when test="error_code = 'userWithLoginAlreadyExists'">&labelMessageUserWithLoginAlreadyExists;</xsl:when>
						<xsl:when test="error_code = 'requiredFieldsNotFilled'">&labelMessageRequiredFieldsNotFilled;</xsl:when>
						<xsl:when test="error_code = 'wrongCsrf'">&labelMessageWrongCsrf;</xsl:when>
						<xsl:otherwise>Unknown Error</xsl:otherwise>
					</xsl:choose>
				</div>
			</xsl:if>

			<!-- Show Error -->
			<xsl:if test="error/node()">
				<div id="error">
					<xsl:value-of select="error" />
				</div>
			</xsl:if>

			<p>&labelLine5;
				<br />&labelRequired;</p>

			<div class="comment">
				<form action="/users/registration/" method="post" enctype="multipart/form-data">
					<div class="row">
						<div class="caption">&labelLogin;</div>
						<div class="field">
							<input name="login" type="text" value="{login}" size="40" /> *
						</div>
					</div>

					<div class="row">
						<div class="caption">&labelPassword;</div>
						<div class="field">
							<input name="password" type="password" value="" size="40" />

							<!-- Для авторизированного пользователя заполнять пароль при редактирвоании данных необязательно -->
							<xsl:if test="@id = ''"> *</xsl:if>
						</div>
					</div>
					<div class="row">
						<div class="caption">&labelPassword2;</div>
						<div class="field">
							<input name="password2" type="password" value="" size="40" />

							<!-- Для авторизированного пользователя заполнять пароль при редактирвоании данных необязательно -->
							<xsl:if test="@id = ''"> *</xsl:if>
						</div>
					</div>
					<div class="row">
						<div class="caption">&labelEmail;</div>
						<div class="field">
							<input name="email" type="text" value="{email}" size="40" /> *</div>
					</div>

					<div class="row">
						<div class="caption" style="vertical-align: top; padding-top: 27px"><strong>Компании</strong></div>
						<xsl:choose>
							<xsl:when test="@id > 0 and count(siteuser_company)">
								<xsl:apply-templates select="siteuser_company"></xsl:apply-templates>
							</xsl:when>
							<xsl:when test="@id = ''">
								<xsl:call-template name="siteuser_company"></xsl:call-template>
							</xsl:when>
							<xsl:otherwise></xsl:otherwise>
						</xsl:choose>
					</div>

					<div class="row">
						<div class="caption" style="vertical-align: top; padding-top: 27px"><strong>Представители</strong></div>
						<xsl:choose>
							<xsl:when test="@id > 0 and count(siteuser_person)">
								<xsl:apply-templates select="siteuser_person"></xsl:apply-templates>
							</xsl:when>
							<xsl:when test="@id = ''">
								<xsl:call-template name="siteuser_person"></xsl:call-template>
							</xsl:when>
							<xsl:otherwise></xsl:otherwise>
						</xsl:choose>
					</div>

					<!-- Внешние параметры -->
					<xsl:if test="count(properties/property[type != 10])">
						<xsl:apply-templates select="properties/property[type != 10]" />
					</xsl:if>

					<xsl:if test="@id > 0 and count(maillist) > 0">
						<div class="row">
						<div class="caption" style="vertical-align: top; padding-top: 27px"><strong>&labelMaillist;</strong></div>
							<div class="field">
								<xsl:apply-templates select="maillist"></xsl:apply-templates>
							</div>
						</div>
					</xsl:if>

					<xsl:if test="not(/siteuser/@id > 0)">
						<div class="row">
							<div class="caption"></div>
							<div class="field">
								<img id="registerUser" class="captcha" src="/captcha.php?id={/siteuser/captcha_id}&amp;height=30&amp;width=100" title="&labelCaptchaId;" name="captcha" />

								<div class="captcha">
									<img src="/images/refresh.png" /> <span onclick="$('#registerUser').updateCaptcha('{/siteuser/captcha_id}', 30); return false">&labelUpdateCaptcha;</span>
								</div>
							</div>
						</div>

						<div class="row">
							<div class="caption">
						&labelCaptchaId;<sup><font color="red">*</font></sup>
							</div>
							<div class="field">
								<input type="hidden" name="captcha_id" value="{/siteuser/captcha_id}" />
								<input type="text" name="captcha" size="15" />
							</div>
						</div>
					</xsl:if>

					<!-- <xsl:choose>
						<xsl:when test="@id > 0 and count(siteuser_company)">
							<div class="row">
							<div class="caption" style="vertical-align: top; padding-top: 27px"><strong>Компании</strong></div>
								<div class="field">
									<xsl:apply-templates select="siteuser_company"></xsl:apply-templates>
								</div>
							</div>
						</xsl:when>
						<xsl:when test="@id > 0 and count(siteuser_person)">
							<div class="row">
							<div class="caption" style="vertical-align: top; padding-top: 27px"><strong>Представители</strong></div>
								<div class="field">
									<xsl:apply-templates select="siteuser_person"></xsl:apply-templates>
								</div>
							</div>
						</xsl:when>
						<xsl:otherwise>

						</xsl:otherwise>
					</xsl:choose> -->

					<!-- Page Redirect after login -->
					<xsl:if test="location/node()">
						<input name="location" type="hidden" value="{location}" />
					</xsl:if>

					<!-- CSRF-токен -->
					<input name="csrf_token" type="hidden" value="{csrf_token}" />

					<!-- Определяем имя кнопки -->
					<xsl:variable name="buttonName"><xsl:choose>
							<xsl:when test="@id > 0">&labelChange;</xsl:when>
							<xsl:otherwise>&labelRegister;</xsl:otherwise>
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
			<legend><xsl:value-of select="name" /></legend>
			<select name="type_{@id}" >

				<option value="0">
				<xsl:if test="$maillist_siteuser/node() and $maillist_siteuser/type = 0"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
					&labelText;
				</option>

				<option value="1">
				<xsl:if test="$maillist_siteuser/node() and $maillist_siteuser/type = 1"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
					HTML
				</option>
			</select>

			<input name="maillist_{@id}" type="checkbox" value="1" id="maillist_{@id}"><xsl:if test="$maillist_siteuser/node()" ><xsl:attribute name="checked">checked</xsl:attribute></xsl:if></input>

			<label for="maillist_{@id}">&labelSubscribe;</label>
		</fieldset>
	</xsl:template>

	<!-- Строка свойства -->
	<xsl:template name="property_values_show">
		<xsl:param name="property" />
		<xsl:param name="node" select="''" />

		<xsl:variable name="name"><xsl:choose>
			<xsl:when test="string-length($node) &gt; 0">property_<xsl:value-of select="$property/@id" />_<xsl:value-of select="$node/@id" /></xsl:when>
			<xsl:otherwise>property_<xsl:value-of select="$property/@id" />[]</xsl:otherwise>
		</xsl:choose></xsl:variable>

		<xsl:variable name="value"><xsl:choose>
			<xsl:when test="string-length($node) &gt; 0">
				<xsl:value-of select="$node/value" />
			</xsl:when>
			<xsl:otherwise></xsl:otherwise>
		</xsl:choose></xsl:variable>

		<!-- form-group или checkbox -->
		<div class="row">
			<div class="caption">
				<xsl:choose>
					<xsl:when test="string-length($node) &gt; 0 and position() = 1">
						<xsl:value-of select="$property/name" />
					</xsl:when>
					<xsl:when test="string-length($node) = 0">
						<xsl:value-of select="$property/name" />
					</xsl:when>
					<xsl:otherwise></xsl:otherwise>
				</xsl:choose>
			</div>
			<div class="field">
				<xsl:choose>
					<!-- Отображаем поле ввода -->
					<xsl:when test="$property/type = 0 or $property/type = 1">
						<input type="text" name="{$name}" value="{$value}" class="property-row" />
					</xsl:when>
					<!-- Отображаем файл -->
					<xsl:when test="$property/type = 2">
							<label for="file-upload-{position()}" class="custom-file-upload">
								<input id="file-upload-{position()}" class="property-row" type="file" name="{$name}" />
							</label>

							<xsl:if test="string-length($node) &gt; 0 and $node/file != ''">
								<a class="input-group-addon green-text" href="{/siteuser/dir}{$node/file}" target="_blank">
									<i class="fa fa-fw fa-picture-o"></i>
								</a>

								<a class="input-group-addon red-text" href="?delete_property_value={$node/@id}" onclick="return confirm('Вы уверены, что хотите удалить?')">
									<i class="fa fa-fw fa-trash"></i>
								</a>
							</xsl:if>
					</xsl:when>
					<!-- Отображаем список -->
					<xsl:when test="$property/type = 3">
						<select name="{$name}" class="property-row">
							<option value="0">...</option>
							<xsl:apply-templates select="$property/list/list_item" />
						</select>
					</xsl:when>
					<!-- Большое текстовое поле, Визуальный редактор -->
					<xsl:when test="$property/type = 4 or $property/type = 6">
						<textarea name="{$name}" class="property-row"><xsl:value-of select="$value" /></textarea>
					</xsl:when>
					<!-- Флажок -->
					<xsl:when test="$property/type = 7">
						<br/>
						<input type="checkbox" name="{$name}" class="property-row">
							<xsl:if test="$value = 1"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if>
						</input>
					</xsl:when>
				</xsl:choose>

					<!-- <xsl:if test="$property/multiple = 1 and position() = last()">
						<a class="input-group-addon gray-text" onclick="$.addPropertyRow(this, {$property/type})">
							<i class="fa fa-fw fa-plus"></i>
						</a>
					</xsl:if> -->
			</div>
		</div>
	</xsl:template>

	<!-- Внешние свойства -->
	<xsl:template match="properties/property">
		<xsl:if test="type != 10">
			<xsl:variable name="id" select="@id" />
			<xsl:variable name="property" select="." />
			<xsl:variable name="property_value" select="/siteuser/property_value[property_id = $id]" />

			<xsl:choose>
				<xsl:when test="count($property_value)">
					<xsl:for-each select="$property_value">
						<xsl:call-template name="property_values_show">
							<xsl:with-param name="property" select="$property" />
							<xsl:with-param name="node" select="." />
						</xsl:call-template>
					</xsl:for-each>
				</xsl:when>
				<xsl:otherwise>
					<xsl:call-template name="property_values_show">
						<xsl:with-param name="property" select="$property" />
					</xsl:call-template>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:if>
	</xsl:template>

	<xsl:template match="list/list_item">
		<!-- Отображаем список -->
		<xsl:variable name="id" select="../../@id" />
		<option value="{@id}">
		<xsl:if test="/siteuser/property_value[property_id=$id]/value = value"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
			<xsl:value-of disable-output-escaping="yes" select="value" />
		</option>
	</xsl:template>

	<xsl:template name="siteuser_company" match="siteuser_company">
		<xsl:variable name="suffix"><xsl:choose><xsl:when test="@id > 0"><xsl:value-of select="@id" /></xsl:when><xsl:otherwise>[]</xsl:otherwise></xsl:choose></xsl:variable>

		<div style="margin-bottom: 15px;">
			<div class="row">
				<div class="field">
					<input type="text" name="company_name{$suffix}" value="{name}" placeholder="Название" class="width2" style="width: 95%" />
				</div>
			</div>

			<xsl:if test="/siteuser/@id = ''">
				<div class="row">
					<div class="field">
						<input type="text" name="company_address{$suffix}" value="{name}" placeholder="Адрес" class="width2" style="width: 95%" />
					</div>
				</div>

				<div class="row">
					<div class="field">
						<input type="text" name="company_phone{$suffix}" value="{name}" placeholder="Телефон" class="width2" style="width: 95%" />
					</div>
				</div>

				<div class="row">
					<div class="field">
						<input type="text" name="company_email{$suffix}" value="{name}" placeholder="E-mail" class="width2" style="width: 95%" />
					</div>
				</div>

				<div class="row">
					<div class="field">
						<input type="text" name="company_website{$suffix}" value="{name}" placeholder="Сайт" class="width2" style="width: 95%" />
					</div>
				</div>
			</xsl:if>

			<xsl:if test="/siteuser/@id > 0 and count(directory_address)">
				<div class="row">
					<div class="field">
						<fieldset class="maillist_fieldset">
							<legend>Адреса</legend>
							<xsl:apply-templates select="directory_address" mode="siteuser_company">
								<xsl:with-param name="siteuser_company_id" select="@id" />
							</xsl:apply-templates>
						</fieldset>
					</div>
				</div>
			</xsl:if>

			<xsl:if test="count(directory_phone)">
				<div class="row">
					<div class="field">
						<fieldset class="maillist_fieldset">
							<legend>Телефоны</legend>
							<xsl:apply-templates select="directory_phone" mode="siteuser_company">
								<xsl:with-param name="siteuser_company_id" select="@id" />
							</xsl:apply-templates>
						</fieldset>
					</div>
				</div>
			</xsl:if>

			<xsl:if test="count(directory_email)">
				<div class="row">
					<div class="field">
						<fieldset class="maillist_fieldset">
							<legend>E-mail</legend>
							<xsl:apply-templates select="directory_email" mode="siteuser_company">
								<xsl:with-param name="siteuser_company_id" select="@id" />
							</xsl:apply-templates>
						</fieldset>
					</div>
				</div>
			</xsl:if>

			<xsl:if test="count(directory_website)">
				<div class="row">
					<div class="field">
						<fieldset class="maillist_fieldset">
							<legend>Сайты</legend>
							<xsl:apply-templates select="directory_website" mode="siteuser_company">
								<xsl:with-param name="siteuser_company_id" select="@id" />
							</xsl:apply-templates>
						</fieldset>
					</div>
				</div>
			</xsl:if>
		</div>
	</xsl:template>

	<xsl:template name="siteuser_person" match="siteuser_person">
		<xsl:variable name="suffix"><xsl:choose><xsl:when test="@id > 0"><xsl:value-of select="@id" /></xsl:when><xsl:otherwise>[]</xsl:otherwise></xsl:choose></xsl:variable>

		<div style="margin-bottom: 15px;">
			<div class="row">
				<div class="field">
					<input type="text" name="person_name{$suffix}" placeholder="Имя" value="{name}" class="width1" />
					<input type="text" name="person_surname{$suffix}" placeholder="Фамилия" value="{surname}" class="width1" />
					<input type="text" name="person_patronymic{$suffix}" placeholder="Отчество" value="{patronymic}" class="width1" />
				</div>
			</div>
			<!-- <div class="row">
				<div class="field">
					<input type="text" name="person_postcode{$suffix}" placeholder="Индекс" value="{postcode}" class="width1" />
					<input type="text" name="person_country{$suffix}" placeholder="Страна" value="{country}" class="width1" />
					<input type="text" name="person_city{$suffix}" placeholder="Город" value="{city}" class="width1" />
				</div>
			</div> -->

			<xsl:if test="/siteuser/@id = ''">
				<div class="row">
					<div class="field">
						<input type="text" name="person_address{$suffix}" placeholder="Адрес" value="{address}" class="width2" style="width: 95%" />
					</div>
				</div>

				<div class="row">
					<div class="field">
						<input type="text" name="person_phone{$suffix}" value="{name}" placeholder="Телефон" class="width2" style="width: 95%" />
					</div>
				</div>

				<div class="row">
					<div class="field">
						<input type="text" name="person_email{$suffix}" value="{name}" placeholder="E-mail" class="width2" style="width: 95%" />
					</div>
				</div>

				<div class="row">
					<div class="field">
						<input type="text" name="person_website{$suffix}" value="{name}" placeholder="Сайт" class="width2" style="width: 95%" />
					</div>
				</div>
			</xsl:if>

			<xsl:if test="/siteuser/@id > 0 and count(directory_address)">
				<div class="row">
					<div class="field">
						<fieldset class="maillist_fieldset">
							<legend>Адреса</legend>
							<xsl:apply-templates select="directory_address" mode="siteuser_person">
								<xsl:with-param name="siteuser_person_id" select="@id" />
							</xsl:apply-templates>
						</fieldset>
					</div>
				</div>
			</xsl:if>

			<xsl:if test="count(directory_phone)">
				<div class="row">
					<div class="field">
						<fieldset class="maillist_fieldset">
							<legend>Телефоны</legend>
							<xsl:apply-templates select="directory_phone" mode="siteuser_person">
								<xsl:with-param name="siteuser_person_id" select="@id" />
							</xsl:apply-templates>
						</fieldset>
					</div>
				</div>
			</xsl:if>

			<xsl:if test="count(directory_email)">
				<div class="row">
					<div class="field">
						<fieldset class="maillist_fieldset">
							<legend>E-mail</legend>
							<xsl:apply-templates select="directory_email" mode="siteuser_person">
								<xsl:with-param name="siteuser_person_id" select="@id" />
							</xsl:apply-templates>
						</fieldset>
					</div>
				</div>
			</xsl:if>

			<xsl:if test="count(directory_website)">
				<div class="row">
					<div class="field">
						<fieldset class="maillist_fieldset">
							<legend>Сайты</legend>
							<xsl:apply-templates select="directory_website" mode="siteuser_person">
								<xsl:with-param name="siteuser_person_id" select="@id" />
							</xsl:apply-templates>
						</fieldset>
					</div>
				</div>
			</xsl:if>
		</div>
	</xsl:template>

	<xsl:template match="directory_address" mode="siteuser_company">
		<xsl:param name="siteuser_company_id" />

		<div class="row">
			<div class="field">
				<input type="text" name="company_{$siteuser_company_id}_address_{@id}" value="{value}" class="width1" />
				<select name="company_{$siteuser_company_id}_directory_address_type_{@id}">
					<xsl:apply-templates select="/siteuser/directory_address_types/directory_address_type">
						<xsl:with-param name="directory_address_type_id" select="directory_address_type_id" />
					</xsl:apply-templates>
				</select>
			</div>
		</div>
	</xsl:template>

	<xsl:template match="directory_address" mode="siteuser_person">
		<xsl:param name="siteuser_person_id" />

		<div class="row">
			<div class="field">
				<input type="text" name="person_{$siteuser_person_id}_address_{@id}" value="{value}" class="width1" />
				<select name="person_{$siteuser_person_id}_directory_address_type_{@id}">
					<xsl:apply-templates select="/siteuser/directory_address_types/directory_address_type">
						<xsl:with-param name="directory_address_type_id" select="directory_address_type_id" />
					</xsl:apply-templates>
				</select>
			</div>
		</div>
	</xsl:template>

	<xsl:template match="directory_address_type">
		<xsl:param name="directory_address_type_id" />

		<option value="{@id}">
			<xsl:if test="@id = $directory_address_type_id"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
			<xsl:value-of select="name" />
		</option>
	</xsl:template>

	<xsl:template match="directory_phone" mode="siteuser_person">
		<xsl:param name="siteuser_person_id" />

		<div class="row">
			<div class="field">
				<input type="text" name="person_{$siteuser_person_id}_phone_{@id}" value="{value}" class="width1" />
				<select name="person_{$siteuser_person_id}_directory_phone_type_{@id}">
					<xsl:apply-templates select="/siteuser/directory_phone_types/directory_phone_type">
						<xsl:with-param name="directory_phone_type_id" select="directory_phone_type_id" />
					</xsl:apply-templates>
				</select>
			</div>
		</div>
	</xsl:template>

	<xsl:template match="directory_phone" mode="siteuser_company">
		<xsl:param name="siteuser_company_id" />

		<div class="row">
			<div class="field">
				<input type="text" name="company_{$siteuser_company_id}_phone_{@id}" value="{value}" class="width1" />
				<select name="company_{$siteuser_company_id}_directory_phone_type_{@id}">
					<xsl:apply-templates select="/siteuser/directory_phone_types/directory_phone_type">
						<xsl:with-param name="directory_phone_type_id" select="directory_phone_type_id" />
					</xsl:apply-templates>
				</select>
			</div>
		</div>
	</xsl:template>

	<xsl:template match="directory_phone_type">
		<xsl:param name="directory_phone_type_id" />

		<option value="{@id}">
			<xsl:if test="@id = $directory_phone_type_id"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
			<xsl:value-of select="name" />
		</option>
	</xsl:template>

	<xsl:template match="directory_email" mode="siteuser_person">
		<xsl:param name="siteuser_person_id" />

		<div class="row">
			<div class="field">
				<input type="text" name="person_{$siteuser_person_id}_email_{@id}" value="{value}" class="width1" />
				<select name="person_{$siteuser_person_id}_directory_email_type_{@id}">
					<xsl:apply-templates select="/siteuser/directory_email_types/directory_email_type">
						<xsl:with-param name="directory_email_type_id" select="directory_email_type_id" />
					</xsl:apply-templates>
				</select>
			</div>
		</div>
	</xsl:template>

	<xsl:template match="directory_email" mode="siteuser_company">
		<xsl:param name="siteuser_company_id" />

		<div class="row">
			<div class="field">
				<input type="text" name="company_{$siteuser_company_id}_email_{@id}" value="{value}" class="width1" />
				<select name="company_{$siteuser_company_id}_directory_email_type_{@id}">
					<xsl:apply-templates select="/siteuser/directory_email_types/directory_email_type">
						<xsl:with-param name="directory_email_type_id" select="directory_email_type_id" />
					</xsl:apply-templates>
				</select>
			</div>
		</div>
	</xsl:template>

	<xsl:template match="directory_email_type">
		<xsl:param name="directory_email_type_id" />

		<option value="{@id}">
			<xsl:if test="@id = $directory_email_type_id"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
			<xsl:value-of select="name" />
		</option>
	</xsl:template>

	<xsl:template match="directory_website" mode="siteuser_person">
		<xsl:param name="siteuser_person_id" />

		<div class="row">
			<div class="field">
				<input type="text" name="person_{$siteuser_person_id}_website_{@id}" value="{value}" class="width1" />
			</div>
		</div>
	</xsl:template>

	<xsl:template match="directory_website" mode="siteuser_company">
		<xsl:param name="siteuser_company_id" />

		<div class="row">
			<div class="field">
				<input type="text" name="company_{$siteuser_company_id}_website_{@id}" value="{value}" class="width1" />
			</div>
		</div>
	</xsl:template>
</xsl:stylesheet>