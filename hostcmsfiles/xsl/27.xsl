<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>
	
	<xsl:template match="/siteuser">
		
		<xsl:choose>
			<!-- Авторизованный пользователь -->
			<xsl:when test="@id > 0">
				<h1>Пользователь <xsl:value-of select="login" /></h1>
				
				<!-- Выводим меню -->
				<ul class="users">
					<xsl:apply-templates select="item"/>
				</ul>
			</xsl:when>
			<!-- Неавторизованный пользователь -->
			<xsl:otherwise>
				<div class="authorization">
					<h1>Личный кабинет</h1>
					
					<!-- Выводим ошибку, если она была передана через внешний параметр -->
					<xsl:if test="error/node()">
						<div id="error">
							<xsl:value-of select="error"/>
						</div>
					</xsl:if>
					
					<form action="/users/" method="post">
						<p>Пользователь:
							<br /><input name="login" type="text" size="30" class="large" />
						</p>
						<p>Пароль:
							<br /><input name="password" type="password" size="30" class="large" />
						</p>
						<p>
							<label><input name="remember" type="checkbox" /> Запомнить меня на сайте.</label>
						</p>
						<input name="apply" type="submit" value="Войти" class="button" />
						
						<!-- Страница редиректа после авторизации -->
						<xsl:if test="location/node()">
							<input name="location" type="hidden" value="{location}" />
						</xsl:if>
					</form>
					
				<p>Первый раз на сайте? — <a href="/users/registration/">Зарегистрируйтесь</a>!</p>
					
				<p>Забыли пароль? Мы можем его <a href="/users/restore_password/">восстановить</a>.</p>
				</div>
				
				<xsl:if test="count(site/siteuser_identity_provider)">
					<div class="authorization">
						
						<h1>OAuth</h1>
						
						<xsl:for-each select="site/siteuser_identity_provider[image != '' and type = 1]">
							<xsl:element name="a">
								<xsl:attribute name="href">
									?oauth_provider=<xsl:value-of select="@id"/>
								</xsl:attribute>
								<img src="{dir}{image}" alt="{name}"/>
							</xsl:element>&#160;
						</xsl:for-each>
					
						<h1>OpenID</h1>
						
						<!-- Выводим ошибку, если она была передана через внешний параметр -->
						<xsl:if test="provider_error/node()">
							<div id="error">
								<xsl:value-of select="provider_error"/>
							</div>
						</xsl:if>
						
						<form action="/users/" method="post">
							<p>Войти с помощью:</p>
							<xsl:for-each select="site/siteuser_identity_provider[image != '' and type = 0]">
								<label>
									<input type="radio" name="identity_provider" value="{@id}">
										<xsl:if test="position() = 1">
											<xsl:attribute name="checked">checked</xsl:attribute>
										</xsl:if>
									</input> <img src="{dir}{image}" alt="{name}" title="{name}" />
								</label>
							</xsl:for-each>
							
							<p>Логин в выбранном сервисе:
								<br /><input name="openid_login" type="text" size="30" class="large" />
							</p>
							
							<input name="applyOpenIDLogin" type="submit" value="Войти" class="button" />
						</form>
						
						<form action="/users/" method="post">
							<p>или введите OpenID вручную:
								<br /><input name="openid" type="text" size="30" class="large" />
							</p>
							<input name="applyOpenID" type="submit" value="Войти" class="button" />
						</form>
					</div>
				</xsl:if>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	
	<xsl:template match="item">
		<li style="background: url('{image}') no-repeat 11px 5px">
			<a href="{path}">
				<xsl:value-of select="name"/>
			</a>
		</li>
	</xsl:template>
</xsl:stylesheet>