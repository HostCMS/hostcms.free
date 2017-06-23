<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>

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
							r2.find('input').attr('value', '');
							r.after(r2);
							return false;
						});

						// Проверка формы
						$('.validate').validate({
							focusInvalid: true,
							errorClass: "input_error"
						});

						// Удаление фотографий
						$.fn.extend({
							delete_photo: function() {
								request = $.ajax({
									url: this.attr('href'),
									context: this,
									type: "GET",
									dataType: "json",
									success: function(data) {
										if (data.delete)
										{
											this.parents('.row').find('input[name="image"]').length
												? this.parents('.row').find('a').remove()
												: this.parents('.row').remove();
										}
									}
								});
								return false;
							}
						});
					});
					]]>
				</xsl:text>
			</xsl:comment>
		</SCRIPT>

		<xsl:apply-templates select="shop/shop_item" />
	</xsl:template>

	<xsl:template match="shop_item">
		<h1><xsl:value-of select="name" /></h1>

		<xsl:for-each select="/shop/messages/message">
			<div id="message"><xsl:value-of select="."/></div>
		</xsl:for-each>

		<form action="{/shop/structure/link}{@id}/" method="post" enctype="multipart/form-data" class="validate">
			<div class="comment">
				<div class="row">
				<div class="caption">Заголовок<sup><font color="red">*</font></sup></div>
					<div class="field"><input size="50" type="text" name="name" value="{name}" class="required" minlength="1" title="Заполните поле Заголовок" /></div>
				</div>
				<div class="row">
					<div class="caption">Цена</div>
					<div class="field"><input size="15" type="text" name="price" value="{price}" /></div>
				</div>
				<div class="row">
					<div class="caption">Текст объявления</div>
					<div class="field">
						<textarea name="text" cols="50" rows="5"><xsl:value-of disable-output-escaping="yes" select="text"/></textarea>
					</div>
				</div>
				<div class="row">
					<div class="caption">Фото</div>
					<div class="field"><input type="file" name="image" />
						<xsl:if test="image_large/node()">
							<xsl:text> </xsl:text><a target="_blank" href="{dir}{image_large}"><img src="/hostcmsfiles/images/preview.gif" /></a><xsl:text> </xsl:text>
							<a href="{/shop/structure/link}{/shop/shop_item/@id}/?photo" onclick="return $(this).delete_photo();"><img src="/hostcmsfiles/images/delete.gif" /></a>
						</xsl:if>
					</div>
				</div>

				<xsl:for-each select="/shop/shop_item_properties//property[type = 2]">
					<xsl:sort select="sorting" />
					<xsl:variable name="property_id" select="@id" />

					<xsl:for-each select="/shop/shop_item/property_value[property_id = $property_id][file != '']">
						<div class="row">
							<div class="caption"></div>
							<div class="field">
								<input type="file" name="property_value_{@id}" />
								<xsl:text> </xsl:text><a target="_blank" href="{../dir}{file}"><img src="/hostcmsfiles/images/preview.gif" /></a>
								<xsl:text> </xsl:text>
								<a href="{/shop/structure/link}{/shop/shop_item/@id}/?photo={@id}" onclick="return $(this).delete_photo();"><img src="/hostcmsfiles/images/delete.gif" /></a>
							</div>
						</div>
					</xsl:for-each>
				</xsl:for-each>

				<xsl:for-each select="/shop/shop_item_properties//property">
					<xsl:sort select="sorting" />
					<div class="row">
						<div class="caption">
							<xsl:value-of select="name" />
							<xsl:if test="shop_measure/node()">(<xsl:value-of select="shop_measure/name" />)</xsl:if>
						</div>
						<div class="field">
							<xsl:variable name="property_id" select="@id" />
							<xsl:variable name="property_value" select="//shop_item/property_value[property_id = $property_id]/value" />

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
												<xsl:if test="$property_value = 1">
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
										<a id="addFile" href="#">Ещё файл...</a>
									</xsl:if>
								</xsl:when>

								<!-- Выпадающий список -->
								<xsl:when test="type = 3">
									<select name="property_{$property_id}">
										<option value="0">...</option>
										<xsl:for-each select="list/list_item">
											<option value="{@id}">
												<xsl:if test="value = $property_value">
													<xsl:attribute name="selected">selected</xsl:attribute>
												</xsl:if>
												<xsl:value-of disable-output-escaping="no" select="value" /></option>
										</xsl:for-each>
									</select>
								</xsl:when>
								<xsl:otherwise>
									<textarea name="property_{@id}" cols="50" rows="5">
										<xsl:value-of select="$property_value" />
									</textarea>
								</xsl:otherwise>
							</xsl:choose>
						</div>
					</div>
				</xsl:for-each>

				<div class="row">
					<div class="caption"></div>
					<div class="field"><input value="Сохранить" class="button" type="submit" name="update" /></div>
				</div>
			</div>
		</form>
	</xsl:template>
</xsl:stylesheet>