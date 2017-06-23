<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict"
		doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8"
		indent="yes" method="html" omit-xml-declaration="no" version="1.0"
		media-type="text/xml" />
	
	<xsl:template match="/forum">
		<h1>Форумы</h1>
		<xsl:if test="error != ''">
			<div id="error">
				<xsl:value-of disable-output-escaping="yes" select="error" />
			</div>
		</xsl:if>
		
		<xsl:if test = "siteuser/node()">
	<div style="float: right"><strong><a href="{url}myPosts/">Мои сообщения</a></strong></div>
		</xsl:if>
		
		<div style="clear: both; height: 10px"></div>
		
		<table class="table_group_forums" width="100%">
			<xsl:apply-templates select="forum_group"></xsl:apply-templates>
		</table>
		
		<xsl:if	test="last_siteuser/siteuser/node()">
			<p>
				<xsl:text>Последний зарегистрированный пользователь: </xsl:text><img style="margin: 0px 5px -4px 0px;" src="/hostcmsfiles/images/user.gif" /><a href="/users/info/{last_siteuser/siteuser/login}/">
					<xsl:value-of select="last_siteuser/siteuser/login" />
				</a>
			</p>
		</xsl:if>
		
		<!--
		Форма идентификации пользователя на сайте или приветствия
		залогинившегося пользователя
		-->
		<table class="table_identification" border="1">
			<tr class="row_title_identification">
				<xsl:choose>
					<xsl:when test="not(siteuser/node())">
						<td align="center">
							<b>Авторизация</b>
						</td>
					</xsl:when>
					<xsl:otherwise>
						<td align="center">
							Добро пожаловать,
							<span class="name_users">
								<xsl:value-of select="siteuser/login" />
							</span>!
						</td>
					</xsl:otherwise>
				</xsl:choose>
			</tr>
			<tr>
				<td align="left" style="padding-left: 5px">
					<xsl:if test="error_reg != ''">
						<div id="error">
							<xsl:choose>
								<xsl:when test="error_reg = -1">
									Введен некорректный электронный адрес
								</xsl:when>
								<xsl:when test="error_reg = -2">
									Пользователь с указанным электронным адресом зарегистрирован
									ранее
								</xsl:when>
								<xsl:when test="error_reg = -3">
									Пользователь с указанным логином зарегистрирован ранее
								</xsl:when>
								<xsl:when test="error_reg = -4">
									Заполните, пожалуйста, все обязательные параметры
								</xsl:when>
								<xsl:otherwise></xsl:otherwise>
							</xsl:choose>
						</div>
					</xsl:if>
					
					<div id="div_form" style="margin-top: 10px; margin-bottom: 5px;">
						<xsl:choose>
							<xsl:when test="not(siteuser/node())">
								<input id="rad1" type="radio" name="autoriz" value="reg_user" onclick="HideShow('new', 'auto')">
									<xsl:if test="not(/forum/quick/node() and /forum/quick='quick')">
										<xsl:attribute name="checked">checked</xsl:attribute>
									</xsl:if>
								</input>
								<label for="rad1" id="lab1">Зарегистрированный пользователь</label>
								
								<input id="rad2" type="radio" name="autoriz" value="new_user" onclick="HideShow('auto', 'new')">
									<xsl:if test="/forum/quick/node() and /forum/quick='quick'">
										<xsl:attribute name="checked">checked</xsl:attribute>
									</xsl:if>
								</input>
								<label for="rad2" id="lab2">Новый пользователь</label>
								
								<div id="auto" style="margin-left: 0px">
									<form name="mainform" action="/users/" method="post">
										Логин:
										<input name="login" type="text" size="12" value="" />
										Пароль:
										<input name="password" type="password" size="12" value="" /><xsl:text> </xsl:text>
										<input name="apply" class="button" type="submit" value="Войти" />
										<br />
										<input type="hidden" name="location" value="{url}" />
									</form>
								</div>
								
								<div id="new" style="display: none; margin-left: 0px">
									<div class="comment" style="width: 430px">
										<form name="mainform1" action="/users/registration/" method="post">
											<div class="row">
										<div class="caption">Логин<sup><font color="red">*</font></sup></div>
												<div class="field"><input type="text" size="40" value="" name="login" /></div>
											</div>
											<div class="row">
										<div class="caption">Пароль<sup><font color="red">*</font></sup></div>
												<div class="field"><input type="password" size="40" value="" name="password"/></div>
											</div>
											<div class="row">
										<div class="caption">E-mail<sup><font color="red">*</font></sup></div>
												<div class="field"><input type="text" size="40" value="" name="email" /></div>
											</div>
											
											
											<div class="row">
												<div class="caption"></div>
												<div class="field">
													<img name="captcha" title="Контрольное число" src="/captcha.php?id={captcha_id}&amp;height=30&amp;width=100" class="captcha" id="registerUser"/>
													<div class="captcha">
														<img src="/images/refresh.png" />
														<span onclick="$('#registerUser').updateCaptcha('{captcha_id}', 30); return false">Показать другое число</span>
													</div>
												</div>
											</div>
											<div class="row">
												<div class="caption">
										Контрольное число<sup><font color="red">*</font></sup></div>
												<div class="field">
													<input type="hidden" name="captcha_id" value="{captcha_id}"/>
													<input type="text" size="15" name="captcha" />
												</div>
											</div>
											<div class="row">
												<div class="caption"></div>
												<div class="field">
													<input type="submit" class="button" value="Зарегистрироваться" name="apply" />
												</div>
											</div>
										</form>
									</div>
								</div>
								<xsl:choose>
									<xsl:when test="/forum/quick/node() and /forum/quick='quick'">
										<SCRIPT>
											<xsl:comment>
												<xsl:text disable-output-escaping="yes">
													<![CDATA[
													HideShow('auto', 'new');
													]]>
												</xsl:text>
											</xsl:comment>
										</SCRIPT>
									</xsl:when>
									<xsl:otherwise>
										<SCRIPT>
											<xsl:comment>
												<xsl:text disable-output-escaping="yes">
													<![CDATA[
													HideShow('new', 'auto');
													]]>
												</xsl:text>
											</xsl:comment>
										</SCRIPT>
									</xsl:otherwise>
								</xsl:choose>
							</xsl:when>
							<xsl:otherwise>
								<div align="center">
									<a href="/users/">Кабинет пользователя</a>
								</div>
							</xsl:otherwise>
						</xsl:choose>
					</div>
				</td>
			</tr>
		</table>
	</xsl:template>
	
	<!-- Форумы -->
	<xsl:template match="forum_category">
		<!-- Шаблон для вывода строки форума -->
		<xsl:param name="group_id" select="0" />
		<tr id="{$group_id}_{position()}">
			<td align="center" width="40">
				<!-- Атрибуты форума -->
				<xsl:if test="closed=1 and new_posts=1">
					<img src="/hostcmsfiles/forum/forum_locked_new.gif" title="Закрытый форум с новыми сообщениями"
					alt="X+"></img>
				</xsl:if>
				<xsl:if test="closed=1 and new_posts=0">
					<img src="/hostcmsfiles/forum/forum_locked.gif" title="Закрытый форум без новых сообщений"
					alt="X-"></img>
				</xsl:if>
				<xsl:if test="closed=0 and new_posts=1">
					<img src="/hostcmsfiles/forum/forum_new.gif" title="Открытый форум с новыми сообщениями"
					alt="O+"></img>
				</xsl:if>
				<xsl:if test="closed=0 and new_posts=0">
					<img src="/hostcmsfiles/forum/forum.gif" title="Открытый форум без новых сообщений"
					alt="O-"></img>
				</xsl:if>
			</td>
			<td>
				<!-- Название и описание форума -->
				<a href="{/forum/url}{@id}/">
					<xsl:value-of select="name" />
				</a>
				<br />
				<xsl:value-of select="description" />
			</td>
			<td align="left">
				<xsl:choose>
					<xsl:when test="forum_topic/node()">
						<!-- Длина названия темы -->
						<xsl:variable name="lenght_theme_name" select="string-length(forum_topic/last/forum_topic_post/subject)"/>
						
						<!-- Формируем название темы -->
						<xsl:variable name="total_theme_name"><xsl:choose>
								<!-- Длина названия темы больше 30 символов -->
								<xsl:when test="$lenght_theme_name > 50">
									<!-- Получаем первые 30 символов названия темы -->
									<xsl:variable name="theme_name_30" select="substring(forum_topic/last/forum_topic_post/subject, 1, 30)" />
									
									<!-- Получаем подстроку из названия темы начиная с 30 символа до первого пробела -->
									<xsl:variable name="theme_name_appendix" select="substring-before(substring(forum_topic/last/forum_topic_post/subject, 31), ' ')" />
									
									<!-- После 30 символа в названиии темы отсутствуют пробелы -->
									<xsl:choose>
										<xsl:when test="string-length($theme_name_appendix) = 0">
											<xsl:value-of select="substring(forum_topic/last/forum_topic_post/subject, 0, 50)"/>
										</xsl:when>
										<xsl:otherwise>
											<xsl:value-of select="$theme_name_30"/> <xsl:value-of select="$theme_name_appendix"/>
										</xsl:otherwise>
									</xsl:choose>
									
									<xsl:if test="$lenght_theme_name > 50">...</xsl:if>
									
								</xsl:when>
								<xsl:otherwise><xsl:value-of select="forum_topic/last/forum_topic_post/subject"/></xsl:otherwise>
						</xsl:choose></xsl:variable>
						
						
					<strong><a href="{/forum/url}{@id}/{forum_topic/@id}/"><xsl:value-of select="$total_theme_name" /></a></strong>
						<br />
					от<xsl:text> </xsl:text><img src="/hostcmsfiles/images/user.gif" style="margin: 0px 5px -4px 0px;"/>
						
						<!-- Автор последнего сообщения -->
						<xsl:choose>
							<xsl:when test="not(forum_topic/last/forum_topic_post/siteuser/login/node())">
								Гость
							</xsl:when>
							<xsl:otherwise>
								<a href="/users/info/{forum_topic/last/forum_topic_post/siteuser/login}/"><xsl:value-of select="forum_topic/last/forum_topic_post/siteuser/login" /></a>
							</xsl:otherwise>
						</xsl:choose>
						
						<!-- Дата последнего сообщения -->
						<br /><xsl:value-of select="forum_topic/last/forum_topic_post/datetime" />
					</xsl:when>
					<xsl:otherwise>Нет сообщений</xsl:otherwise>
				</xsl:choose>
				
			</td>
			<td align="center" width="40">
				<xsl:value-of select="count_topics" />
			</td>
			<td align="center" width="80">
				<!-- Количество тем и сообщений в форуме -->
				<xsl:value-of select="count_topic_posts" />
			</td>
			
		</tr>
	</xsl:template>
	
	<!-- Конец шаблона вывода строк форумов -->
	<xsl:template match="forum_group">
		<tr>
			<!-- Скрываем/открываем форумы текущей группы -->
			<td class="row_title_group_forums"></td>
			<td style="padding:0px">
				<!-- Шапка группы форумов -->
				<table width="100%" class="table_group_title" cellspacing="0"
					cellpadding="0">
					<tr>
						<td border="0">
							<span class="title_group_forums">
								<xsl:value-of select="name" />
							</span>
							<xsl:if test="description != 0">
								<br />
								<span class="desc_group_forums">
									<xsl:value-of select="description" />
								</span>
							</xsl:if>
						</td>
					</tr>
				</table>
			</td>
			<td align="center" width="200" class="row_title_group_forums">Последнее сообщение</td>
			<td align="center" width="40" class="row_title_group_forums">Тем</td>
			<td align="center" width="80" class="row_title_group_forums">Сообщений</td>
		</tr>
		<xsl:apply-templates select="forum_category">
			<!-- Вызов шаблона строк форумов -->
			<xsl:with-param name="group_id" select="@id" />
		</xsl:apply-templates>
	</xsl:template>
</xsl:stylesheet>