<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>

	<!-- СписокЗаписейГостевойКниги  -->

	<xsl:variable name="n" select="number(3)"/>

	<xsl:template match="/informationsystem">

		<!-- Выводим сообщение -->
		<xsl:if test="message/node()">
			<div id="message">
				<xsl:value-of disable-output-escaping="yes" select="message"/>
			</div>
		</xsl:if>

		<!-- Выводим ошибку, если она была передана через внешний параметр -->
		<xsl:if test="error/node()">
			<div id="error">
				<xsl:value-of select="error"/>
			</div>
		</xsl:if>

		<!-- Получаем ID родительской группы и записываем в переменную $group -->
		<xsl:variable name="group" select="group"/>

		<!-- Если в находимся корне - выводим название информационной системы -->
		<xsl:choose>
			<xsl:when test="$group = 0">
				<h1>
					<xsl:value-of select="name"/>
				</h1>

				<!-- Описание выводится при отсутствии фильтрации по тэгам -->
				<xsl:if test="count(tag) = 0 and page = 0">
					<xsl:value-of disable-output-escaping="yes" select="description"/>
				</xsl:if>
			</xsl:when>
			<xsl:otherwise>
				<h1>
					<xsl:value-of select=".//informationsystem_group[@id=$group]/name"/>
				</h1>

				<!-- Описание выводим только на первой странице -->
				<xsl:if test="page = 0">
					<xsl:value-of disable-output-escaping="yes" select=".//informationsystem_group[@id=$group]/description"/>
				</xsl:if>

				<!-- Путь к группе -->
				<p>
					<xsl:apply-templates select=".//informationsystem_group[@id=$group]" mode="breadCrumbs"/>
				</p>
			</xsl:otherwise>
		</xsl:choose>

		<!-- Обработка выбранных тэгов -->
		<xsl:if test="count(tag)">
		<p class="h2">Метка — <strong><xsl:value-of select="tag/name" /></strong>.</p>
			<xsl:if test="tag/description != ''">
				<p><xsl:value-of select="tag/description" disable-output-escaping="yes" /></p>
			</xsl:if>
		</xsl:if>

		<!-- Отображение подгрупп данной группы, только если подгруппы есть и не идет фильтра по меткам -->
		<xsl:if test="count(tag) = 0 and count(.//informationsystem_group[parent_id=$group]) &gt; 0">
			<div class="group_list">
				<xsl:apply-templates select=".//informationsystem_group[parent_id=$group][position() mod $n = 1]" mode="groups"/>
			</div>
		</xsl:if>

		<p class="button" onclick="$('#AddRecord').toggle('slow')">
			Добавить запись
		</p>

		<div id="AddRecord" style="display: none">
			<div class="comment">

			<!--Отображение формы добавления комментария-->
				<form action="./" method="post">

					<xsl:if test="/informationsystem/siteuser_id = 0">
						<div class="row">
							<div class="caption">ФИО</div>
							<div class="field">
								<input type="text" name="author" size="50" value="{/informationsystem/adding_item/author}"/>
							</div>
						</div>

						<div class="row">
							<div class="caption">E-mail</div>
							<div class="field">
								<input type="text" name="email" size="50" value="{/informationsystem/adding_item/email}"/>
							</div>
						</div>
					</xsl:if>

					<div class="row">
						<div class="caption">Тема</div>
						<div class="field">
							<input type="text" name="subject" size="50" value="{/informationsystem/adding_item/subject}"/>
						</div>
					</div>

					<div class="row">
						<div class="caption">Текст сообщения</div>
						<div class="field">
							<textarea type="text" name="text" cols="68" rows="5">
								<xsl:value-of select="/informationsystem/adding_item/text"/>
							</textarea>
						</div>
					</div>

					<!-- Обработка CAPTCHA -->
					<xsl:if test="/informationsystem/captcha_id != 0 and /informationsystem/siteuser_id = 0">
						<div class="row">
							<div class="caption"></div>
							<div class="field">
								<img id="guestBookForm" class="captcha" src="/captcha.php?id={/informationsystem/captcha_id}&amp;height=30&amp;width=100" title="Контрольное число" name="captcha"/>

								<div class="captcha">
									<img src="/images/refresh.png" /> <span onclick="$('#guestBookForm').updateCaptcha('{/informationsystem/captcha_id}', 30); return false">Показать другое число</span>
								</div>
							</div>
						</div>

						<div class="row">
							<div class="caption">
								Контрольное число<sup><font color="red">*</font></sup>
							</div>
							<div class="field">
								<input type="hidden" name="captcha_id" value="{/informationsystem/captcha_id}"/>
								<input type="text" name="captcha" size="15"/>
							</div>
						</div>
					</xsl:if>

					<div class="row">
						<div class="caption"></div>
						<div class="field">
							<input type="submit" name="submit_question" value="Добавить запись" class="button"/>
						</div>
					</div>

				</form>
			</div>
		</div>

		<!-- Отображение записи информационной системы -->
		<xsl:apply-templates select="informationsystem_item[active=1]"/>

		<!-- Строка ссылок на другие страницы информационной системы -->
		<xsl:if test="ОтображатьСсылкиНаСледующиеСтраницы=1">
			<div>
				<!-- Ссылка, для которой дописываются суффиксы page-XX/ -->
				<xsl:variable name="link">
					<xsl:value-of select="/informationsystem/url"/>
					<xsl:if test="$group != 0">
						<xsl:value-of select="/informationsystem//informationsystem_group[@id = $group]/url"/>
					</xsl:if>
				</xsl:variable>

				<xsl:if test="total &gt; 0 and limit &gt; 0">

					<xsl:variable name="count_pages" select="ceiling(total div limit)"/>

					<xsl:variable name="visible_pages" select="5"/>

					<xsl:variable name="real_visible_pages"><xsl:choose>
						<xsl:when test="$count_pages &lt; $visible_pages"><xsl:value-of select="$count_pages"/></xsl:when>
						<xsl:otherwise><xsl:value-of select="$visible_pages"/></xsl:otherwise>
					</xsl:choose></xsl:variable>

					<!-- Считаем количество выводимых ссылок перед текущим элементом -->
					<xsl:variable name="pre_count_page"><xsl:choose>
						<xsl:when test="page - (floor($real_visible_pages div 2)) &lt; 0">
							<xsl:value-of select="page"/>
						</xsl:when>
						<xsl:when test="($count_pages - page - 1) &lt; floor($real_visible_pages div 2)">
							<xsl:value-of select="$real_visible_pages - ($count_pages - page - 1) - 1"/>
						</xsl:when>
						<xsl:otherwise>
							<xsl:choose>
								<xsl:when test="round($real_visible_pages div 2) = $real_visible_pages div 2">
									<xsl:value-of select="floor($real_visible_pages div 2) - 1"/>
								</xsl:when>
								<xsl:otherwise>
									<xsl:value-of select="floor($real_visible_pages div 2)"/>
								</xsl:otherwise>
							</xsl:choose>
						</xsl:otherwise>
					</xsl:choose></xsl:variable>

					<!-- Считаем количество выводимых ссылок после текущего элемента -->
					<xsl:variable name="post_count_page"><xsl:choose>
							<xsl:when test="0 &gt; page - (floor($real_visible_pages div 2) - 1)">
								<xsl:value-of select="$real_visible_pages - page - 1"/>
							</xsl:when>
							<xsl:when test="($count_pages - page - 1) &lt; floor($real_visible_pages div 2)">
								<xsl:value-of select="$real_visible_pages - $pre_count_page - 1"/>
							</xsl:when>
							<xsl:otherwise>
								<xsl:value-of select="$real_visible_pages - $pre_count_page - 1"/>
							</xsl:otherwise>
					</xsl:choose></xsl:variable>

					<xsl:variable name="i"><xsl:choose>
							<xsl:when test="page + 1 = $count_pages"><xsl:value-of select="page - $real_visible_pages + 1"/></xsl:when>
							<xsl:when test="page - $pre_count_page &gt; 0"><xsl:value-of select="page - $pre_count_page"/></xsl:when>
							<xsl:otherwise>0</xsl:otherwise>
					</xsl:choose></xsl:variable>

					<p>
						<xsl:call-template name="for">
							<xsl:with-param name="limit" select="limit"/>
							<xsl:with-param name="page" select="page"/>
							<xsl:with-param name="items_count" select="total"/>
							<xsl:with-param name="i" select="$i"/>
							<xsl:with-param name="post_count_page" select="$post_count_page"/>
							<xsl:with-param name="pre_count_page" select="$pre_count_page"/>
							<xsl:with-param name="visible_pages" select="$real_visible_pages"/>
						</xsl:call-template>
					</p>
					<div style="clear: both"></div>
				</xsl:if>
			</div>
		</xsl:if>
	</xsl:template>

	<xsl:template match="informationsystem_item">

		<div class="comment">
			<xsl:if test="name != ''">
				<b><xsl:value-of select="name"/></b><br/>
			</xsl:if>

			<xsl:value-of disable-output-escaping="yes" select="text"/>

			<p class="tags">
				<!-- Если сообщение от авторизованного пользователя -->
				<img src="/images/user.png" />
				<span>
				<xsl:choose>
					<xsl:when test="count(siteuser) &gt; 0">
						<a href="/users/info/{siteuser/login}/"><xsl:value-of select="siteuser/login"/></a>
					</xsl:when>
					<xsl:otherwise>
						<xsl:choose>
							<xsl:when test="property_value[tag_name='email']/value != ''">
							<a href="mailto:{property_value[tag_name='email']/value}"><xsl:value-of select="property_value[tag_name='author']/value"/></a>
							</xsl:when>
							<xsl:otherwise>
								<xsl:value-of select="property_value[tag_name='author']/value"/>
							</xsl:otherwise>
						</xsl:choose>
					</xsl:otherwise>
				</xsl:choose>
				</span>

				<img src="/images/calendar.png" /> <span><xsl:value-of select="datetime"/></span>
			</p>
		</div>

		<!-- Отображение комментариев  -->
		<xsl:if test="count(comment) &gt; 0">
			<div class="comment_sub">
				<xsl:apply-templates select="comment"/>
			</div>
		</xsl:if>

	</xsl:template>

	<!-- Отображение комментариев -->
	<xsl:template match="comment">
		<!-- Отображаем комментарий, если задан текст или тема комментария -->
		<xsl:if test="text != '' or subject != ''">
			<a name="comment{@id}"></a>
			<div class="comment" id="comment{@id}">
				<xsl:if test="subject != ''">
					<div class="subject"><xsl:value-of select="subject"/></div>
				</xsl:if>

				<xsl:value-of select="text" disable-output-escaping="yes"/>

				<p class="tags">
					<!-- Оценка комментария -->
					<xsl:if test="grade != 0">
						<span><xsl:call-template name="show_average_grade">
							<xsl:with-param name="grade" select="grade"/>
							<xsl:with-param name="const_grade" select="5"/>
						</xsl:call-template></span>
					</xsl:if>

					<img src="/images/user.png" />
					<xsl:choose>
					<!-- Комментарий добавил авторизированный пользователь -->
					<xsl:when test="count(siteuser) &gt; 0">
						<span><a href="/users/info/{siteuser/login}/"><xsl:value-of select="siteuser/login"/></a></span>
					</xsl:when>
					<!-- Комментарй добавил неавторизированный пользователь -->
					<xsl:otherwise>
						<span><xsl:value-of select="author" /></span>
					</xsl:otherwise>
					</xsl:choose>

					<img src="/images/calendar.png" /> <span><xsl:value-of select="datetime"/></span>
				</p>
			</div>

			<!-- Выбираем дочерние комментарии -->
			<xsl:if test="count(comment) &gt; 0">
				<div class="comment_sub">
					<xsl:apply-templates select="comment"/>
				</div>
			</xsl:if>
		</xsl:if>
	</xsl:template>

	<!-- Шаблон выводит рекурсивно ссылки на группы инф. элемента -->
	<xsl:template match="informationsystem_group" mode="breadCrumbs">
		<xsl:variable name="parent_id" select="parent_id"/>

		<xsl:apply-templates select="//informationsystem_group[@id=$parent_id]" mode="breadCrumbs"/>

		<xsl:if test="parent_id=0">
			<a href="{/informationsystem/url}">
				<xsl:value-of select="/informationsystem/name"/>
			</a>
		</xsl:if>

		<span><xsl:text> → </xsl:text></span>

		<a href="{url}">
			<xsl:value-of select="name"/>
		</a>
	</xsl:template>

	<!-- Шаблон выводит ссылки подгруппы информационного элемента -->
	<xsl:template match="informationsystem_group" mode="groups">
		<ul>
			<xsl:for-each select=". | following-sibling::informationsystem_group[position() &lt; $n]">
				<li>
					<xsl:if test="image_small!=''">
						<a href="{url}" target="_blank">
							<img src="{dir}{image_small}" align="middle"/>
					</a><xsl:text> </xsl:text></xsl:if>
				<a href="{url}"><xsl:value-of select="name"/></a><xsl:text> </xsl:text><span class="count">(<xsl:value-of select="items_total_count"/>)</span>
				</li>
			</xsl:for-each>
		</ul>
	</xsl:template>

	<!-- Вывод рейтинга -->
	<xsl:template name="show_average_grade">
		<xsl:param name="grade" select="0"/>
		<xsl:param name="const_grade" select="0"/>

		<!-- Чтобы избежать зацикливания -->
		<xsl:variable name="current_grade" select="$grade * 1"/>

		<xsl:choose>
			<!-- Если число целое -->
			<xsl:when test="floor($current_grade) = $current_grade and not($const_grade &gt; ceiling($current_grade))">

				<xsl:if test="$current_grade - 1 &gt; 0">
					<xsl:call-template name="show_average_grade">
						<xsl:with-param name="grade" select="$current_grade - 1"/>
						<xsl:with-param name="const_grade" select="$const_grade - 1"/>
					</xsl:call-template>
				</xsl:if>

				<xsl:if test="$current_grade != 0">
					<img src="/images/star-full.png"/>
				</xsl:if>
			</xsl:when>
			<xsl:when test="$current_grade != 0 and not($const_grade &gt; ceiling($current_grade))">

				<xsl:if test="$current_grade - 0.5 &gt; 0">
					<xsl:call-template name="show_average_grade">

						<xsl:with-param name="grade" select="$current_grade - 0.5"/>
						<xsl:with-param name="const_grade" select="$const_grade - 1"/>
					</xsl:call-template>
				</xsl:if>

				<img src="/images/star-half.png"/>
			</xsl:when>

			<!-- Выводим серые звездочки, пока текущая позиция не дойдет то значения, увеличенного до целого -->
			<xsl:otherwise>
				<xsl:call-template name="show_average_grade">
					<xsl:with-param name="grade" select="$current_grade"/>
					<xsl:with-param name="const_grade" select="$const_grade - 1"/>
				</xsl:call-template>
				<img src="/images/star-empty.png"/>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

	<!-- Цикл для вывода строк ссылок -->
	<xsl:template name="for">

		<xsl:param name="limit"/>
		<xsl:param name="page"/>
		<xsl:param name="pre_count_page"/>
		<xsl:param name="post_count_page"/>
		<xsl:param name="i" select="0"/>
		<xsl:param name="items_count"/>
		<xsl:param name="visible_pages"/>

		<xsl:variable name="n" select="ceiling($items_count div $limit)"/>

		<xsl:variable name="start_page"><xsl:choose>
				<xsl:when test="$page + 1 = $n"><xsl:value-of select="$page - $visible_pages + 1"/></xsl:when>
				<xsl:when test="$page - $pre_count_page &gt; 0"><xsl:value-of select="$page - $pre_count_page"/></xsl:when>
				<xsl:otherwise>0</xsl:otherwise>
		</xsl:choose></xsl:variable>

		<xsl:if test="$i = $start_page and $page != 0">
			<span class="ctrl">
				← Ctrl
			</span>
		</xsl:if>

		<xsl:if test="$i = ($page + $post_count_page + 1) and $n != ($page+1)">
			<span class="ctrl">
				Ctrl →
			</span>
		</xsl:if>

		<xsl:if test="$items_count &gt; $limit and ($page + $post_count_page + 1) &gt; $i">
			<!-- Заносим в переменную $group идентификатор текущей группы -->
			<xsl:variable name="group" select="/informationsystem/group"/>

			<!-- Путь для тэга -->
			<xsl:variable name="tag_path">
				<xsl:choose>
					<!-- Если не нулевой уровень -->
					<xsl:when test="count(/informationsystem/tag) != 0">tag/<xsl:value-of select="/informationsystem/tag/urlencode"/>/</xsl:when>
					<!-- Иначе если нулевой уровень - просто ссылка на страницу со списком элементов -->
					<xsl:otherwise></xsl:otherwise>
				</xsl:choose>
			</xsl:variable>

			<!-- Определяем группу для формирования адреса ссылки -->
			<xsl:variable name="group_link">
				<xsl:choose>
					<!-- Если группа не корневая (!=0) -->
					<xsl:when test="$group != 0">
						<xsl:value-of select="/informationsystem//informationsystem_group[@id=$group]/url"/>
					</xsl:when>
					<!-- Иначе если нулевой уровень - просто ссылка на страницу со списком элементов -->
					 <xsl:otherwise><xsl:value-of select="/informationsystem/url"/></xsl:otherwise>
				</xsl:choose>
			</xsl:variable>

			<!-- Определяем адрес ссылки -->
			<xsl:variable name="number_link">
				<xsl:choose>
					<!-- Если не нулевой уровень -->
					<xsl:when test="$i != 0">page-<xsl:value-of select="$i + 1"/>/</xsl:when>
					<!-- Иначе если нулевой уровень - просто ссылка на страницу со списком элементов -->
					<xsl:otherwise></xsl:otherwise>
				</xsl:choose>
			</xsl:variable>

			<!-- Выводим ссылку на первую страницу -->
			<xsl:if test="$page - $pre_count_page &gt; 0 and $i = $start_page">
				<a href="{$group_link}{$tag_path}" class="page_link" style="text-decoration: none;">←</a>
			</xsl:if>

			<!-- Ставим ссылку на страницу-->
			<xsl:if test="$i != $page">
				<xsl:if test="($page - $pre_count_page) &lt;= $i and $i &lt; $n">
					<!-- Выводим ссылки на видимые страницы -->
					<a href="{$group_link}{$number_link}{$tag_path}" class="page_link">
						<xsl:value-of select="$i + 1"/>
					</a>
				</xsl:if>

				<!-- Выводим ссылку на последнюю страницу -->
				<xsl:if test="$i+1 &gt;= ($page + $post_count_page + 1) and $n &gt; ($page + 1 + $post_count_page)">
					<!-- Выводим ссылку на последнюю страницу -->
					<a href="{$group_link}page-{$n}/{$tag_path}" class="page_link" style="text-decoration: none;">→</a>
				</xsl:if>
			</xsl:if>

			<!-- Ссылка на предыдущую страницу для Ctrl + влево -->
			<xsl:if test="$page != 0 and $i = $page">
				<xsl:variable name="prev_number_link">
					<xsl:choose>
						<!-- Если не нулевой уровень -->
						<xsl:when test="$page &gt; 1">page-<xsl:value-of select="$i"/>/</xsl:when>
						<!-- Иначе если нулевой уровень - просто ссылка на страницу со списком элементов -->
						<xsl:otherwise></xsl:otherwise>
					</xsl:choose>
				</xsl:variable>

				<a href="{$group_link}{$prev_number_link}{$tag_path}" id="id_prev"></a>
			</xsl:if>

			<!-- Ссылка на следующую страницу для Ctrl + вправо -->
			<xsl:if test="($n - 1) > $page and $i = $page">
				<a href="{$group_link}page-{$page+2}/{$tag_path}" id="id_next"></a>
			</xsl:if>

			<!-- Не ставим ссылку на страницу-->
			<xsl:if test="$i = $page">
				<span class="current">
					<xsl:value-of select="$i+1"/>
				</span>
			</xsl:if>

			<!-- Рекурсивный вызов шаблона. НЕОБХОДИМО ПЕРЕДАВАТЬ ВСЕ НЕОБХОДИМЫЕ ПАРАМЕТРЫ! -->
			<xsl:call-template name="for">
				<xsl:with-param name="i" select="$i + 1"/>
				<xsl:with-param name="limit" select="$limit"/>
				<xsl:with-param name="page" select="$page"/>
				<xsl:with-param name="items_count" select="$items_count"/>
				<xsl:with-param name="pre_count_page" select="$pre_count_page"/>
				<xsl:with-param name="post_count_page" select="$post_count_page"/>
				<xsl:with-param name="visible_pages" select="$visible_pages"/>
			</xsl:call-template>
		</xsl:if>
	</xsl:template>

</xsl:stylesheet>