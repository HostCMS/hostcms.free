<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "lang://38">
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="no" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>

	<!-- ТемыФорума -->
	<xsl:template match="/forum">
		<h1>
			<xsl:value-of select="forum_category/name"/>
		</h1>
		<xsl:if test="forum_category/description != ''">
			<!-- Описание форума -->
			<p>
				<span class="desc_forum">
					<xsl:value-of select="forum_category/description"/>
				</span>
			</p>
		</xsl:if>
		<xsl:if test="error != ''">
			<div id="error">
				<xsl:value-of disable-output-escaping="yes" select="error"/>
			</div>
		</xsl:if>
		<p>
			<a href="{url}">&labelForumsList;</a>
			<span><xsl:text> → </xsl:text></span>
			<a href="{url}{forum_category/@id}/" class="current_page_link">
				<xsl:value-of select="forum_category/name"/>
			</a>
		</p>

		<xsl:variable name="current_siteuser_id"><xsl:choose><xsl:when test="siteuser/node()"><xsl:value-of select="siteuser/@id"/></xsl:when><xsl:otherwise>0</xsl:otherwise></xsl:choose></xsl:variable>

		<xsl:variable name="moderator">
		<xsl:choose><xsl:when test = "forum_category/moderators/siteuser/node()">
			<!-- Поле действий для модератора -->
			<xsl:choose><xsl:when test="forum_category/moderators//siteuser[@id = $current_siteuser_id]/node()">1</xsl:when><xsl:otherwise>0</xsl:otherwise></xsl:choose>
		</xsl:when><xsl:otherwise>0</xsl:otherwise></xsl:choose></xsl:variable>

		<div style="float: left">
			<xsl:choose>
				<!-- Для закрытого форума появляется картинка - форум закрыт -->
				<xsl:when test="forum_category/closed = 1 and not($moderator = 1)">
					&labelModerator1;
				</xsl:when>
				<xsl:when test="forum_category/closed = 0 and not(siteuser/login) and forum_category/allow_guest_posting = 0">
					&labelModerator0;
				</xsl:when>
				<!-- Для открытого форума или, если пользователь - модератор, появляется кнопка добавить тему -->
				<xsl:otherwise>
					<p class="button">
						<a href="{url}{forum_category/@id}/addTopic/">&labelAddTopic;</a>
					</p>
				</xsl:otherwise>
			</xsl:choose>
		</div>
		<xsl:if test = "siteuser/node()">
			<div style="float: right"><strong><a href="{forums_path}myPosts/">&labelMyPosts;</a></strong></div>
		</xsl:if>
		<div style="clear: both; height: 10px"></div>

		<form action="{url}" method="post">
			
			<table class="table_forum table_themes">
				<tr class="row_title_themes">
					<td align="center" class="theme_td_attribute"></td>
					<td align="center" class="theme_td_title">&labelSubject;</td>
					<td align="center" class="theme_td_author">&labelAuthor;</td>
					<td align="center" class="theme_td_count_answer">&labelAnswers;</td>
					<td align="center" class="theme_td_last_message">&labelLastMessage;</td>

					<xsl:if test="$moderator = 1">
						<td align="center" class="theme_td_action">
							<b>&labelAction;</b>
						</td>
					</xsl:if>

				</tr>
				<!-- Если есть темы для форума -->
				<xsl:if test="count(forum_topics/forum_topic) > 0">
					<xsl:apply-templates select="forum_topics/forum_topic[($moderator = 1) or (visible = 1)]"/>
				</xsl:if>

			</table>
		</form>

		<div class="clearing" style="margin-bottom: 10px"></div>

		<!-- Список модераторов -->
		<b>&labelModerators;</b>
		<span>
			<xsl:choose>
				<xsl:when test="count(forum_category/moderators/siteuser) = 0">&labelNo;</xsl:when>
				<xsl:otherwise>
					<xsl:apply-templates select="forum_category/moderators/siteuser"/>
				</xsl:otherwise>
			</xsl:choose>
		</span>

		<!-- Rss -->
		<div class="rss" style="margin-right: 10px;">
			<img src="/images/rss.png"/><xsl:text> </xsl:text><a href="{/forum/url}{forum_category/@id}/rss/">RSS</a>
		</div>

		<p>
			<!-- Pagination -->
			<xsl:call-template name="for">
				<xsl:with-param name="limit" select="limit"/>
				<xsl:with-param name="page" select="page"/>
				<xsl:with-param name="count_items" select="forum_category/count_topics"/>
				<xsl:with-param name="visible_pages" select="6"/>
			</xsl:call-template>
			<div style="clear: both"></div>
		</p>

		<!-- Форма идентификации пользователя на сайте или приветствия пользователя -->
		<table class="table_identification" border="1">
			<tr class="row_title_identification">
				<td align="center">
				<xsl:choose>
					<xsl:when test="siteuser/login/node()">
						&labelWelcome; <span class="name_users"><xsl:value-of select="siteuser/login"/></span> !
					</xsl:when>
					<xsl:otherwise><b>&labelAuthorization;</b></xsl:otherwise>
				</xsl:choose>
				</td>
			</tr>
			<tr>
				<td align="left" style="padding-left: 5px">
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
										<input name="login" type="text" value="" />
										&labelPassword;
										<input name="password" type="password" value="" /><xsl:text> </xsl:text>
										<input name="apply" class="button" type="submit" value="&labelEnter;" />
										<br />
										<input type="hidden" name="location" value="{url}{forum_category/@id}/" />
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

		<p>
			<a href="{url}">&labelForumsList;</a>
			<span><xsl:text> → </xsl:text></span>
			<a href="{url}{forum_category/@id}/" class="current_page_link">
				<xsl:value-of select="forum_category/name"/>
			</a>
		</p>
	</xsl:template>

	<!-- Строка ссылок в теме -->
	<xsl:template name="pages">
		<xsl:param name="i" select="0"/>
		<xsl:param name="n"/>
		<xsl:param name="current_page"/>
		<xsl:param name="theme_id"/>
		<xsl:if test="$n &gt; $i">
			<!-- Set $link variable -->
			<xsl:choose>
				<!-- Если число страниц меньше 7 и больше 1 -->
				<xsl:when test="7 &gt; $n">
					<xsl:variable name="number_link">
						<xsl:choose>
							
							<xsl:when test="$i != 0">page-<xsl:value-of select="$i + 1"/>/</xsl:when>
							
							<xsl:otherwise></xsl:otherwise>
						</xsl:choose>
					</xsl:variable>

					<!-- Pagination item -->
					<a href="{/forum/url}{/forum/forum_category/@id}/{$theme_id}/{$number_link}">
						<xsl:value-of select="$i + 1"/>
					</a>
					<xsl:if test="$n - 1 &gt; $i ">,</xsl:if>
					<!-- Recursive Template -->
					<xsl:call-template name="pages">
						<xsl:with-param name="i" select="$i + 1"/>
						<xsl:with-param name="n" select="$n"/>
						<xsl:with-param name="current_page" select="$current_page"/>
						<xsl:with-param name="theme_id" select="$theme_id"/>
					</xsl:call-template>
				</xsl:when>
				<xsl:otherwise>
					<a href="{/forum/url}{/forum/forum_category/@id}/{$theme_id}/">1</a>,
					<a href="{/forum/url}{/forum/forum_category/@id}/{$theme_id}/page-2/">2</a>,
					<a href="{/forum/url}{/forum/forum_category/@id}/{$theme_id}/page-3/">3</a>...
					<a href="{/forum/url}{/forum/forum_category/@id}/{$theme_id}/page-{$n - 2}/">
						<xsl:value-of select="$n - 2"/>
					</a>,
					<a href="{/forum/url}{/forum/forum_category/@id}/{$theme_id}/page-{$n - 1}/">
						<xsl:value-of select="$n - 1"/>
					</a>,
					<a href="{/forum/url}{/forum/forum_category/@id}/{$theme_id}/page-{$n}/">
						<xsl:value-of select="$n"/>
					</a>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:if>
	</xsl:template>

	<!-- Шаблон вывода модераторов через запятую -->
	<xsl:template match="forum_category/moderators/siteuser">
		<a href="/users/info/{login}/"><xsl:value-of select="login"/></a>
		<xsl:choose>
			<xsl:when test="position() != last()">, </xsl:when>
			<xsl:otherwise>.</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

	<!-- Шаблон вывода строк тем -->
	<xsl:template match="forum_topics/forum_topic">
		<xsl:variable name="current_siteuser_id"><xsl:choose><xsl:when test="/forum/siteuser/node()"><xsl:value-of select="/forum/siteuser/@id"/></xsl:when><xsl:otherwise>0</xsl:otherwise></xsl:choose></xsl:variable>

		<xsl:variable name="moderator">
		<xsl:choose><xsl:when test = "/forum/forum_category/moderators/siteuser/node()">
			<!-- Поле действий для модератора -->
			<xsl:choose><xsl:when test="/forum/forum_category/moderators//siteuser[@id = $current_siteuser_id]/node()">1</xsl:when><xsl:otherwise>0</xsl:otherwise></xsl:choose>
		</xsl:when><xsl:otherwise>0</xsl:otherwise></xsl:choose></xsl:variable>

		<!-- Row Style -->
		<xsl:variable name="color_theme">
			<xsl:choose>
				<xsl:when test="(position() mod 2 = 0) and not (($moderator = 1) and (visible = 0))">color_2_theme</xsl:when>
				<xsl:when test="($moderator = 1) and (visible = 0)">color_hidden_theme</xsl:when>
				<xsl:otherwise></xsl:otherwise>
			</xsl:choose>
		</xsl:variable>

		
		<xsl:if test="($moderator = 1) or (visible = 1)">
			<tr class="{$color_theme}">
				<!-- Атрибуты темы -->
				<td align="center" class="theme_td_attribute">
					<xsl:if test="closed = 1 and announcement = 0 and new_posts = 1">
						<img src="/hostcmsfiles/forum/theme_locked_new.gif" title="&labelThemeLockedNew;" alt="X+"/>
					</xsl:if>
					<xsl:if test="closed = 1 and announcement = 0 and new_posts = 0">
						<img src="/hostcmsfiles/forum/theme_locked.gif" title="&labelThemeLocked;" alt="X-"/>
					</xsl:if>
					<xsl:if test="closed = 0 and announcement = 1 and new_posts = 1">
						<img src="/hostcmsfiles/forum/theme_notice_new.gif" title="&labelThemeNoticeNew;" alt="O!+"/>
					</xsl:if>
					<xsl:if test="closed = 0 and announcement = 1 and new_posts = 0">
						<img src="/hostcmsfiles/forum/theme_notice.gif" title="&labelThemeNotice;" alt="O!-"/>
					</xsl:if>
					<xsl:if test="closed = 1 and announcement = 1 and new_posts = 1">
						<img src="/hostcmsfiles/forum/theme_notice_close_new.gif" title="&labelThemeNoticeCloseNew;" alt="X!+"/>
					</xsl:if>
					<xsl:if test="closed = 1 and announcement = 1 and new_posts = 0">
						<img src="/hostcmsfiles/forum/theme_notice_close.gif" title="&labelThemeNoticeClose;" alt="X!-"/>
					</xsl:if>
					<xsl:if test="closed = 0 and announcement = 0 and new_posts = 1">
						<img src="/hostcmsfiles/forum/theme_new.gif" title="&labelThemeNew;" alt="O+"/>
					</xsl:if>
					<xsl:if test="closed = 0 and announcement = 0 and new_posts = 0">
						<img src="/hostcmsfiles/forum/theme.gif" title="&labelTheme;" alt="O-"/>
					</xsl:if>
				</td>

				<!-- Если тема - объявление, выводим жирным -->
				<xsl:variable name="style_theme_name">
					<xsl:choose>
						<xsl:when test="announcement = 1">font-weight: bold</xsl:when>
						<!-- Закрытая тема выводится зачеркнутым -->
						<xsl:when test="closed = 1">text-decoration: line-through</xsl:when>
						<xsl:otherwise></xsl:otherwise>
					</xsl:choose>
				</xsl:variable>

				<!-- Topic Subject -->
				<td class="theme_td_title">
					<a href="{/forum/url}{/forum/category}/{@id}/" style="{$style_theme_name}">
						<xsl:value-of select="forum_topic_post/subject"/>
					</a>

					<!-- Число страниц с ответами темы -->
					<xsl:variable name="count_message_page" select="ceiling((count_posts) div /forum/posts_on_page)"/>

					<xsl:choose>
						<!-- Если число больше 1 -->
						<xsl:when test="$count_message_page &gt; 1">(<xsl:call-template name="pages">
								<xsl:with-param name="n" select="$count_message_page"/>
								<xsl:with-param name="current_page" select="0"/>
							<xsl:with-param name="theme_id" select="@id"/></xsl:call-template>)</xsl:when>
						<xsl:otherwise></xsl:otherwise>
					</xsl:choose>
				</td>
				<td align="center" class="theme_td_author">
					<xsl:choose>
						<xsl:when test="forum_topic_post/siteuser/login/node()">
							<a href="/users/info/{forum_topic_post/siteuser/login}/"><xsl:value-of select="forum_topic_post/siteuser/login"/></a>
						</xsl:when>
						<xsl:otherwise>&labelGuest;</xsl:otherwise>
					</xsl:choose>

					<!-- Дата создания темы -->
					<br/>
					<xsl:value-of select="forum_topic_post/datetime"/>
				</td>

				<!-- Количество ответов в теме -->
				<td align="center" class="theme_td_count_answer">
					<xsl:value-of select="count_posts - 1"/>
				</td>

				<!-- Последнее сообщение в теме -->
				<td align="center" class="theme_td_last_message">

					<!-- Автор последнего сообщения -->
					<xsl:choose>
						<xsl:when test="last/forum_topic_post/siteuser/login/node()">
							<a href="/users/info/{last/forum_topic_post/siteuser/login}/"><xsl:value-of select="last/forum_topic_post/siteuser/login"/></a>
						</xsl:when>
						<xsl:otherwise>&labelGuest;</xsl:otherwise>
					</xsl:choose>

					<br/>

					<!-- Дата последнего сообщения -->
					<xsl:value-of select="last/forum_topic_post/datetime"/>
				</td>
				<xsl:if test="$moderator = 1">
					<!-- Действия над темой доступны лишь модераторам форума -->
					<td align="center" class="theme_td_action">
						<xsl:choose>
							<!-- Видимость темы -->
							<xsl:when test="visible = 0"><a href="?visible_topic_id={@id}"><img src="/hostcmsfiles/forum/theme_visible_button.gif" title="&labelThemeVisibleButton;" alt="&labelShow;"/></a> </xsl:when>
							<xsl:otherwise><a href="?visible_topic_id={@id}"><img src="/hostcmsfiles/forum/theme_hidden_button.gif" title="&labelThemeInvisibleButton;" alt="&labelHide;"/></a> </xsl:otherwise>
						</xsl:choose>

						<!-- Объявление --><xsl:choose>
							<xsl:when test="announcement = 0"><a href="?notice_topic_id={@id}"><img src="/hostcmsfiles/forum/theme_notice_button.gif" title="&labelThemeNoticeButton;" alt="&labelNotice;"/></a> </xsl:when>
							<xsl:otherwise><a href="?notice_topic_id={@id}"><img src="/hostcmsfiles/forum/notice_theme_button.gif" title="&labelThemeNormalButton;" alt="&labelNormal;"/></a> </xsl:otherwise>
						</xsl:choose>

						<!-- Закрыть/открыть -->
						<xsl:choose>
							<xsl:when test="closed = 0"><a href="?close_topic_id={@id}"><img src="/hostcmsfiles/forum/theme_lock_button.gif" title="Закрыть" alt="Закрыть"/></a> </xsl:when>
							<xsl:otherwise><a href="?close_topic_id={@id}"><img src="/hostcmsfiles/forum/theme_unlock_button.gif" title="&labelUnlock;" alt="&labelUnlock;"/></a> </xsl:otherwise>
						</xsl:choose>

						<xsl:variable name="current_page">
							<xsl:choose><xsl:when test="/forum/page > 0">page-<xsl:value-of select="/forum/page + 1" />/</xsl:when><xsl:otherwise></xsl:otherwise></xsl:choose>
						</xsl:variable>
						<!-- Редактировать -->
						<a href="{/forum/url}{/forum/forum_category/@id}/editTopic-{@id}/{$current_page}"><img src="/hostcmsfiles/forum/edit.gif" title="&labelEdit;" alt="&labelEdit;"/></a> <!-- Удалить --><a href="?delete_topic_id={@id}" onclick="return confirm('&labelDeleteAlert;')"><img src="/hostcmsfiles/forum/delete.gif" title="&labelDelete;" alt="&labelDelete;"/></a>
					</td>
				</xsl:if>
			</tr>
		</xsl:if>
	</xsl:template>

	<!-- Pagination -->
	<xsl:template name="for">
		<xsl:param name="i" select="0"/>
		<xsl:param name="limit"/>
		<xsl:param name="page"/>
		<xsl:param name="count_items"/>
		<xsl:param name="visible_pages"/>

		<xsl:variable name="n" select="$count_items div $limit"/>


		<!-- Current page link -->
		<xsl:variable name="link">
			<xsl:value-of select="/forum/url"/>
			<xsl:value-of select="/forum/forum_category/@id"/>/</xsl:variable>

		<!-- Links before current -->
		<xsl:variable name="pre_count_page">
			<xsl:choose>
				<xsl:when test="$page &gt; ($n - (round($visible_pages div 2) - 1))">
					<xsl:value-of select="$visible_pages - ($n - $page)"/>
				</xsl:when>
				<xsl:otherwise>
					<xsl:value-of select="round($visible_pages div 2) - 1"/>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:variable>

		<!-- Links after current -->
		<xsl:variable name="post_count_page">
			<xsl:choose>
				<xsl:when test="0 &gt; $page - (round($visible_pages div 2) - 1)">
					<xsl:value-of select="$visible_pages - $page - 1"/>
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

		<xsl:if test="$count_items &gt; $limit and $n &gt; $i">
			<!-- Pagination item -->
			<xsl:if test="$i != $page">

				<!-- Set $link variable -->
				<xsl:variable name="number_link">
					<xsl:choose>
						
						<xsl:when test="$i != 0">page-<xsl:value-of select="$i + 1"/>/</xsl:when>
						
						<xsl:otherwise></xsl:otherwise>
					</xsl:choose>
				</xsl:variable>

				<!-- First pagination item -->
				<xsl:if test="$page - $pre_count_page &gt; 0 and $i = 0">
					<a href="{$link}" class="page_link" style="text-decoration: none;">←</a>
				</xsl:if>

				<xsl:choose>
					<xsl:when test="$i &gt;= ($page - $pre_count_page) and ($page + $post_count_page) &gt;= $i">

						<!-- Pagination item -->
						<a href="{$link}{$number_link}" class="page_link">
							<xsl:value-of select="$i + 1"/>
						</a>
					</xsl:when>
					<xsl:otherwise>
					</xsl:otherwise>
				</xsl:choose>

				<!-- Last pagination item -->
				<xsl:if test="$i+1 &gt;= $n and $n &gt; ($page + 1 + $post_count_page)">
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
			<xsl:if test="$i = $page">
				<span class="current">
					<xsl:value-of select="$i+1"/>
				</span>
			</xsl:if>

			<!-- Recursive Template -->
			<xsl:call-template name="for">
				<xsl:with-param name="i" select="$i + 1"/>
				<xsl:with-param name="limit" select="$limit"/>
				<xsl:with-param name="page" select="$page"/>
				<xsl:with-param name="count_items" select="$count_items"/>
				<xsl:with-param name="visible_pages" select="$visible_pages"/>
			</xsl:call-template>
		</xsl:if>
	</xsl:template>
</xsl:stylesheet>