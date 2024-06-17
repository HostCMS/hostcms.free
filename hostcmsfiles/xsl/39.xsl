<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "lang://39">
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>

	<!-- СообщенияТемы -->
	<xsl:template match="/forum">
		<!-- Topic Subject -->
		<h1>
			<xsl:value-of select="forum_topic/forum_topic_post/subject"/>
		</h1>

		<!-- Вывод сообщения о событии, если оно есть -->
		<xsl:if test="error/node()">
			<div id="error">
				<xsl:value-of disable-output-escaping="yes" select="error"/>
			</div>
		</xsl:if>

		<script type="text/javascript">
			<xsl:comment>
				<xsl:text disable-output-escaping="yes">
					<![CDATA[
					$(document).ready(function() {
					$("#new_message").bbedit({lang: 'ru'});

					$("#form_add_post").validate({
					focusInvalid: true,
					errorClass: "input_error"
					});
					});

					function Reply(title)
					{
					$("#post_title")
					.val("Re: "+title.replace("&amp;quot;","\""))
					.focus();
					}

					function GetSelection()
					{
					if (window.getSelection)
					{
					selectedtext = window.getSelection().toString();
					}
					else if (document.getSelection)
					{
					selectedtext = document.getSelection();
					}
					else if (document.selection)
					{
					selectedtext = document.selection.createRange().text;
					}
					}

					function Quote(author,text)
					{
					$("#new_message")
					.focus()
					.val($("#new_message").val() + "[quote=" + author + "]" + text + "[/quote]");
					}
					]]>
				</xsl:text>
			</xsl:comment>
		</script>

		<!-- Хлебные крошки -->
		<p>
			<a href="{url}">&labelForumsList;</a>
			<span><xsl:text> → </xsl:text></span>
			<a href="{url}{forum_category/@id}/">
				<xsl:value-of select="forum_category/name"/>
			</a>
		</p>

		<!-- <xsl:if test = "valid_user/node() and valid_user != '' ">
	<div style="float: right"><strong><a href="{forums_path}posts/">Мои сообщения</a></strong></div>
		</xsl:if> -->

		<!--
<xsl:variable name="current_siteuser_id"><xsl:choose><xsl:when test="/forum/siteuser/node()"><xsl:value-of select="/forum/siteuser/@id"/></xsl:when><xsl:otherwise>0</xsl:otherwise></xsl:choose></xsl:variable>

		<xsl:variable name="moderator">
			<xsl:choose><xsl:when test = "/forum/moderators/siteuser/node()">
			<xsl:choose><xsl:when test="/forum/moderators//siteuser[@id = $current_siteuser_id]/node()">1</xsl:when><xsl:otherwise>0</xsl:otherwise></xsl:choose>
	</xsl:when><xsl:otherwise>0</xsl:otherwise></xsl:choose></xsl:variable>
		-->

		<div style="clear: both; height: 10px"></div>
<xsl:variable name="path_page"><xsl:choose><xsl:when test="page != 0">/page-<xsl:value-of select="page + 1"/></xsl:when><xsl:otherwise></xsl:otherwise></xsl:choose></xsl:variable>
		<form id="form_add_post" name="mainform" action="{url}{forum_category/@id}/{forum_topic/@id}{$path_page}/" method="post">
			<table class="table_messages" border="0">
				<!-- Выводим сообщения темы -->
				<xsl:apply-templates select="forum_topic_post"/>
			</table>

			<!-- Если тема и форум не закрыты -->
			<!--<xsl:if test="$moderator = 1 or not(forum_topic/closed = 1 or forum_category/closed = 1)">-->
				<xsl:if test="forum_topic/allow_add_post = 1">

					<!-- Строка добавления нового сообщения -->
					<div class="add_message_table">

						<div class="add_row">
							<!-- Заголовок темы-->
							<div style="float: left; width: 50px; margin-top: 2px">&labelSubject;</div>

				<xsl:variable name="post_title"><xsl:choose><xsl:when test="error_post_title/node()"><xsl:value-of select="error_post_title"/></xsl:when><xsl:otherwise>Re: <xsl:value-of select="forum_topic/forum_topic_post/subject" /></xsl:otherwise></xsl:choose></xsl:variable>

							<div style="margin-left: 50px; padding-right: 10px"><input id="post_title" style="width: 97%;" name="post_title" value="{$post_title}" type="text"/></div>
						</div>

						<!-- Якорь -->
						<a name="answer"/>
						<div class="add_row" style="padding-right: 10px">
							<textarea id="new_message" rows="9" name="post_text" style="width: 97%;">
								<xsl:if test="error_post_text/node()"><xsl:value-of select="error_post_text"/></xsl:if>
							</textarea>
						</div>

						<!-- Если показывать CAPTCHA -->
						<xsl:if test="not(siteuser/node()) and forum_category/use_captcha = 1">
							<div class="add_row">
								<div>
									&labelCaptchaId;
									<input type="text" name="captcha" size="5" class="required" minlength="4" title="&labelEnterNumber;"/>

									<div class="captcha" style="margin: 5px 0 5px 125px">
										<img id="addForumPost" class="captcha" src="/captcha.php?id={forum_category/captcha_id}&amp;height=30&amp;width=100" title="&labelCaptchaId;" name="captcha"/>
									<div><img src="/images/refresh.png" /> <span onclick="$('#addForumPost').updateCaptcha('{forum_category/captcha_id}', 30); return false">&labelUpdateCaptcha;</span></div>
									</div>
								</div>
								<input type="hidden" name="captcha_id" value="{forum_category/captcha_id}"/>
							</div>
						</xsl:if>

							<!--<xsl:if test="not(theme_close = 1 or forums_close = 1)">-->
							<div class="add_row">
								<input border="0" name="add_post" type="submit" class="button" value="&labelAddMessage;"/>
							</div>
							<!--</xsl:if>-->
					</div>
				</xsl:if>

				<p>
					<!-- Pagination -->
					<xsl:call-template name="for">
						<xsl:with-param name="items_on_page" select="limit"/>
						<xsl:with-param name="current_page" select="page"/>
						<xsl:with-param name="count_items" select="forum_topic/count_posts"/>
						<xsl:with-param name="visible_pages" select="6"/>
					</xsl:call-template>
				</p>
				<div style="clear: both"></div>

				<input type="hidden" name="page_theme" value="{current_page_theme}"/>
				<input type="hidden" name="page_message" value="{current_page_message}"/>
			</form>

			<!-- Форма идентификации пользователя на сайте или приветствия залогинившегося пользователя -->
			<table class="table_identification" border="1">
				<tr class="row_title_identification">
					<xsl:choose>
						<xsl:when test="not(siteuser/login/node())">
							<td align="center">
								<b>&labelAuthorization;</b>
							</td>
						</xsl:when>
						<xsl:otherwise>
						<td align="center">&labelWelcome; <span class="name_users"><xsl:value-of select="siteuser/login"/></span> !</td>
						</xsl:otherwise>
					</xsl:choose>
				</tr>
				<tr>
					<td align="left" style="padding-left: 5px">

						<xsl:if test="error_reg != ''">

							<xsl:variable name="error_text">
								<xsl:choose>
									<xsl:when test="error_reg = -1">&labelError1;</xsl:when>
									<xsl:when test="error_reg = -2">&labelError2;</xsl:when>
									<xsl:when test="error_reg = -3">&labelError3;</xsl:when>
									<xsl:when test="error_reg = -4">&labelError4;</xsl:when>
									<xsl:otherwise></xsl:otherwise>
								</xsl:choose>
							</xsl:variable>

							<div id="error">
								<xsl:value-of select="$error_text" />
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
									<label for="rad1" id="lab1">&labelRegistered;</label>

									<input id="rad2" type="radio" name="autoriz" value="new_user" onclick="HideShow('auto', 'new')">
										<xsl:if test="/forum/quick/node() and /forum/quick='quick'">
											<xsl:attribute name="checked">checked</xsl:attribute>
										</xsl:if>
									</input>
									<label for="rad2" id="lab2">&labelNewUser;</label>

									<div id="auto" style="margin-left: 0px">
										<form name="mainform" action="/users/" method="post">
											&labelLogin;
											<input name="login" type="text" size="12" value="" />
											&labelPassword;
											<input name="password" type="password" size="12" value="" /><xsl:text> </xsl:text>
											<input name="apply" class="button" type="submit" value="&labelEnter;" />
											<br />

											<!-- CSRF-токен -->
											<input name="csrf_token" type="hidden" value="{csrf_token}" />

											<input type="hidden" name="location" value="{url}{forum_category/@id}/{forum_topic/@id}/" />
										</form>
									</div>

									<div id="new" style="display: none; margin-left: 0px">
										<div class="comment" style="width: 430px">
											<form name="mainform1" action="/users/registration/" method="post">
												<div class="row">
											<div class="caption">&labelLogin;<sup><font color="red">*</font></sup></div>
													<div class="field"><input type="text" size="40" value="" name="login" /></div>
												</div>
												<div class="row">
											<div class="caption">&labelPassword;<sup><font color="red">*</font></sup></div>
													<div class="field"><input type="password" size="40" value="" name="password"/></div>
												</div>
												<div class="row">
											<div class="caption">&labelEmail;<sup><font color="red">*</font></sup></div>
													<div class="field"><input type="text" size="40" value="" name="email" /></div>
												</div>


												<div class="row">
													<div class="caption"></div>
													<div class="field">
														<img name="captcha" title="&labelCaptchaId;" src="/captcha.php?id={captcha_id}&amp;height=30&amp;width=100" class="captcha" id="registerUser"/>
														<div class="captcha">
															<img src="/images/refresh.png" />
															<span onclick="$('#registerUser').updateCaptcha('{captcha_id}', 30); return false">&labelUpdateCaptcha;</span>
														</div>
													</div>
												</div>
												<div class="row">
													<div class="caption">
											&labelCaptchaId;<sup><font color="red">*</font></sup></div>
													<div class="field">
														<input type="hidden" name="captcha_id" value="{captcha_id}"/>
														<input type="text" size="15" name="captcha" />
													</div>
												</div>
												<div class="row">
													<div class="caption"></div>
													<div class="field">
														<input type="submit" class="button" value="&labelSignUp;" name="apply" />
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
										<a href="/users/">&labelAccount;</a>
									</div>
								</xsl:otherwise>
							</xsl:choose>
						</div>
					</td>
				</tr>
			</table>

			<!-- Хлебные крошки -->
			<p>
				<a href="{url}">&labelForumsList;</a>
				<span><xsl:text> → </xsl:text></span>
				<a href="{url}{forum_category/@id}/">
					<xsl:value-of select="forum_category/name"/>
				</a>
			</p>
		</xsl:template>

		<!-- Post Template -->
		<xsl:template match="forum_topic_post">
<xsl:variable name="current_siteuser_id"><xsl:choose><xsl:when test="/forum/siteuser/node()"><xsl:value-of select="/forum/siteuser/@id"/></xsl:when><xsl:otherwise>0</xsl:otherwise></xsl:choose></xsl:variable>

			<xsl:variable name="moderator">
				<xsl:choose><xsl:when test = "/forum/forum_category/moderators/siteuser/node()">
						<!-- Поле действий для модератора -->
				<xsl:choose><xsl:when test="/forum/forum_category/moderators//siteuser[@id = $current_siteuser_id]/node()">1</xsl:when><xsl:otherwise>0</xsl:otherwise></xsl:choose>
		</xsl:when><xsl:otherwise>0</xsl:otherwise></xsl:choose></xsl:variable>

			<xsl:variable name="current_page" select="/forum/page + 1"/>

			<tr class="row_title_messages">
				<td colspan="2">

					<table border="0" cellpaading="0" cellspacing="0" width="100%" class="table_title_message">
						<tr>
							<!-- Тема сообщения -->
							<td id="title{@id}">
								<span class="title_messages">
									<xsl:value-of select="subject"/>
								</span>
							</td>
							<!-- Аватар с правой стороны -->
							<td align="right">
								<xsl:text> </xsl:text>

								<!-- Действия с открытым форумом -->
								<xsl:if test="$moderator = 1 or not(../forum_topic/closed = 1 or ../forum_category/closed = 1)">
									<!-- Для модераторов -->
									<xsl:if test="allow_edit = 1">
										<a href="{/forum/url}{/forum/forum_category/@id}/{/forum/forum_topic/@id}/editPost-{@id}/page-{$current_page}/"><img src="/hostcmsfiles/forum/edit.gif" alt="&labelEdit;" title="&labelEdit;"/></a>
									</xsl:if>

									<xsl:if test="allow_delete = 1">
										<!-- Если это первое сообщение темы, то при его удалении удаляется вся тема целиком -->
										<xsl:choose>
											<xsl:when test="../forum_topic/forum_topic_post/@id = @id">
												<a href="{/forum/url}{/forum/category}/?delete_topic_id={../forum_topic/@id}" onclick="return confirm('&labelDeleteTopicAlert;')"><img src="/hostcmsfiles/forum/delete.gif" alt="&labelDeleteTopic;" title="&labelDeleteTopic;"/></a>
											</xsl:when>
											<xsl:otherwise>
												<a href="?del_post_id={@id}" onclick="return confirm('&labelDeleteMessageAlert;')"><img src="/hostcmsfiles/forum/delete.gif" alt="&labelDelete;" title="&labelDelete;" /></a>
											</xsl:otherwise>
										</xsl:choose>
									</xsl:if>

									<!-- Кнопка ответить формирует тему для создаваемого сообщения -->
									<xsl:if test="../forum_topic/allow_add_post = 1">
										<a href="#answer"><img alt="&labelAnswer;" title="&labelAnswer;" src="/hostcmsfiles/forum/reply_button.gif" onclick="Reply(&quot;{subject}&quot;)"/></a>
									</xsl:if>

									<xsl:if test="position() = 1 and $current_siteuser_id != 0">
										<!-- Кнопка подписки на рассылку сообщений темы -->
										<xsl:choose>
											<xsl:when test="../forum_topic/subscribed/node() and ../forum_topic/subscribed = 1">
												<a href="./?action=topic_unsubscribe"><img alt="&labelUnsubscribe;" title="&labelUnsubscribe;" src="/hostcmsfiles/forum/unsubscribe_theme_button.gif" /></a>
											</xsl:when>
											<xsl:otherwise>
												<a href="./?action=topic_subscribe"><img alt="&labelSubscribe;" title="&labelSubscribe;" src="/hostcmsfiles/forum/subscribe_theme_button.gif" /></a>
											</xsl:otherwise>
										</xsl:choose>
									</xsl:if>
								</xsl:if>
							</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<!-- Панель информации об авторе сообщения -->
				<td width="140" align="center" valign="top" rowspan="2">
					<xsl:choose>
						<xsl:when test="siteuser/login/node()">
							<!-- Имя автора -->
							<span class="author_name">
								<xsl:value-of select="siteuser/login"/>
							</span>
							<br/>
							<!-- Идентификатор автора поста -->
							<xsl:variable name="post_author_id"><xsl:value-of select="siteuser_id"/></xsl:variable>

							<xsl:variable name="post_author_is_moderator"><xsl:choose><xsl:when test = "/forum/forum_category/moderators/siteuser/node()"><xsl:choose><xsl:when test="/forum/forum_category/moderators//siteuser[@id = $post_author_id]/node()">1</xsl:when><xsl:otherwise>0</xsl:otherwise></xsl:choose></xsl:when><xsl:otherwise>0</xsl:otherwise></xsl:choose></xsl:variable>

							<!-- Звание автора -->
							<xsl:if test="$post_author_is_moderator = 1">
								<span class="message_author_status">&labelModerator;</span>
								<br/>
							</xsl:if>

							<!-- Аватар автора -->
							<xsl:choose>
								<xsl:when test="siteuser/property_value[tag_name = 'avatar']/file != '' ">
									<!-- Аватр есть, выводим его -->
									<img src="{siteuser/dir}{siteuser/property_value[tag_name = 'avatar']/file}" style="padding: 10px" />
								</xsl:when>
								<xsl:otherwise>
									<!-- Аватара нет, выводим заглушку -->
									<img src="/hostcmsfiles/forum/avatar.gif" style="padding: 10px"/>
								</xsl:otherwise>
							</xsl:choose>

							<!-- Дата регистрации -->
							<br/>
							&labelRegistrationDate; <xsl:value-of select="siteuser/date"/>
						</xsl:when>
						<xsl:otherwise>
							<!-- Если автор не был дерегистрирован на сайте,
							то сообщение помечается как созданное гостем -->
							<center>
								<b>&labelGuest;</b>
							</center>
							<!-- Аватара нет, выводим заглушку -->
							<img src="/hostcmsfiles/forum/avatar.gif" style="padding: 10px;"/>
						</xsl:otherwise>
					</xsl:choose>
					<xsl:if test="not(../forum_topic/closed = 1 or ../forum_category/closed = 1)">
						<xsl:variable name="quote_login">
							<xsl:choose>
								<xsl:when test="siteuser/login/node()"><xsl:value-of select="siteuser/login"/></xsl:when>
								<xsl:otherwise>&labelGuest;</xsl:otherwise>
							</xsl:choose>
						</xsl:variable>

						<br/>
						<span class="selectedquote" onmouseover="GetSelection()" onclick="Quote('{$quote_login}',selectedtext)">&labelQuote;</span>
					</xsl:if>
				</td>

				<!-- Если вдруг автор сообщения дерегистрирован, то выравниваем высоту строки сообщения -->
				<td valign="top" style="height: 100px; padding: 5px" id="text{@id}">
					<xsl:value-of disable-output-escaping="yes" select="text"/>

					<xsl:if test="siteuser/property_value[tag_name = 'signature']/value/node()
						and siteuser/property_value[tag_name = 'signature']/value != ''">
						<div class="forum_message_signature">
							<hr width="100%"/>
							<span><xsl:value-of select="siteuser/property_value[tag_name = 'signature']/value"/></span>
						</div>
					</xsl:if>
				</td>
			</tr>
			<tr style="height: 20px;">
				<td>
					<!-- Средства сообщения с автором сообщения -->
					<xsl:if test="siteuser/node()">
					<a href="/users/info/{siteuser/path}/">&labelProfile;</a><xsl:text> | </xsl:text>
					&labelCountMessages; <xsl:value-of select="siteuser/messages_count"/><xsl:text> | </xsl:text>
					</xsl:if>
					&labelDate; <xsl:value-of select="datetime"/>
					<!-- Для модераторов -->
					<xsl:if test="$moderator = 1">
						<xsl:text> | </xsl:text>IP: <xsl:value-of select="ip"/>
					</xsl:if>
				</td>
			</tr>
		</xsl:template>

		<!-- Pagination -->
		<xsl:template name="for">
			<xsl:param name="i" select="0"/>
			<xsl:param name="items_on_page"/>
			<xsl:param name="current_page"/>
			<xsl:param name="count_items"/>
			<xsl:param name="visible_pages"/>

			<xsl:variable name="n" select="$count_items div $items_on_page"/>

			<!-- Current page link -->
			<xsl:variable name="link">
				<xsl:value-of select="/forum/url"/>
				<xsl:value-of select="/forum/forum_category/@id"/>/<xsl:value-of select="/forum/forum_topic/@id"/>/</xsl:variable>

			<!-- Links before current -->
			<xsl:variable name="pre_count_page">
				<xsl:choose>
					<xsl:when test="$current_page &gt; ($n - (round($visible_pages div 2) - 1))">
						<xsl:value-of select="$visible_pages - ($n - $current_page)"/>
					</xsl:when>
					<xsl:otherwise>
						<xsl:value-of select="round($visible_pages div 2) - 1"/>
					</xsl:otherwise>
				</xsl:choose>
			</xsl:variable>

			<!-- Links after current -->
			<xsl:variable name="post_count_page">
				<xsl:choose>
					<xsl:when test="0 &gt; $current_page - (round($visible_pages div 2) - 1)">
						<xsl:value-of select="$visible_pages - $current_page - 1"/>
					</xsl:when>
					<xsl:otherwise>
						<xsl:choose>
							<xsl:when test="round($visible_pages div 2) = ($visible_pages div 2)">
								<xsl:value-of select="$visible_pages div 2"/>
							</xsl:when>
							<xsl:otherwise>
								<xsl:value-of select="round($visible_pages div 2) - 1"/>
							</xsl:otherwise>
						</xsl:choose>
					</xsl:otherwise>
				</xsl:choose>
			</xsl:variable>


			<xsl:if test="$count_items &gt; $items_on_page and $n &gt; $i">

				<!-- Pagination item -->
				<xsl:if test="$i != $current_page">

					<!-- Set $link variable -->
					<xsl:variable name="number_link">
						<xsl:choose>

							<xsl:when test="$i != 0">page-<xsl:value-of select="$i + 1"/>/</xsl:when>

							<xsl:otherwise></xsl:otherwise>
						</xsl:choose>
					</xsl:variable>

					<!-- First pagination item -->
					<xsl:if test="$current_page - $pre_count_page &gt; 0 and $i = 0">
						<a href="{$link}" class="page_link" style="text-decoration: none;">←</a>
					</xsl:if>

					<xsl:choose>
						<xsl:when test="$i &gt;= ($current_page - $pre_count_page) and ($current_page + $post_count_page) &gt;= $i">
							<!-- Pagination item -->
							<a href="{$link}{$number_link}" class="page_link">
								<xsl:value-of select="$i + 1"/>
							</a>
						</xsl:when>
						<xsl:otherwise>
						</xsl:otherwise>
					</xsl:choose>

					<!-- Last pagination item -->
					<xsl:if test="$i+1 &gt;= $n and $n &gt; ($current_page + 1 + $post_count_page)">
						<xsl:choose>
							<xsl:when test="$n &gt; round($n)">
								<!-- Last pagination item -->
								<a href="{$link}page-{round($n+1)}/" class="page_link" style="text-decoration: none;">→</a>
							</xsl:when>
							<xsl:otherwise>
								<a href="{$link}page-{round($n)}/" class="page_link" style="text-decoration: none;">→</a>
							</xsl:otherwise>
						</xsl:choose>
					</xsl:if>
				</xsl:if>

				<!-- Current pagination item -->
				<xsl:if test="$i = $current_page">
					<span class="current">
						<xsl:value-of select="$i+1"/>
					</span>
				</xsl:if>

				<!-- Recursive Template -->
				<xsl:call-template name="for">
					<xsl:with-param name="i" select="$i + 1"/>
					<xsl:with-param name="items_on_page" select="$items_on_page"/>
					<xsl:with-param name="current_page" select="$current_page"/>
					<xsl:with-param name="count_items" select="$count_items"/>
					<xsl:with-param name="visible_pages" select="$visible_pages"/>
				</xsl:call-template>
			</xsl:if>
		</xsl:template>

	</xsl:stylesheet>