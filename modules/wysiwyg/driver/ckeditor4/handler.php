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

		$Core_Html_Entity_Script = new Core_Html_Entity_Script();
		$Core_Html_Entity_Script
			->value("$(function() { setTimeout(function(){ $('#" . Core_Str::escapeJavascriptVariable($windowId) . " #" . Core_Str::escapeJavascriptVariable($oAdmin_Form_Entity_Textarea->id) . "').ckeditor({ {$sInit} }); }, 300); });")
			->execute();
	}
}