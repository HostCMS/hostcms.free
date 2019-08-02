<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "lang://19">
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>

	<xsl:template match="/">
		<xsl:apply-templates select="/informationsystem"/>
	</xsl:template>

	<xsl:variable name="n" select="number(3)"/>

	<xsl:decimal-format name="my" decimal-separator = "." grouping-separator = " " />

	<xsl:template match="/informationsystem">

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
		<p class="h2">&labelTag; — <strong><xsl:value-of select="tag/name" /></strong>.</p>
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

		<!-- Show informationsystem_item -->
		<dl class="news_list full_list">
			<xsl:apply-templates select="informationsystem_item"/>
		</dl>

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

		<div style="clear: both"></div>
	</xsl:template>

	<!-- informationsystem_item template -->
	<xsl:template match="informationsystem_item">

		<!-- Text representation of a date -->
		<dt>
			<xsl:value-of select="substring-before(date, '.')"/>
			<xsl:variable name="month_year" select="substring-after(date, '.')"/>
			<xsl:variable name="month" select="substring-before($month_year, '.')"/>
			<xsl:choose>
				<xsl:when test="$month = 1"> &labelMonth1; </xsl:when>
				<xsl:when test="$month = 2"> &labelMonth2; </xsl:when>
				<xsl:when test="$month = 3"> &labelMonth3; </xsl:when>
				<xsl:when test="$month = 4"> &labelMonth4; </xsl:when>
				<xsl:when test="$month = 5"> &labelMonth5; </xsl:when>
				<xsl:when test="$month = 6"> &labelMonth6; </xsl:when>
				<xsl:when test="$month = 7"> &labelMonth7; </xsl:when>
				<xsl:when test="$month = 8"> &labelMonth8; </xsl:when>
				<xsl:when test="$month = 9"> &labelMonth9; </xsl:when>
				<xsl:when test="$month = 10"> &labelMonth10; </xsl:when>
				<xsl:when test="$month = 11"> &labelMonth11; </xsl:when>
				<xsl:otherwise> &labelMonth12; </xsl:otherwise>
			</xsl:choose>
			<xsl:value-of select="substring-after($month_year, '.')"/>
		</dt>

		<dd>
			<a href="{url}">
				<xsl:value-of select="name"/>
			</a>

			<!-- Image -->
			<xsl:if test="image_small != ''">
				<a href="{url}" class="news_title">
					<img src="{dir}{image_small}" class="news_img" alt="" align="left"/>
				</a>
			</xsl:if>

			<xsl:value-of disable-output-escaping="yes" select="description"/>
		</dd>

		<xsl:if test="property_value[tag_name='file']/file != ''">
			<!-- Определение типа файла -->
			<xsl:variable name="file_type">
				<xsl:call-template name="file_type">
					<xsl:with-param name="str" select="property_value[tag_name = 'file']/file" />
				</xsl:call-template>
			</xsl:variable>

			<!-- Если файл - изображение -->
			<!-- <xsl:if test="$file_type = 'bmp.gif' or $file_type = 'png.gif'	or $file_type = 'jpg.gif' or $file_type = 'gif.gif'">
				<b>Изображение:</b><xsl:text> </xsl:text><xsl:value-of select="property_value[tag_name = 'file']/file/@width" />&#215;<xsl:value-of select="property_value[tag_name = 'file']/file/@height" />
				<br/>
			</xsl:if>

			<b>Размер:</b><xsl:text> </xsl:text><xsl:value-of select="format-number(property_value[tag_name = 'file']/file/@size div 1024, '0.##', 'my')" /> КБ<br/>-->
			<img src="/hostcmsfiles/images/icons/{$file_type}" class="img" /><xsl:text> </xsl:text><a href="{dir}{property_value[tag_name='file']/file}" target="_blank">&labelDownload;</a>
		</xsl:if>

		<xsl:if test="count(tag) &gt; 0 or count(comment) &gt; 0 or count(siteuser) &gt; 0">
			<p class="tags">
				<xsl:if test="count(tag)">
					<img src="/images/tag.png" /><span><xsl:apply-templates select="tag"/></span>
				</xsl:if>

				<xsl:if test="count(siteuser) &gt; 0">
				<img src="/images/user.png" /><span><a href="/users/info/{siteuser/path}/"><xsl:value-of select="siteuser/login"/></a></span>
				</xsl:if>

				<xsl:if test="count(comment)">
			<img src="/images/comment.png" /><span><a href="{url}#comments"><xsl:value-of select="comments_count"/><xsl:text> </xsl:text><xsl:call-template name="declension"> <xsl:with-param name="number" select="comments_count"/></xsl:call-template></a></span>
				</xsl:if>
			</p>
		</xsl:if>

		<br class="clearing" />

		<hr />
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

	<!-- Цикл для определения типа файла -->
	<xsl:template name="file_type">
		<xsl:param name="str"/>

		<xsl:variable name="sub_str">
			<xsl:value-of select="substring-after($str, '.')" />
		</xsl:variable>

		<xsl:choose>
			<xsl:when test="$sub_str = ''">file.gif</xsl:when>
			<xsl:when test="$sub_str = 'sql'">sql.gif</xsl:when>
			<xsl:when test="$sub_str = 'css'">css.gif</xsl:when>
			<xsl:when test="$sub_str = 'gif'">gif.gif</xsl:when>
			<xsl:when test="$sub_str = 'bmp'">bmp.gif</xsl:when>
			<xsl:when test="$sub_str = 'png'">png.gif</xsl:when>
			<xsl:when test="$sub_str = 'ico'">image.gif</xsl:when>
			<xsl:when test="$sub_str = 'xml'">xml.gif</xsl:when>
			<xsl:when test="$sub_str = 'xsl'">xsl.gif</xsl:when>
			<xsl:when test="$sub_str = 'rar'">rar.gif</xsl:when>
			<xsl:when test="$sub_str = 'pdf'">pdf.gif</xsl:when>
			<xsl:when test="$sub_str = 'rb'">rb.gif</xsl:when>
			<xsl:when test="$sub_str = 'mdb'">mdb.gif</xsl:when>
			<xsl:when test="$sub_str = 'h'">h.gif</xsl:when>
			<xsl:when test="$sub_str = 'xls'">xls.gif</xsl:when>
			<xsl:when test="$sub_str = 'cpp'">cpp.gif</xsl:when>
			<xsl:when test="$sub_str = 'chm'">chm.gif</xsl:when>
			<xsl:when test="$sub_str = 'doc' or $sub_str = 'docx'">doc.gif</xsl:when>
			<xsl:when test="$sub_str = 'htm' or $sub_str = 'html'">html.gif</xsl:when>
			<xsl:when test="$sub_str = 'php' or $sub_str = 'php3'">php.gif</xsl:when>
			<xsl:when test="$sub_str = 'jpg' or $sub_str = 'jpeg'">jpg.gif</xsl:when>
			<xsl:when test="$sub_str = 'fla' or $sub_str = 'fla'">flash.gif</xsl:when>
			<xsl:when test="$sub_str = 'zip' or $sub_str = 'gz' or $sub_str = '7z'">zip.gif</xsl:when>
			<xsl:when test="$sub_str = 'cdr' or $sub_str = 'ai' or $sub_str = 'eps'">vector.gif</xsl:when>
			<xsl:when test="$sub_str = 'ppt' or $sub_str = 'pptx' or $sub_str = 'pptm'">ppt.gif</xsl:when>
			<xsl:otherwise>
				<xsl:call-template name="file_type">
					<xsl:with-param name="str" select="$sub_str"/>
				</xsl:call-template>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

	<!-- Tags Template -->
	<xsl:template match="tag">
		<a href="{/informationsystem/url}tag/{urlencode}/" class="tag">
			<xsl:value-of select="name"/>
		</a>
<xsl:if test="position() != last()"><xsl:text>, </xsl:text></xsl:if></xsl:template>

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