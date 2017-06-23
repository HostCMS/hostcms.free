<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "lang://183">
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>

	<xsl:template match="/">
		<xsl:apply-templates select="helpdesk" />
	</xsl:template>

	<!-- ВыводСпискаТикетов -->

	<xsl:template match="helpdesk">
		<SCRIPT type="text/javascript">
			<xsl:comment>
				<xsl:text disable-output-escaping="yes">
					<![CDATA[
						$(function() {
							$('#addFile').click(function(){
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

		<h1>&labelHelpdesk; <xsl:value-of select="name"/></h1>

		<xsl:if test="message/node()">
			<div id="message">
				<xsl:value-of disable-output-escaping="yes" select="message"/>
			</div>
		</xsl:if>

		<form method="get" action="{/helpdesk/url}">
			&labelTicketStatus;
			<select name="status">
				<option value="-1">&labelAny;</option>
				<option value="1">
					<xsl:if test="apply_filter = 1"><xsl:attribute name="selected"></xsl:attribute></xsl:if>&labelOpen;
				</option>
				<option value="0">
					<xsl:if test="apply_filter = 0"><xsl:attribute name="selected"></xsl:attribute></xsl:if>&labelClose;
				</option>
			</select>
			<input type="submit" class="button" style="margin-left: 15px" value="&labelShow;" name="apply_filter"/>
		</form>

		<div class="clearing"></div>

		<div id="ShowAddTicket">
			<p>
			<img src="/hostcmsfiles/images/add.gif" alt="&labelAddTicket;" style="margin: 0px 5px -2px 6px"/>
			<a href="#" onclick="$('#AddTicket').toggle('slow'); return false;">&labelAddTicket;</a>
			<img src="/hostcmsfiles/images/calendar.gif" alt="&labelWorkSchedule;" style="margin: 0px 5px -2px 6px; margin-left: 10px"/>
			<a href="{url}worktime/">&labelWorkSchedule;</a>
			</p>
		</div>

		<div id="AddTicket" style="display: none">
			<xsl:call-template name="AddTicketForm"></xsl:call-template>
		</div>

		<xsl:choose>
			<xsl:when test="helpdesk_ticket/node()">
				<table class="table">
					<tr>
						<th width="65px">&labelTicket;</th>
						<th>&labelSubject;</th>
						<th width="125px">&labelDate;</th>
						<th width="80px">&labelProcessed;</th>
						<th>&labelStatus;</th>
						<th style="text-align: center">—</th>
					</tr>
					<xsl:apply-templates select="helpdesk_ticket" />
				</table>
			</xsl:when>
			<xsl:otherwise>
				<p><span style="color: #777">&labelNone;</span></p>
			</xsl:otherwise>
		</xsl:choose>

		<p>
			<xsl:call-template name="for">
				<xsl:with-param name="link" select="/helpdesk/url"/>
				<xsl:with-param name="ticket_on_page" select="/helpdesk/limit"/>
				<xsl:with-param name="current_page" select="/helpdesk/page"/>
				<xsl:with-param name="ticket_count" select="/helpdesk/total"/>
				<xsl:with-param name="visible_pages" select="5"/>
			</xsl:call-template>
		</p>
		<div style="clear: both"></div>
	</xsl:template>

	<xsl:template match="helpdesk_ticket">
		<tr>
			<td>
				<!-- Тикет -->
				<a href="{/helpdesk/url}ticket-{@id}/">
					<xsl:value-of select="number"/>
				</a>
			</td>
			<td>
				<!-- Тема -->
				<b>
					<xsl:choose>
					<xsl:when test="open = 0">
						<strike><xsl:value-of select="helpdesk_message/subject"/></strike>
					</xsl:when>
					<xsl:otherwise>
						<xsl:value-of select="helpdesk_message/subject"/>
					</xsl:otherwise>
					</xsl:choose>
				</b>
				<xsl:variable name="category_id" select="helpdesk_category_id" />

				<xsl:if test="/helpdesk//helpdesk_category[@id = $category_id]/node() and /helpdesk//helpdesk_category[@id = $category_id]/name != ''">
					<br/>&labelIn; "<xsl:value-of disable-output-escaping="yes" select="/helpdesk//helpdesk_category[@id = $category_id]/name"/>"
				</xsl:if>
			</td>
			<td>
				<!-- Дата -->
				<xsl:value-of select="datetime"/>
			</td>
			<td align="center">
				<!-- Обработано -->
				<b><xsl:value-of select="processed_messages_count"/></b>/<xsl:value-of select="messages_count"/>
			</td>
			<td>
				<!-- Состояние -->
				<xsl:choose>
					<xsl:when test="open = 0">
						<span class="helpdesk_status_1">&labelTicketClose;</span>
					</xsl:when>
					<xsl:otherwise>
						<xsl:choose>
							<xsl:when test="processed_messages_count = messages_count">
								<!-- <span class="helpdesk_status_1">&labelWait;</span> -->
							</xsl:when>
							<xsl:otherwise>
								<!-- Возможные состояния
								1 - Ответ не дан вовремя
								2 - Ожидание ответа пользователя-->
								<xsl:choose>
									<xsl:when test="expire > 0">
										<xsl:if test="expire = 1"><span class="helpdesk_status_2">&labelReplySoon;</span></xsl:if>
										<xsl:if test="expire = 2"><span class="helpdesk_status_1">&labelWait;</span></xsl:if>
									</xsl:when>
									<xsl:otherwise>
										<xsl:if test="expire_after_days/node() or expire_after_hours/node() or expire_after_minutes/node()">
										<span class="helpdesk_status_2">&labelAnswer;<xsl:choose>
										<xsl:when test="expire_after_days/node()">
										<xsl:if test="expire_after_days/node()"><xsl:text> </xsl:text><xsl:value-of select="expire_after_days"/><xsl:text> </xsl:text>
												<xsl:variable name="nominative">&labelDay;</xsl:variable>
												<xsl:variable name="genitive_singular">&labelDays;</xsl:variable>
												<xsl:variable name="genitive_plural">&labelDays;</xsl:variable>

												<xsl:call-template name="declension">
													<xsl:with-param name="number" select="expire_after_days"/>
													<xsl:with-param name="nominative" select="$nominative"/>
													<xsl:with-param name="genitive_singular" select="$genitive_singular"/>
													<xsl:with-param name="genitive_plural" select="$genitive_plural"/>
												</xsl:call-template>
											</xsl:if>
											</xsl:when>
											<xsl:otherwise>
											<xsl:if test="expire_after_hours/node()"><xsl:text> </xsl:text><xsl:value-of select="expire_after_hours"/><xsl:text> </xsl:text>
												<xsl:variable name="nominative">&labelHour;</xsl:variable>
												<xsl:variable name="genitive_singular">&labelHours;</xsl:variable>
												<xsl:variable name="genitive_plural">&labelHours;</xsl:variable>

												<xsl:call-template name="declension">
													<xsl:with-param name="number" select="expire_after_hours"/>
													<xsl:with-param name="nominative" select="$nominative"/>
													<xsl:with-param name="genitive_singular" select="$genitive_singular"/>
													<xsl:with-param name="genitive_plural" select="$genitive_plural"/>
												</xsl:call-template>
											</xsl:if>
											<xsl:if test="expire_after_minutes/node()"><xsl:text> </xsl:text><xsl:value-of select="expire_after_minutes"/><xsl:text> </xsl:text><xsl:variable name="nominative">&labelMinute;</xsl:variable>
												<xsl:variable name="genitive_singular">&labelMinutes;</xsl:variable>
												<xsl:variable name="genitive_plural">&labelMinutes;</xsl:variable>

												<xsl:call-template name="declension">
													<xsl:with-param name="number" select="expire_after_minutes"/>
													<xsl:with-param name="nominative" select="$nominative"/>
													<xsl:with-param name="genitive_singular" select="$genitive_singular"/>
													<xsl:with-param name="genitive_plural" select="$genitive_plural"/>
												</xsl:call-template>
											</xsl:if>.
											</xsl:otherwise>
											</xsl:choose>
											</span>
										</xsl:if>
									</xsl:otherwise>
								</xsl:choose>
							</xsl:otherwise>
						</xsl:choose>
					</xsl:otherwise>
				</xsl:choose>
			</td>
			<!-- Закрыть/открыть тикет -->
			<td align="center">
				<xsl:choose>
					<xsl:when test="open = 0">
						<a href="./?open_ticket={@id}"><img src="/hostcmsfiles/images/lock.gif" alt="&labelOpenTicket;" title="&labelOpenTicket;" /></a>
					</xsl:when>
					<xsl:otherwise>
						<a href="./?close_ticket={@id}"><img src="/hostcmsfiles/images/lock_open.gif" alt="&labelCloseTicket;" title="&labelCloseTicket;"/></a>
					</xsl:otherwise>
				</xsl:choose>
			</td>
		</tr>
	</xsl:template>

	<!-- Шаблон для уровней критичности -->
	<xsl:template match="helpdesk_criticality_level">
		<option value="{@id}">
			<xsl:if test="@id = /helpdesk/helpdesk_criticality_level_id">
				<xsl:attribute name="selected"></xsl:attribute>
			</xsl:if>
			<xsl:value-of select="name"/>
		</option>
	</xsl:template>

	<!-- Шаблон для категорий тикетов -->
	<xsl:template match="helpdesk_category">
		<xsl:variable name="i" select="count(ancestor::helpdesk_category)" />
		<option value="{@id}">
			<xsl:if test="@id = /helpdesk/helpdesk_category_id">
				<xsl:attribute name="selected"></xsl:attribute>
			</xsl:if>
			<xsl:call-template name="for_criticality_level_name">
				<xsl:with-param name="i" select="$i"/>
			</xsl:call-template>
			<xsl:value-of select="name"/>
		</option>
		<xsl:apply-templates select="helpdesk_category" />
	</xsl:template>

	<!-- Цикл для вывода пробелов перед категориями -->
	<xsl:template name="for_criticality_level_name">
		<xsl:param name="i" select="0" />
		<xsl:if test="$i > 0">
			<xsl:text> </xsl:text>
			<xsl:call-template name="for_criticality_level_name">
				<xsl:with-param name="i" select="$i - 1"/>
			</xsl:call-template>
		</xsl:if>
	</xsl:template>

	<!-- Pagination -->
	<xsl:template name="for">
		<xsl:param name="i" select="0"/>
		<xsl:param name="prefix">page</xsl:param>
		<xsl:param name="link"/>
		<xsl:param name="ticket_on_page"/>
		<xsl:param name="current_page"/>
		<xsl:param name="ticket_count"/>
		<xsl:param name="visible_pages"/>

		<xsl:variable name="n" select="$ticket_count div $ticket_on_page"/>

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

		<xsl:if test="$i = 0 and $current_page != 0">
			<span class="ctrl">
				← Ctrl
			</span>
		</xsl:if>

		<xsl:if test="$i >= $n and ($n - 1) > $current_page">
			<span class="ctrl">
				Ctrl →
			</span>
		</xsl:if>

		<xsl:if test="$ticket_count &gt; $ticket_on_page and $n &gt; $i">

			<!-- Set $link variable -->
			<xsl:variable name="number_link">
				<xsl:choose>
					
					<xsl:when test="$i != 0">
						<xsl:value-of select="$prefix"/>-<xsl:value-of select="$i + 1"/>/</xsl:when>
					
					<xsl:otherwise></xsl:otherwise>
				</xsl:choose>
			</xsl:variable>

			<!-- Filter String -->
			<xsl:variable name="filter">
				<xsl:choose>
					<xsl:when test="/helpdesk/apply_filter/node()">?action=apply_filter&amp;status=<xsl:value-of select="/helpdesk/apply_filter"/>
					</xsl:when>
					<xsl:otherwise></xsl:otherwise>
				</xsl:choose>
			</xsl:variable>

			<!-- Определяем первый символ вопрос или амперсанд -->
			<xsl:variable name="first_symbol">
				<xsl:choose>
					<xsl:when test="$filter != ''">&amp;</xsl:when>
					<xsl:otherwise>?</xsl:otherwise>
				</xsl:choose>
			</xsl:variable>

			<!-- Pagination item -->
			<xsl:if test="$i != $current_page">
				<!-- First pagination item -->
				<xsl:if test="$current_page - $pre_count_page &gt; 0 and $i = 0">
					<a href="{$link}{$filter}" class="page_link" style="text-decoration: none;">←</a>
				</xsl:if>

				<xsl:choose>
					<xsl:when test="$i &gt;= ($current_page - $pre_count_page) and ($current_page + $post_count_page) &gt;= $i">
						<!-- Pagination item -->
						<a href="{$link}{$number_link}{$filter}" class="page_link">
							<xsl:value-of select="$i + 1"/>
						</a>
					</xsl:when>
					<xsl:otherwise></xsl:otherwise>
				</xsl:choose>

				<!-- Last pagination item -->
				<xsl:if test="$i+1 &gt;= $n and $n &gt; ($current_page + 1 + $post_count_page)">
					<xsl:choose>
						<xsl:when test="$n &gt; round($n)">
							<!-- Last pagination item -->
							<a href="{$link}{$filter}{$prefix}-{round($n+1)}/" class="page_link" style="text-decoration: none;">→</a>
						</xsl:when>
						<xsl:otherwise>
							<a href="{$link}{$filter}{$prefix}-{round($n)}/" class="page_link" style="text-decoration: none;">→</a>
						</xsl:otherwise>
					</xsl:choose>
				</xsl:if>
			</xsl:if>

			<!-- Ctrl+left link -->
			<xsl:if test="$current_page != 0 and $i = $current_page">
				<xsl:variable name="prev_number_link">
					<xsl:choose>
						
						<xsl:when test="($current_page - 1) != 0">page-<xsl:value-of select="$i"/>/</xsl:when>
						
						<xsl:otherwise></xsl:otherwise>
					</xsl:choose>
				</xsl:variable>

				<a href="{$link}{$prev_number_link}{$filter}" id="id_prev"></a>
			</xsl:if>

			<!-- Ctrl+right link -->
			<xsl:if test="($n - 1) > $current_page and $i = $current_page">
				<a href="{$link}{$filter}page-{$current_page+2}/" id="id_next"></a>
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
				<xsl:with-param name="prefix" select="$prefix"/>
				<xsl:with-param name="link" select="$link"/>
				<xsl:with-param name="ticket_on_page" select="$ticket_on_page"/>
				<xsl:with-param name="current_page" select="$current_page"/>
				<xsl:with-param name="ticket_count" select="$ticket_count"/>
				<xsl:with-param name="visible_pages" select="$visible_pages"/>
			</xsl:call-template>
		</xsl:if>
	</xsl:template>


	<!-- Шаблон вывода добавления сообщения-->
	<xsl:template name="AddTicketForm">
		<div class="comment">
			<!--Отображение формы добавления запроса в службу техподдержки-->
			<form action="{/helpdesk/url}" name="" method="post" enctype="multipart/form-data">
				<div class="row">
					<div class="caption">&labelSubject;</div>
					<div class="field">
						<input type="text" size="62" name="subject" value=""/>
					</div>
				</div>

				<xsl:if test="helpdesk_criticality_level/node()">
					<div class="row">
						<div class="caption">&labelCriticalityLevel;</div>
						<div class="field">
							<select name="criticality_level_id">
								<xsl:apply-templates select="helpdesk_criticality_level"/>
							</select>
						</div>
					</div>
				</xsl:if>

				<xsl:if test="helpdesk_category/node()">
					<div class="row">
						<div class="caption">&labelCategory;</div>
						<div class="field">
							<select name="helpdesk_category_id">
								<option value="0">…</option>
								<xsl:apply-templates select="helpdesk_category" />
							</select>
						</div>
					</div>
				</xsl:if>

				<div class="row">
					<div class="caption">&labelText;</div>
					<div class="field">
						<xsl:choose>
							<xsl:when test="/helpdesk/messages_type = 0">
								<textarea name="text" cols="70" rows="5" class="mceEditor"></textarea>
							</xsl:when>
							<xsl:otherwise>
								<textarea name="text" cols="50" rows="10"/>
							</xsl:otherwise>
						</xsl:choose>
					</div>
				</div>

				<div class="row">
					<div class="caption"></div>
					<div class="field">
						<p>
							<input id="check_2" type="checkbox" name="send_email" checked="checked" value="1" />
							<label for="check_2">&labelSendMail;</label>
						</p>
					</div>
				</div>

				<div class="row">
					<div class="caption">&labelAddFile;</div>
					<div class="field">
						<p id="helpdesk_upload_file">
							<input size="30" name="attachment[]" type="file" title="&labelAddFile;" />
							<xsl:text> </xsl:text><a href="#" id="addFile">&labelMoreFile;</a>
						</p>
					</div>
				</div>

				<div class="row">
					<div class="caption"></div>
					<div class="field">
						<input type="submit" name="add_ticket" value="&labelSend;" class="button" />
					</div>
				</div>
			</form>
		</div>
	</xsl:template>

	<!-- Declension of the numerals -->
	<xsl:template name="declension">

		<xsl:param name="number" select="number"/>
		<!-- Nominative case / Именительный падеж -->
		<xsl:param name="nominative" select="nominative"/>
		<!-- Genitive singular / Родительный падеж, единственное число -->
		<xsl:param name="genitive_singular" select="genitive_singular"/>
		<!-- Родительный падеж, множественное число -->
		<xsl:param name="genitive_plural" select="genitive_plural"/>

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
			<xsl:when test="$last_digit = 2 and $last_two_digits != 12 or $last_digit = 3 and $last_two_digits != 13 or $last_digit = 4 and $last_two_digits != 14">
				<xsl:value-of select="$genitive_singular"/>
			</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="$genitive_plural"/>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
</xsl:stylesheet>