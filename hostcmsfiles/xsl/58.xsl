<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "lang://58">
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>

	<xsl:template match="/siteuser">

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
					<a href="/users/registration/" onclick="$('#fastRegistrationDescription').hide('slow'); $('#fastRegistration').show('slow'); return false">&labelForm;</a>
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

			<!-- Show Error -->
			<xsl:if test="error/node()">
				<div id="error">
					<xsl:value-of select="error"/>
				</div>
			</xsl:if>

			<p>&labelLine5;
				<br />&labelRequired;</p>

			<div class="comment">
				<form action="/users/registration/" method="post" enctype="multipart/form-data">
					<div class="row">
						<div class="caption">&labelLogin;</div>
						<div class="field">
							<input name="login" type="text" value="{login}" size="35"/> *
						</div>
					</div>

					<div class="row">
						<div class="caption">&labelPassword;</div>
						<div class="field">
							<input name="password" type="password" value="" size="35"/>

							<!-- Для авторизированного пользователя заполнять пароль при редактирвоании данных необязательно -->
							<xsl:if test="@id = 0"> *</xsl:if>
						</div>
					</div>
					<div class="row">
						<div class="caption">&labelEmail;</div>
						<div class="field">
							<input name="email" type="text" value="{email}" size="35"/> *</div>
					</div>
					<div class="row">
						<div class="caption">&labelName;</div>
						<div class="field">
							<input name="name" type="text" value="{name}" size="35"/>
						</div>
					</div>
					<div class="row">
						<div class="caption">&labelSurname;</div>
						<div class="field">
							<input name="surname" type="text" value="{surname}" size="35"/>
						</div>
					</div>

					<xsl:if test="not(/siteuser/@id > 0)">
						<div class="row">
							<div class="caption"></div>
							<div class="field">
								<img id="registerUser" class="captcha" src="/captcha.php?id={/siteuser/captcha_id}&amp;height=30&amp;width=100" title="&labelCaptchaId;" name="captcha"/>

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
								<input type="hidden" name="captcha_id" value="{/siteuser/captcha_id}"/>
								<input type="text" name="captcha" size="15"/>
							</div>
						</div>
					</xsl:if>

					<!-- Page Redirect after login -->
					<xsl:if test="location/node()">
						<input name="location" type="hidden" value="{location}" />
					</xsl:if>

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

</xsl:stylesheet>