<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>
	
	<!-- СписокУслуг -->
	
	<xsl:template match="/">
		<div class="info_block">
			<xsl:apply-templates select="/informationsystem/informationsystem_item" />
		</div>
		
		<div>
			<!-- Получаем ID родительской группы и записываем в переменную $group -->
			<xsl:variable name="group" select="/informationsystem/group"/>
			<!-- Ссылка, для которой дописываются суффиксы page-XX/ -->
			<xsl:variable name="link">
				<xsl:value-of select="/informationsystem/url"/>
				<xsl:if test="$group != 0">
					<xsl:value-of select="/informationsystem//informationsystem_group[@id = $group]/url"/>
				</xsl:if>
			</xsl:variable>
			
			<xsl:if test="/informationsystem/total &gt; 0 and /informationsystem/limit &gt; 0">
				
				<xsl:variable name="count_pages" select="ceiling(/informationsystem/total div /informationsystem/limit)"/>
				
				<xsl:variable name="visible_pages" select="5"/>
				
				<xsl:variable name="real_visible_pages"><xsl:choose>
						<xsl:when test="$count_pages &lt; $visible_pages"><xsl:value-of select="$count_pages"/></xsl:when>
						<xsl:otherwise><xsl:value-of select="$visible_pages"/></xsl:otherwise>
				</xsl:choose></xsl:variable>
				
				<!-- Считаем количество выводимых ссылок перед текущим элементом -->
				<xsl:variable name="pre_count_page"><xsl:choose>
						<xsl:when test="page - (floor($real_visible_pages div 2)) &lt; 0">
							<xsl:value-of select="/informationsystem/page"/>
						</xsl:when>
						<xsl:when test="($count_pages - /informationsystem/page - 1) &lt; floor($real_visible_pages div 2)">
							<xsl:value-of select="$real_visible_pages - ($count_pages - /informationsystem/page - 1) - 1"/>
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
						<xsl:when test="0 &gt; /informationsystem/page - (floor($real_visible_pages div 2) - 1)">
							<xsl:value-of select="$real_visible_pages - /informationsystem/page - 1"/>
						</xsl:when>
						<xsl:when test="($count_pages - /informationsystem/page - 1) &lt; floor($real_visible_pages div 2)">
							<xsl:value-of select="$real_visible_pages - $pre_count_page - 1"/>
						</xsl:when>
						<xsl:otherwise>
							<xsl:value-of select="$real_visible_pages - $pre_count_page - 1"/>
						</xsl:otherwise>
				</xsl:choose></xsl:variable>
				
				<xsl:variable name="i"><xsl:choose>
						<xsl:when test="/informationsystem/page + 1 = $count_pages"><xsl:value-of select="/informationsystem/page - $real_visible_pages + 1"/></xsl:when>
						<xsl:when test="/informationsystem/page - $pre_count_page &gt; 0"><xsl:value-of select="/informationsystem/page - $pre_count_page"/></xsl:when>
						<xsl:otherwise>0</xsl:otherwise>
				</xsl:choose></xsl:variable>
				
				<p>
					<xsl:call-template name="for">
						<xsl:with-param name="limit" select="/informationsystem/limit"/>
						<xsl:with-param name="page" select="/informationsystem/page"/>
						<xsl:with-param name="items_count" select="/informationsystem/total"/>
						<xsl:with-param name="i" select="$i"/>
						<xsl:with-param name="post_count_page" select="$post_count_page"/>
						<xsl:with-param name="pre_count_page" select="$pre_count_page"/>
						<xsl:with-param name="visible_pages" select="$real_visible_pages"/>
					</xsl:call-template>
				</p>
				<div style="clear: both"></div>
			</xsl:if>
		</div>
	</xsl:template>
	
	<xsl:template match="informationsystem_item">
		<dl>
			<xsl:attribute name="class">
				<xsl:choose>
					<xsl:when test="position() mod 2 = 0">right</xsl:when>
					<xsl:otherwise>left</xsl:otherwise>
				</xsl:choose>
			</xsl:attribute>
			
		<dt><a href="{url}" hostcms:id="{@id}" hostcms:field="name" hostcms:entity="informationsystem_item"><xsl:value-of select="name"/></a></dt>
			<dd hostcms:id="{@id}" hostcms:field="description" hostcms:entity="informationsystem_item" hostcms:type="wysiwyg">
				<xsl:value-of disable-output-escaping="yes" select="description"/>
			</dd>
		</dl>
		
		<xsl:if test="position() mod 2 = 0 and position() != last()">
			<xsl:text disable-output-escaping="yes">
				&lt;/div&gt;
				&lt;div class="info_block"&gt;
			</xsl:text>
		</xsl:if>
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