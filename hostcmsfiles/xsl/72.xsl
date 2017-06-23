<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "lang://72">
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>

	<xsl:template match="/">
		<xsl:apply-templates select="shop"/>
	</xsl:template>

	<!-- Основной шаблон, формирующий таблицу сравнения -->
	<xsl:template match="shop">

		<h1>&labelTitle;</h1>

		<table class="shop_cart shop_cart_compare">
			<tr>
				<th>&labelName;</th>
				<xsl:apply-templates select="comparing/shop_item" mode="name"/>
			</tr>
			<tr>
				<th>&labelPhoto;</th>
				<xsl:apply-templates select="comparing/shop_item" mode="image"/>
			</tr>
			<tr>
				<th>&labelPrice;</th>
				<xsl:apply-templates select="comparing/shop_item" mode="price"/>
			</tr>
			<tr>
				<th>&labelWeight;</th>
				<xsl:apply-templates select="comparing/shop_item" mode="weight"/>
			</tr>
			<tr>
				<th>&labelProducer;</th>
				<xsl:apply-templates select="comparing/shop_item" mode="shop_producer"/>
			</tr>
			<tr>
				<th>&labelDescription;</th>
				<xsl:apply-templates select="comparing/shop_item" mode="text"/>
			</tr>
			<xsl:apply-templates select="shop_item_properties//property"/>
			<tr>
				<th>
					&labelCompare;
				</th>
				<xsl:apply-templates select="comparing/shop_item" mode="comparing"/>
			</tr>
		</table>
	</xsl:template>

	<!-- Шаблон, формирующий свойства -->
	<xsl:template match="property">
		<!-- Есть хотя бы одно значение свойства -->
		<xsl:variable name="property_id" select="@id" />
		<xsl:if test="count(/shop/comparing/shop_item/property_value[property_id=$property_id][not(file/node()) and value != '' or file != ''])">
			<tr>
				<th>
					<xsl:value-of select="name"/>
				</th>
				<xsl:apply-templates select="/shop/comparing/shop_item" mode="property">
					<!-- Передаем через параметр ID свойства -->
					<xsl:with-param name="property_id" select="@id"/>
				</xsl:apply-templates>
			</tr>
		</xsl:if>
	</xsl:template>

	<!-- Шаблон, формирующий значения свойств -->
	<xsl:template match="shop_item" mode="property">
		<!-- Принимаем параметр - ID свойства -->
		<xsl:param name="property_id"/>
		<td class="compare{@id}">
			<xsl:choose>
				<xsl:when test="count(property_value[property_id=$property_id])">
					<xsl:apply-templates select="property_value[property_id=$property_id]" />
				</xsl:when>
				<xsl:otherwise>—</xsl:otherwise>
			</xsl:choose>
		</td>
	</xsl:template>

	<!-- Шаблон вывода значений свойств -->
	<xsl:template match="property_value">
		<xsl:choose>
			<xsl:when test="not(file)">
				<xsl:value-of disable-output-escaping="yes" select="value"/>
			</xsl:when>
			<xsl:when test="file/node()">
				<a target="_blank" href="{../dir}{file}"><xsl:value-of select="file_name"/></a>
			</xsl:when>
		</xsl:choose>
		<xsl:if test="position() != last()">, </xsl:if>
	</xsl:template>


	<!-- Шаблон, формирующий названия товаров -->
	<xsl:template match="comparing/shop_item" mode="name">
		<td class="compare{@id}">
			<a href="{url}" style="font-weight: bold">
				<xsl:value-of select="name"/>
			</a>
		</td>
	</xsl:template>

	<!-- Шаблон, формирующий изображения товаров -->
	<xsl:template match="comparing/shop_item" mode="image">
		<td class="compare{@id}">
			<!-- Изображение для товара, если есть -->
			<xsl:choose>
			<xsl:when test="image_small != ''">
				<img src="{dir}{image_small}" alt="{name}" title="{name}"/>
			</xsl:when>
			<xsl:otherwise>
				<img src="/images/no-image.png" alt="{name}" title="{name}"/>
			</xsl:otherwise>
			</xsl:choose>
		</td>
	</xsl:template>

	<!-- Шаблон, формирующий цены товаров -->
	<xsl:template match="comparing/shop_item" mode="price">
		<th class="compare{@id}">
			<xsl:value-of select="price"/><xsl:text> </xsl:text><xsl:value-of select="currency" disable-output-escaping="yes" />
		</th>
	</xsl:template>

	<!-- Шаблон, формирующий вес товаров -->
	<xsl:template match="comparing/shop_item" mode="weight">
		<td class="compare{@id}">
			<xsl:if test="weight > 0">
				<xsl:value-of select="weight"/><xsl:text> </xsl:text><xsl:value-of select="/shop/shop_measure/name"/>
			</xsl:if>
		</td>
	</xsl:template>

	<!-- Шаблон, формирующий производителей товаров -->
	<xsl:template match="comparing/shop_item" mode="shop_producer">
		<td class="compare{@id}">
			<xsl:value-of select="shop_producer/name"/>
		</td>
	</xsl:template>

	<!-- Шаблон, формирующий подробную информацию о товаре -->
	<xsl:template match="comparing/shop_item" mode="text">
		<td class="compare{@id}">
			<xsl:value-of select="description" disable-output-escaping="yes" />
		</td>
	</xsl:template>

	<!-- Шаблон, отображающий ссылки на удаление из списка сравнения -->
	<xsl:template match="comparing/shop_item" mode="comparing">
		<td class="compare{@id}">
			<div onclick="$('.compare{@id}').hide('slow'); return $.addCompare('{/shop/url}', {@id}, this);" class="compare current"></div>
		</td>
	</xsl:template>
</xsl:stylesheet>