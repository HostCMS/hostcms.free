<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Syntaxhighlighter_Driver_Monaco_Handler.
 *
 * @package HostCMS
 * @subpackage Syntaxhighlighter
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2025 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Syntaxhighlighter_Driver_Monaco_Handler extends Syntaxhighlighter_Handler
{
	/**
	 * Base path
	 * @var string
	 */
	static protected $_basePath = '/modules/syntaxhighlighter/driver/monaco';

	/**
	 * Get driver syntaxhighlighter options config
	 * @return array|NULL
	 */
	public function getConfig()
	{
		return Core_Config::instance()->get('syntaxhighlighter_monaco', array());
	}

	/**
	 * Get driver js list
	 * @return array
	 */
	public function getJsList()
	{
		return array(
			self::$_basePath . '/syntaxhighlighter.js'
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
	public function getJs()
	{
		ob_start();

		?>
		<link type="text/css" data-name="vs/editor/editor.main" href="<?php echo self::$_basePath?>/min/vs/editor/editor.main.css" rel="stylesheet" />
		<script src="<?php echo self::$_basePath?>/min/vs/loader.js"></script>
		<script src="<?php echo self::$_basePath?>/min/vs/editor/editor.main.js"></script>

		<script>
			require.config({ paths: { 'vs': 'https://www.hostcms.ru/download/cdn/monaco-editor/min/vs' }});
			window.MonacoEnvironment = { getWorkerUrl: () => proxy };

			let proxy = URL.createObjectURL(new Blob([`
				self.MonacoEnvironment = {
					baseUrl: 'https://www.hostcms.ru/download/cdn/monaco-editor/min/'
				};
				importScripts('https://www.hostcms.ru/download/cdn/monaco-editor/min/vs/base/worker/workerMain.js');
			`], { type: 'text/javascript' }));
		</script>
		<?php

		return ob_get_clean();
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
			case 'smarty':
				$mode = 'php';
			break;
			default:
				$mode = $oAdmin_Form_Entity_Textarea->syntaxHighlighterMode;
		}

		$aConfig['language'] = '"' . $mode . '"';

		foreach ($aConfig as $key => $value)
		{
			is_string($value) && !is_numeric($value) && $value[0] != '"' && $value[0] != '{' && $value = '"' . $value . '"';
			is_bool($value) && $value = $value ? 'true' : 'false';

			$aTmp[] = Core_Str::escapeJavascriptVariable($key) . ": " . (
				is_array($value)
					? json_encode($value)
					: $value
				);
		}

		?><script>
			$(function() {
				require(["vs/editor/editor.main"], function () {
					let textarea = document.getElementById('<?php echo Core_Str::escapeJavascriptVariable($oAdmin_Form_Entity_Textarea->id)?>'),
						div = document.createElement('div'),
						jTextarea = $(textarea);

					div.id = textarea.id;
					div.style.height = <?php echo $oAdmin_Form_Entity_Textarea->rows * 15?> + 'px';

					$(div).insertAfter(jTextarea.hide());

					let editor = monaco.editor.create(div, { value: jTextarea.val(), <?php echo implode(",\n", $aTmp)?> });
				});
			});
		</script><?php
	}
}