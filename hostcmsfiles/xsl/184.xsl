<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "lang://184">
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	xmlns:exsl="http://exslt.org/common"
    extension-element-prefixes="exsl"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>

	<xsl:template match="/">
		<SCRIPT type="text/javascript">
			<xsl:comment>
				<xsl:text disable-output-escaping="yes">
					<![CDATA[
						$(function() {
							$('.addFile').click(function(){
								r = $(this).parents('.row');
								r2 = r.clone();
								r2.find('.caption').text('');
								r2.find('a').remove();
								r.after(r2);
								return false;
							});
						});
					]]>
				</xsl:text>
			</xsl:comment>
		</SCRIPT>

		<xsl:apply-templates select="helpdesk/helpdesk_ticket" />
	</xsl:template>

	<!-- ВыводСпискаСообщенийТикета -->
	<xsl:template match="helpdesk_ticket">
		<h1>
			<xsl:value-of select="helpdesk_message/subject"/><xsl:text> — [</xsl:text><xsl:value-of select="number"/>]
		</h1>

		<!-- Хлебные крошки -->
		<p>
			<a href="{/helpdesk/url}">&labelHelpdesk; <xsl:value-of select="/helpdesk/name"/></a>
			<span><xsl:text> → </xsl:text></span>
			<xsl:value-of select="number"/>
		</p>

		<xsl:if test="error_message/node()">
			<div id="message">
				<xsl:value-of disable-output-escaping="yes" select="error_message"/>
			</div>
		</xsl:if>

		<xsl:if test="count(helpdesk_message[parent_id = 0])">
			<div style="margin-bottom: 20px"><xsl:apply-templates select="helpdesk_message[parent_id = 0]" /></div>
		</xsl:if>

		<p class="button" onclick="$('.comment_reply').hide('slow');$('#AddMessage').toggle('slow')">&labelAddMessage;</p>

		<div id="AddMessage" class="comment_reply">
			<xsl:call-template name="AddReplyForm"></xsl:call-template>
		</div>
	</xsl:template>

		<!-- Шаблон для вывода сообщения -->
	<xsl:template match="helpdesk_message">
		<div id="{@id}" class="comment">
			<div class="subject"><xsl:value-of select="subject"/></div>

			<div>
				<xsl:value-of disable-output-escaping="yes" select="message"/>
			</div>

			<p class="tags">
				<!-- Оценка сообщения - только для исходящих -->
				<xsl:if test="inbox = 0">
					<!-- <span><xsl:call-template name="show_grade">
						<xsl:with-param name="grade" select="grade"/>
						<xsl:with-param name="const_grade" select="5"/>
					</xsl:call-template></span> -->

					<xsl:variable name="options">
						<option value="1">Poor</option>
						<option value="2">Fair</option>
						<option value="3">Average</option>
						<option value="4">Good</option>
						<option value="5">Excellent</option>
					</xsl:variable>

					<xsl:variable name="grade" select="grade" />

					<div id="grade{@id}">
					<select name="grade">
						<xsl:for-each select="exsl:node-set($options)/option">
							<option value="{@value}">
								<xsl:if test="@value = $grade">
									<xsl:attribute name="selected">selected</xsl:attribute>
								</xsl:if>
								<xsl:value-of select="." />
							</option>
						</xsl:for-each>
					</select>
					</div>
					<span><xsl:text> </xsl:text></span>

					<SCRIPT>
					$(function() {
						$('#grade<xsl:value-of select="@id" />').stars({inputType: "select", disableValue: false, callback: function(object, type, value, e){
								$.ajax({
									url: './',
									type: "POST",
									dataType: "json",
									data: {ajaxGrade: 1, value: value, id: '<xsl:value-of select="@id" />'}
								});
							}
						});
					});
					</SCRIPT>
				</xsl:if>

				<xsl:if test="user/node() or ../siteuser/node()">
					<img src="/images/user.png" />
					<span>
						<xsl:choose>
								<!-- Сообщение добавил пользователь сайта -->
								<xsl:when test="inbox = 1">
									<xsl:value-of select="../siteuser/login"/>
								</xsl:when>
								<!-- Сообщение добавил пользователь центра администрирования-->
								<xsl:otherwise>
									<xsl:value-of select="user/position"/><xsl:text> </xsl:text><xsl:value-of select="user/name"/><xsl:text> </xsl:text><xsl:value-of select="user/surname"/>
								</xsl:otherwise>
						</xsl:choose>
					</span>
				</xsl:if>

				<img src="/images/calendar.png" /> <span><xsl:value-of select="datetime"/></span>

				<span class="red" onclick="$('.comment_reply').hide('slow');$('#cr_{@id}').toggle('slow')">&labelReply;</span>
			</p>

			<xsl:if test="helpdesk_attachment/node()">
				<div>
					<p>
						&labelAttachment;
					</p>
					<ul style="list-style-type: none; margin-top: 5px; padding-left: 5px">
						<xsl:apply-templates select="helpdesk_attachment"/>
					</ul>
				</div>
			</xsl:if>
		</div>

		<div class="comment_reply" id="cr_{@id}">
			<xsl:call-template name="AddReplyForm">
				<xsl:with-param name="message_id" select="@id"/>
				<xsl:with-param name="message_parent_id" select="@id"/>
				<xsl:with-param name="message_comment_subject">Re: <xsl:value-of select="subject" /></xsl:with-param>
			</xsl:call-template>
		</div>

		<!-- Выбираем дочерние сообщения-->
		<xsl:variable name="id" select="@id"/>
		<xsl:if test="count(//helpdesk_message[parent_id = $id]) > 0">
			<div class="comment_sub">
				<xsl:apply-templates select="../helpdesk_message[parent_id = $id]"/>
			</div>
		</xsl:if>
	</xsl:template>

	<!-- Оценка -->
	<xsl:template name="show_grade">
		<xsl:param name="grade" select="0"/>
		<xsl:param name="const_grade" select="0"/>

		<!-- To avoid loops -->
		<xsl:variable name="current_grade" select="$grade * 1"/>

		<xsl:choose>
			<!-- If a value is an integer -->
			<xsl:when test="not($const_grade &gt; $current_grade)">
				<xsl:if test="$current_grade - 1 &gt; 0">
					<xsl:call-template name="show_grade">
						<xsl:with-param name="grade" select="$current_grade - 1"/>
						<xsl:with-param name="const_grade" select="$const_grade - 1"/>
					</xsl:call-template>
				</xsl:if>
				<xsl:if test="$current_grade != 0">
					<img src="/images/star-full.png"/>
				</xsl:if>
			</xsl:when>

			<!-- Show the gray stars until the current position does not reach the value increased to an integer -->
			<xsl:otherwise>
				<xsl:call-template name="show_grade">
					<xsl:with-param name="grade" select="$current_grade"/>
					<xsl:with-param name="const_grade" select="$const_grade - 1"/>
				</xsl:call-template>
				<img src="/images/star-empty.png"/>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

	<xsl:variable name="lowCase">абвгдеёжзийклмнопрстуфхцчшщыъьэюяabcdefghijklmnopqrstuvwxyz</xsl:variable>
	<xsl:variable name="upCase">АБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЫЪЬЭЮЯABCDEFGHIJKLMNOPQRSTUVWXYZ</xsl:variable>

	<xsl:template name="upper">
		<xsl:param name="str" />
		<xsl:value-of select="translate($str, $lowCase, $upCase)"/>
	</xsl:template>

	<xsl:template name="lower">
		<xsl:param name="str" />
		<xsl:value-of select="translate($str, $upCase, $lowCase)"/>
	</xsl:template>

	<!-- Вывод вложенных файлов сообщения -->
	<xsl:template match="helpdesk_attachment">
		<!-- Ссылка на вложенный файл -->
		<!-- Определяем ссылку -->
		<xsl:variable name="attachment">
			./?get_attachment_id=<xsl:value-of select="@id"/>
		</xsl:variable>

		<xsl:variable name="file_name">
			<xsl:call-template name="lower">
				<xsl:with-param name="str"><xsl:value-of select="file_name"/></xsl:with-param>
			</xsl:call-template>
		</xsl:variable>

		<!-- Определяем расширения файла -->
		<xsl:variable name="extension">
			<xsl:call-template name="Extension">
				<xsl:with-param name="string" select="$file_name"/>
			</xsl:call-template>
		</xsl:variable>
		<li>
			<!-- Пиктограмма в соответствии с расширением файла -->
			<xsl:choose>
				<!-- Если есть точка в подстроке -->
				<xsl:when test="$extension='zip'">
					<img src="/hostcmsfiles/images/icons/zip.gif"/>
				</xsl:when>
				<xsl:when test="$extension='bmp'">
					<img src="/hostcmsfiles/images/icons/bmp.gif"/>
				</xsl:when>
				<xsl:when test="$extension='chm'">
					<img src="/hostcmsfiles/images/icons/chm.gif"/>
				</xsl:when>
				<xsl:when test="$extension='config'">
					<img src="/hostcmsfiles/images/icons/config.gif"/>
				</xsl:when>
				<xsl:when test="$extension='cpp'">
					<img src="/hostcmsfiles/images/icons/cpp.gif"/>
				</xsl:when>
				<xsl:when test="$extension='css'">
					<img src="/hostcmsfiles/images/icons/css.gif"/>
				</xsl:when>
				<xsl:when test="$extension='doc'">
					<img src="/hostcmsfiles/images/icons/doc.gif"/>
				</xsl:when>
				<xsl:when test="$extension='fh'">
					<img src="/hostcmsfiles/images/icons/fh.gif"/>
				</xsl:when>
				<xsl:when test="$extension='flash'">
					<img src="/hostcmsfiles/images/icons/flash.gif"/>
				</xsl:when>
				<xsl:when test="$extension='gif'">
					<img src="/hostcmsfiles/images/icons/gif.gif"/>
				</xsl:when>
				<xsl:when test="$extension='h'">
					<img src="/hostcmsfiles/images/icons/h.gif"/>
				</xsl:when>
				<xsl:when test="$extension='html' or $extension='htm' or $extension='xhtml'">
					<img src="/hostcmsfiles/images/icons/html.gif"/>
				</xsl:when>
				<xsl:when test="$extension='image'">
					<img src="/hostcmsfiles/images/icons/image.gif"/>
				</xsl:when>
				<xsl:when test="$extension='jpg' or $extension='jpeg'">
					<img src="/hostcmsfiles/images/icons/jpg.gif"/>
				</xsl:when>
				<xsl:when test="$extension='mdb'">
					<img src="/hostcmsfiles/images/icons/mdb.gif"/>
				</xsl:when>
				<xsl:when test="$extension='pdf'">
					<img src="/hostcmsfiles/images/icons/pdf.gif"/>
				</xsl:when>
				<xsl:when test="$extension='php'">
					<img src="/hostcmsfiles/images/icons/php.gif"/>
				</xsl:when>
				<xsl:when test="$extension='png'">
					<img src="/hostcmsfiles/images/icons/png.gif"/>
				</xsl:when>
				<xsl:when test="$extension='ppt'">
					<img src="/hostcmsfiles/images/icons/ppt.gif"/>
				</xsl:when>
				<xsl:when test="$extension='rar'">
					<img src="/hostcmsfiles/images/icons/rar.gif"/>
				</xsl:when>
				<xsl:when test="$extension='rb'">
					<img src="/hostcmsfiles/images/icons/rb.gif"/>
				</xsl:when>
				<xsl:when test="$extension='sql'">
					<img src="/hostcmsfiles/images/icons/sql.gif"/>
				</xsl:when>
				<xsl:when test="$extension='txt'">
					<img src="/hostcmsfiles/images/icons/txt.gif"/>
				</xsl:when>
				<xsl:when test="$extension='vector'">
					<img src="/hostcmsfiles/images/icons/vector.gif"/>
				</xsl:when>
				<xsl:when test="$extension='xls'">
					<img src="/hostcmsfiles/images/icons/xls.gif"/>
				</xsl:when>
				<xsl:when test="$extension='xml'">
					<img src="/hostcmsfiles/images/icons/xml.gif"/>
				</xsl:when>
				<xsl:when test="$extension='xsl'">
					<img src="/hostcmsfiles/images/icons/xsl.gif"/>
				</xsl:when>
				<xsl:otherwise>
					<img src="/hostcmsfiles/images/icons/file.gif"/>
				</xsl:otherwise>

			</xsl:choose>

		<xsl:text> </xsl:text><a href="{$attachment}" target="blank"><xsl:value-of select="file_name"/></a><xsl:text> </xsl:text><span class="tags">(<xsl:value-of select="size"/><xsl:text> </xsl:text><xsl:value-of select="size_measure"/>)</span>
		</li>
	</xsl:template>

	<!-- Определение расширения файла -->
	<xsl:template name="Extension">
		<xsl:param name="string" select="string"/>

		<!-- Получаем подстроку после точки -->
		<xsl:variable name="ext">
			<xsl:value-of select="substring-after($string, '.')"/>
		</xsl:variable>

		<xsl:choose>
			<!-- Если есть точка в подстроке -->
			<xsl:when test="contains($ext, '.')">
				<xsl:call-template name="Extension">
					<xsl:with-param name="string" select="$ext"/>
				</xsl:call-template>
			</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="$ext"/>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

	<!-- Star Rating -->
	<xsl:template name="show_average_grade">
		<xsl:param name="grade" select="0"/>
		<xsl:param name="const_grade" select="0"/>

		<!-- To avoid loops -->
		<xsl:variable name="current_grade" select="$grade * 1"/>

		<xsl:choose>
			<!-- If a value is an integer -->
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

			<!-- Show the gray stars until the current position does not reach the value increased to an integer -->
			<xsl:otherwise>
				<xsl:call-template name="show_average_grade">
					<xsl:with-param name="grade" select="$current_grade"/>
					<xsl:with-param name="const_grade" select="$const_grade - 1"/>
				</xsl:call-template>
				<img src="/hostcmsfiles/images/stars_gray.gif"/>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

	<!-- Шаблон вывода добавления сообщения-->
	<xsl:template name="AddReplyForm">
		<xsl:param name="message_id" select="0" />
		<xsl:param name="message_parent_id" select="0" />
		<xsl:param name="message_comment_subject" />

		<div class="comment">

			
			<form action="{/url}" name="message_form_0{$message_id}" method="post" enctype="multipart/form-data">

				<input type="hidden" name="parent_id" value="{$message_parent_id}"/>

				<div class="row">
					<div class="caption">&labelSubject;</div>
					<div class="field">
						<input type="text" size="70" name="message_subject" value="{$message_comment_subject}"/>
					</div>
				</div>

				<div class="row">
					<div class="caption">&labelText;</div>
					<div class="field">
						<xsl:choose>
							<xsl:when test="/helpdesk/message_type = 0">
								<textarea name="message_text" cols="68" rows="5" class="mceEditor"></textarea>
							</xsl:when>
							<xsl:otherwise>
								<textarea name="message_text" cols="68" rows="5"/>
							</xsl:otherwise>
						</xsl:choose>
					</div>
				</div>

				<!-- {$message_id} добавляется для придания имени блока уникальности, т.к. таких блоков несколько -->
				<div id="helpdesk_upload_file{$message_id}" class="row">
					<div class="caption">&labelAddFile;</div>
					<div class="field">
					<input size="30" name="attachment[]" type="file" title="&labelAddFile;" />
					<xsl:text> </xsl:text><a href="#" class="addFile">&labelMoreFile;</a></div>
				</div>

				<div class="row">
					<div class="caption"></div>
					<div class="field"><input type="submit" name="send_message" class="button" value="&labelSend;"/></div>
				</div>
			</form>
		</div>
	</xsl:template>
</xsl:stylesheet>