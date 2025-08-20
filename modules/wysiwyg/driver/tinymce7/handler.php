<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Wysiwyg_Driver_Tinymce7_Handler.
 *
 * @package HostCMS
 * @subpackage Wysiwyg
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2025 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Wysiwyg_Driver_Tinymce7_Handler extends Wysiwyg_Handler
{
	/**
	 * Base path
	 * @var string
	 */
	static protected $_basePath = '/modules/wysiwyg/driver/tinymce7';

	/**
	 * Get driver wysiwyg options config
	 * @return array|NULL
	 */
	public function getConfig()
	{
		return Core_Config::instance()->get('wysiwyg_tinymce7', array());
	}

	/**
	 * Get driver js list
	 * @return array
	 */
	public function getJsList()
	{
		return array(
			self::$_basePath . "/tinymce.min.js",
			self::$_basePath . "/jquery.tinymce.min.js",
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
			'toolbar1',
			'menubar',
			'file_picker_callback',
			'content_css'
		);
	}

	/**
	 * Init
	 * @param Admin_Form_Entity $oAdmin_Form_Entity_Textarea
	 */
	public function init($oAdmin_Form_Entity_Textarea)
	{
		$basePath = self::$_basePath;

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

		/*$init = is_null($this->wysiwygOptions)
			? Core_Config::instance()->get('core_wysiwyg')
			: $this->wysiwygOptions;*/

		switch ($oAdmin_Form_Entity_Textarea->wysiwygMode)
		{
			case 'full':
			default:
				$init = $this->getConfig();
			break;
			case 'short':
				$init = array(
					'menubar' => 'false',
					'statusbar' => 'false',
					'plugins' => '"advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table code wordcount"',
					//'toolbar1' => '"bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat"'
					'toolbar1' => '"copy paste | undo redo | formatselect | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist | removeformat"',
				);
			break;
			case 'fullpage':
				$init = $this->getConfig();

				$init['plugins'] = '"' . trim($init['plugins'], '"\'') . ' fullpage"';
				$init['fullpage_default_doctype'] = '"<!DOCTYPE html>"';
			break;
		}

		// add
		$init += array(
			//'script_url' => Admin_Form_Controller::correctBackendPath("'{$basePath}/tinymce.min.js?v=" . HOSTCMS_UPDATE_NUMBER . "'"),
			'language' => '"' . $lng . '"',
			'language_url' => Admin_Form_Controller::correctBackendPath("'{$basePath}/langs/{$lng}.js'"),
			'cache_suffix' => "'?v=" . HOSTCMS_UPDATE_NUMBER  . "'",
			'promotion' => "false",
			//'elements' => '"' . $oAdmin_Form_Entity_Textarea->id . '"',
			'init_instance_callback' => 'function(editor) { $(\'body\').trigger(\'afterTinyMceInit\', [editor]); }',
			'images_reuse_filename' => 'true',
			'images_upload_handler' => 'function (blobInfo, progress) { return hostcms_image_upload_handler(blobInfo, progress) }'
		);

		if (Core::moduleIsActive('shortcode'))
		{
			$aShortcodes = Core_Entity::factory('Shortcode')->getAllByActive(1);

			$aTmpShortcodes = array();

			foreach ($aShortcodes as $oShortcode)
			{
				$aTmpShortcodes[] = "{ text: '" . Core_Str::escapeJavascriptVariable($oShortcode->name) . " [" . $oShortcode->id . "]', value: '" . Core_Str::escapeJavascriptVariable($oShortcode->example) . "' }";
			}

			$sShortcodes = implode(',', $aTmpShortcodes);

			$init['setup'] = 'function(editor) {
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

		!isset($init['height'])
			&& $init['height'] = '"' . ($oAdmin_Form_Entity_Textarea->rows * 30) . 'px"';

		// $init['theme'] = '$(window).width() < 700 ? "inlite" : "modern"';

		$userCss = trim(Core_Array::get($init, 'content_css', ''), '\'"');

		$aUserCsses = $userCss != ''
			? array_merge(explode(',', $userCss), $aCSS)
			: $aCSS;

		count($aUserCsses)
			&& $init['content_css'] = "['" . implode("','", $aUserCsses) . "']";

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
		$init['link_list'] = '[' . $tinyMCELinkList . ']';

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

		$Core_Html_Entity_Script = new Core_Html_Entity_Script();
		$Core_Html_Entity_Script
			->value("$(function() { setTimeout(function(){ $('#" . Core_Str::escapeJavascriptVariable($windowId) . " #" . Core_Str::escapeJavascriptVariable($oAdmin_Form_Entity_Textarea->id) . "').tinymce({ {$sInit} }); }, 300); });")
			->execute();
	}
}