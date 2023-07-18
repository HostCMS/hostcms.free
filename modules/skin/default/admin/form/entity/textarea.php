<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Admin forms.
 *
 * @package HostCMS
 * @subpackage Skin
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Skin_Default_Admin_Form_Entity_Textarea extends Admin_Form_Entity
{
	/**
	 * Config
	 * @var array
	 */
	protected $_init = NULL;

	/**
	 * Skip properties
	 * @var array
	 */
	protected $_skipProperties = array(
		'divAttr', // array
		'caption',
		'format', // array, массив условий форматирования
		'value', // идет в значение <textarea>
		'template_id', // ID макета для визуального редактора
		'syntaxHighlighter',
		'syntaxHighlighterOptions',
		'wysiwygOptions',
	);

	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'wysiwyg',
		'wysiwygOptions',
		'syntaxHighlighter',
		'syntaxHighlighterOptions'
	);

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		// Combine
		$this->_skipProperties = array_combine($this->_skipProperties, $this->_skipProperties);

		$oCore_Html_Entity_Textarea = new Core_Html_Entity_Textarea();
		$this->_allowedProperties += $oCore_Html_Entity_Textarea->getAllowedProperties();

		// Свойства, исключаемые для <textarea>, добавляем в список разрешенных объекта
		$this->_allowedProperties += $this->_skipProperties;

		parent::__construct();

		$oCore_Registry = Core_Registry::instance();
		$iAdmin_Form_Count = $oCore_Registry->get('Admin_Form_Count', 0);
		$oCore_Registry->set('Admin_Form_Count', $iAdmin_Form_Count + 1);

		$aConfig = Core_Config::instance()->get('core_syntaxhighlighter', array()) + array(
			'mode' => 'ace/mode/php',
			'theme' => 'ace/theme/chrome',
			'showPrintMargin' => false,
			'autoScrollEditorIntoView' => true,
			'wrap' => true
		);

		$this->id = $this->name = 'field_id_' . $iAdmin_Form_Count;
		$this->style('width: 100%')
			->rows(3)
			->syntaxHighlighterOptions(
				$aConfig
				/*array(
					'mode' => 'ace/mode/php',
					'theme' => 'ace/theme/github',
					'showPrintMargin' => false,
					'autoScrollEditorIntoView' => true,
					'wrap' => true
				)*/
			);

		$this->class .= ' form-control';
		$this->divAttr = array('class' => 'form-group col-xs-12');
	}

	/**
	 * Executes the business logic.
	 */
	public function execute()
	{
		$windowId = $this->_Admin_Form_Controller->getWindowId();

		($this->wysiwyg || $this->syntaxHighlighter)
			&& $this->id = $windowId . '_' . $this->id;

		$aAttr = $this->getAttrsString();

		$aDefaultDivAttr = array('class' => 'item_div');
		$this->divAttr = Core_Array::union($this->divAttr, $aDefaultDivAttr);

		$aDivAttr = array();

		// Установим атрибуты div'a.
		if (is_array($this->divAttr))
		{
			foreach ($this->divAttr as $attrName => $attrValue)
			{
				$aDivAttr[] = "{$attrName}=\"" . htmlspecialchars((string) $attrValue) . "\"";
			}
		}

		?><div <?php echo implode(' ', $aDivAttr)?>><?php

		?><span class="caption"><?php echo $this->caption?></span><?php

		if (count($this->_children))
		{
			?><div class="input-group"><?php
		}

		$this->_init = is_null($this->wysiwygOptions)
			? Core_Config::instance()->get('core_wysiwyg')
			: $this->wysiwygOptions;

		$tagName = isset($this->_init['inline'])
			? 'div'
			: 'textarea';

		$this->_format();

		?><<?php echo $tagName?> <?php echo implode(' ', $aAttr) ?>><?php echo htmlspecialchars((string) $this->value)?></<?php echo $tagName?>><?php

		// $this->_format();

		if (count($this->_children))
		{
			// Могут быть дочерние элементы элементы
			$this->executeChildren();
			?></div><?php
		}

		?></div><?php
	}

	protected function _format()
	{
		$windowId = $this->_Admin_Form_Controller->getWindowId();

		if ($this->wysiwyg)
		{
			if (!defined('USE_WYSIWYG') || USE_WYSIWYG)
			{
				$aCSS = array();

				if ($this->template_id)
				{
					$oTemplate = Core_Entity::factory('Template', $this->template_id);

					do{
						$aCSS[] = "/templates/template{$oTemplate->id}/style.css?" . Core_Date::sql2timestamp($oTemplate->timestamp);
					} while ($oTemplate = $oTemplate->getParent());
				}

				$lng = Core_I18n::instance()->getLng();

				// add
				$this->_init['script_url'] = "'/admin/wysiwyg/tinymce.min.js?v=" . HOSTCMS_UPDATE_NUMBER . "'";
				$this->_init['language'] = '"' . $lng . '"';
				$this->_init['language_url'] = "'/admin/wysiwyg/langs/{$lng}.js'";
				$this->_init['cache_suffix'] = "'?v=" . HOSTCMS_UPDATE_NUMBER  . "'";
				$this->_init['promotion'] = "false";
				//$this->_init['elements'] = '"' . $this->id . '"';

				$this->_init['init_instance_callback'] = 'function(editor) { $(\'body\').trigger(\'afterTinyMceInit\', [editor]); }';

				if (Core::moduleIsActive('shortcode'))
				{
					$aShortcodes = Core_Entity::factory('Shortcode')->getAllByActive(1);

					$aTmpShortcodes = array();

					foreach ($aShortcodes as $oShortcode)
					{
						$aTmpShortcodes[] = "{ text: '" . Core_Str::escapeJavascriptVariable($oShortcode->name) . " [" . $oShortcode->id . "]', value: '" . Core_Str::escapeJavascriptVariable($oShortcode->example) . "' }";
					}

					$sShortcodes = implode(',', $aTmpShortcodes);

					$this->_init['setup'] = 'function(editor) {
						editor.ui.registry.addButton(\'insertShortcode\', {
							text: "' . Core::_('Shortcode.title') . '",
							type: \'button\',
							onAction: function (_) {
								tinymce.activeEditor.windowManager.open({
									width: 320,
									height: 240,
									title: "' . Core::_('Shortcode.title') . '",
									body: {
										type: \'panel\',
										items: [
											{
												type: \'listbox\', // component type
												name: \'shortcode\', // identifier
												enabled: true, // enabled state
												items: [' . $sShortcodes . ']
											}
										]
									},
									buttons: [
										{
										  type: \'custom\',
										  name: \'applyShortcode\',
										  enabled: true,
										  text: \'OK\',
										  buttonType: \'primary\',
										}
									],
									onAction: (api, details) => {
										const data = api.getData();

										if (data.shortcode !== \'\')
										{
											tinymce.activeEditor.execCommand(\'mceInsertContent\', false, data.shortcode);
										}

										api.close();
									}
								});
							}
						});
					}';
				}

				!isset($this->_init['height'])
					&& $this->_init['height'] = '"' . ($this->rows * 30) . 'px"';

				// $this->_init['theme'] = '$(window).width() < 700 ? "inlite" : "modern"';

				$userCss = trim(Core_Array::get($this->_init, 'content_css', ''), '\'"');

				$aUserCsses = $userCss != ''
					? array_merge(explode(',', $userCss), $aCSS)
					: $aCSS;

				if (count($aUserCsses))
				{
					$this->_init['content_css'] = "['" . implode("','", $aUserCsses) . "']";
				}

				// Array of structures
				$aStructures = $this->_fillStructureList(CURRENT_SITE);

				$tinyMCELinkListArray = array();

				foreach ($aStructures as $oStructure)
				{
					// Внешняя ссылка есть, если значение внешней ссылки не пустой
					$link = $oStructure->type != 3
						? $oStructure->getPath()
						: $oStructure->url;

					if ($link != '')
					{
						$tinyMCELinkListArray[] = '{title: \'' . Core_Str::escapeJavascriptVariable($oStructure->dataTitle) . '\', value: \'' . Core_Str::escapeJavascriptVariable($link) . '\'}';
					}
				}

				$tinyMCELinkList = implode(',', $tinyMCELinkListArray);

				unset($tinyMCELinkListArray);

				// Передаем в конфигураци
				$this->_init['link_list'] = '[' . $tinyMCELinkList . ']';

				if (count($this->_init) > 0)
				{
					$aInit = array();
					foreach ($this->_init as $init_name => $init_value)
					{
						$aInit[] = "{$init_name}: {$init_value}";
					}
					$sInit = implode(", \n", $aInit);
				}
				else
				{
					$sInit = '';
				}

				$Core_Html_Entity_Script = new Core_Html_Entity_Script();
				$Core_Html_Entity_Script
					->value("$(function() { setTimeout(function(){ $('#" . Core_Str::escapeJavascriptVariable($windowId) . " #" . Core_Str::escapeJavascriptVariable($this->id) . "').tinymce({ {$sInit} }); }, 300); });")
					->execute();
			}
		}
		elseif ($this->syntaxHighlighter)
		{
			$aTmp = array();
			foreach ($this->syntaxHighlighterOptions as $key => $value)
			{
				$aTmp[] = "'" . Core_Str::escapeJavascriptVariable($key) . "': '" . Core_Str::escapeJavascriptVariable($value) . "'";
			}

			// $sHeight = ($this->rows * 15) . 'px';

			$Core_Html_Entity_Script = new Core_Html_Entity_Script();
			$Core_Html_Entity_Script
				/*->value("$(function() { var editor = CodeMirror.fromTextArea(document.getElementById('" . Core_Str::escapeJavascriptVariable($this->id) . "'), {
					" . implode(",\n", $aTmp) . "
				});
				editor.setSize(null, '{$sHeight}');
				});")*/
				->value("
					$(function() {
						var textarea = document.getElementById('" . Core_Str::escapeJavascriptVariable($this->id) . "'),
							div = document.createElement('div'),
							jTextarea = $(textarea);

						$(div).insertAfter(jTextarea.hide()).text(jTextarea.val());

						var editor = ace.edit(div, {
							" . implode(",\n", $aTmp) . "
						});

						editor.setOptions({
							maxLines: '" . $this->rows . "',
							minLines: '" . $this->rows . "'
						});
					});
				")
				->execute();
		}
		else
		{
			$this->_showFormat();
		}
	}

	/**
	 * Fill structure list
	 * @param int $iSiteId site ID
	 * @param int $iParentId parent node ID
	 * @param int $iLevel current nesting level
	 * @return array
	 */
	protected function _fillStructureList($iSiteId, $iParentId = 0, $iLevel = 0)
	{
		$iSiteId = intval($iSiteId);
		$iParentId = intval($iParentId);
		$iLevel = intval($iLevel);

		$aReturn = array();

		$oSite = Core_Entity::factory('Site', $iSiteId);

		$oStructures = $oSite->Structures;
		$oStructures->queryBuilder()
			->clearOrderBy()
			->orderBy('structures.sorting', 'ASC');

		$aChildren = $oStructures->getAllByparent_id($iParentId);
		foreach ($aChildren as $oStructure)
		{
			$oStructure->dataTitle = str_repeat('  ', $iLevel) . $oStructure->name;
			$aReturn[$oStructure->id] = $oStructure;
			$aReturn += $this->_fillStructureList($iSiteId, $oStructure->id, $iLevel + 1);
		}

		return $aReturn;
	}
}