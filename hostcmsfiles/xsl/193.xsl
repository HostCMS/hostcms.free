<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>
	
	<xsl:template match="/">
		<xsl:apply-templates select="/document/item"/>
	</xsl:template>
	
	<xsl:template match="/document/item">

		<!-- Получаем ID родительской группы и записываем в переменную $parent_group_id -->
		<xsl:variable name="parent_group_id" select="/document/information_system/parent_group_id"/>
		
		<h1>
			<xsl:value-of disable-output-escaping="yes" select="item_name"/>
		</h1>
		
		<!-- Путь к группе -->
		<xsl:apply-templates select="//group[@id=$parent_group_id]" mode="breadCrumbs"/>
		
		<!-- Выводим сообщение -->
		<xsl:if test="/document/message/node()">
			<div id="message">
				<xsl:value-of disable-output-escaping="yes" select="/document/message"/>
			</div>
		</xsl:if>
		
		<!-- Фотогафия к информационному элементу -->
		<xsl:if test="item_small_image!=''">
			<!-- Проверяем задан ли путь к файлу большого изображения -->
			<xsl:if test="item_image!=''">
				<a href="{item_image}" target="_blank" onclick="ShowImgWindow('{item_name}','{item_image}', {item_image/@width}, {item_image/@height}); return false;">
					<img align="left" src="{item_small_image}" class="news_img"/>
				</a>
			</xsl:if>
			
			<xsl:if test="item_image =''">
				<img align="left" src="{item_small_image}" style="margin-right: 10px; margin-bottom: 10px"/>
			</xsl:if>
		</xsl:if>
		
		<xsl:if test="//group[@id = $parent_group_id]/propertys/property[@xml_name='big_text'] != ''">
			<p>
				<xsl:value-of disable-output-escaping="yes" select="//group[@id = $parent_group_id]/propertys/property[@xml_name='big_text']/value"/>
			</p>
		</xsl:if>
		
		<!-- Текст информационного элемента -->
		<xsl:choose>
		<xsl:when test="parts_count > 1">
			<xsl:value-of disable-output-escaping="yes" select="text"/>
		</xsl:when>
		<xsl:otherwise>
			<div hostcms:id="{@id}" hostcms:field="text" hostcms:entity="informationsystem_item" hostcms:type="wysiwyg">
				<xsl:value-of disable-output-escaping="yes" select="text"/>
			</div>
		</xsl:otherwise>
		</xsl:choose>
		
		<!-- Средняя оценка элемента -->
		<xsl:if test="item_comments/average_grade/node() and item_comments/average_grade != 0">
			<div style="float: left; margin: 0px 0px 0px 0px">Оценка: <xsl:call-template name="show_average_grade">
					<xsl:with-param name="grade" select="item_comments/average_grade"/>
				<xsl:with-param name="const_grade" select="5"/></xsl:call-template></div>
			
			<div style="clear: both"></div>
		</xsl:if>
		
		<xsl:if test="count(site_user) &gt; 0">
			<p>—
				<img src="/hostcmsfiles/images/user.gif" style="margin: 0px 5px -4px 0px"/>
				<strong>
					<a href="/users/info/{site_user/site_user_login}/" class="c_u_l">
						<xsl:value-of select="site_user/site_user_login"/>
					</a>
				</strong>
			</p>
		</xsl:if>
		
		<!-- Тэги для информационного элемента -->
		<xsl:if test="count(tags/tag) &gt; 0">
			<p>
				<img src="/hostcmsfiles/images/tags.gif" align="left" style="margin: 0px 5px -2px 0px"/>
				<xsl:apply-templates select="tags/tag"/>
			</p>
		</xsl:if>
		
		
		<!-- Ссылка 1-2-3 на части документа -->
		<xsl:if test="parts_count &gt; 1">
			<div class="read_more">Читать дальше:</div>
			
			<xsl:call-template name="for">
				<xsl:with-param name="items_on_page">1</xsl:with-param>
				<xsl:with-param name="current_page" select="/informationsystem/part"/>
				<xsl:with-param name="link" select="/document/item/item_path"/>
				<xsl:with-param name="count_items" select="parts_count"/>
				<xsl:with-param name="visible_pages">6</xsl:with-param>
				<xsl:with-param name="prefix">part</xsl:with-param>
			</xsl:call-template>
			
			<div style="clear: both"></div>
		</xsl:if>
		
		<xsl:if test="count(/document/properties_items_dir)">
			<div style="margin: 10px 0px;">
				<h2>Атрибуты элемента инфосистемы</h2>
				
				<xsl:if test="count(/document/item/item_propertys/item_property[@parent_id = 0])">
					<table border="0">
						<xsl:apply-templates select="/document/item/item_propertys/item_property[@parent_id = 0]"/>
					</table>
				</xsl:if>
				
				<xsl:apply-templates select="/document/properties_items_dir"/>
			</div>
		</xsl:if>
		
		<!-- Если указано отображать комментарии -->
		<xsl:if test="/document/ОтображатьКомментарии/node() and /document/ОтображатьКомментарии = 1">
			
			<!-- Отображение комментариев  -->
			<xsl:if test="count(item_comments/comment) &gt; 0">
				<p class="title">
				<a name="comments"></a>Комментарии</p>
				<xsl:apply-templates select="item_comments/comment"/>
				
			</xsl:if>
		</xsl:if>
		
		<!-- Если разрешено отображать формы добавления комментария
		1 - Только авторизированным
		2 - Всем -->
		<xsl:if test="/document/ПользовательИмеетПравоДобавлятьКомментарии/node()  and ((/document/ПользовательИмеетПравоДобавлятьКомментарии = 1 and /document/site_user_id &gt; 0)  or /document/ПользовательИмеетПравоДобавлятьКомментарии = 2)">
			
			<div id="ShowAddComment">
				<a href="javascript:void(0)" onclick="javascript:cr('AddComment')">Добавить комментарий</a>
			</div>
			
			<div id="AddComment" style="display: none">
				<xsl:call-template name="AddCommentForm"></xsl:call-template>
			</div>
		</xsl:if>
	</xsl:template>
	
	
	<!-- /// Метки для информационного элемента /// -->
	<xsl:template match="tags/tag">
		<a href="{/document/information_system/url}tag/{tag_path_name}/" class="tag">
			<xsl:value-of select="tag_name"/>
		</a>
	<xsl:if test="position() != last()"><xsl:text>, </xsl:text></xsl:if></xsl:template>
	
	<!-- Шаблон для вывода звездочек (оценки) -->
	<xsl:template name="for_star">
		<xsl:param name="i" select="0"/>
		<xsl:param name="n"/>

		<br/>
		
		<xsl:if test="$n &gt; $i and $n &gt; 1">
			<xsl:call-template name="for_star">
				<xsl:with-param name="i" select="$i + 1"/>
				<xsl:with-param name="n" select="$n"/>
			</xsl:call-template>
		</xsl:if>
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
					<img src="/hostcmsfiles/images/stars_single.gif"/>
				</xsl:if>
			</xsl:when>
			<xsl:when test="$current_grade != 0 and not($const_grade &gt; ceiling($current_grade))">
				
				<xsl:if test="$current_grade - 0.5 &gt; 0">
					<xsl:call-template name="show_average_grade">
						<xsl:with-param name="grade" select="$current_grade - 0.5"/>
						<xsl:with-param name="const_grade" select="$const_grade - 1"/>
					</xsl:call-template>
				</xsl:if>
				
				<img src="/hostcmsfiles/images/stars_half.gif"/>
			</xsl:when>
			
			<!-- Выводим серые звездочки, пока текущая позиция не дойдет то значения, увеличенного до целого -->
			<xsl:otherwise>
				<xsl:call-template name="show_average_grade">
					<xsl:with-param name="grade" select="$current_grade"/>
					<xsl:with-param name="const_grade" select="$const_grade - 1"/>
				</xsl:call-template>
				<img src="/hostcmsfiles/images/stars_gray.gif"/>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	
	<!-- Шаблон выводит рекурсивно ссылки на группы инф. элемента -->
	<xsl:template match="group" mode="breadCrumbs">
		<xsl:variable name="parent_id" select="@parent_id"/>
		
		<!-- Выбираем рекурсивно вышестоящую группу -->
		<xsl:apply-templates select="//group[@id=$parent_id]" mode="breadCrumbs"/>
		
		<xsl:if test="@parent_id=0">
			<a href="{/document/information_system/url}">
				<xsl:value-of disable-output-escaping="yes" select="/document/information_system/name"/>
			</a>
		</xsl:if>
		
		<span><xsl:text> → </xsl:text></span>
		
		<a href="{/document/information_system/url}{fullpath}">
			<xsl:value-of select="name"/>
		</a>
	</xsl:template>
	
	<!-- Отображение комментариев -->
	<!-- Отображение комментариев -->
	<xsl:template match="comment">
		
		<!-- Отображаем комментарий, если задан текст или тема комментария -->
		<xsl:if test="comment_text != '' or comment_subject != ''">
			<a name="comment{comment_id}"></a>
			<div class="comment" id="comment{comment_id}">
				<div class="tl"></div>
				<div class="tr"></div>
				<div class="bl"></div>
				<div class="br"></div>
				
				<xsl:if test="comment_subject != ''">
					<div>
						<strong>
							<xsl:value-of select="comment_subject"/>
						</strong>
					</div>
				</xsl:if>
				
				<xsl:value-of select="comment_text" disable-output-escaping="yes"/>
				
				<!-- Оценка комментария -->
				<xsl:if test="comment_grade != 0">
					<div>Оценка:
						<xsl:call-template name="show_average_grade">
							<xsl:with-param name="grade" select="comment_grade"/>
							<xsl:with-param name="const_grade" select="5"/>
						</xsl:call-template>
					</div>
				</xsl:if>
			</div>
			
			<div class="comment_desc">
				<xsl:choose>
					<!-- Комментарий добавил авторизированный пользователь -->
					<xsl:when test="site_user_login/node()">
						<a href="/users/info/{site_user_login}/" class="c_u_l">
							<xsl:value-of select="site_user_login"/>
						</a><xsl:text> · </xsl:text><xsl:value-of select="comment_datetime"/>
					</xsl:when>
					<!-- Комментарй добавил неавторизированный пользователь -->
					<xsl:otherwise>
						<xsl:value-of select="comment_fio"/><xsl:text> · </xsl:text><xsl:value-of select="comment_datetime"/></xsl:otherwise>
				</xsl:choose><xsl:text> · </xsl:text><xsl:if
					test="/document/ПользовательИмеетПравоДобавлятьКомментарии/node()
					and ((/document/ПользовательИмеетПравоДобавлятьКомментарии = 1 and /document/site_user_id > 0)
		or /document/ПользовательИмеетПравоДобавлятьКомментарии = 2)"><a href="javascript:cr('cr_{comment_id}');">ответить</a><xsl:text> · </xsl:text></xsl:if><a href="{/document/item/item_path}#comment{comment_id}" title="ссылка">#</a>
				
			</div>
			
			<!-- Отображаем только авторизированным пользователям -->
			<xsl:if test="/document/ПользовательИмеетПравоДобавлятьКомментарии/node() and ((/document/ПользовательИмеетПравоДобавлятьКомментарии = 1 and /document/site_user_id > 0) or /document/ПользовательИмеетПравоДобавлятьКомментарии = 2)">
				<div class="cr" id="cr_{comment_id}">
					
					<xsl:call-template name="AddCommentForm">
						<xsl:with-param name="comment_id" select="comment_id"/>
					</xsl:call-template>
				</div>
			</xsl:if>
			
			<!-- Выбираем дочерние комментарии -->
			<xsl:if test="count(comment) > 0">
				<div class="csd">
					<xsl:apply-templates select="comment"/>
				</div>
			</xsl:if>
		</xsl:if>
	</xsl:template>
	
	<!-- Шаблон выводит группы свойств для элемента инфосистемы -->
	<xsl:template match="properties_items_dir">
		
		<p>
			<b>
				<xsl:value-of select="information_propertys_items_dir_name"/>
			</b>
		</p>
		
		<xsl:variable name="dir_id" select="@id"/>
		
		<xsl:if test="count(/document/item/item_propertys/item_property[@parent_id = $dir_id])">
			<table border="0">
				<xsl:apply-templates select="/document/item/item_propertys/item_property[@parent_id = $dir_id]"/>
			</table>
		</xsl:if>
		
		<xsl:if test="count(properties_items_dir)">
			<blockquote>
				<xsl:apply-templates select="properties_items_dir"/>
			</blockquote>
		</xsl:if>
	</xsl:template>
	
	<!-- Вывод строки со значением свойства -->
	<xsl:template match="item_property">
		<tr>
			<td style="padding: 5px" bgcolor="#eeeeee">
				<b>
					<xsl:value-of select="property_name"/>
				</b>
			</td>
			<td style="padding: 5px" bgcolor="#eeeeee">
				<xsl:choose>
					<xsl:when test="type = 1">
						<a href="{file_path}">Скачать файл</a>
					</xsl:when>
					<xsl:when test="type = 7">
						<xsl:choose>
							<xsl:when test="value = 1">
								<input type="checkbox" checked="" disabled=""/>
							</xsl:when>
							<xsl:otherwise>
								<input type="checkbox" disabled=""/>
							</xsl:otherwise>
						</xsl:choose>
					</xsl:when>
					<xsl:otherwise>
						<xsl:value-of disable-output-escaping="yes" select="value"/>
					</xsl:otherwise>
				</xsl:choose>
			</td>
		</tr>
	</xsl:template>
	
	<!-- Шаблон вывода добавления комментария -->
	<xsl:template name="AddCommentForm">
		<xsl:param name="comment_id" select="0"/>
		
		<div class="comment">
			<div class="tl"></div>
			<div class="tr"></div>
			<div class="bl"></div>
			<div class="br"></div>
			
			<!--Отображение формы добавления комментария-->
			<form action="{/document/url}" name="comment_form_0{comment_id}" method="post">
				<!-- Авторизированным не показываем -->
				<xsl:if test="/document/site_user_id = 0">Имя
					<br/>
					<input type="text" size="70" name="comment_autor" value=""/>
					
					<p>E-mail
						<br/>
						<input type="text" size="70" name="comment_email" value=""/>
					</p>
					
					<p>Тема
						<br/>
						<input type="text" size="70" name="comment_subject" value=""/>
					</p>
				</xsl:if>
				
				<p>Комментарий
					<br/>
					<textarea name="comment_text" cols="68" rows="5" class="mceEditor"></textarea>
				</p>
				
				<p>Оценка
					<br/>
					<input type="hidden" name="comment_grade" value="0"/>
					
					<ul id="0{comment_id}_stars" class="stars">
						<li onclick="set_rate(this.id, this.id)" onmouseover="set_rate(this.id, '-1')" onmouseout="set_rate(this.id, 0)" id="{comment_id}1"></li>
						<li onclick="set_rate(this.id, this.id)" onmouseover="set_rate(this.id, '-1')" onmouseout="set_rate(this.id, 0)" id="{comment_id}2"></li>
						<li onclick="set_rate(this.id, this.id)" onmouseover="set_rate(this.id, '-1')" onmouseout="set_rate(this.id, 0)" id="{comment_id}3"></li>
						<li onclick="set_rate(this.id, this.id)" onmouseover="set_rate(this.id, '-1')" onmouseout="set_rate(this.id, 0)" id="{comment_id}4"></li>
						<li onclick="set_rate(this.id, this.id)" onmouseover="set_rate(this.id, '-1')" onmouseout="set_rate(this.id, 0)" id="{comment_id}5"></li>
					</ul>
				</p>
				
				<!-- Обработка CAPTCHA -->
				<xsl:if test="//captcha_key != 0 and /document/site_user_id = 0">
					<p>Код подтверждения
						<br/>
						<div style="float: left">
							<img class="image" src="/captcha.php?get_captcha={//captcha_key}&amp;height=28" />
						</div>
						
						<div style="float: left; margin-left: 10px; margin-top: 5px">
							<input type="hidden" name="captcha_key" value="{//captcha_key}"/>
							<input type="text" name="captcha_keystring" size="15"/>
						</div>
						
						<div style="clear: both"></div>
					</p>
				</xsl:if>
				
				<xsl:if test="$comment_id != 0">
					<input type="hidden" name="comment_parent_id" value="{comment_id}"/>
				</xsl:if>
				
				<p>
					<input type="submit" name="add_comment" value="Опубликовать"/>
				</p>
			</form>
		</div>
	</xsl:template>
	
	<!-- Цикл для вывода строк ссылок -->
	<xsl:template name="for">
		<xsl:param name="i" select="0"/>
		<xsl:param name="prefix">page</xsl:param>
		<xsl:param name="link"/>
		<xsl:param name="items_on_page"/>
		<xsl:param name="current_page"/>
		<xsl:param name="count_items"/>
		<xsl:param name="visible_pages"/>
		
		<xsl:variable name="n" select="$count_items div $items_on_page"/>
		
		<!-- Заносим в переменную $parent_group_id идентификатор текущей группы -->
		<xsl:variable name="parent_group_id" select="/document/blocks/parent_group_id"/>
		
		
		<!-- Считаем количество выводимых ссылок перед текущим элементом -->
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
		
		<!-- Считаем количество выводимых ссылок после текущего элемента -->
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
			<!-- Ставим ссылку на страницу-->
			<xsl:if test="$i != $current_page">
				<!-- Определяем адрес тэга -->
				<xsl:variable name="tag_link">
					<xsl:choose>
						<!-- Если не нулевой уровень -->
						<xsl:when test="count(/document/blocks/selected_tags/tag) != 0">tag/<xsl:value-of select="/document/blocks/selected_tags/tag/tag_path_name"/>/</xsl:when>
						<!-- Иначе если нулевой уровень - просто ссылка на страницу со списком элементов -->
						<xsl:otherwise></xsl:otherwise>
					</xsl:choose>
				</xsl:variable>
				
				<!-- Определяем адрес ссылки -->
				<xsl:variable name="number_link">
					<xsl:choose>
						<!-- Если не нулевой уровень -->
						<xsl:when test="$i != 0">
							<xsl:value-of select="$prefix"/>-<xsl:value-of select="$i + 1"/>/</xsl:when>
						<!-- Иначе если нулевой уровень - просто ссылка на страницу со списком элементов -->
						<xsl:otherwise></xsl:otherwise>
					</xsl:choose>
				</xsl:variable>
				
				<!-- Выводим ссылку на первую страницу -->
				<xsl:if test="$current_page - $pre_count_page &gt; 0 and $i = 0">
					<a href="{$link}" class="page_link" style="text-decoration: none;">←</a>
				</xsl:if>
				
				<xsl:choose>
					<xsl:when test="$i &gt;= ($current_page - $pre_count_page) and ($current_page + $post_count_page) &gt;= $i">
						
						<!-- Выводим ссылки на видимые страницы -->
						<a href="{$link}{$tag_link}{$number_link}" class="page_link">
							<xsl:value-of select="$i + 1"/>
						</a>
					</xsl:when>
					<xsl:otherwise></xsl:otherwise>
				</xsl:choose>
				
				<!-- Выводим ссылку на последнюю страницу -->
				<xsl:if test="$i+1 &gt;= $n and $n &gt; ($current_page + 1 + $post_count_page)">
					<xsl:choose>
						<xsl:when test="$n &gt; round($n)">
							<!-- Выводим ссылку на последнюю страницу -->
							<a href="{$link}{$prefix}-{round($n+1)}/" class="page_link" style="text-decoration: none;">→</a>
						</xsl:when>
						<xsl:otherwise>
							<a href="{$link}{$prefix}-{round($n)}/" class="page_link" style="text-decoration: none;">→</a>
						</xsl:otherwise>
					</xsl:choose>
				</xsl:if>
			</xsl:if>
			
			<!-- Не ставим ссылку на страницу-->
			<xsl:if test="$i = $current_page">
				<span class="current">
					<xsl:value-of select="$i+1"/>
				</span>
			</xsl:if>
			
			<!-- Рекурсивный вызов шаблона. НЕОБХОДИМО ПЕРЕДАВАТЬ ВСЕ НЕОБХОДИМЫЕ ПАРАМЕТРЫ! -->
			<xsl:call-template name="for">
				<xsl:with-param name="i" select="$i + 1"/>
				<xsl:with-param name="prefix" select="$prefix"/>
				<xsl:with-param name="link" select="$link"/>
				<xsl:with-param name="items_on_page" select="$items_on_page"/>
				<xsl:with-param name="current_page" select="$current_page"/>
				<xsl:with-param name="count_items" select="$count_items"/>
				<xsl:with-param name="visible_pages" select="$visible_pages"/>
			</xsl:call-template>
		</xsl:if>
	</xsl:template>
	
	<!-- Склонение после числительных -->
	<xsl:template name="declension">
		
		<xsl:param name="number" select="number"/>
		
		<!-- Именительный падеж -->
		<xsl:variable name="nominative">
			<xsl:text>просмотр</xsl:text>
		</xsl:variable>
		
		<!-- Родительный падеж, единственное число -->
		<xsl:variable name="genitive_singular">
			<xsl:text>просмотра</xsl:text>
		</xsl:variable>
		
		
		<xsl:variable name="genitive_plural">
			<xsl:text>просмотров</xsl:text>
		</xsl:variable>
		
		<xsl:variable name="last_digit">
			<xsl:value-of select="$number mod 10"/>
		</xsl:variable>
		
		<xsl:variable name="last_two_digits">
			<xsl:value-of select="$number mod 100"/>
		</xsl:variable>
		
		<xsl:choose>
			<xsl:when test="$last_digit = 1 and $last_two_digits != 11">
				<xsl:value-of select="$nominative"/>
			</xsl:when>
			<xsl:when test="$last_digit = 2 and $last_two_digits != 12      or $last_digit = 3 and $last_two_digits != 13      or $last_digit = 4 and $last_two_digits != 14">
				<xsl:value-of select="$genitive_singular"/>
			</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="$genitive_plural"/>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
</xsl:stylesheet>