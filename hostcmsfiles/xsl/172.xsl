<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "lang://172">
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>

	<xsl:decimal-format name="my" decimal-separator="," grouping-separator=" "/>
	<xsl:variable name="n" select="number(3)"/>

	<!-- СписокОбъявлений -->

	<xsl:template match="/">
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
								r2.find('input').val('');
								r.after(r2);
								return false;
							});

							// Проверка формы
							$('.validate').validate({
								focusInvalid: true,
								errorClass: "input_error"
							});
						});
					]]>
				</xsl:text>
			</xsl:comment>
		</SCRIPT>

		<xsl:apply-templates select="/shop"/>
	</xsl:template>

	<!-- Шаблон для магазина -->
	<xsl:template match="/shop">
		<!-- Store parent id in a variable -->
		<xsl:variable name="group" select="group"/>

		<xsl:choose>
			<xsl:when test="$group = 0">
				<h1 hostcms:id="{@id}" hostcms:field="name" hostcms:entity="shop">
					<xsl:value-of select="name"/>
				</h1>

				<!-- Description displays if there is no filtering by tags -->
				<xsl:if test="count(tag) = 0 and page = 0 and description != ''">
					<div hostcms:id="{@id}" hostcms:field="description" hostcms:entity="shop" hostcms:type="wysiwyg"><xsl:value-of disable-output-escaping="yes" select="description"/></div>
				</xsl:if>
			</xsl:when>
		<xsl:otherwise>
			<h1 hostcms:id="{$group}" hostcms:field="name" hostcms:entity="shop_group">
				<xsl:value-of select=".//shop_group[@id=$group]/name"/>
			</h1>

			<!-- Description displayed only in the first page -->
			<xsl:if test="page = 0 and .//shop_group[@id=$group]/description != ''">
				<div hostcms:id="{$group}" hostcms:field="description" hostcms:entity="shop_group" hostcms:type="wysiwyg"><xsl:value-of disable-output-escaping="yes" select=".//shop_group[@id=$group]/description"/></div>
			</xsl:if>

			<!-- Breadcrumbs -->
			<p>
				<xsl:apply-templates select=".//shop_group[@id=$group]" mode="breadCrumbs"/>
			</p>
		</xsl:otherwise>
		</xsl:choose>

		<!-- Сообщения -->
		<xsl:for-each select="errors/error">
			<div id="error"><xsl:value-of select="."/></div>
		</xsl:for-each>

		<xsl:for-each select="messages/message">
			<div id="message"><xsl:value-of select="."/></div>
		</xsl:for-each>

		<xsl:if test="$group != 0">
			<!--  Метка для перехода при выводе сообщения -->
			<a name="FocusAddItemMessage"></a>

			<p class="button" style="margin: 15px 0" onclick="$('#AddItemForm').toggle('slow')">&labelAddItem;</p>

			<form action="{//shop_group[@id = $group]/url}" method="post" enctype="multipart/form-data" class="validate">
				<div class="comment" style="display: none" id="AddItemForm">
					<div class="row">
						<div class="caption">&labelName;<sup><font color="red">*</font></sup></div>
						<div class="field"><input size="50" type="text" name="name" value="{add_item/name}" class="required" minlength="1" title="&labelNameTitle;" /></div>
					</div>
					<div class="row">
						<div class="caption">&labelAmount;</div>
						<div class="field"><input size="15" type="text" name="price" value="{add_item/price}" /></div>
					</div>
					<div class="row">
						<div class="caption">&labelText;</div>
						<div class="field">
							<textarea name="text" cols="50" rows="5"><xsl:value-of select="add_item/text" /></textarea>
						</div>
					</div>
					<div class="row">
						<div class="caption">&labelPhoto;</div>
						<div class="field"><input type="file" name="image" /></div>
					</div>
					<xsl:for-each select="shop_item_properties//property">
						<xsl:sort select="sorting" />
						<div class="row">
							<div class="caption">
								<xsl:value-of select="name" />
								<xsl:if test="shop_measure/node()">(<xsl:value-of select="shop_measure/name" />)</xsl:if>
							</div>
							<div class="field">
								<xsl:variable name="property_id" select="@id" />
								<xsl:variable name="property_value" select="//add_item/property[id = $property_id]/value" />

								<xsl:choose>
									<!-- Текстовое поле -->
									<xsl:when test="type &lt; 3 or type &gt; 6 and type != 10">
										<input name="property_{@id}">
											<xsl:attribute name="type">
												<xsl:choose>
													<xsl:when test="type = 2">file</xsl:when>
													<xsl:when test="type = 7">checkbox</xsl:when>
													<xsl:otherwise>text</xsl:otherwise>
												</xsl:choose>
											</xsl:attribute>

											<xsl:if test="type = 2">
												<xsl:attribute name="name">property_<xsl:value-of select="@id" />[]</xsl:attribute>
											</xsl:if>

											<!-- Значение полей по умолчанию -->
											<xsl:choose>
												<xsl:when test="type = 7">
													<xsl:if test="default_value != 0 and not($property_value) or $property_value = 'on'">
														<xsl:attribute name="checked">checked</xsl:attribute>
													</xsl:if>
												</xsl:when>
												<xsl:otherwise>
													<xsl:attribute name="value">
														<xsl:choose>
															<xsl:when test="$property_value"><xsl:value-of select="$property_value"/></xsl:when>
															<xsl:otherwise><xsl:value-of select="default_value"/></xsl:otherwise>
														</xsl:choose>
													</xsl:attribute>

													<!-- Размер поля INPUT в зависимости от типа -->
													<xsl:choose>
														<xsl:when test="type = 1">
															<xsl:attribute name="size">50</xsl:attribute>
														</xsl:when>
														<xsl:when test="type = 0 or type = 8 or type = 9">
															<xsl:attribute name="size">15</xsl:attribute>
														</xsl:when>
													</xsl:choose>
												</xsl:otherwise>
											</xsl:choose>
										</input>

										<xsl:if test="type = 2">
											<a id="addFile" href="#">&labelAddFile;</a>
										</xsl:if>
									</xsl:when>

									<!-- Выпадающий список -->
									<xsl:when test="type = 3">
										<select name="property_{$property_id}">
											<option value="0">...</option>
											<xsl:for-each select="list/list_item">
												<option value="{@id}">
													<xsl:if test="@id = $property_value">
														<xsl:attribute name="selected">selected</xsl:attribute>
													</xsl:if>
													<xsl:value-of disable-output-escaping="no" select="value" /></option>
											</xsl:for-each>
										</select>
									</xsl:when>
									<xsl:otherwise>
										<textarea name="property_{@id}" cols="50" rows="5">
											<xsl:choose>
												<xsl:when test="not($property_value)">
													<xsl:value-of select="default_value" />
												</xsl:when>
												<xsl:otherwise>
													<xsl:value-of select="$property_value" />
												</xsl:otherwise>
											</xsl:choose>
										</textarea>
									</xsl:otherwise>
								</xsl:choose>
							</div>
						</div>
					</xsl:for-each>

					<!-- Код подтверждения -->
					<xsl:if test="captcha_id != 0 and siteuser_id/node() = 0">
						<div class="row">
							<div class="caption"></div>
							<div class="field">
								<img id="formCaptcha_{@id}_{captcha_id}" src="/captcha.php?id={captcha_id}&amp;height=30&amp;width=100" class="captcha" name="captcha" />
								<div class="captcha">
									<img src="/images/refresh.png" /> <span onclick="$('#formCaptcha_{@id}_{captcha_id}').updateCaptcha('{captcha_id}', 30); return false">&labelUpdateCaptcha;</span>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="caption">
								&labelCaptchaId;<sup><font color="red">*</font></sup>
							</div>
							<div class="field">
								<input type="hidden" name="captcha_id" value="{captcha_id}"/>
								<input type="text" name="captcha" size="15" class="required" minlength="4" title="&labelCaptchaIdTitle;"/>
							</div>
						</div>
					</xsl:if>

					<div class="row">
						<div class="caption"></div>
						<div class="field"><input value="&labelSend;" class="button" type="submit" name="send_ad" /></div>
					</div>
				</div>
			</form>

		</xsl:if>

		<xsl:variable name="count">1</xsl:variable>

		<!-- Show subgroups if there are subgroups and not processing of the selected tag -->
		<xsl:if test="count(tag) = 0 and count(shop_producer) = 0 and count(//shop_group[parent_id=$group]) &gt; 0">
			<div class="group_list">
				<xsl:apply-templates select=".//shop_group[parent_id=$group][position() mod $n = 1]"/>
			</div>
		</xsl:if>

		<xsl:if test="count(shop_item) &gt; 0 or /shop/filter = 1">
			<!-- дополнение пути для action, если выбрана метка -->
			<xsl:variable name="form_tag_url"><xsl:if test="count(tag) = 1">tag/<xsl:value-of select="tag/urlencode"/>/</xsl:if></xsl:variable>

			<form method="get" action="{//shop_group[@id=$group]/url}{$form_tag_url}">
				<div class="shop_filter">
					<div class="sorting">
						<select name="sorting" onchange="$(this).parents('form:first').submit()">
							<option disabled="disabled">&labelSorting;</option>
							<option value="1">
								<xsl:if test="/shop/sorting = 1"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
								&labelSorting1;
							</option>
							<option value="2">
								<xsl:if test="/shop/sorting = 2"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
								&labelSorting2;
							</option>
							<option value="3">
								<xsl:if test="/shop/sorting = 3"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
								&labelSorting3;
							</option>
						</select>
					</div>

					<div class="priceFilter">
						<xsl:text>&labelPriceFrom; </xsl:text>
						<input name="price_from" size="5" type="text">
							<xsl:if test="/shop/price_from != 0">
								<xsl:attribute name="value"><xsl:value-of select="/shop/price_from"/></xsl:attribute>
							</xsl:if>
						</input>

						<xsl:text>&labelPriceTo; </xsl:text>
						<input name="price_to" size="5" type="text">
							<xsl:if test="/shop/price_to != 0">
								<xsl:attribute name="value"><xsl:value-of select="/shop/price_to"/></xsl:attribute>
							</xsl:if>
						</input>
					</div>

					<xsl:if test="count(shop_item_properties//property[filter != 0 and (type = 0 or type = 3 or type = 7)]) &gt; 0">
						<!-- <p><b>Фильтр по дополнительным свойствам товара:</b></p> -->
						<span class="table_row"></span>
						<xsl:apply-templates select="shop_item_properties//property[filter != 0 and (type = 0 or type = 3 or type = 7)]" mode="propertyList"/>
					</xsl:if>

					<input name="filter" class="button" value="&labelApply;" type="submit"/>
				</div>

				<!-- Таблица с элементами для сравнения -->
				<xsl:if test="count(/shop/compare_items/compare_item) &gt; 0">
					<table cellpadding="5px" cellspacing="0" border="0">
						<tr>
							<td>
								<input type="checkbox" onclick="SelectAllItemsByPrefix(this.checked, 'del_compare_id_')" />
							</td>
							<td>
								<b>&labelComparedItems;</b>
							</td>
						</tr>
						<xsl:apply-templates select="compare_items/compare_item"/>
					</table>
				</xsl:if>

				<div class="shop_table board">
					<!-- Выводим товары магазина -->
					<xsl:apply-templates select="shop_item" />
				</div>

				<p class="button" id="compareButton">
					<xsl:if test="count(/shop/comparing/shop_item) = 0">
						<xsl:attribute name="style">display: none</xsl:attribute>
					</xsl:if>
					<a href="{/shop/url}compare_items/">&labelCompare;</a>
				</p>

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
			</form>
		</xsl:if>
	</xsl:template>

	<!-- Шаблон для фильтра производителей -->
	<xsl:template match="shop_producerslist/producer">
		<option value="{@id}">
			<xsl:if test="@id = /shop/producer_id">
				<xsl:attribute name="selected">
				</xsl:attribute>
			</xsl:if>
			<xsl:value-of select="name"/>
		</option>
	</xsl:template>

	<!-- Шаблон для фильтра по дополнительным свойствам -->
	<xsl:template match="property" mode="propertyList">
		<xsl:variable name="nodename">property_<xsl:value-of select="@id"/></xsl:variable>
		<xsl:variable name="nodename_from">property_<xsl:value-of select="@id"/>_from</xsl:variable>
		<xsl:variable name="nodename_to">property_<xsl:value-of select="@id"/>_to</xsl:variable>

		<div class="filterField">
			<xsl:value-of select="name"/><xsl:text> </xsl:text>
			<xsl:choose>
			<!-- Отображаем поле ввода -->
			<xsl:when test="filter = 1">
				<br/>
				<input type="text" name="property_{@id}">
					<xsl:if test="/shop/*[name()=$nodename] != ''">
						<xsl:attribute name="value"><xsl:value-of select="/shop/*[name()=$nodename]"/></xsl:attribute>
					</xsl:if>
				</input>
			</xsl:when>
			<!-- Отображаем список -->
			<xsl:when test="filter = 2">
				<br/>
				<select name="property_{@id}">
					<option value="0">...</option>
					<xsl:apply-templates select="list/list_item"/>
				</select>
			</xsl:when>
			<!-- Отображаем переключатели -->
			<xsl:when test="filter = 3">
				<br/>
				<div class="propertyInput">
					<input type="radio" name="property_{@id}" value="0" id="id_prop_radio_{@id}_0"></input>
					<label for="id_prop_radio_{@id}_0">&labelAny;</label>
					<xsl:apply-templates select="list/list_item"/>
				</div>
			</xsl:when>
			<!-- Отображаем флажки -->
			<xsl:when test="filter = 4">
				<div class="propertyInput">
					<xsl:apply-templates select="list/list_item"/>
				</div>
			</xsl:when>
			<!-- Отображаем флажок -->
			<xsl:when test="filter = 5">
				<br/>
				<input type="checkbox" name="property_{@id}" id="property_{@id}" style="padding-top:4px">
					<xsl:if test="/shop/*[name()=$nodename] != ''">
						<xsl:attribute name="checked"><xsl:value-of select="/shop/*[name()=$nodename]"/></xsl:attribute>
					</xsl:if>
				</input>
				<label for="property_{@id}">&labelYes;</label>
			</xsl:when>
			<!-- Отображение полей "от и до" -->
			<xsl:when test="filter = 6">
				<br/>
				&labelFrom; <input type="text" name="property_{@id}_from" size="2" value="{/shop/*[name()=$nodename_from]}"/> &labelTo; <input type="text" name="property_{@id}_to" size="2" value="{/shop/*[name()=$nodename_to]}"/>
			</xsl:when>
			<!-- Отображаем список с множественным выбором-->
			<xsl:when test="filter = 7">
				<br/>
				<select name="property_{@id}[]" multiple="multiple">
					<xsl:apply-templates select="list/list_item"/>
				</select>
			</xsl:when>
			</xsl:choose>
		</div>
	</xsl:template>

	<xsl:template match="list/list_item">
		<xsl:if test="../../filter = 2">
			<!-- Отображаем список -->
			<xsl:variable name="nodename">property_id_<xsl:value-of select="../../@id"/></xsl:variable>
			<option value="{@id}">
				<xsl:if test="/shop/*[name()=$nodename] = @id">
					<xsl:attribute name="selected">
					</xsl:attribute>
				</xsl:if>
				<xsl:value-of disable-output-escaping="yes" select="value"/>
			</option>
		</xsl:if>
		<xsl:if test="../../filter = 3">
			<!-- Отображаем переключатели -->
			<xsl:variable name="nodename">property_id_<xsl:value-of select="../../@id"/></xsl:variable>
			<br/>
			<input type="radio" name="property_id_{../../@id}" value="{@id}" id="id_property_id_{../../@id}_{@id}">
				<xsl:if test="/shop/*[name()=$nodename] = @id">
					<!--<xsl:attribute name="checked"> </xsl:attribute>-->
				</xsl:if>
				<label for="id_property_id_{../../@id}_{@id}">
					<xsl:value-of disable-output-escaping="yes" select="value"/>
				</label>
			</input>
		</xsl:if>
		<xsl:if test="../../filter = 4">
			<!-- Отображаем флажки -->
			<xsl:variable name="nodename">property_id_<xsl:value-of select="../../@id"/>_item_id_<xsl:value-of select="@id"/></xsl:variable>
			<br/>
			<input type="checkbox" name="property_id_{../../@id}_item_id_{@id}" id="id_property_id_{../../@id}_{@id}">
				<xsl:if test="/shop/*[name()=$nodename] = @id">
					<xsl:attribute name="checked"> </xsl:attribute>
				</xsl:if>
				<label for="id_property_id_{../../@id}_{@id}">
					<xsl:value-of disable-output-escaping="yes" select="value"/>
				</label>
			</input>
		</xsl:if>
		<xsl:if test="../../filter = 7">
			<!-- Отображаем список -->
			<xsl:variable name="nodename">property_id_<xsl:value-of select="../../@id"/></xsl:variable>
			<option value="{@id}">
				<xsl:if test="/shop/*[name()=$nodename] = @id">
					<xsl:attribute name="selected">
					</xsl:attribute>
				</xsl:if>
				<xsl:value-of disable-output-escaping="yes" select="value"/>
			</option>
		</xsl:if>
	</xsl:template>

	<!-- Цикл с шагом 10 для select'a количества элементов на страницу -->
	<xsl:template name="for_on_page">
		<xsl:param name="i" select="0"/>
		<xsl:param name="n"/>

		<option value="{$i}">
			<xsl:if test="$i = /shop/on_page">
				<xsl:attribute name="selected">
				</xsl:attribute>
			</xsl:if>
			<xsl:value-of select="$i"/>
		</option>

		<xsl:if test="$n &gt; $i">
			<!-- Рекурсивный вызов шаблона -->
			<xsl:call-template name="for_on_page">
				<xsl:with-param name="i" select="$i + 10"/>
				<xsl:with-param name="n" select="$n"/>
			</xsl:call-template>
		</xsl:if>
	</xsl:template>

	<!-- Шаблон для групп товара -->
	<xsl:template match="shop_group">
		<ul>
			<xsl:for-each select=". | following-sibling::shop_group[position() &lt; $n]">
				<li>
					<a href="{url}" hostcms:id="{@id}" hostcms:field="name" hostcms:entity="shop_group"><xsl:value-of select="name"/></a><xsl:text> </xsl:text><span class="shop_count"><xsl:value-of select="items_total_count"/></span>
				</li>
			</xsl:for-each>
		</ul>
	</xsl:template>

	<!-- Шаблон для подразделов -->
	<xsl:template match="shop_group" mode="sub_group">
		<a href="{url}">
			<xsl:value-of select="name"/>
		</a>
		<xsl:variable name="parent_id" select="parent_id"/>
		<!-- Ставим запятую после группы, за которой следуют еще группы из данной родителской группы -->
		<xsl:if test="position() != last() and count(//shop_group[parent_id = $parent_id]) &gt; 1"><xsl:text>, </xsl:text></xsl:if>
	</xsl:template>

	<!-- Шаблон для товара -->
	<xsl:template match="shop_item">
		<div class="table_row">
			<div class="date" style="display: table-cell">
				<b><xsl:value-of disable-output-escaping="yes" select="format-number(substring-before(datetime, '.'), '#')"/></b>
				<xsl:variable name="month_year" select="substring-after(datetime, '.')"/>
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
				<br/> &labelIn;
				<!-- Время -->
				<xsl:variable name="full_time" select="substring-after($month_year, ' ')"/>
				<b><xsl:value-of select="substring($full_time, 1, 5)" /><xsl:text> </xsl:text></b>
			</div>
			<div class="image" style="display: table-cell">
				<!-- Изображение для товара, если есть -->
				<a href="{url}">
					<xsl:choose>
						<xsl:when test="image_small != ''">
							<img src="{dir}{image_small}" alt="{name}" title="{name}"/>
						</xsl:when>
						<xsl:otherwise>
							<img src="/images/no-image.png" alt="{name}" title="{name}"/>
						</xsl:otherwise>
					</xsl:choose>
				</a>
			</div>
			<div>

				<a href="{url}" hostcms:id="{@id}" hostcms:field="name" hostcms:entity="shop_item">
					<xsl:value-of select="name"/>
				</a>

				<xsl:variable name="city_name" select="property_value[tag_name='city']/value"/>

				<xsl:if test="$city_name != ''">
					<br/><xsl:value-of select="$city_name" />
				</xsl:if>
			</div>
			<div class="price">
				<xsl:value-of select="format-number(price, '### ##0,00', 'my')"/><xsl:text> </xsl:text><xsl:value-of select="currency"/>
			</div>
		</div>
	</xsl:template>

	<!-- Шаблон для модификаций -->
	<xsl:template match="modifications/shop_item">
		<tr>
			<td>
				<!-- Название модификации -->
				<a href="{url}">
					<xsl:value-of select="name"/>
				</a>
			</td>
			<td>
				<!-- Цена модификации -->
				<xsl:choose>
					<xsl:when test="price != 0">
						<xsl:value-of select="price"/><xsl:text> </xsl:text><xsl:value-of disable-output-escaping="yes" select="currency"/>
					</xsl:when>
					<xsl:otherwise>&labelNegotiable;</xsl:otherwise>
				</xsl:choose>
			</td>
		</tr>
	</xsl:template>

	<!-- Шаблон для скидки -->
	<xsl:template match="discount">
		<br/>
		<xsl:value-of select="name"/><xsl:text> </xsl:text><xsl:value-of disable-output-escaping="yes" select="value"/>%</xsl:template>

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
			<xsl:variable name="group" select="/shop/group"/>

			<!-- Tag Path -->
			<xsl:variable name="tag_path"><xsl:if test="count(/shop/tag) != 0">tag/<xsl:value-of select="/shop/tag/urlencode"/>/</xsl:if></xsl:variable>

			<!-- Compare Product Path -->
			<xsl:variable name="shop_producer_path"><xsl:if test="count(/shop/shop_producer)">producer-<xsl:value-of select="/shop/shop_producer/@id"/>/</xsl:if></xsl:variable>

			<!-- Choose Group Path -->
			<xsl:variable name="group_link"><xsl:choose><xsl:when test="$group != 0"><xsl:value-of select="/shop//shop_group[@id=$group]/url"/></xsl:when><xsl:otherwise><xsl:value-of select="/shop/url"/></xsl:otherwise></xsl:choose></xsl:variable>

			<!-- Set $link variable -->
			<xsl:variable name="number_link"><xsl:if test="$i != 0">page-<xsl:value-of select="$i + 1"/>/</xsl:if></xsl:variable>

			<!-- First pagination item -->
			<xsl:if test="$page - $pre_count_page &gt; 0 and $i = $start_page">
				<a href="{$group_link}{$tag_path}{$shop_producer_path}" class="page_link" style="text-decoration: none;">←</a>
			</xsl:if>

			<!-- Pagination item -->
			<xsl:if test="$i != $page">
				<xsl:if test="($page - $pre_count_page) &lt;= $i and $i &lt; $n">
					<!-- Pagination item -->
					<a href="{$group_link}{$number_link}{$tag_path}{$shop_producer_path}" class="page_link">
						<xsl:value-of select="$i + 1"/>
					</a>
				</xsl:if>

				<!-- Last pagination item -->
				<xsl:if test="$i+1 &gt;= ($page + $post_count_page + 1) and $n &gt; ($page + 1 + $post_count_page)">
					<!-- Last pagination item -->
					<a href="{$group_link}page-{$n}/{$tag_path}{$shop_producer_path}" class="page_link" style="text-decoration: none;">→</a>
				</xsl:if>
			</xsl:if>

			<!-- Ctrl+left link -->
			<xsl:if test="$page != 0 and $i = $page"><xsl:variable name="prev_number_link"><xsl:if test="$page &gt; 1">page-<xsl:value-of select="$i"/>/</xsl:if></xsl:variable><a href="{$group_link}{$prev_number_link}{$tag_path}{$shop_producer_path}" id="id_prev"></a></xsl:if>

			<!-- Ctrl+right link -->
			<xsl:if test="($n - 1) > $page and $i = $page">
				<a href="{$group_link}page-{$page+2}/{$tag_path}{$shop_producer_path}" id="id_next"></a>
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

	<!-- Шаблон выводит рекурсивно ссылки на группы магазина -->
	<xsl:template match="shop_group" mode="breadCrumbs">
		<xsl:param name="parent_id" select="parent_id"/>

		<!-- Store parent id in a variable -->
		<xsl:param name="group" select="/shop/shop_group"/>

		<xsl:apply-templates select="//shop_group[@id=$parent_id]" mode="breadCrumbs"/>

		<xsl:if test="parent_id=0">
			<a href="{/shop/url}" hostcms:id="{/shop/@id}" hostcms:field="name" hostcms:entity="shop">
				<xsl:value-of select="/shop/name"/>
			</a>
		</xsl:if>

		<span><xsl:text> → </xsl:text></span>

		<a href="{url}" hostcms:id="{@id}" hostcms:field="name" hostcms:entity="shop_group">
			<xsl:value-of select="name"/>
		</a>
	</xsl:template>
</xsl:stylesheet>