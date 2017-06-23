<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "lang://48">
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">

	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>

	<!-- ЭлементПортфолио  -->

	<xsl:template match="/">
		<xsl:apply-templates select="/informationsystem/informationsystem_item"/>
	</xsl:template>

	<xsl:template match="/informationsystem/informationsystem_item">

		<!-- Store parent id in a variable -->
		<!-- <xsl:variable name="group" select="informationsystem_group_id"/> -->

		<h1 hostcms:id="{@id}" hostcms:field="name" hostcms:entity="informationsystem_item"><xsl:value-of select="name"/></h1>

		<!-- Breadcrumbs -->
		<!-- <xsl:apply-templates select="//informationsystem_group[@id=$group]" mode="breadCrumbs"/> -->

		<!-- Show Message -->
		<xsl:if test="/informationsystem/message/node()">
			<xsl:value-of disable-output-escaping="yes" select="/informationsystem/message"/>
		</xsl:if>

		<!-- Image -->
		<img src="{dir}{image_large}" />

		<p class="tags">
			<!-- Average Grade -->
			<xsl:if test="comments_average_grade/node() and comments_average_grade != 0">
				<span><xsl:call-template name="show_average_grade">
						<xsl:with-param name="grade" select="comments_average_grade"/>
					<xsl:with-param name="const_grade" select="5"/></xsl:call-template></span>
			</xsl:if>

			<!-- Processing of the selected tag -->
			<xsl:if test="count(tag)">
				<img src="/images/tag.png" /><span><xsl:apply-templates select="tag"/></span>
			</xsl:if>

			<xsl:if test="count(siteuser) &gt; 0">
				<img src="/images/user.png" /><span><a href="/users/info/{siteuser/login}/"><xsl:value-of select="siteuser/login"/></a></span>
			</xsl:if>

			<!-- Date -->
			<img src="/images/calendar.png" /> <xsl:value-of select="date"/>, <span hostcms:id="{@id}" hostcms:field="showed" hostcms:entity="informationsystem_item"><xsl:value-of select="showed"/></span>
			<xsl:text> </xsl:text>
			<xsl:call-template name="declension">
				<xsl:with-param name="number" select="showed"/>
			</xsl:call-template><xsl:text>. </xsl:text>
		</p>

		<!-- Links 1-2-3 to the parts of the document -->
		<xsl:if test="parts_count &gt; 1">
			<div class="read_more">&labelReadMore;</div>

			<xsl:call-template name="for">
				<xsl:with-param name="limit">1</xsl:with-param>
				<xsl:with-param name="page" select="/informationsystem/part"/>
				<xsl:with-param name="link" select="/informationsystem/informationsystem_item/url"/>
				<xsl:with-param name="items_count" select="parts_count"/>
				<xsl:with-param name="visible_pages">6</xsl:with-param>
				<xsl:with-param name="prefix">part</xsl:with-param>
			</xsl:call-template>

			<div style="clear: both"></div>
		</xsl:if>

		<xsl:if test="count(property_value[value != '' or file != ''])">
			<p class="h2">&labelAttributes;</p>
			<table border="0" class="news_properties">
				<xsl:apply-templates select="property_value[value != '' or file != '']"/>
			</table>
		</xsl:if>

		
		<xsl:if test="/informationsystem/show_comments/node() and /informationsystem/show_comments = 1">

			<!-- Show Reviews -->
			<xsl:if test="count(comment)">
				<p class="h1"><a name="comments"></a>&labelReviews;</p>
				<xsl:apply-templates select="comment"/>
			</xsl:if>
		</xsl:if>

		<!-- If allowed to display add comment form,
		1 - Only authorized
		2 - All
		-->
		<xsl:if test="/informationsystem/show_add_comments/node() and ((/informationsystem/show_add_comments = 1 and /informationsystem/siteuser_id &gt; 0) or /informationsystem/show_add_comments = 2)">

			<p class="button" onclick="$('.comment_reply').hide('slow');$('#AddComment').toggle('slow')">
				&labelAddReview;
			</p>

			<div id="AddComment" class="comment_reply">
				<xsl:call-template name="AddCommentForm"></xsl:call-template>
			</div>
		</xsl:if>
	</xsl:template>

	<!-- Tag Template -->
	<xsl:template match="tag">
		<a href="{/informationsystem/url}tag/{urlencode}/" class="tag">
			<xsl:value-of select="name"/>
		</a>
	<xsl:if test="position() != last()"><xsl:text>, </xsl:text></xsl:if>
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

			<!-- Show the gray stars until the current position does not reach the value increased to an integer -->
			<xsl:otherwise>
				<xsl:call-template name="show_average_grade">
					<xsl:with-param name="grade" select="$current_grade"/>
					<xsl:with-param name="const_grade" select="$const_grade - 1"/>
				</xsl:call-template>
				<img src="/images/star-empty.png"/>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

	<!-- Breadcrumb -->
	<xsl:template match="informationsystem_group" mode="breadCrumbs">
		<xsl:variable name="parent_id" select="parent_id"/>

		<!-- Call recursively parent group -->
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

	<!-- Review template -->
	<xsl:template match="comment">
		<!-- Text or subject is not empty -->
		<xsl:if test="text != '' or subject != ''">
			<a name="comment{@id}"></a>
			<div class="comment" id="comment{@id}">
				<xsl:if test="subject != ''">
					<div class="subject" hostcms:id="{@id}" hostcms:field="subject" hostcms:entity="comment"><xsl:value-of select="subject"/></div>
				</xsl:if>

				<div hostcms:id="{@id}" hostcms:field="text" hostcms:entity="comment" hostcms:type="wysiwyg"><xsl:value-of select="text" disable-output-escaping="yes"/></div>

				<p class="tags">
					<!-- Grade -->
					<xsl:if test="grade != 0">
						<span><xsl:call-template name="show_average_grade">
							<xsl:with-param name="grade" select="grade"/>
							<xsl:with-param name="const_grade" select="5"/>
						</xsl:call-template></span>
					</xsl:if>

					<img src="/images/user.png" />
					<xsl:choose>
					<!-- Review was added an authorized user -->
					<xsl:when test="count(siteuser) &gt; 0">
						<span><a href="/users/info/{siteuser/login}/"><xsl:value-of select="siteuser/login"/></a></span>
					</xsl:when>
					<!-- Review was added an unauthorized user -->
					<xsl:otherwise>
						<span><xsl:value-of select="author" /></span>
					</xsl:otherwise>
					</xsl:choose>

					<img src="/images/calendar.png" /> <span><xsl:value-of select="datetime"/></span>

					<xsl:if test="/informationsystem/show_add_comments/node()
						and ((/informationsystem/show_add_comments = 1 and /informationsystem/siteuser_id > 0)
						or /informationsystem/show_add_comments = 2)">
					<span class="red" onclick="$('.comment_reply').hide('slow');$('#cr_{@id}').toggle('slow')">&labelAddReply;</span></xsl:if>

					<span class="red"><a href="{/informationsystem/informationsystem_item/url}#comment{@id}" title="&labelCommentLink;">#</a></span>
				</p>
			</div>

			<!-- Only for authorized users -->
			<xsl:if test="/informationsystem/show_add_comments/node() and ((/informationsystem/show_add_comments = 1 and /informationsystem/siteuser_id > 0) or /informationsystem/show_add_comments = 2)">
				<div class="comment_reply" id="cr_{@id}">
					<xsl:call-template name="AddCommentForm">
						<xsl:with-param name="id" select="@id"/>
					</xsl:call-template>
				</div>
			</xsl:if>

			<!-- Child Reviews -->
			<xsl:if test="count(comment)">
				<div class="comment_sub">
					<xsl:apply-templates select="comment"/>
				</div>
			</xsl:if>
		</xsl:if>
	</xsl:template>

	<!-- AddCommentForm Template -->
	<xsl:template name="AddCommentForm">
		<xsl:param name="id" select="0"/>

		
		<xsl:variable name="subject">
			<xsl:if test="/informationsystem/comment/parent_id/node() and /informationsystem/comment/parent_id/node() and /informationsystem/comment/parent_id= $id">
				<xsl:value-of select="/informationsystem/comment/subject"/>
			</xsl:if>
		</xsl:variable>
		<xsl:variable name="email">
			<xsl:if test="/informationsystem/comment/email/node() and /informationsystem/comment/parent_id/node() and /informationsystem/comment/parent_id= $id">
				<xsl:value-of select="/informationsystem/comment/email"/>
			</xsl:if>
		</xsl:variable>
		<xsl:variable name="phone">
			<xsl:if test="/informationsystem/comment/phone/node() and /informationsystem/comment/parent_id/node() and /informationsystem/comment/parent_id= $id">
				<xsl:value-of select="/informationsystem/comment/phone"/>
			</xsl:if>
		</xsl:variable>
		<xsl:variable name="text">
			<xsl:if test="/informationsystem/comment/text/node() and /informationsystem/comment/parent_id/node() and /informationsystem/comment/parent_id= $id">
				<xsl:value-of select="/informationsystem/comment/text"/>
			</xsl:if>
		</xsl:variable>
		<xsl:variable name="name">
			<xsl:if test="/informationsystem/comment/author/node() and /informationsystem/comment/parent_id/node() and /informationsystem/comment/parent_id= $id">
				<xsl:value-of select="/informationsystem/comment/author"/>
			</xsl:if>
		</xsl:variable>

		<div class="comment">
			
			<form action="{/informationsystem/informationsystem_item/url}" name="comment_form_0{$id}" method="post">
				<!-- Only for unauthorized users -->
				<xsl:if test="/informationsystem/siteuser_id = 0">

					<div class="row">
						<div class="caption">&labelCommentName;</div>
						<div class="field">
							<input type="text" size="70" name="author" value="{$name}"/>
						</div>
					</div>

					<div class="row">
						<div class="caption">&labelCommentEmail;</div>
						<div class="field">
							<input id="email{$id}" type="text" size="70" name="email" value="{$email}" />
							<div id="error_email{$id}"></div>
						</div>
					</div>

					<div class="row">
						<div class="caption">&labelCommentPhone;</div>
						<div class="field">
							<input type="text" size="70" name="phone" value="{$phone}"/>
						</div>
					</div>
				</xsl:if>

				<div class="row">
					<div class="caption">&labelCommentSubject;</div>
					<div class="field">
						<input type="text" size="70" name="subject" value="{$subject}"/>
					</div>
				</div>

				<div class="row">
					<div class="caption">&labelCommentText;</div>
					<div class="field">
						<textarea name="text" cols="68" rows="5" class="mceEditor"><xsl:value-of select="$text"/></textarea>
					</div>
				</div>

				<div class="row">
					<div class="caption">&labelGrade;</div>
					<div class="field stars">
						<select name="grade">
							<option value="1">Poor</option>
							<option value="2">Fair</option>
							<option value="3">Average</option>
							<option value="4">Good</option>
							<option value="5">Excellent</option>
						</select>
					</div>
				</div>

				<!-- Showing captcha -->
				<xsl:if test="//captcha_id != 0 and /informationsystem/siteuser_id = 0">
					<div class="row">
						<div class="caption"></div>
						<div class="field">
							<img id="comment_{$id}" class="captcha" src="/captcha.php?id={//captcha_id}{$id}&amp;height=30&amp;width=100" title="&labelCaptchaId;" name="captcha"/>

							<div class="captcha">
								<img src="/images/refresh.png" /> <span onclick="$('#comment_{$id}').updateCaptcha('{//captcha_id}{$id}', 30); return false">&labelUpdateCaptcha;</span>
							</div>
						</div>
					</div>

					<div class="row">
						<div class="caption">
							&labelCaptchaId;<sup><font color="red">*</font></sup>
						</div>
						<div class="field">
							<input type="hidden" name="captcha_id" value="{//captcha_id}{$id}"/>
							<input type="text" name="captcha" size="15"/>
						</div>
					</div>
				</xsl:if>

				<xsl:if test="$id != 0">
					<input type="hidden" name="parent_id" value="{$id}"/>
				</xsl:if>

				<div class="row">
					<div class="caption"></div>
					<div class="field">
						<input id="submit_email{$id}" type="submit" name="add_comment" value="&labelPublish;" class="button" />
					</div>
				</div>
			</form>
		</div>
	</xsl:template>

	<!-- Show property item -->
	<xsl:template match="property_value">
		<xsl:variable name="property_id" select="property_id" />
		<xsl:variable name="property" select="/informationsystem/informationsystem_item_properties/property[@id=$property_id]" />

		<tr>
			<th>
				<xsl:value-of select="$property/name"/>
			</th>
			<td>
				<xsl:choose>
					<xsl:when test="$property/type = 2">
						<a href="{/informationsystem/informationsystem_item/dir}{file}">&labelDownloadFile;</a>
					</xsl:when>
					<xsl:when test="$property/type = 7">
						<input type="checkbox" disabled="disabled">
							<xsl:if test="value = 1">
								<xsl:attribute name="checked">checked</xsl:attribute>
							</xsl:if>
						</input>
					</xsl:when>
					<xsl:otherwise>
						<xsl:value-of disable-output-escaping="yes" select="value"/>
					</xsl:otherwise>
				</xsl:choose>
			</td>
		</tr>
	</xsl:template>

	<!-- Pagination -->
	<xsl:template name="for">
		<xsl:param name="i" select="0"/>
		<xsl:param name="prefix">page</xsl:param>
		<xsl:param name="link"/>
		<xsl:param name="limit"/>
		<xsl:param name="page"/>
		<xsl:param name="items_count"/>
		<xsl:param name="visible_pages"/>

		<xsl:variable name="n" select="$items_count div $limit"/>

		<!-- Store in the variable $group ID of the current group -->
		<xsl:variable name="group" select="/informationsystem/group"/>


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
					<xsl:value-of select="$visible_pages - $page"/>
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

		<xsl:if test="$items_count &gt; $limit and $n &gt; $i">
			<!-- Pagination item -->
			<xsl:if test="$i != $page">
				<!-- Определяем адрес тэга -->
				<xsl:variable name="tag_link">
					<xsl:choose>
						
						<xsl:when test="count(/informationsystem/tag)">tag/<xsl:value-of select="/informationsystem/tag/urlencode"/>/</xsl:when>
						
						<xsl:otherwise></xsl:otherwise>
					</xsl:choose>
				</xsl:variable>

				<!-- Set $link variable -->
				<xsl:variable name="number_link">

					<xsl:choose>
						
						<xsl:when test="$i != 0">
							<xsl:value-of select="$prefix"/>-<xsl:value-of select="$i + 1"/>/</xsl:when>
						
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
						<a href="{$link}{$tag_link}{$number_link}" class="page_link">
							<xsl:value-of select="$i + 1"/>
						</a>
					</xsl:when>
					<xsl:otherwise></xsl:otherwise>
				</xsl:choose>

				<!-- Last pagination item -->
				<xsl:if test="$i+1 &gt;= $n and $n &gt; ($page + 1 + $post_count_page)">
					<xsl:choose>
						<xsl:when test="$n &gt; round($n)">
							<!-- Last pagination item -->
							<a href="{$link}{$prefix}-{round($n+1)}/" class="page_link" style="text-decoration: none;">→</a>
						</xsl:when>
						<xsl:otherwise>
							<a href="{$link}{$prefix}-{round($n)}/" class="page_link" style="text-decoration: none;">→</a>
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
				<xsl:with-param name="prefix" select="$prefix"/>
				<xsl:with-param name="link" select="$link"/>
				<xsl:with-param name="limit" select="$limit"/>
				<xsl:with-param name="page" select="$page"/>
				<xsl:with-param name="items_count" select="$items_count"/>
				<xsl:with-param name="visible_pages" select="$visible_pages"/>
			</xsl:call-template>
		</xsl:if>
	</xsl:template>

	<!-- Declension of the numerals -->
	<xsl:template name="declension">

		<xsl:param name="number" select="number"/>

		<!-- Nominative case / Именительный падеж -->
		<xsl:variable name="nominative"><xsl:text>&labelNominative;</xsl:text></xsl:variable>

		<!-- Genitive singular / Родительный падеж, единственное число -->
		<xsl:variable name="genitive_singular"><xsl:text>&labelGenitiveSingular;</xsl:text></xsl:variable>

		<xsl:variable name="genitive_plural"><xsl:text>&labelGenitivePlural;</xsl:text></xsl:variable>
		<xsl:variable name="last_digit"><xsl:value-of select="$number mod 10"/></xsl:variable>
		<xsl:variable name="last_two_digits"><xsl:value-of select="$number mod 100"/></xsl:variable>

		<xsl:choose>
			<xsl:when test="$last_digit = 1 and $last_two_digits != 11">
				<xsl:value-of select="$nominative"/>
			</xsl:when>
			<xsl:when test="$last_digit = 2 and $last_two_digits != 12
				or $last_digit = 3 and $last_two_digits != 13
				or $last_digit = 4 and $last_two_digits != 14">
				<xsl:value-of select="$genitive_singular"/>
			</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="$genitive_plural"/>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
</xsl:stylesheet>