<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "lang://173">
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>

	<xsl:decimal-format name="my" decimal-separator="," grouping-separator=" "/>

	<xsl:template match="/shop">
		<xsl:apply-templates select="shop_item"/>
	</xsl:template>

	<xsl:template match="shop_item">
		<h1 hostcms:id="{@id}" hostcms:field="name" hostcms:entity="shop_item"><xsl:value-of select="name"/></h1>

		<!-- Store parent id in a variable -->
		<xsl:variable name="group" select="/shop/group"/>

		<p>
			<xsl:if test="$group = 0">
				<a href="{/shop/url}" hostcms:id="{/shop/@id}" hostcms:field="name" hostcms:entity="shop">
					<xsl:value-of select="/shop/name"/>
				</a>
			</xsl:if>

			<!-- Breadcrumbs -->
			<xsl:apply-templates select="/shop//shop_group[@id=$group]" mode="breadCrumbs"/>

			<!-- Если модификация, выводим в пути родительский товар -->
			<xsl:if test="shop_item/node()">
			<span><xsl:text> → </xsl:text></span>
				<a href="{shop_item/url}">
					<xsl:value-of disable-output-escaping="yes" select="shop_item/name"/>
				</a>
			</xsl:if>

		<span><xsl:text> → </xsl:text></span>

		<b><a href="{url}" hostcms:id="{@id}" hostcms:field="name" hostcms:entity="shop_item"><xsl:value-of select="name"/></a></b>
		</p>

		<div style="clear: both"></div>

		<xsl:if test="image_large or property_value[file/node()]">
			<xsl:variable name="image_width" >
				<xsl:choose>
					<xsl:when test="image_large/node()"><xsl:value-of select="image_large_width"/></xsl:when>
					<xsl:otherwise>
						<xsl:value-of select="/shop/image_large_max_width"/>
					</xsl:otherwise>
				</xsl:choose>
			</xsl:variable>


			<div id="gallery" class="board" style="max-width: {$image_width + 50}px">
				<xsl:if test="image_large/node()">
					<a href="{dir}{image_large}" target="_blank"><img src="{dir}{image_large}" width="{image_large_width}" height="{image_large_height}" alt="{name}" /></a>
				</xsl:if>

				<xsl:apply-templates select="property_value[file_small/node()]" mode="property_image"/>
			</div>
		</xsl:if>

		<!-- Информация об ошибках -->
		<xsl:variable name="error_code" select="/shop/error"/>

		<div style="display: table-cell; vertical-align: top">

			<!-- Text -->
			<div hostcms:id="{@id}" hostcms:field="text" hostcms:entity="shop_item" hostcms:type="wysiwyg">
				<xsl:value-of disable-output-escaping="yes" select="text"/>
			</div>

			<!-- Цена товара -->
			<div class="shop_block">&labelAmount;
				<xsl:choose>
					<xsl:when test="price != 0">

						<xsl:variable name="price" select="price"/>

						<span style="font-size: 11pt; font-weight: bold;">
						<xsl:value-of select="format-number($price, '### ###', 'my')"/><xsl:text> </xsl:text><xsl:value-of disable-output-escaping="yes" select="currency"/></span>
					</xsl:when>
					<xsl:otherwise>&labelNegotiable;</xsl:otherwise>
				</xsl:choose>
			</div>

			<div class="board_property">
				<xsl:for-each select="/shop/shop_item_properties//property[type!=2][type!=5][type!=10]">
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
								<xsl:when test="type = 7">
									<xsl:choose>
										<xsl:when test="$property_value = 1">&labelYes;</xsl:when>
										<xsl:otherwise>&labelNo;</xsl:otherwise>
									</xsl:choose>
								</xsl:when>
								<xsl:otherwise>
									<xsl:value-of disable-output-escaping="no" select="$property_value" />
								</xsl:otherwise>
							</xsl:choose>
						</div>
					</div>
				</xsl:for-each>
			</div>

			<p class="tags">
				<!-- Date -->
				<img src="/images/calendar.png" /> <xsl:value-of select="date"/>, <span hostcms:id="{@id}" hostcms:field="showed" hostcms:entity="shop_item"><xsl:value-of select="showed"/></span>
				<xsl:text> </xsl:text>
				<xsl:call-template name="declension">
					<xsl:with-param name="number" select="showed"/>
			</xsl:call-template><xsl:text>. </xsl:text>
			</p>

		</div>

		<!--<xsl:if test="(count(//property[value != '']) - count(//property[type!=1]) - 1)>0">-->
			<div style="margin-top: 10px;">
				<!--<h2>Дополнительные сведения</h2>-->

				<!-- Выводим список дополнительных свойств -->
				<table border="0">
					<xsl:apply-templates select="property[type!=1]"/>
				</table>
			</div>
			<!--</xsl:if>-->
	</xsl:template>

	<!-- Шаблон изображений из дополнительных свойств -->
	<xsl:template match="property_value" mode="property_image">
		<div>
			<xsl:choose>
				<xsl:when test="file/node()">
					<a href="{../dir}{file}" target="_blank"><img src="{../dir}{file_small}" /></a>
				</xsl:when>
				<xsl:otherwise>
					<img src="{../dir}{file_small}" />
				</xsl:otherwise>
			</xsl:choose>
		</div>
	</xsl:template>

	<!-- Шаблон для скидки -->
	<xsl:template match="discount">
		<br/>
	<xsl:value-of select="name"/><xsl:text> </xsl:text><xsl:value-of disable-output-escaping="yes" select="value"/>%</xsl:template>

	<!-- Шаблон вывода дополнительных свойств не являющихся файлами -->
	<xsl:template match="property">
		<!-- Не отображаем дату добавления объявления, идентификатор автора и e-mail -->
		<xsl:if test="@id!=61 and @id!=6 and  value != ''">

			<xsl:choose>
				<!-- Тип свойства - флажок -->
				<xsl:when test="type=7">
					<xsl:if test="value!=0">
						<tr>
							<td class="shop_block" style="border: none;">
								<center>
									<img src="/images/check.gif"/>
								</center>
							</td>
							<td style="padding: 5px;">
								<strong>
									<xsl:value-of select="name"/>
								</strong>
							</td>
						</tr>
					</xsl:if>					
				</xsl:when>
				<xsl:when test="@id=213">
					<tr>
						<td class="shop_block" style="border: none;">
							<xsl:value-of select="name"/>:</td>
						<td style="padding: 5px;">
							<strong>
								<a href="/users/info/{/shop/autor_login}/">
									<xsl:value-of select="/shop/autor_login"/>
								</a>
							</strong>
						</td>
					</tr>

				</xsl:when>
				<!-- Остальные типы доп. свойств -->
				<xsl:otherwise>
					<tr>
						<td class="shop_block" style="border: none;">
							<xsl:value-of select="name"/>:</td>
						<td style="padding: 5px;">
							<strong>
								<xsl:value-of disable-output-escaping="yes" select="value"/>
							</strong>
						</td>
					</tr>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:if>
	</xsl:template>

	<xsl:template match="tying/shop_item">

		<div style="clear: both">
			<p>
				<a href="/shop/{url}">
					<xsl:value-of select="name"/>
				</a>
			</p>

			<!-- Изображение для товара, если есть -->
			<xsl:if test="image_small != ''">
				<a href="{url}">
					<img src="{dir}{image_small}" align="left" style="border: 1px solid #000000; margin: 0px 5px 5px 0px"/>
				</a>
			</xsl:if>

			<div>
				<xsl:value-of disable-output-escaping="yes" select="description"/>
			</div>

			<!-- Если указан вес товара -->
			<xsl:if test="weight != 0">
				<br/>&labelWeight; <xsl:value-of select="weight"/> <xsl:value-of select="/shop/shop_measure/name"/></xsl:if>

			<!-- Показываем скидки -->
			<xsl:if test="count(discount) &gt; 0">
				<xsl:apply-templates select="discount"/>
			</xsl:if>

			<!-- Показываем количество на складе, если больше нуля -->
			<xsl:if test="rest &gt; 0">
				<br/>&labelRest; <xsl:value-of select="rest"/></xsl:if>

			<xsl:if test="shop_producer/name != ''">
				<br/>&labelProducer; <xsl:value-of select="shop_producer/name"/></xsl:if>
		</div>
	</xsl:template>

	<!-- Шаблон выводит хлебные крошки -->
	<xsl:template match="shop_group" mode="breadCrumbs">
		<xsl:variable name="parent_id" select="parent_id"/>

		<!-- Call recursively parent group -->
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