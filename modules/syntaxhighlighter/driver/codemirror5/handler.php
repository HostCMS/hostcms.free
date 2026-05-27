<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Syntaxhighlighter_Driver_Codemirror5_Handler.
 *
 * @package HostCMS
 * @subpackage Syntaxhighlighter
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2025 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Syntaxhighlighter_Driver_Codemirror5_Handler extends Syntaxhighlighter_Handler
{
	/**
	 * Base path
	 * @var string
	 */
	static protected $_basePath = '/modules/syntaxhighlighter/driver/codemirror5';

	/**
	 * Get driver syntaxhighlighter options config
	 * @return array|NULL
	 */
	public function getConfig()
	{
		return Core_Config::instance()->get('syntaxhighlighter_codemirror5', array());
	}

	/**
	 * Get driver js list
	 * @return array
	 */
	public function getJsList()
	{
		// https://www.jsdelivr.com/package/npm/codemirror?version=5.65.19

		return array(
			self::$_basePath . '/lib/codemirror.js',
			self::$_basePath . '/mode/css/css.js',
			self::$_basePath . '/mode/javascript/javascript.js',
			self::$_basePath . '/mode/php/php.js',
			self::$_basePath . '/mode/htmlmixed/htmlmixed.js',
			self::$_basePath . '/mode/clike/clike.js',
			self::$_basePath . '/mode/smarty/smarty.js',
			self::$_basePath . '/mode/sql/sql.js',
			self::$_basePath . '/mode/xml/xml.js',
			self::$_basePath . '/addon/display/autorefresh.js',
			self::$_basePath . '/addon/hint/show-hint.js',
			self::$_basePath . '/addon/hint/sql-hint.js',
			self::$_basePath . '/syntaxhighlighter.js'
		);
	}

	/**
	 * Get driver css list
	 * @return array
	 */
	public function getCssList()
	{
		return array(
			self::$_basePath . '/lib/codemirror.css',
			self::$_basePath . '/theme/eclipse.css',
			self::$_basePath . '/addon/hint/show-hint.css'
		);
	}

	/**
	 * Get driver raw js
	 * @return array
	 */
	public function getJs()
	{
		return NULL;
	}

	/**
	 * Init
	 * @param Admin_Form_Entity $oAdmin_Form_Entity_Textarea
	 */
	public function init($oAdmin_Form_Entity_Textarea)
	{
		$aTmp = array();

		$aConfig = $this->getConfig();

		switch ($oAdmin_Form_Entity_Textarea->syntaxHighlighterMode)
		{
			case 'less':
			case 'scss':
				$mode = 'css';
			break;
			default:
				$mode = $oAdmin_Form_Entity_Textarea->syntaxHighlighterMode;
		}

		$aConfig['mode'] = '"' . $mode . '"';

		foreach ($aConfig as $key => $value)
		{
			is_string($value) && !is_numeric($value) && $value[0] != '"' && $value = '"' . $value . '"';
			is_bool($value) && $value = $value ? 'true' : 'false';

			$aTmp[] = Core_Str::escapeJavascriptVariable($key) . ": " . (
				is_array($value)
					? json_encode($value)
					: $value
				);
		}

		$Core_Html_Entity_Script = new Core_Html_Entity_Script();
		$Core_Html_Entity_Script
			->value("
				$(function() {
					var textarea = document.getElementById('" . Core_Str::escapeJavascriptVariable($oAdmin_Form_Entity_Textarea->id) . "');
					var editor = CodeMirror.fromTextArea(textarea, {" . implode(",\n", $aTmp) . "});

					$(textarea).data('CodeMirrorInstance', editor);

					// SQL autocomplete
					if (editor.getMode().name == 'sql')
					{
						editor.on('keyup', function (cm, event) {
							if (!cm.state.completionActive && event.keyCode != 13 && event.keyCode != 32) {
								CodeMirror.commands.autocomplete(editor);
							}
						});
					}
				});
			")
			->execute();
	}
}