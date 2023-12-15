<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "lang://10">
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>

	<!-- СписокЗаписейГостевойКниги  -->

	<xsl:variable name="n" select="number(3)"/>

	<xsl:template match="/informationsystem">

		<!-- Show Message -->
		<xsl:if test="message/node()">
			<div id="message">
				<xsl:value-of disable-output-escaping="yes" select="message"/>
			</div>
		</xsl:if>

		<!-- Show Error -->
		<xsl:if test="error/node()">
			<div id="error">
				<xsl:value-of select="error"/>
			</div>
		</xsl:if>

		<!-- Store parent id in a variable -->
		<xsl:variable name="group" select="group"/>

		<xsl:choose>
			<xsl:when test="$group = 0">
				<h1>
					<xsl:value-of select="name"/>
				</h1>

				<!-- Description displays if there is no filtering by tags -->
				<xsl:if test="count(tag) = 0 and page = 0">
					<xsl:value-of disable-output-escaping="yes" select="description"/>
				</xsl:if>
			</xsl:when>
			<xsl:otherwise>
				<h1>
					<xsl:value-of select=".//informationsystem_group[@id=$group]/name"/>
				</h1>

				<!-- Description displayed only in the first page -->
				<xsl:if test="page = 0">
					<xsl:value-of disable-output-escaping="yes" select=".//informationsystem_group[@id=$group]/description"/>
				</xsl:if>

				<!-- Breadcrumbs -->
				<p>
					<xsl:apply-templates select=".//informationsystem_group[@id=$group]" mode="breadCrumbs"/>
				</p>
			</xsl:otherwise>
		</xsl:choose>

		<!-- Processing of the selected tag -->
		<xsl:if test="count(tag)">
		<p class="h2">&labelTagName; — <strong><xsl:value-of select="tag/name" /></strong>.</p>
			<xsl:if test="tag/description != ''">
				<p><xsl:value-of select="tag/description" disable-output-escaping="yes" /></p>
			</xsl:if>
		</xsl:if>

		<!-- Show subgroups if there are subgroups and not processing of the selected tag -->
		<xsl:if test="count(tag) = 0 and count(.//informationsystem_group[parent_id=$group]) &gt; 0">
			<div class="group_list">
				<xsl:apply-templates select=".//informationsystem_group[parent_id=$group][position() mod $n = 1]" mode="groups"/>
			</div>
		</xsl:if>

		<p class="button" onclick="$('#AddRecord').toggle('slow')">&labelAddItem;</p>

		<div id="AddRecord" style="display: none">
			<div class="comment">
				
				<form action="./" method="post" enctype="multipart/form-data">
					<xsl:if test="/informationsystem/siteuser_id = 0">
						<div class="row">
							<div class="caption">&labelCommentName;</div>
							<div class="field">
								<input type="text" name="author" size="50" value="{/informationsystem/adding_item/author}"/>
							</div>
						</div>

						<div class="row">
							<div class="caption">&labelCommentEmail;</div>
							<div class="field">
								<input type="text" name="email" size="50" value="{/informationsystem/adding_item/email}"/>
							</div>
						</div>
					</xsl:if>

					<div class="row">
						<div class="caption">&labelCommentSubject;</div>
						<div class="field">
							<input type="text" name="subject" size="50" value="{/informationsystem/adding_item/subject}"/>
						</div>
					</div>

					<div class="row">
						<div class="caption">&labelCommentText;</div>
						<div class="field">
							<textarea type="text" name="text" cols="68" rows="5">
								<xsl:value-of select="/informationsystem/adding_item/text"/>
							</textarea>
						</div>
					</div>

					<!-- Showing captcha -->
					<xsl:if test="/informationsystem/captcha_id != 0 and /informationsystem/siteuser_id = 0">
						<div class="row">
							<div class="caption"></div>
							<div class="field">
								<img id="guestBookForm" class="captcha" src="/captcha.php?id={/informationsystem/captcha_id}&amp;height=30&amp;width=100" title="&labelCaptchaId;" name="captcha"/>

								<div class="captcha">
									<img src="/images/refresh.png" /> <span onclick="$('#guestBookForm').updateCaptcha('{/informationsystem/captcha_id}', 30); return false">&labelUpdateCaptcha;</span>
								</div>
							</div>
						</div>

						<div class="row">
							<div class="caption">
								&labelCaptchaId;<sup><font color="red">*</font></sup>
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
							<input type="submit" name="submit_question" value="&labelAddItem;" class="button"/>
						</div>
					</div>
				</form>
			</div>
		</div>

		<!-- Show informationsystem_item -->
		<xsl:apply-templates select="informationsystem_item[active=1]"/>

		<!-- Pagination -->
		<xsl:if test="ОтображатьСсылкиНаСледующиеСтраницы=1">
			<div>
				<!-- Current page link -->
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

					<!-- Links before current -->
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

					<!-- Links after current -->
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
				
				<img src="/images/user.png" />
				<span>
				<xsl:choose>
					<xsl:when test="count(siteuser) &gt; 0">
						<a href="/users/info/{siteuser/path}/"><xsl:value-of select="siteuser/login"/></a>
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

		<!-- Show Reviews -->
		<xsl:if test="count(comment)">
			<div class="comment_sub">
				<xsl:apply-templates select="comment"/>
			</div>
		</xsl:if>

	</xsl:template>

	<!-- Review template -->
	<xsl:template match="comment">
		<!-- Text or subject is not empty -->
		<xsl:if test="text != '' or subject != ''">
			<a name="comment{@id}"></a>
			<div class="comment" id="comment{@id}">
				<xsl:if test="subject != ''">
					<div class="subject"><xsl:value-of select="subject"/></div>
				</xsl:if>

				<xsl:value-of select="text" disable-output-escaping="yes"/>

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
						<span><a href="/users/info/{siteuser/path}/"><xsl:value-of select="siteuser/login"/></a></span>
					</xsl:when>
					<!-- Review was added an unauthorized user -->
					<xsl:otherwise>
						<span><xsl:value-of select="author" /></span>
					</xsl:otherwise>
					</xsl:choose>

					<img src="/images/calendar.png" /> <span><xsl:value-of select="datetime"/></span>
				</p>
			</div>

			<!-- Child Reviews -->
			<xsl:if test="count(comment)">
				<div class="comment_sub">
					<xsl:apply-templates select="comment"/>
				</div>
			</xsl:if>
		</xsl:if>
	</xsl:template>

	<!-- Breadcrumb -->
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

	<!-- Subgroups Template -->
	<xsl:template match="informationsystem_group" mode="groups">
		<ul>
			<xsl:for-each select=". | following-sibling::informationsystem_group[position() &lt; $n]">
				<li>
					<xsl:if test="image_small != ''">
						<a href="{url}" target="_blank">
							<img src="{dir}{image_small}" align="middle"/>
					</a><xsl:text> </xsl:text></xsl:if>
				<a href="{url}"><xsl:value-of select="name"/></a><xsl:text> </xsl:text><span class="count">(<xsl:value-of select="items_total_count"/>)</span>
				</li>
			</xsl:for-each>
		</ul>
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

	<!-- Pagination -->
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
			<!-- Store in the variable $group ID of the current group -->
			<xsl:variable name="group" select="/informationsystem/group"/>

			<!-- Tag Path -->
			<xsl:variable name="tag_path">
				<xsl:choose>
					
					<xsl:when test="count(/informationsystem/tag)">tag/<xsl:value-of select="/informationsystem/tag/urlencode"/>/</xsl:when>
					
					<xsl:otherwise></xsl:otherwise>
				</xsl:choose>
			</xsl:variable>

			<!-- Choose Group Path -->
			<xsl:variable name="group_link">
				<xsl:choose>
					<!-- If the group is not root -->
					<xsl:when test="$group != 0">
						<xsl:value-of select="/informationsystem//informationsystem_group[@id=$group]/url"/>
					</xsl:when>
					
					 <xsl:otherwise><xsl:value-of select="/informationsystem/url"/></xsl:otherwise>
				</xsl:choose>
			</xsl:variable>

			<!-- Set $link variable -->
			<xsl:variable name="number_link">
				<xsl:choose>
					
					<xsl:when test="$i != 0">page-<xsl:value-of select="$i + 1"/>/</xsl:when>
					
					<xsl:otherwise></xsl:otherwise>
				</xsl:choose>
			</xsl:variable>

			<!-- First pagination item -->
			<xsl:if test="$page - $pre_count_page &gt; 0 and $i = $start_page">
				<a href="{$group_link}{$tag_path}" class="page_link" style="text-decoration: none;">←</a>
			</xsl:if>

			<!-- Pagination item -->
			<xsl:if test="$i != $page">
				<xsl:if test="($page - $pre_count_page) &lt;= $i and $i &lt; $n">
					<!-- Pagination item -->
					<a href="{$group_link}{$number_link}{$tag_path}" class="page_link">
						<xsl:value-of select="$i + 1"/>
					</a>
				</xsl:if>

				<!-- Last pagination item -->
				<xsl:if test="$i+1 &gt;= ($page + $post_count_page + 1) and $n &gt; ($page + 1 + $post_count_page)">
					<!-- Last pagination item -->
					<a href="{$group_link}page-{$n}/{$tag_path}" class="page_link" style="text-decoration: none;">→</a>
				</xsl:if>
			</xsl:if>

			<!-- Ctrl+left link -->
			<xsl:if test="$page != 0 and $i = $page">
				<xsl:variable name="prev_number_link">
					<xsl:choose>
						
						<xsl:when test="$page &gt; 1">page-<xsl:value-of select="$i"/>/</xsl:when>
						
						<xsl:otherwise></xsl:otherwise>
					</xsl:choose>
				</xsl:variable>

				<a href="{$group_link}{$prev_number_link}{$tag_path}" id="id_prev"></a>
			</xsl:if>

			<!-- Ctrl+right link -->
			<xsl:if test="($n - 1) > $page and $i = $page">
				<a href="{$group_link}page-{$page+2}/{$tag_path}" id="id_next"></a>
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
				<xsl:with-param name="items_count" select="$items_count"/>
				<xsl:with-param name="pre_count_page" select="$pre_count_page"/>
				<xsl:with-param name="post_count_page" select="$post_count_page"/>
				<xsl:with-param name="visible_pages" select="$visible_pages"/>
			</xsl:call-template>
		</xsl:if>
	</xsl:template>
</xsl:stylesheet>