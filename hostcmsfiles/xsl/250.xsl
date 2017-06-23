<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>
	
	<!-- СписокЭлементовИнфосистемы -->
	
	<xsl:template match="/">
		<xsl:apply-templates select="/informationsystem"/>
	</xsl:template>
	
	<xsl:template match="/informationsystem">
		<div class="slider-wrapper">
			<div class="indent">
				<div id="loopedSlider">
					<div class="container">
						<div class="slides">
							<!-- Отображение записи информационной системы -->
							<xsl:apply-templates select="informationsystem_item[active=1]"/>
						</div>
					</div>
					<a href="#" class="previous"></a>
					<a href="#" class="next"></a>
				</div>
			</div>
		</div>
	</xsl:template>
	
	<!-- Шаблон вывода информационного элемента -->
	<xsl:template match="informationsystem_item">
		<div>
			<div class="inner">
				<div class="wrapper">
					<div class="mainText">
						<span hostcms:id="{@id}" hostcms:field="name" hostcms:entity="informationsystem_item">
							<xsl:value-of disable-output-escaping="yes" select="name"/>
						</span>
					</div>
					<div class="text">
						<xsl:if test="description != ''">
							<p hostcms:id="{@id}" hostcms:field="description" hostcms:entity="informationsystem_item" hostcms:type="wysiwyg">
								<xsl:value-of disable-output-escaping="yes" select="description"/>
							</p>
						</xsl:if>
						
						<div class="alignright">
							<a href="{url}" class="button">Подробнее</a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</xsl:template>
</xsl:stylesheet>