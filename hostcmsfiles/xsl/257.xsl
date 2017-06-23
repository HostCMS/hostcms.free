<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml" />
	
	<!-- ОтобразитьФорму -->
	
	<xsl:template match="/">
		<xsl:apply-templates select="/form" />
	</xsl:template>
	
	<xsl:template match="/form">
		<!-- Проверка формы -->
		<SCRIPT type="text/javascript" language="JavaScript">
			$(document).ready(function(){
			$("#form<xsl:value-of select="@id" />").validate({
			focusInvalid: true,
			errorClass: "input_error"
			})
			});
		</SCRIPT>
		
		<h1><xsl:value-of disable-output-escaping="yes" select="name" /></h1>
		
		<xsl:choose>
			<xsl:when test="success/node() and success = 1">
				<p>Спасибо! Запрос получен, в ближайшее время Вам будет дан ответ.</p>
			</xsl:when>
			<xsl:otherwise>
				<xsl:choose>
					<!-- Выводим ошибку (error), если она была передана через внешний параметр -->
					<xsl:when test="error != ''">
						<div id="error">
							<xsl:value-of disable-output-escaping="yes" select="error" />
						</div>
					</xsl:when>
					<xsl:when test="errorId/node()">
						<div id="error">
							<xsl:choose>
								<xsl:when test="errorId = 0">
									Вы неверно ввели число подтверждения отправки формы!
								</xsl:when>
								<xsl:when test="errorId = 1">
									Заполните все обязательные поля!
								</xsl:when>
								<xsl:when test="errorId = 2">
									Прошло слишком мало времени с момента последней отправки Вами формы!
								</xsl:when>
							</xsl:choose>
						</div>
					</xsl:when>
					<xsl:otherwise>
						<xsl:value-of disable-output-escaping="yes" select="description" />
					</xsl:otherwise>
				</xsl:choose>
				
				<!-- Параметр action формы должен быть "./", если обработчик на этой же странице, либо "./form/", если обработчик на другой странице, например ./form/ -->
				<form name="form{@id}" id="form{@id}" class="validate contacts-form" action="./" method="post" enctype="multipart/form-data">
					<fieldset>
						<!-- Вывод списка полей формы -->
						<xsl:apply-templates select="form_field" mode="show_form1" />
						<!-- Вывод списка полей формы -->
						<xsl:apply-templates select="form_field" mode="show_form2" />
						
						<!-- Код подтверждения -->
						<xsl:if test="captcha_id != 0">
							<table border="0" cellpadding="2" cellspacing="0">
								<tr>
									<td>Контрольные цифры:
										<sup>
											<font color="red">*</font>
										</sup>
									</td>
								</tr>
								<tr>
									<td>
										<img id="formCaptcha_{/form/@id}_{/form/captcha_id}" src="/captcha.php?id={captcha_id}&amp;height=30&amp;width=100" class="image" name="captcha" />
										
										
										<div class="captcha">
											<img src="/hostcmsfiles/images/refresh.gif" /> <span onclick="$('#formCaptcha_{/form/@id}_{/form/captcha_id}').updateCaptcha('{/form/captcha_id}', 30); return false">Показать другое число</span>
										</div>
										<!--
										<div id="captcha">
											<img style="border: 0px" src="/hostcmsfiles/images/refresh.gif" /> <a href="javascript:void(0);" onclick="ReNewCaptchaById('form_captcha_{/document/forms_id}_{/document/forms_captcha_key}', '{forms_captcha_key}', 30); return false;">Показать другое число</a>
										</div>
										-->
										
										<input type="hidden" name="captcha_id" value="{/form/captcha_id}" size="5" />
										<input type="text" name="captcha" class="required" minlength="4" title="Введите число, которое указано выше." />
										<!-- <div id="captcha">Введите число, которое указано выше.</div> -->
									</td>
								</tr>
							</table>
						</xsl:if>
				<div class="alignright"><a onclick="if ($('#form{@id}').validate().form()){{return document.getElementById('form{@id}').submit()}} else {{return false;}}" href="javascript:void(0);"  class="button">Отправить</a><a onclick="document.getElementById('form{@id}').reset()" href="javascript:void(0);" class="button">Очистить</a></div>
						<input type="hidden" name="Submit" valie="1"/>
					</fieldset>
				</form>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	
	<xsl:template match="form_field" mode="show_form1">
		<!-- Не скрытое поле и не надпись -->
		<xsl:if test="type != 7 and type != 8 and type != 5">
			<div class="input_field">
				<xsl:value-of disable-output-escaping="yes" select="caption" />:
				<xsl:if test="obligatory=1">
					<sup>
						<font color="red">*</font>
					</sup>
				</xsl:if><br />
				
				<!-- Текстовые поля -->
				<xsl:if test="type = 0 or type = 1 or type = 2">
					
					<input type="text" name="{name}" value="{value}" size="{size}">
						<xsl:choose>
							<xsl:when test="type = 1">
								<xsl:attribute name="type">password</xsl:attribute>
							</xsl:when>
							<!-- Поле для ввода пароля -->
							<xsl:when test="type = 2">
								<xsl:attribute name="type">file</xsl:attribute>
							</xsl:when>
							<!-- Поле загрузки файла -->
							<xsl:otherwise>
								<xsl:attribute name="type">text</xsl:attribute>
							</xsl:otherwise>
						</xsl:choose>
						<xsl:if test="obligatory = 1">
							<xsl:attribute name="class">required</xsl:attribute>
							<xsl:attribute name="minlength">1</xsl:attribute>
							<xsl:attribute name="title">Заполните поле <xsl:value-of disable-output-escaping="yes" select="caption" /></xsl:attribute>
						</xsl:if>
					</input>
				</xsl:if>
				
				<!-- Радиокнопки -->
				<xsl:if test="type = 3 or type = 9">
					<xsl:apply-templates select="list/list_item" />
					<label class="input_error" for="{name}" style="display: none">Выберите, пожалуйста, значение.</label>
				</xsl:if>
				
				<!-- Checkbox -->
				<xsl:if test="type = 4">
					<input type="checkbox" name="{name}">
						<xsl:if test="checked = 1">
							<xsl:attribute name="checked">checked</xsl:attribute>
						</xsl:if>
					</input>
				</xsl:if>
				
				<!-- Список -->
				<xsl:if test="type = 6">
					<select name="{name}">
						<xsl:if test="obligatory = 1">
							<xsl:attribute name="class">required</xsl:attribute>
							<xsl:attribute name="title">Заполните поле <xsl:value-of disable-output-escaping="yes" select="caption" /></xsl:attribute>
						</xsl:if>
						<option value="">...</option>
						<xsl:apply-templates select="list/list_item" />
					</select>
				</xsl:if>
			</div>
		</xsl:if>
		
		<!-- скрытое поле -->
		<xsl:if test="type = 7">
			<input type="hidden" name="{name}" value="{value}" />
		</xsl:if>
		
		<!-- Надпись -->
		<xsl:if test="type = 8">
			<strong><xsl:value-of disable-output-escaping="yes" select="caption" /></strong>
		</xsl:if>
		
	</xsl:template>
	
	<xsl:template match="form_field" mode="show_form2">
		<!-- Textarea -->
		<xsl:if test="type = 5">
			<xsl:value-of disable-output-escaping="yes" select="caption" />:
			<xsl:if test="obligatory=1">
				<sup>
					<font color="red">*</font>
				</sup>
			</xsl:if>
			<br />
			<textarea name="{name}" cols="{cols}" rows="{rows}" wrap="off">
				<xsl:if test="obligatory = 1">
					<xsl:attribute name="class">required</xsl:attribute>
					<xsl:attribute name="minlength">1</xsl:attribute>
					<xsl:attribute name="title">Заполните поле <xsl:value-of disable-output-escaping="yes" select="caption" /></xsl:attribute>
				</xsl:if>
				<xsl:value-of disable-output-escaping="yes" select="value" />
			</textarea>
		</xsl:if>
	</xsl:template>
	
	<!-- Формируем радиогруппу или выпадающий список -->
	<xsl:template match="list/list_item">
		<xsl:choose>
			<xsl:when test="../../type = 3">
				<input id="{../../name}_{@id}" type="radio" name="{../../name}" value="{value}">
					<xsl:if test="value = ../../value">
						<xsl:attribute name="checked">checked</xsl:attribute>
					</xsl:if>
					<xsl:if test="../../obligatory = 1">
						<xsl:attribute name="class">required</xsl:attribute>
						<xsl:attribute name="minlength">1</xsl:attribute>
						<xsl:attribute name="title">Заполните поле <xsl:value-of disable-output-escaping="yes" select="caption" /></xsl:attribute>
					</xsl:if>
			</input><xsl:text> </xsl:text>
				<label for="{../../name}_{@id}"><xsl:value-of disable-output-escaping="yes" select="value" /></label>
				<br/>
			</xsl:when>
			<xsl:when test="../../type = 6">
				<option value="{value}">
					<xsl:if test="value = ../../value">
						<xsl:attribute name="selected">selected</xsl:attribute>
					</xsl:if>
					<xsl:value-of disable-output-escaping="yes" select="value" />
				</option>
			</xsl:when>
			<xsl:when test="../../type = 9">
				<input id="{../../name}_{@id}" type="checkbox" name="{../../name}" value="{value}">
					<xsl:if test="value = ../../value">
						<xsl:attribute name="checked">checked</xsl:attribute>
					</xsl:if>
					<xsl:if test="../../obligatory = 1">
						<xsl:attribute name="class">required</xsl:attribute>
						<xsl:attribute name="minlength">1</xsl:attribute>
						<xsl:attribute name="title">Заполните поле <xsl:value-of disable-output-escaping="yes" select="caption" /></xsl:attribute>
					</xsl:if>
			</input><xsl:text> </xsl:text>
				<label for="{../../name}_{@id}"><xsl:value-of disable-output-escaping="yes" select="value" /></label>
				<br/>
			</xsl:when>
		</xsl:choose>
	</xsl:template>
	
</xsl:stylesheet>