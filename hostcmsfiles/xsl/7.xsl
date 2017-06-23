<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "lang://7">
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>

<xsl:template match="/"><xsl:apply-templates select="/document/comment"/></xsl:template>

<xsl:template match="/document/comment">&labelTitle;

&labelAuthor; <xsl:value-of select="author" />
&labelEmail; <xsl:value-of select="email" />
&labelSubject; <xsl:value-of select="subject" />
&labelReview; <xsl:value-of select="text" />
&labelInformationsystem; <xsl:value-of select="../informationsystem/name" />
&labelInformationItem; <xsl:value-of select="../informationsystem_item/name" />
&labelLink; http:<xsl:value-of select="../informationsystem/http" /><xsl:value-of select="../informationsystem_item/url" />
&labelDate; <xsl:value-of select="datetime" />
&labelIp; <xsl:value-of select="ip" />

---
&labelHostcms;
&labelHostcmsLink;
</xsl:template>
</xsl:stylesheet>