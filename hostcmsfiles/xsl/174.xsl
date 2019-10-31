<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "lang://174">
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>

	<!-- Анкетные данные -->
	<xsl:template match="/siteuser">
		<xsl:choose>
			<!-- Пользователь отсутствует или у него не подтверждена регистрация -->
			<xsl:when test="not(login/node())">
				<h1>&labelTitleLine1;</h1>
				<p>&labelTitleLine2;</p>
			</xsl:when>
			<xsl:otherwise>
				<xsl:choose>
					<xsl:when test="property_value[tag_name = 'avatar']/file != ''">
						<!-- Отображаем картинку-аватарку -->
						<img src="{dir}{property_value[tag_name = 'avatar']/file}" alt="" class="userAvatar"/>
						<br/>
					</xsl:when>
					<xsl:otherwise>
						<!-- Отображаем картинку, символизирующую пустую аватарку -->
						<img src="/hostcmsfiles/forum/avatar.gif" alt="" class="userAvatar"/>
						<br/>
					</xsl:otherwise>
				</xsl:choose>

				<h1><xsl:value-of select="login"/></h1>

				<div class="clearing"></div>

				<xsl:variable name="current_siteuser_id" select="/siteuser/current_siteuser_id" />
				<xsl:choose>
					<xsl:when test="/siteuser/current_siteuser_relation/siteuser_relationship[siteuser_id = $current_siteuser_id]/node() and /siteuser/current_siteuser_relation/siteuser_relationship[recipient_siteuser_id = $current_siteuser_id]/node()">
						<!-- вы взаимные друзья. -->
					<p class="button float_left"><span onclick="$.clientRequest({{path: '?removeFriend', 'callBack': $.friendOperations, context: $(this)}}); return false">&labelDeleteFriend1;</span></p>
					</xsl:when>
					<xsl:when test="/siteuser/current_siteuser_relation/siteuser_relationship[siteuser_id = $current_siteuser_id]/node()">
						<!-- вы подписчик. -->
					<p class="button float_left"><span onclick="$.clientRequest({{path: '?removeFriend', 'callBack': $.friendOperations, context: $(this)}}); return false">&labelDeleteFriend2;</span></p>
					</xsl:when>
					<xsl:when test="/siteuser/@id != $current_siteuser_id">
					<p class="button float_left"><span onclick="$.clientRequest({{path: '?addFriend', 'callBack': $.friendOperations, context: $(this)}}); return false">&labelAddFriend;</span></p>
					</xsl:when>
					<xsl:otherwise></xsl:otherwise>
				</xsl:choose>

				<!-- Список -->
				<xsl:if test="/siteuser/current_siteuser_id/node()">
					<div class="friendlist">
						<select name="friendlist" onchange="$.clientRequest({{path: '?changeType&amp;type=' + $(this).val(), 'callBack': function(){{ $.loadingScreen('hide');}}, context: $(this)}}); return false">
							<option value="0">...</option>
							<xsl:apply-templates select="siteuser_relationship_type[@id != 0]" mode="friendlist"/>
						</select>
					</div>
				</xsl:if>

				<div class="clearing"></div>

				<div class="userData">
					<!-- Проверяем, указано ли имя -->
					<xsl:if test="name != ''">
						<dl>
							<dt>&labelName; </dt>
							<dd><xsl:value-of select="name" /></dd>
						</dl>
					</xsl:if>

					<!-- E-mail -->
					<xsl:if test="property_value[tag_name = 'public_email']/value != 0">
						<dl>
							<dt>&labelEmail; </dt>
						<dd><a href="mailto:{email}">	<xsl:value-of select="email"/></a></dd>
						</dl>
					</xsl:if>

					<!-- Страна -->
					<xsl:if test="country != ''">
						<dl>
							<dt>&labelCountry; </dt>
							<dd><xsl:value-of select="country" /></dd>
						</dl>
					</xsl:if>

					<!-- Город -->
					<xsl:if test="city != ''">
						<dl>
							<dt>&labelCity; </dt>
							<dd><xsl:value-of select="city" /></dd>
						</dl>
					</xsl:if>

					<!-- Компания -->
					<xsl:if test="company != ''">
						<dl>
							<dt>&labelCompany; </dt>
							<dd><xsl:value-of select="company" /></dd>
						</dl>
					</xsl:if>

					<!-- Телефон -->
					<xsl:if test="phone != ''">
						<dl>
							<dt>&labelPhone; </dt>
							<dd><xsl:value-of select="phone" /></dd>
						</dl>
					</xsl:if>

					<!-- Факс -->
					<xsl:if test="fax != ''">
						<dl>
							<dt>&labelFax; </dt>
							<dd><xsl:value-of select="fax" /></dd>
						</dl>
					</xsl:if>

					<xsl:variable name="url">
						<xsl:choose>
							<xsl:when test="starts-with(website, 'http://') or starts-with(website, 'https://')"><xsl:value-of select="website"/></xsl:when>
							<xsl:when test="website != ''">http://<xsl:value-of select="website"/></xsl:when>
							<xsl:otherwise></xsl:otherwise>
						</xsl:choose>
					</xsl:variable>

					<!-- Сайт -->
					<xsl:if test="$url != ''">
						<dl>
							<dt>&labelSite; </dt>
						<dd><a href="{$url}" rel="nofollow" target="_blank"><xsl:value-of select="website"/></a></dd>
						</dl>
					</xsl:if>

					<!-- ICQ -->
					<xsl:if test="icq != ''">
						<dl>
							<dt>&labelICQ; </dt>
							<dd><img src="http://status.icq.com/online.gif?icq={icq}&#38;img=5" alt="Статус ICQ" title="Статус ICQ" style="position: relative; top: 4px;"/> <xsl:value-of select="icq"/></dd>
						</dl>
					</xsl:if>

					<!-- Зарегистрирован -->
					<xsl:if test="date != ''">
						<dl>
							<dt>&labelRegistred; </dt>
							<dd><xsl:value-of select="date"/></dd>
						</dl>
					</xsl:if>
				</div>
				<div class="userData">
					<dl>
						<dt></dt>
						<dd>
						</dd>
					</dl>

					<!-- Друзья -->
					<xsl:apply-templates select="siteuser_relationship_type"/>

					<!-- Подписчики -->
					<xsl:if test="count(siteuser_subscribers/siteuser_relationship)">
						<dl>
							<dt>&labelSubscribers;</dt>
							<dd>
								<div>
									<xsl:for-each select="siteuser_subscribers/siteuser_relationship">
										<a href="/users/info/{siteuser/path}/"><xsl:value-of select="siteuser/login"/></a>
									<xsl:if test="position() != last()"><xsl:text>, </xsl:text></xsl:if>
									</xsl:for-each>
								</div>
							</dd>
						</dl>
					</xsl:if>
				</div>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

	<xsl:template match="siteuser_relationship_type">
		<!-- Вывод просто друзей или в гурппе есть друзья -->
		<xsl:if test="count(siteuser_relationship)">
			<dl>
				<dt>
					<!-- Название группы -->
					<xsl:choose>
						<xsl:when test="@id = 0">&labelFriends;</xsl:when>
						<xsl:otherwise><xsl:value-of select="name" />:</xsl:otherwise>
					</xsl:choose>
				</dt>
				<dd>
					<xsl:variable name="current_siteuser_id" select="/siteuser/current_siteuser_id" />
					
					<!-- Друзья -->
					<div>
						<xsl:for-each select="siteuser_relationship">
							<a href="/users/info/{siteuser/path}/"><xsl:value-of select="siteuser/login"/></a>
						<xsl:if test="position() != last()"><xsl:text>, </xsl:text></xsl:if>
						</xsl:for-each>
					</div>
				</dd>
			</dl>
		</xsl:if>
	</xsl:template>

	<xsl:template match="siteuser_relationship_type" mode="friendlist">
		<option value="{@id}">
			<xsl:value-of select="name"/>
		</option>
	</xsl:template>
</xsl:stylesheet>