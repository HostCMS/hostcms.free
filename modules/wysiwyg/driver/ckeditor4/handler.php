<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Wysiwyg_Driver_Ckeditor4_Handler.
 *
 * @package HostCMS
 * @subpackage Wysiwyg
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2025 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Wysiwyg_Driver_Ckeditor4_Handler extends Wysiwyg_Handler
{
	/**
	 * Base path
	 * @var string
	 */
	static protected $_basePath = '/modules/wysiwyg/driver/ckeditor4';

	/**
	 * Get driver wysiwyg options config
	 * @return array|NULL
	 */
	public function getConfig()
	{
		return Core_Config::instance()->get('wysiwyg_ckeditor4', array());
	}

	/**
	 * Get driver js list
	 * @return array
	 */
	public function getJsList()
	{
		return array(
			self::$_basePath . "/ckeditor.js",
			self::$_basePath . "/adapters/jquery.js",
			self::$_basePath . "/wysiwyg.js"
		);
	}

	/**
	 * Get driver css list
	 * @return array
	 */
	public function getCssList()
	{
		return array();
	}

	/**
	 * Get driver raw js
	 * @return array
	 */
	public function getJs(){}

	/**
	 * Get exclude driver wysiwyg options config
	 * @return array
	 */
	public function getExcludeOptions()
	{
		return array(
			'plugins',
			'contentsCss',
			'removeButtons',
			'toolbarGroups'
		);
	}

	/**
	 * Init
	 * @param Admin_Form_Entity $oAdmin_Form_Entity_Textarea
	 */
	public function init($oAdmin_Form_Entity_Textarea)
	{
		$windowId = $oAdmin_Form_Entity_Textarea->getAdminFormController()->getWindowId();

		$aCSS = array();

		if ($oAdmin_Form_Entity_Textarea->template_id)
		{
			$oTemplate = Core_Entity::factory('Template', $oAdmin_Form_Entity_Textarea->template_id);

			do{
				$aCSS[] = "/templates/template{$oTemplate->id}/style.css?" . Core_Date::sql2timestamp($oTemplate->timestamp);
			} while ($oTemplate = $oTemplate->getParent());
		}

		$lng = Core_I18n::instance()->getLng();

		switch ($oAdmin_Form_Entity_Textarea->wysiwygMode)
		{
			case 'full':
			default:
				$init = $this->getConfig();
			break;
			case 'short':
				$init = array(
					'toolbarGroups' => "[
						{ name: 'document', groups: [ 'mode', 'document', 'doctools' ] },
						{ name: 'clipboard', groups: [ 'clipboard', 'undo' ] },
						{ name: 'editing', groups: [ 'find', 'selection', 'spellchecker', 'editing' ] },
						{ name: 'forms', groups: [ 'forms' ] },
						{ name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
						{ name: 'paragraph', groups: [ 'list', 'indent', 'blocks', 'align', 'bidi', 'paragraph' ] },
						{ name: 'links', groups: [ 'links' ] },
						{ name: 'insert', groups: [ 'insert' ] },
						'/',
						{ name: 'styles', groups: [ 'styles' ] },
						{ name: 'colors', groups: [ 'colors' ] },
						{ name: 'tools', groups: [ 'tools' ] },
						{ name: 'others', groups: [ 'others' ] },
						{ name: 'about', groups: [ 'about' ] }
					]",
					'removeButtons' => '"Source,Save,NewPage,ExportPdf,Preview,Print,Templates,PasteText,PasteFromWord,Find,Replace,SelectAll,Scayt,Form,Checkbox,Radio,TextField,Textarea,Select,Button,ImageButton,HiddenField,Subscript,Superscript,CopyFormatting,Outdent,Indent,Blockquote,CreateDiv,BidiLtr,BidiRtl,Language,Link,Unlink,Anchor,Image,Table,HorizontalRule,Smiley,SpecialChar,PageBreak,Iframe,Format,Styles,Font,FontSize,TextColor,BGColor,Maximize,ShowBlocks,About"'
				);
			break;
			case 'fullpage':
				$init = $this->getConfig();

				$init['fullPage'] = true;
			break;
		}

		$init += array(
			'language' => '"' . $lng . '"',
			'versionCheck' => false,
			'stylesSet' => '[]',
			'on' => '{ fileUploadRequest: function (evt) { return wysiwyg.uploadImageHandler(evt) } }'
		);

		$aiJs = '';
		$shortcodeJs = '';

		// Интеграция кнопок AI и Shortcode
		if (Core::moduleIsActive('ai') || Core::moduleIsActive('shortcode'))
		{
			if (Core::moduleIsActive('ai'))
			{
				$oSite = Core_Entity::factory('Site', CURRENT_SITE);
				$oAi = $oSite->Ais->getDefault();

				if (!is_null($oAi))
				{
					$aiPromptDefault = Core_Str::escapeJavascriptVariable($oAdmin_Form_Entity_Textarea->data('ai_prompt_default'));

					$aiJs = "
					CKEDITOR.dialog.add('aiDialog', function(editor) {
						return {
							title: 'AI',
							minWidth: 400,
							minHeight: 180,
							contents: [{
								id: 'info',
								elements: [{
									type: 'textarea',
									id: 'ai_prompt',
									label: 'Prompt',
									'default': '{$aiPromptDefault}',
									inputStyle: 'height: 120px;'
								}]
							}],
							onOk: function() {
								var prompt = this.getValueOf('info', 'ai_prompt');
								if (prompt !== '') {
									$.loadingScreen('show');
									$.ajax({
										url: hostcmsBackend + '/ai/index.php',
										data: { 'aiSendWysiwygQuery': 1, 'ai_id': " . $oAi->id . ", 'query': prompt },
										dataType: 'json',
										type: 'POST',
										success: function(response){
											$.loadingScreen('hide');
											if (response.status == 'success') {
												editor.insertHtml(response.text);
											} else {
												Notify('<span>AI response error! Please try again later.</span>', '', 'bottom-left', '5000', 'danger', 'fa-solid fa-ban', true);
											}
										}
									});
								}
							}
						};
					});
					editor.addCommand('aiDialog', new CKEDITOR.dialogCommand('aiDialog'));
					editor.ui.addButton('insertAiResponse', {
						label: 'AI',
						command: 'aiDialog',
						toolbar: 'insert,100'
					});";
				}
			}

			if (Core::moduleIsActive('shortcode'))
			{
				$aShortcodes = Core_Entity::factory('Shortcode')->getAllByActive(1);
				$aTmpShortcodes = array();

				foreach ($aShortcodes as $oShortcode)
				{
					$label = Core_Str::escapeJavascriptVariable($oShortcode->name) . " [" . $oShortcode->id . "]";
					$value = Core_Str::escapeJavascriptVariable($oShortcode->example);
					$aTmpShortcodes[] = "['" . $label . "', '" . $value . "']";
				}
				$sShortcodes = implode(',', $aTmpShortcodes);
				$shortcodeTitle = Core::_('Shortcode.title');

				$shortcodeJs = "
				CKEDITOR.dialog.add('shortcodeDialog', function(editor) {
					return {
						title: '{$shortcodeTitle}',
						minWidth: 320,
						minHeight: 100,
						contents: [{
							id: 'info',
							elements: [{
								type: 'select',
								id: 'shortcode',
								label: '{$shortcodeTitle}',
								items: [" . $sShortcodes . "]
							}]
						}],
						onOk: function() {
							var val = this.getValueOf('info', 'shortcode');
							if (val !== '') {
								editor.insertHtml(val);
							}
						}
					};
				});
				editor.addCommand('shortcodeDialog', new CKEDITOR.dialogCommand('shortcodeDialog'));
				editor.ui.addButton('insertShortcode', {
					label: '{$shortcodeTitle}',
					command: 'shortcodeDialog',
					toolbar: 'insert,101'
				});";
			}
		}

		$pluginJs = "";
		if ($aiJs !== "" || $shortcodeJs !== "")
		{
			$pluginJs = "
			if (typeof CKEDITOR !== 'undefined' && !CKEDITOR.plugins.get('hostcms_custom')) {
				CKEDITOR.plugins.add('hostcms_custom', {
					init: function(editor) {
						{$aiJs}
						{$shortcodeJs}
					}
				});
			}";

			$init['extraPlugins'] = isset($init['extraPlugins'])
				? '"' . trim($init['extraPlugins'], '"\'') . ',hostcms_custom"'
				: '"hostcms_custom"';
		}

		// Интеграция автокомплита для mentions
		if (is_array($oAdmin_Form_Entity_Textarea->wysiwygMentions) && count($oAdmin_Form_Entity_Textarea->wysiwygMentions)
			&& $oAdmin_Form_Entity_Textarea->wysiwygMentionTemplate != ''
		)
		{
			$init['mentions'] = "[{
				feed: function(options, callback) {
					var searchString = options.query.toLowerCase();
					var employees = " . json_encode($oAdmin_Form_Entity_Textarea->wysiwygMentions, defined('JSON_UNESCAPED_UNICODE') ? JSON_UNESCAPED_UNICODE : 0) . ";

					var filtered = employees.filter(emp =>
						emp.name.toLowerCase().includes(searchString) ||
						emp.login.toLowerCase().includes(searchString)
					);

					var results = filtered.map(emp => {
						emp.displayText = emp.name != ''
							? emp.name + ' (@' + emp.login + ')'
							: '@' + emp.login;

						return emp;
					});

					callback(results);
				},
				itemTemplate: '<li data-id=\"{id}\">{displayText}</li>',
				outputTemplate: function(item) {
					const selectedEmp = item;

					const mentionText = selectedEmp.name != ''
						? selectedEmp.name + ' (@' + selectedEmp.login + ')'
						: selectedEmp.login;

					return `" . $oAdmin_Form_Entity_Textarea->wysiwygMentionTemplate . "`;
				},
				minChars: 0
			}]";

			$init['extraPlugins'] = isset($init['extraPlugins'])
				? '"' . trim($init['extraPlugins'], '"\'') . ',mentions"'
				: '"mentions"';
		}

		!isset($init['height'])
			&& $init['height'] = '"' . ($oAdmin_Form_Entity_Textarea->rows * 30) . 'px"';

		$userCss = trim(Core_Array::get($init, 'contentsCss', ''), '\'"');

		$aUserCsses = $userCss != ''
			? array_merge(explode(',', $userCss), $aCSS)
			: $aCSS;

		count($aUserCsses)
			&& $init['contentsCss'] = "['" . implode("','", $aUserCsses) . "']";

		if (count($init) > 0)
		{
			$aInit = array();
			foreach ($init as $init_name => $init_value)
			{
				is_bool($init_value) && $init_value = $init_value ? 'true' : 'false';
				$aInit[] = "{$init_name}: {$init_value}";
			}
			$sInit = implode(", \n", $aInit);
		}
		else
		{
			$sInit = '';
		}

		$customCssJs = '';
		if ($oAdmin_Form_Entity_Textarea->wysiwygContentStyle != '')
		{
			// Экранируем стили для безопасной передачи в JS
			$safeCss = Core_Str::escapeJavascriptVariable($oAdmin_Form_Entity_Textarea->wysiwygContentStyle);
			$customCssJs = "if (typeof CKEDITOR !== 'undefined') { CKEDITOR.addCss('{$safeCss}'); }";
		}

		$Core_Html_Entity_Script = new Core_Html_Entity_Script();
		$Core_Html_Entity_Script
			->value("$(function() {
				{$pluginJs}
				{$customCssJs}
				setTimeout(function(){
					$('#" . Core_Str::escapeJavascriptVariable($windowId) . " #" . Core_Str::escapeJavascriptVariable($oAdmin_Form_Entity_Textarea->id) . "').ckeditor({ {$sInit} });
				}, 300);
			});")
			->execute();
	}
}