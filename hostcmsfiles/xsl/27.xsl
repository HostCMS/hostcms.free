<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "lang://27">
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>
	
	<xsl:template match="/siteuser">
		
		<xsl:choose>
			<!-- Authorized User -->
			<xsl:when test="@id > 0">
				<h1>&labelUser; <xsl:value-of select="login" /></h1>
				
				<!-- Show Menu -->
				<ul class="users">
					<xsl:apply-templates select="item"/>
				</ul>
			</xsl:when>
			<!-- Unauthorized user -->
			<xsl:otherwise>
				<div class="authorization">
					<h1>&labelAccount;</h1>
					
					<!-- Show Error -->
					<xsl:if test="error/node()">
						<div id="error">
							<xsl:value-of select="error"/>
						</div>
					</xsl:if>
					
					<form action="/users/" method="post">
						<p>&labelLogin;
							<br /><input name="login" type="text" size="30" class="large" />
						</p>
						<p>&labelPassword;
							<br /><input name="password" type="password" size="30" class="large" />
						</p>
						<p>
							<label><input name="remember" type="checkbox" /> &labelRemember;</label>
						</p>
						<input name="apply" type="submit" value="&labelLoginButton;" class="button" />
						
						<!-- Page Redirect after login -->
						<xsl:if test="location/node()">
							<input name="location" type="hidden" value="{location}" />
						</xsl:if>
					</form>
					
				<p>&labelLine1; — <a href="/users/registration/">&labelRegister;</a></p>
				<p>&labelLine2; <a href="/users/restore_password/">&labelRestore;</a></p>
				</div>
				
				<div class="authorization">
					<h1>&labelNewAccount;</h1>
					
					<p>&labelNewAccountLine1;</p>
					
					<ul class="account">
						<li>&labelNewAccountAdvantage1;</li>
						<li>&labelNewAccountAdvantage2;</li>
						<li>&labelNewAccountAdvantage3;</li>
					</ul>
					
					<p class="button">&labelRegister;</p>
				</div>
				
				<xsl:if test="count(site/siteuser_identity_provider[image != '' and type = 1])">
					<div class="row">
						<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
							<div class="social-authorization">
								<h1>&labelLoginWithSocialAccount;</h1>
								<xsl:for-each select="site/siteuser_identity_provider[image != '' and type = 1]">
									<xsl:element name="a">
									<xsl:attribute name="href">/users/?oauth_provider=<xsl:value-of select="@id"/><xsl:if test="/siteuser/location/node()">&amp;location=<xsl:value-of select="/siteuser/location"/></xsl:if></xsl:attribute>
										<xsl:attribute name="class">social-icon</xsl:attribute>
										<img src="{dir}{image}" alt="{name}" title="{name}" />
								</xsl:element><xsl:text> </xsl:text>
								</xsl:for-each>
							</div>
						</div>
					</div>
				</xsl:if>
				
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	
	<xsl:template match="item">
		<li style="background: url('{image}') no-repeat 11px 5px">
			<a href="{path}">
				<xsl:value-of select="name"/>
			</a>
		</li>
	</xsl:template>
</xsl:stylesheet>