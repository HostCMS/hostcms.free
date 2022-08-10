<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "lang://230">
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>

	<xsl:decimal-format name="my" decimal-separator="," grouping-separator=" "/>

	<xsl:template match="/">

		<script type="text/javascript">
			<xsl:comment>
				<xsl:text disable-output-escaping="yes">
					<![CDATA[
					function applyFilter()
					{
						var jForm = $('.filter').closest('form'),
							path = jForm.attr('action'),
							producerOption = jForm.find('select[name = producer_id] option:selected'),
							sortingOption = jForm.find('select[name = sorting] option:selected'),
							priceFrom = jForm.find('input[name = price_from]').val(),
							priceTo = jForm.find('input[name = price_to]').val(),
							priceFromOriginal = jForm.find('input[name = price_from_original]').val(),
							priceToOriginal = jForm.find('input[name = price_to_original]').val();

						if (parseInt(producerOption.attr('value')))
						{
							path += producerOption.data('producer') + '/';
						}

						if (typeof priceFrom !== 'undefined' && typeof priceTo !== 'undefined'
							&& (priceFrom !== priceFromOriginal || priceTo !== priceToOriginal)
						)
						{
							path += 'price-' + priceFrom + '-' + priceTo + '/';
						}

						var inputs = jForm.find('*[data-property]'),
							tag_name;

						$.each(inputs, function (index, value) {
							var type = this.type || this.tagName.toLowerCase(),
								jObject = $(this),
								value = null,
								setValue = false;

							if (typeof jObject.attr('name') !== 'undefined' && jObject.attr('name').indexOf('_to') !== -1)
							{
								return;
							}

							switch (type)
							{
								case 'checkbox':
								case 'radio':
									value = +jObject.is(':checked');
									setValue = type != 'checkbox' ? true : jObject.attr('name').indexOf('[]') !== -1;
								break;
								case 'option':
									value = +jObject.is(':selected');
									setValue = true;
								break;
								case 'text':
									value = jObject.val();
									setValue = true;
								break;
							}

							if (value && jObject.data('property') !== tag_name)
							{
								tag_name = jObject.data('property');

								if (typeof jObject.attr('name') !== 'undefined' && jObject.attr('name').indexOf('_from') !== -1)
								{
									path += '';
								}
								else
								{
									path += tag_name + '/';
								}
							}

							if (setValue && value)
							{
								if (typeof jObject.attr('name') !== 'undefined' && jObject.attr('name').indexOf('_from') !== -1)
								{
									path += encodeURIComponent(tag_name + '-' + jObject.val() + '-' + jObject.nextAll('input').eq(0).val()) + '/';
								}
								else
								{
									path += encodeURIComponent(
											typeof jObject.data('value') !== 'undefined'
												? jObject.data('value')
												: value
										) + '/';
								}
							}
						});

						path += jForm.data('tag');

						if (parseInt(sortingOption.attr('value')))
						{
							path += '?sorting=' + sortingOption.val();
						}

						// console.log(path);

						window.location.href = path;
					}

					function fastFilter(form)
					{
						this._timerId = false;
						this._form = form;

						this.filterChanged = function(obj) {
							if (this._timerId)
							{
								clearTimeout(this._timerId);
							}

							var $this = this;

							this._timerId = setTimeout(function() {
								$this._loadJson(obj);
							}, 1500);

							return this;
						}

						this._loadJson = function(obj) {
							var data = this._serializeObject();

							$.loadingScreen('show');

							$.ajax({
								url: './',
								type: "POST",
								data: data,
								dataType: 'json',
								success: function (result) {
									$.loadingScreen('hide');

									if (typeof result.count !== 'undefined')
									{
										var jParent = obj.parents('fieldset').length
											? obj.parents('fieldset')
											: obj.parent();

										$('.popup-filter').remove();

										jParent.css('position', 'relative');
										jParent.append('<div class="popup-filter"><div>Найдено: ' + result.count + '</div><br/><div><button class="button" onclick="applyFilter(); return false;">Применить</button></div></div>');

										setTimeout(function() {
											$('.popup-filter').remove();
										}, 5000);
									}
								}
							});
						}

						this._serializeObject = function () {
							var o = {fast_filter: 1};
							var a = this._form.serializeArray();
							$.each(a, function () {
								if (o[this.name] !== undefined) {
									if (!o[this.name].push) {
										o[this.name] = [o[this.name]];
									}
									o[this.name].push(this.value || '');
								} else {
									o[this.name] = this.value || '';
								}
							});

							return o;
						};
					}

					$(function() {
						var jForm = $('.filter').closest('form');
						mainFastFilter = new fastFilter(jForm);

						$(':input:not(:hidden):not(button)').on('change', function(){
							mainFastFilter.filterChanged($(this));
						});

						$('.filter-color').on('click', function(){
							var bg = $(this).css('background-color');

							$('.filter-color').each(function (index, value) {
								$(this).removeClass('active');
							});

							$(this).addClass('active');

							$('.color-input').remove();

							var property_id = $(this).data('id'),
								list_item_id = $(this).data('item-id');

							$(this).append('<input type="hidden" class="color-input" id="property_' + property_id + '_' + list_item_id +'" name="property_' + property_id + '" data-property="' + $(this).data('property') + '" data-value="' + $(this).data('value') + '" value="' + list_item_id + '"/>');

							mainFastFilter.filterChanged($(this));
						});

						jForm.on('submit', function(e) {
							e.preventDefault();

							applyFilter();
						});
					});
					]]>
				</xsl:text>
			</xsl:comment>
		</script>

		<xsl:apply-templates select="/shop"/>
	</xsl:template>

	<xsl:variable name="n" select="number(3)"/>

	<xsl:template match="/shop">

		<!-- Store parent id in a variable -->
		<xsl:variable name="group" select="group"/>

		<xsl:variable name="path">
			<xsl:choose>
				<xsl:when test="/shop//shop_group[@id=$group]/node()"><xsl:value-of select="/shop//shop_group[@id=$group]/url"/></xsl:when>
				<xsl:otherwise><xsl:value-of select="/shop/url"/></xsl:otherwise>
			</xsl:choose>
		</xsl:variable>

		<!-- дополнение пути для action, если выбрана метка -->
		<xsl:variable name="form_tag_url"><xsl:if test="count(tag) = 1">tag/<xsl:value-of select="tag/urlencode"/>/</xsl:if></xsl:variable>

		<form method="get" action="{$path}" data-tag="{$form_tag_url}">
			<div class="filter">
				<div class="sorting">
					<select name="sorting" onchange="$(this).parents('form:first').submit()">
						<option value="0">&labelSorting;</option>
						<option value="1">
							<xsl:if test="sorting = 1"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
							&labelSorting1;
						</option>
						<option value="2">
							<xsl:if test="sorting = 2"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
							&labelSorting2;
						</option>
						<option value="3">
							<xsl:if test="sorting = 3"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
							&labelSorting3;
						</option>
					</select>
				</div>

				<div class="priceFilter">
					<xsl:text>&labelPriceFrom; </xsl:text>
					<input name="price_from" size="5" type="text" value="{/shop/min_price}">
						<xsl:if test="/shop/price_from != 0">
							<xsl:attribute name="value"><xsl:value-of select="/shop/price_from"/></xsl:attribute>
						</xsl:if>
					</input>

					<xsl:text>&labelPriceTo; </xsl:text>
					<input name="price_to" size="5" type="text" value="{/shop/max_price}">
						<xsl:if test="/shop/price_to != 0">
							<xsl:attribute name="value"><xsl:value-of select="/shop/price_to"/></xsl:attribute>
						</xsl:if>
					</input>

					<input name="price_from_original" value="{/shop/min_price}" hidden="hidden" />
					<input name="price_to_original" value="{/shop/max_price}" hidden="hidden" />
				</div>

				<div class="slider"></div><br/>

				<fieldset>
					<legend>
						<span>Производитель</span>
					</legend>

					<select name="producer_id">
						<option value="0">...</option>
						<xsl:apply-templates select="/shop/producers/shop_producer" />
					</select>
				</fieldset>

				<!-- Фильтр по дополнительным свойствам товара: -->
				<xsl:if test="count(shop_item_properties//property[filter != 0 and (type = 0 or type = 1 or type = 3 or type = 7 or type = 11)])">
					<span class="table_row"></span>
					<xsl:apply-templates select="shop_item_properties//property[filter != 0 and (type = 0 or type = 1 or type = 3 or type = 7 or type = 11)]" mode="propertyList"/>
				</xsl:if>

				<xsl:if test="/shop/on_page/node() and /shop/on_page &gt; 0">
					<input type="hidden" name="on_page" value="{/shop/on_page}" />
				</xsl:if>

				<!-- <input name="filter" class="button" value="Применить" type="submit"/> -->
				<button class="button">Применить</button>
			</div>
		</form>
	</xsl:template>

	<!-- Шаблон для фильтра по дополнительным свойствам -->
	<xsl:template match="property" mode="propertyList">
		<xsl:variable name="nodename">property_<xsl:value-of select="@id"/></xsl:variable>
		<xsl:variable name="nodename_from">property_<xsl:value-of select="@id"/>_from</xsl:variable>
		<xsl:variable name="nodename_to">property_<xsl:value-of select="@id"/>_to</xsl:variable>

		<xsl:variable name="filteringNode" select="/shop/*[name()=$nodename]" />

		<fieldset>
			<!-- Не флажок -->
			<xsl:if test="filter != 5">
				<legend>
					<span><xsl:value-of select="name"/></span>
				</legend>
			</xsl:if>

			<xsl:choose>
				<!-- Отображаем поле ввода -->
				<xsl:when test="filter = 1">
					<br/>
					<input type="text" name="property_{@id}" data-property="{tag_name}">
						<xsl:if test="$filteringNode != ''">
							<xsl:attribute name="value"><xsl:value-of select="$filteringNode"/></xsl:attribute>
						</xsl:if>
					</input>
				</xsl:when>
				<!-- Отображаем список -->
				<xsl:when test="filter = 2">
					<br/>
					<select name="property_{@id}">
						<option value="0">...</option>

						<xsl:apply-templates select="list/list_item">
							<xsl:with-param name="filteringNode" select="$filteringNode"/>
							<xsl:with-param name="propertyNode" select="."/>
						</xsl:apply-templates>
					</select>
				</xsl:when>
				<!-- Отображаем переключатели -->
				<xsl:when test="filter = 3">
					<br/>
					<div class="propertyInput">
						<input type="radio" name="property_{@id}" value="0" id="id_prop_radio_{@id}_0"></input>
						<label for="id_prop_radio_{@id}_0">&labelAny;</label>

						<xsl:apply-templates select="list/list_item">
							<xsl:with-param name="filteringNode" select="$filteringNode"/>
							<xsl:with-param name="propertyNode" select="."/>
						</xsl:apply-templates>
					</div>
				</xsl:when>
				<!-- Отображаем флажки -->
				<xsl:when test="filter = 4">
					<div class="propertyInput">
						<xsl:apply-templates select="list/list_item">
							<xsl:with-param name="filteringNode" select="$filteringNode"/>
							<xsl:with-param name="propertyNode" select="."/>
						</xsl:apply-templates>
					</div>
					<div style="display:none; text-align: center;">
						...
					</div>
				</xsl:when>
				<!-- Отображаем флажок -->
				<xsl:when test="filter = 5">
					<input type="checkbox" name="property_{@id}" id="property_{@id}" value="1" style="padding-top:4px" data-property="{tag_name}">
						<xsl:if test="$filteringNode != ''">
							<xsl:attribute name="checked"><xsl:value-of select="$filteringNode"/></xsl:attribute>
						</xsl:if>
					</input>
					<label for="property_{@id}">
						<xsl:value-of select="name"/><xsl:text> </xsl:text>
					</label>
				</xsl:when>
				<!-- Отображение полей "от и до" -->
				<xsl:when test="filter = 6">
					<div class="propertyInput">
						<div>
							<xsl:text>&labelFrom; </xsl:text>
							<input name="property_{@id}_from" size="5" type="text" value="{min}" data-property="{tag_name}">
								<xsl:if test="/shop/*[name()=$nodename_from] != 0">
									<xsl:attribute name="value"><xsl:value-of select="/shop/*[name()=$nodename_from]"/></xsl:attribute>
								</xsl:if>
							</input>

							<xsl:text>&labelTo; </xsl:text>
							<input name="property_{@id}_to" size="5" type="text" value="{max}" data-property="{tag_name}">
								<xsl:if test="/shop/*[name()=$nodename_to] != 0">
									<xsl:attribute name="value"><xsl:value-of select="/shop/*[name()=$nodename_to]"/></xsl:attribute>
								</xsl:if>
							</input>

							<input name="property_{@id}_from_original" value="{min}" hidden="hidden" />
							<input name="property_{@id}_to_original" value="{max}" hidden="hidden" />
						</div>
						<div class="slider"></div><br/>
					</div>
				</xsl:when>
				<!-- Отображаем список с множественным выбором-->
				<xsl:when test="filter = 7">
					<br/>
					<select name="property_{@id}[]" multiple="multiple">
						<xsl:apply-templates select="list/list_item">
							<xsl:with-param name="filteringNode" select="$filteringNode"/>
							<xsl:with-param name="propertyNode" select="."/>
						</xsl:apply-templates>
					</select>
				</xsl:when>
			</xsl:choose>
		</fieldset>
	</xsl:template>

	<xsl:template match="list_item">
		<xsl:param name="filteringNode" />
		<xsl:param name="propertyNode" />
		<xsl:param name="sub"/>

		<xsl:variable name="list_item_id" select="@id"/>

		<xsl:variable name="value">
			<xsl:choose>
				<xsl:when test="/shop/filter_mode = 1 and path != ''">
					<xsl:value-of select="path" />
				</xsl:when>
				<xsl:otherwise>
					<xsl:value-of select="value" />
				</xsl:otherwise>
			</xsl:choose>
		</xsl:variable>

		<xsl:if test="$sub = 1">
			<xsl:text disable-output-escaping="yes">&amp;</xsl:text>nbsp;
		</xsl:if>

		<xsl:if test="$propertyNode/filter = 2">
			<!-- Отображаем список -->
			<xsl:variable name="nodename">property_<xsl:value-of select="$propertyNode/@id"/></xsl:variable>
			<option value="{@id}" data-property="{$propertyNode/tag_name}" data-value="{$value}">
				<xsl:if test="$filteringNode = @id">
					<xsl:attribute name="selected">selected</xsl:attribute>
				</xsl:if>

				<xsl:if test="@available = 'false'">
					<xsl:attribute name="disabled">disabled</xsl:attribute>
				</xsl:if>

				<xsl:value-of select="value"/>

				<xsl:variable name="filterCount" select="$propertyNode/filter_counts/count[@id = $list_item_id]" />
				<xsl:if test="$filterCount/node()">
					<xsl:text> (</xsl:text><xsl:value-of select="$filterCount"/><xsl:text>)</xsl:text>
				</xsl:if>
			</option>
		</xsl:if>
		<xsl:if test="$propertyNode/filter = 3">
			<!-- Отображаем переключатели -->
			<xsl:variable name="nodename">property_<xsl:value-of select="$propertyNode/@id"/></xsl:variable>
			<br/>
			<input type="radio" name="property_{$propertyNode/@id}" value="{@id}" id="id_property_{$propertyNode/@id}_{@id}" data-property="{$propertyNode/tag_name}" data-value="{$value}">
				<xsl:if test="$filteringNode = @id">
					<xsl:attribute name="checked">checked</xsl:attribute>
				</xsl:if>

				<xsl:if test="@available = 'false'">
					<xsl:attribute name="disabled">disabled</xsl:attribute>
				</xsl:if>

				<label for="id_property_{$propertyNode/@id}_{@id}">
					<xsl:value-of select="value"/>

					<xsl:variable name="filterCount" select="$propertyNode/filter_counts/count[@id = $list_item_id]" />
					<xsl:if test="$filterCount/node()">
						<xsl:text> (</xsl:text><xsl:value-of select="$filterCount"/><xsl:text>)</xsl:text>
					</xsl:if>
				</label>
			</input>
		</xsl:if>
		<xsl:if test="$propertyNode/filter = 4">
			<!-- Отображаем флажки -->
			<xsl:variable name="nodename">property_<xsl:value-of select="$propertyNode/@id"/></xsl:variable>
			<input type="checkbox" value="{@id}" name="property_{$propertyNode/@id}[]" id="property_{$propertyNode/@id}_{@id}" data-property="{$propertyNode/tag_name}" data-value="{$value}">
				<xsl:if test="$filteringNode = @id">
					<xsl:attribute name="checked">checked</xsl:attribute>
				</xsl:if>

				<xsl:if test="@available = 'false'">
					<xsl:attribute name="disabled">disabled</xsl:attribute>
				</xsl:if>

				<label for="property_{$propertyNode/@id}_{@id}">
					<xsl:value-of select="value"/>

					<xsl:variable name="filterCount" select="$propertyNode/filter_counts/count[@id = $list_item_id]" />
					<xsl:if test="$filterCount/node()">
						<xsl:text> (</xsl:text><xsl:value-of select="$filterCount"/><xsl:text>)</xsl:text>
					</xsl:if>
				</label>
			</input>
			<br/>
		</xsl:if>
		<xsl:if test="$propertyNode/filter = 7">
			<!-- Отображаем список -->
			<xsl:variable name="nodename">property_<xsl:value-of select="$propertyNode/@id"/></xsl:variable>
			<option value="{@id}" data-property="{$propertyNode/tag_name}" data-value="{$value}">
				<xsl:if test="$filteringNode = @id">
					<xsl:attribute name="selected">selected</xsl:attribute>
				</xsl:if>

				<xsl:if test="@available = 'false'">
					<xsl:attribute name="disabled">disabled</xsl:attribute>
				</xsl:if>

				<xsl:value-of disable-output-escaping="yes" select="value"/>

				<xsl:variable name="filterCount" select="$propertyNode/filter_counts/count[@id = $list_item_id]" />
				<xsl:if test="$filterCount/node()">
					<xsl:text> (</xsl:text><xsl:value-of select="$filterCount"/><xsl:text>)</xsl:text>
				</xsl:if>
			</option>
		</xsl:if>

		<xsl:if test="list_item/node()">
			<xsl:apply-templates select="list_item">
				<xsl:with-param name="propertyNode" select="$propertyNode" />
				<xsl:with-param name="filteringNode" select="$filteringNode" />
				<xsl:with-param name="sub" select="1" />
			</xsl:apply-templates>
		</xsl:if>
	</xsl:template>

	<xsl:template match="shop_producer">
		<xsl:variable name="name">
			<xsl:choose>
				<xsl:when test="/shop/filter_mode = 0">
					<xsl:value-of select="name" />
				</xsl:when>
				<xsl:otherwise>
					<xsl:value-of select="path" />
				</xsl:otherwise>
			</xsl:choose>
		</xsl:variable>

		<option value="{@id}" data-producer="{$name}">
			<xsl:if test="/shop/producer_id = @id">
				<xsl:attribute name="selected">selected</xsl:attribute>
			</xsl:if>

			<xsl:value-of select="name"/>

			<xsl:if test="count/node()">
				<xsl:text> (</xsl:text><xsl:value-of select="count"/><xsl:text>)</xsl:text>
			</xsl:if>
		</option>
	</xsl:template>
</xsl:stylesheet>