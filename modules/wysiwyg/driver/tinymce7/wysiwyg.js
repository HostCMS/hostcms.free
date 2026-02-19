$('body')
	// For TinyMCE init
	.on('afterTinyMceInit', function(event, editor) {
		editor.on('change', function() { mainFormLocker.lock() });
		editor.on('input', function(e) { mainFormAutosave.changed($('form[id ^= "formEdit"]'), e) });
	});

document.addEventListener('focusin', (e) => {
	if (e.target.closest(".tox-tinymce-aux, .moxman-window, .tam-assetmanager-root") !== null) {
		e.stopImmediatePropagation();
	}
});

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
				// if (tinyMCE.getInstanceById(elementId) != null)
				if (tinyMCE.get(elementId) != null)
				{
					// console.log('mceRemoveControl');
					tinyMCE.remove('#' + elementId);
					//tinyMCE.execCommand('mceRemoveControl', false, elementId);
					//jQuery('#content').tinymce().execCommand('mceInsertContent',false, elementId);
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
				tinyMCE.remove('#' + elementId);
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

				// console.log(xhr);

				const json = JSON.parse(xhr.responseText);

				if (!json || typeof json.url != 'string') {
					reject('Invalid JSON: ' + xhr.responseText);
					return;
				}

				// console.log(json);

				if (json.status == 'success' && json.url != '')
				{
					// console.log(entity_id);

					if (entity_id == '')
					{
						// Добавляем скрытое поле
						$form.append('<input type="hidden" name="wysiwyg_images[]" value="' + json.url + '"/>');
					}

					resolve(json.url);
				}
				else
				{
					reject();
					return;
				}
			};

			xhr.onerror = () => {
				reject('Image upload failed due to a XHR Transport error. Code: ' + xhr.status);
			};

			let textarea = tinymce.activeEditor.getElement();
			let $form = $(textarea).parents('form');
			let entity_id = $form.data('entity_id');
			let entity_type = $form.data('entity_type');

			const formData = new FormData();
			formData.append('entity_type', entity_type);
			formData.append('entity_id', entity_id);
			formData.append('filename', blobInfo.filename());
			formData.append('blob', blobInfo.blob());

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
						content = content.replace(object.source, object.destination);
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
			init_instance_callback: function (editor) {
				editor.on('blur', function (e) {
					e.stopImmediatePropagation();
					editor.remove();
					$parent.css('visibility', '');
					$parent.removeClass('editing');
				});
			},
			//script_url: hostcmsBackend + "/wysiwyg/tinymce.min.js",
			menubar: false,
			inline: true,
			plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table  importcss',
			toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist | link unlink image media preview table | removeformat code',
			font_size_formats: "8pt 9pt 10pt 11pt 12pt 14pt 16pt 18pt 20pt 24pt 30pt 36pt 48pt 60pt 72pt 96pt"
		});
	}

	static frontendDbl($parent, settings, aCss)
	{
		$parent.tinymce(hQuery.extend({
			// theme: "silver",
			// toolbar_items_size: "small",
			language: backendLng,
			language_url: '/modules/wysiwyg/driver/tinymce7/langs/' + backendLng + '.js',
			init_instance_callback: function (editor) {
				editor.on('blur', function (e) {
					settings.blur($parent);
				});
			},
			//script_url: hostcmsBackend + "/wysiwyg/tinymce.min.js",
			menubar: false,
			plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table  importcss',
			toolbar: 'undo redo | styleselect formatselect | bold italic underline backcolor | alignleft aligncenter alignright alignjustify | bullist numlist | link unlink image media preview table | removeformat code',
			content_css: aCss
		}, settings.wysiwygConfig));
	}

	static frontendSettingsRow($parent)
	{
		$parent.tinymce({
			language: backendLng,
			language_url: '/modules/wysiwyg/driver/tinymce6/langs/' + backendLng + '.js',
			init_instance_callback: function (editor) {
				editor.on('init', function (e) {
					e.stopImmediatePropagation();
					editor.remove();
					$parent.css('visibility', '');
				});
			},
			// script_url: hostcmsBackend + "/wysiwyg/tinymce.min.js",
			menubar: false,
			toolbar_mode: 'sliding',
			toolbar_items_size: 'small',
			promotion: false,
			statusbar: false,
			// inline: true,
			plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table importcss',
			toolbar: 'undo redo bold italic underline forecolor backcolor | blocks fontfamily fontsize | alignleft aligncenter alignright alignjustify | bullist numlist | link unlink image media preview table | removeformat code',
			font_size_formats: "8pt 9pt 10pt 11pt 12pt 14pt 16pt 18pt 20pt 24pt 30pt 36pt 48pt 60pt 72pt 96pt"
		});
	}
}

// http://www.tinymce.com/wiki.php/How-to_implement_a_custom_file_browser
function wysiwygFileManager() // eslint-disable-line
{
	//this.fileBrowserCallBack = function(field_name, url, type, win)
	this.fileBrowser = function(callback, value, meta)
	{
		this.field = value;
		//this.callerWindow = win;
		this.callback = callback;

		var url = this.field.split('\\').join('/');

		var type = meta.filetype,
			cdir = '',
			dir = '',
			lastPos = url.lastIndexOf('/');

		if (lastPos != -1)
		{
			url = url.substr(0, lastPos);
			// => /upload

			lastPos = url.lastIndexOf('/');

			if (lastPos != -1)
			{
				cdir = url.substr(0, lastPos + 1);
				dir = url.substr(lastPos + 1);
			}
		}

		var path = hostcmsBackend + "/wysiwyg/filemanager/index.php?field_name=" + this.field + "&cdir=" + cdir + "&dir=" + dir + "&type=" + type, width = screen.width / 1.2, height = screen.height / 1.2;

		var x = parseInt(screen.width / 2.0) - (width / 2.0), y = parseInt(screen.height / 2.0) - (height / 2.0);

		this.win = window.open(path, "FM", "top=" + y + ",left=" + x + ",scrollbars=yes,width=" + width + ",height=" + height + ",resizable=yes");

		return false;
	}

	this.insertFile = function(url, openedWindow)
	{
		url = decodeURIComponent(url);
		url = url.replace(new RegExp(/\\/g), '/');

		this.callback(url);

		this.win.close();
	}
}

wysiwygFileManager = new wysiwygFileManager();