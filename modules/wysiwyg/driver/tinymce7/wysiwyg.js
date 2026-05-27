$('body')
	// For TinyMCE init
	.on('afterTinyMceInit', function(event, editor) {
		editor.on('change', function() {
			if (typeof mainFormLocker !== 'undefined') {
				mainFormLocker.lock();
			}
		});
		editor.on('input', function(e) {
			if (typeof mainFormAutosave !== 'undefined') {
				mainFormAutosave.changed($('form[id ^= "formEdit"]'), e);
			}
		});
	});

const focusinHandler = (e) => {
	if (e.target.closest(".tox-tinymce-aux, .moxman-window, .tam-assetmanager-root") !== null) {
		e.stopImmediatePropagation();
	}
};

document.addEventListener('focusin', focusinHandler);

class wysiwyg {
	static saveAll($parent)
	{
		if (typeof tinyMCE != 'undefined')
		{
			tinyMCE.triggerSave();
		}
	}

	static removeAll($parent)
	{
		if (typeof tinyMCE != 'undefined')
		{
			$parent.find('textarea, div[wysiwyg = "1"]').each(function(){
				var elementId = this.id;
				if (tinyMCE.get(elementId) != null)
				{
					tinyMCE.execCommand('mceRemoveEditor', false, elementId);
				}
			});
		}
	}

	static clear($parent)
	{
		$parent.find('.tox.tox-tinymce').remove();

		$parent.find("textarea")
			.removeAttr('wysiwyg')
			.css('display', '');
	}

	static remove($textarea)
	{
		if (typeof tinyMCE != 'undefined')
		{
			var elementId = $textarea.attr('id'),
				elementName = $textarea.attr('name'),
				editor = tinyMCE.get(elementId);

			if (editor != null)
			{
				tinyMCE.execCommand('mceRemoveEditor', false, elementId);
				$textarea.attr('name', elementName);
			}
		}
	}

	static reloadTextarea($textarea, aCss)
	{
		if (typeof tinyMCE != 'undefined')
		{
			var elementId = $textarea.attr('id'),
				editor = tinyMCE.get(elementId);

			if (editor != null)
			{
				$.each(aCss, function( index, value ) {
					editor.dom.loadCSS(value);
				});
			}
		}
	}

	static uploadImageHandler(blobInfo, progress) { // eslint-disable-line
		return new Promise((resolve, reject) => {
			const xhr = new XMLHttpRequest();
			xhr.withCredentials = false;
			xhr.open('POST', hostcmsBackend + '/wysiwyg/upload.php');

			xhr.upload.onprogress = (e) => {
				progress(e.loaded / e.total * 100);
			};

			xhr.onload = () => {
				if (xhr.status === 403) {
					reject({ message: 'HTTP Error: ' + xhr.status, remove: true });
					return;
				}

				if (xhr.status < 200 || xhr.status >= 300) {
					reject('HTTP Error: ' + xhr.status);
					return;
				}

				const json = JSON.parse(xhr.responseText);

				if (!json || typeof json.url != 'string') {
					reject('Invalid JSON: ' + xhr.responseText);
					return;
				}

				if (json.status == 'success' && json.url != '')
				{
					if (typeof entity_id == 'undefined' || entity_id === null || entity_id === '')
					{
						$form.append('<input type="hidden" name="wysiwyg_images[]" value="' + json.url + '"/>');
					}

					resolve(json.url);
				}
				else
				{
					reject('Upload failed');
					return;
				}
			};

			xhr.onerror = () => {
				reject('Image upload failed due to a XHR Transport error. Code: ' + xhr.status);
			};

			let textarea, $form, entity_id, entity_type;

			if (blobInfo.editor) {
				textarea = blobInfo.editor.getElement();
			} else if (tinymce.activeEditor) {
				textarea = tinymce.activeEditor.getElement();
			}

			if (textarea) {
				$form = $(textarea).parents('form');
				entity_id = $form.data('entity_id');
				entity_type = $form.data('entity_type');

				// frontend
				if (typeof entity_id == 'undefined')
				{
					const item = $(textarea).prevAll('.hostcmsEditable').first();
					if (item.length > 0)
					{
						entity_id = item.attr('hostcms:id');
						entity_type = item.attr('hostcms:entity');
					}
				}
			} else {
				$form = $('form').first();
			}

			const formData = new FormData();
			formData.append('entity_type', entity_type || '');
			formData.append('entity_id', entity_id || '');
			formData.append('filename', blobInfo.filename());

			const blob = typeof blobInfo.blob === 'function' ? blobInfo.blob() : blobInfo.blob;
			formData.append('blob', blob);

			xhr.send(formData);
		});
	}

	static replaceWysiwygImages(aConform) // eslint-disable-line
	{
		if (typeof tinyMCE != 'undefined')
		{
			$('textarea, div[wysiwyg = "1"]').each(function(){
				var elementId = this.id;

				if (tinyMCE.get(elementId) != null)
				{
					var content = tinyMCE.get(elementId).getContent();

					$.each(aConform, function(index, object){
						content = content.split(object.source).join(object.destination);
					});

					tinyMCE.get(elementId).setContent(content);
				}
			});
		}
	}

	static frontendInit($parent)
	{
		$parent.tinymce({
			language: backendLng,
			language_url: '/modules/wysiwyg/driver/tinymce7/langs/' + backendLng + '.js',
			setup: function(editor) {
				editor.on('init', () => {
					// Сброс шрифта к стандартному
					const fontFormats = editor.options.get('font_family_formats');
					if (fontFormats && !fontFormats.includes('Default=')) {
						editor.options.set('font_family_formats', 'Default=inherit; ' + fontFormats);
					}
				});

				editor.on('blur', function(e) {
					e.stopImmediatePropagation();
					editor.remove();
					$parent.css('visibility', '');
					$parent.removeClass('editing');
				});
			},
			file_picker_callback: wysiwygFileManager.fileBrowser.bind(wysiwygFileManager),
			images_upload_handler: wysiwyg.uploadImageHandler,
			menubar: false,
			inline: true,
			plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table importcss',
			toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist | link unlink image media preview table | removeformat code',
			font_size_formats: "8pt 9pt 10pt 11pt 12pt 14pt 16pt 18pt 20pt 24pt 30pt 36pt 48pt 60pt 72pt 96pt"
		});
	}

	static frontendDbl($parent, settings, aCss)
	{
		$parent.tinymce(hQuery.extend({
			language: backendLng,
			language_url: '/modules/wysiwyg/driver/tinymce7/langs/' + backendLng + '.js',
			setup: function(editor) {
				editor.on('init', () => {
					// Сброс шрифта к стандартному
					const fontFormats = editor.options.get('font_family_formats');
					if (fontFormats && !fontFormats.includes('Default=')) {
						editor.options.set('font_family_formats', 'Default=inherit; ' + fontFormats);
					}

					// Фокус и скролл после инициализации
					setTimeout(function() {
						if (editor.getWin()) {
							editor.getWin().scrollTo(0, 0);
						}
						editor.execCommand('mceFocus', false, editor.id);
						editor.selection.select(editor.getBody(), true);
						editor.selection.collapse(true);
					}, 300);
				});

				editor.on('blur', function(e) {
					if (settings.blur) {
						settings.blur($parent);
					}
				});
			},
			file_picker_callback: wysiwygFileManager.fileBrowser.bind(wysiwygFileManager),
			images_upload_handler: wysiwyg.uploadImageHandler,
			menubar: false,
			plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table importcss',
			toolbar: 'undo redo | styleselect formatselect | bold italic underline backcolor | alignleft aligncenter alignright alignjustify | bullist numlist | link unlink image media preview table | removeformat code',
			content_css: aCss
		}, settings.wysiwygConfig || {}));
	}

	static frontendSettingsRow($parent)
	{
		$parent.tinymce({
			language: backendLng,
			language_url: '/modules/wysiwyg/driver/tinymce7/langs/' + backendLng + '.js',
			setup: function(editor) {
				editor.on('init', function(e) {
					e.stopImmediatePropagation();

					// Сброс шрифта к стандартному
					const fontFormats = editor.options.get('font_family_formats');
					if (fontFormats && !fontFormats.includes('Default=')) {
						editor.options.set('font_family_formats', 'Default=inherit; ' + fontFormats);
					}

					// editor.remove();
					$parent.css('visibility', '');
				});
			},
			file_picker_callback: wysiwygFileManager.fileBrowser.bind(wysiwygFileManager),
			images_upload_handler: wysiwyg.uploadImageHandler,
			menubar: false,
			toolbar_mode: 'sliding',
			promotion: false,
			statusbar: false,
			plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table importcss',
			toolbar: 'undo redo bold italic underline forecolor backcolor | blocks fontfamily fontsize | alignleft aligncenter alignright alignjustify | bullist numlist | link unlink image media preview table | removeformat code',
			font_size_formats: "8pt 9pt 10pt 11pt 12pt 14pt 16pt 18pt 20pt 24pt 30pt 36pt 48pt 60pt 72pt 96pt"
		});
	}
}

class WysiwygFileManager {
	constructor() {
		this.win = null;
		this.callback = null;
		this.field = null;
	}

	fileBrowser(callback, value, meta) {
		this.field = value;
		this.callback = callback;

		var url = this.field.split('\\').join('/');

		var type = meta.filetype,
			cdir = '',
			dir = '',
			lastPos = url.lastIndexOf('/');

		if (lastPos != -1)
		{
			url = url.substr(0, lastPos);

			lastPos = url.lastIndexOf('/');

			if (lastPos != -1)
			{
				cdir = url.substr(0, lastPos + 1);
				dir = url.substr(lastPos + 1);
			}
		}

		var path = hostcmsBackend + "/wysiwyg/filemanager/index.php?field_name=" + encodeURIComponent(this.field) + "&cdir=" + encodeURIComponent(cdir) + "&dir=" + encodeURIComponent(dir) + "&type=" + encodeURIComponent(type),
			width = screen.width / 1.2,
			height = screen.height / 1.2;

		var x = parseInt(screen.width / 2.0) - (width / 2.0),
			y = parseInt(screen.height / 2.0) - (height / 2.0);

		this.win = window.open(path, "FM", "top=" + y + ",left=" + x + ",scrollbars=yes,width=" + width + ",height=" + height + ",resizable=yes");

		return false;
	}

	insertFile(url, openedWindow)
	{
		url = decodeURIComponent(url);
		url = url.replace(new RegExp(/\\/g), '/');

		if (this.callback) {
			this.callback(url);
		}

		if (this.win) {
			this.win.close();
		}
	}
}

const wysiwygFileManager = new WysiwygFileManager();

window.wysiwygFileManager = wysiwygFileManager;