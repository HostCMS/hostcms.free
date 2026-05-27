<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Syntaxhighlighter_Driver_Ace_Handler.
 *
 * @package HostCMS
 * @subpackage Syntaxhighlighter
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2025 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Syntaxhighlighter_Driver_Ace_Handler extends Syntaxhighlighter_Handler
{
	/**
	 * Base path
	 * @var string
	 */
	static protected $_basePath = '/modules/syntaxhighlighter/driver/ace';

	/**
	 * Get driver syntaxhighlighter options config
	 * @return array|NULL
	 */
	public function getConfig()
	{
		return Core_Config::instance()->get('syntaxhighlighter_ace', array());
	}

	/**
	 * Get driver js list
	 * @return array
	 */
	public function getJsList()
	{
		//https://github.com/ajaxorg/ace-builds

		return array(
			self::$_basePath . '/ace.js',
			self::$_basePath . '/theme-github.js',
			self::$_basePath . '/mode-html.js',
			self::$_basePath . '/mode-php.js',
			self::$_basePath . '/mode-css.js',
			self::$_basePath . '/mode-less.js',
			self::$_basePath . '/mode-scss.js',
			self::$_basePath . '/mode-xml.js',
			self::$_basePath . '/mode-sql.js',
			self::$_basePath . '/mode-smarty.js',
			self::$_basePath . '/ext-language_tools.js',
			self::$_basePath . '/ext-searchbox-hostcms.js',
			self::$_basePath . '/ext-prompt.js',
			self::$_basePath . '/ext-beautify.js',
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
		$aConfig['mode'] = '"ace/mode/' . $oAdmin_Form_Entity_Textarea->syntaxHighlighterMode . '"';

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
					var textarea = document.getElementById('" . Core_Str::escapeJavascriptVariable($oAdmin_Form_Entity_Textarea->id) . "'),
						jTextarea = $(textarea),
						wrapper = $('<div class=\"ace-editor-wrapper\" style=\"position:relative; border: 1px solid #e5e5e5; border-radius: 3px;\"></div>'),
						toolbar = $('<div class=\"ace-toolbar\" style=\"background: #f9f9f9; padding: 5px 10px; border-bottom: 1px solid #e5e5e5; text-align: right; display: flex; justify-content: flex-end; gap: 5px;\"></div>'),
						btnBeautify = $('<button type=\"button\" class=\"btn btn-xs btn-info\" title=\"" . Core::_('Syntaxhighlighter.beautify_code_title') . "\"><i class=\"fa-solid fa-wand-magic\"></i> " . Core::_('Syntaxhighlighter.beautify_code') . "</button>'),
						btnFullscreen = $('<button type=\"button\" class=\"btn btn-xs btn-default\" title=\"" . Core::_('Syntaxhighlighter.fullscreen') . "\"><i class=\"fa-solid fa-expand\"></i></button>'),
						div = document.createElement('div');

					jTextarea.hide().before(wrapper);
					toolbar.append(btnBeautify).append(btnFullscreen);
					wrapper.append(toolbar).append(div);

					$(div).text(jTextarea.val());

					ace.config.set('basePath', '/modules/syntaxhighlighter/driver/ace/');
					ace.config.set('themePath', '/modules/syntaxhighlighter/driver/ace/');
					ace.config.set('workerPath', '/modules/syntaxhighlighter/driver/ace/');

					// Инициализация плагина Beautify
					var beautify = ace.require('ace/ext/beautify');

					var editor = ace.edit(div, {
						" . implode(",\n							", $aTmp) . "
					});

					// Включаем валидатор (он включен по умолчанию, но для надежности)
					editor.session.setUseWorker(true);

					// Перехватываем ошибки от Web Worker и фильтруем ложные срабатывания
					var isFilteringAnnotations = false;
					editor.session.on('changeAnnotation', function() {
						if (isFilteringAnnotations) return;

						var annotations = editor.session.getAnnotations();
						if (!annotations || annotations.length === 0) return;

						var filteredAnnotations = annotations.filter(function(anno) {
							var text = anno.text.toLowerCase();
							// Исключаем ошибки, связанные с DTD сущностями
							var isEntityError = text.indexOf('entity') !== -1 || text.indexOf('not defined') !== -1;
							return !isEntityError;
						});

						// Обновляем аннотации в редакторе только если массив изменился,
						// чтобы избежать бесконечного цикла событий
						if (annotations.length !== filteredAnnotations.length) {
							isFilteringAnnotations = true;
							editor.session.setAnnotations(filteredAnnotations);
							isFilteringAnnotations = false;
						}
					});

					editor.setOptions({
						maxLines: '{$oAdmin_Form_Entity_Textarea->rows}',
						minLines: '{$oAdmin_Form_Entity_Textarea->rows}'
					});

					// Синхронизация и сохранение по Ctrl+S
					// Постоянная синхронизация содержимого с оригинальной textarea
					editor.getSession().on('change', function() {
						jTextarea.val(editor.getSession().getValue());
					});

					editor.commands.addCommand({
						name: 'save',
						bindKey: {win: 'Ctrl-S',  mac: 'Command-S'},
						exec: function() {
							// Принудительно обновляем textarea перед сохранением
							jTextarea.val(editor.getSession().getValue());

							var btnSave = jTextarea.closest('form').find('button[name=\"save\"], input[name=\"save\"]');
							if (btnSave.length) {
								btnSave.click();
							} else {
								jTextarea.closest('form').submit();
							}
						}
					});

					// Полноэкранный режим
					var isFullscreen = false;
					btnFullscreen.on('click', function(e) {
						e.preventDefault();
						isFullscreen = !isFullscreen;

						if (isFullscreen) {
							// Разворачиваем wrapper
							wrapper.css({
								position: 'fixed', top: 0, left: 0, right: 0, bottom: 0,
								zIndex: 100000, background: '#fff', border: 'none', borderRadius: 0
							});
							$(div).css({ height: 'calc(100vh - 40px)', width: '100%' }); // Оставляем место под тулбар

							// Снимаем лимиты строк для корректного ресайза по высоте
							editor.setOptions({ maxLines: null, minLines: null });
							btnFullscreen.html('<i class=\"fa-solid fa-compress\"></i>');
						} else {
							// Возвращаем исходные стили
							wrapper.attr('style', 'position:relative; border: 1px solid #e5e5e5; border-radius: 3px;');
							$(div).css({ height: '', width: '' });

							// Возвращаем лимиты строк из настроек поля
							editor.setOptions({
								maxLines: '{$oAdmin_Form_Entity_Textarea->rows}',
								minLines: '{$oAdmin_Form_Entity_Textarea->rows}'
							});
							btnFullscreen.html('<i class=\"fa-solid fa-expand\"></i>');
						}
						editor.resize();
					});

					// Форматирование кода (Beautify), есть баг в ext-beautify.js с самозакрывающими тегами.
					btnBeautify.on('click', function(e) {
						e.preventDefault();

						var currentValue = editor.getValue();

						// Прячем слеши, включая PHP-теги <? ... ?> внутри атрибутов
						var hideRegex = /(<(?:br|hr|img|meta|link|input|area|base|col|param)\b(?:[^>]|<\?[\s\S]*?\?>)*?)\s*\/>/gi;
						var tempValue = currentValue.replace(hideRegex, '$1 data-ace-slash=\"1\">');

						if (currentValue !== tempValue) {
							// Обновляем значение до форматирования
							editor.setValue(tempValue, -1);
						}

						// Запускаем штатный beautify
						beautify.beautify(editor.session);

						// Возвращаем слеши на место (ищем теги с нашим временным атрибутом)
						var beautifiedValue = editor.getValue();

						var restoreRegex = /(<(?:br|hr|img|meta|link|input|area|base|col|param)\b(?:[^>]|<\?[\s\S]*?\?>)*?)\s*data-ace-slash=\"1\"\s*>/gi;
						var finalValue = beautifiedValue.replace(restoreRegex, '$1 />');

						if (beautifiedValue !== finalValue) {
							// Обновляем финальный код в редакторе с восстановленными тегами
							editor.setValue(finalValue, -1);
						}
					});
				});
			")
			->execute();
	}
}