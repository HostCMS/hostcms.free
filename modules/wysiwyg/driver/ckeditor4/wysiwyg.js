if (typeof CKEDITOR != 'undefined')
{
	if (typeof mainFormLocker != 'undefined' && typeof mainFormAutosave != 'undefined')
	{
		CKEDITOR.on('instanceReady', function(evt) {
			evt.editor.on('change', function() { mainFormLocker.lock() });
			evt.editor.on('input', function(e) { mainFormAutosave.changed($('form[id ^= "formEdit"]'), e) });
		});
	}
}

document.addEventListener('focusin', (e) => {
	if (e.target.closest(".cke") !== null) {
		e.stopImmediatePropagation();
	}
});

class wysiwyg {
	static saveAll($parent)
	{
		/*if (typeof CKEDITOR != 'undefined')
		{

		}*/
	}

	static removeAll($parent)
	{
		if (typeof CKEDITOR != 'undefined')
		{
			for (name in CKEDITOR.instances)
			{
				CKEDITOR.instances[name].destroy()
			}
		}
	}

	static clear($parent)
	{
		$parent.find('.cke').remove();

		$parent.find("textarea")
			.removeAttr('wysiwyg')
			.css('display', '');
	}

	static remove($textarea)
	{
		if (typeof CKEDITOR != 'undefined')
		{
			var elementId = $textarea.attr('id'),
				elementName = $textarea.attr('name'),
				editor = CKEDITOR.instances[elementId];

			if (editor != null)
			{
				CKEDITOR.instances[elementId].destroy();
				$textarea.attr('name', elementName);
			}
		}
	}

	static reloadTextarea($textarea, aCss)
	{
		if (typeof CKEDITOR != 'undefined')
		{
			var elementId = $textarea.attr('id'),
				editor = CKEDITOR.instances[elementId];

			if (editor != null)
			{
				$.each(aCss, function( index, value ) {
					editor.addContentsCss(value);
				});
			}
		}
	}

	static uploadImageHandler(evt)
	{
		// console.log(evt);

		var fileLoader = evt.data.fileLoader,
			formData = new FormData(),
			xhr = fileLoader.xhr;

		xhr.setRequestHeader( 'Cache-Control', 'no-cache' );
		xhr.withCredentials = true;

		xhr.open('POST', hostcmsBackend + '/wysiwyg/upload.php', true);

		xhr.onload = () => {
			if (xhr.status === 403) {
				console.error('HTTP Error: ' + xhr.status);
				return;
			}

			if (xhr.status < 200 || xhr.status >= 300) {
				console.error('HTTP Error: ' + xhr.status);
				return;
			}

			const json = JSON.parse(xhr.responseText);

			if (!json || typeof json.url != 'string') {
				console.error('Invalid JSON: ' + xhr.responseText);
				return;
			}

			// console.log(json);

			if (json.status == 'success' && json.url != '')
			{
				if (entity_id == '')
				{
					// Добавляем скрытое поле
					$form.append('<input type="hidden" name="wysiwyg_images[]" value="' + json.url + '"/>');
				}

				// append img tag to html
				let img = '<img src="' + json.url + '">';
				let newElement = CKEDITOR.dom.element.createFromHtml(img, evt.editor.document);
				evt.editor.insertElement(newElement);
			}
			else
			{
				return;
			}
		};

		xhr.onerror = () => {
			console.error('Image upload failed due to a XHR Transport error. Code: ' + xhr.status);
		};

		let textarea = evt.editor.element.$;
		let $form = $(textarea).parents('form');
		let entity_id = $form.data('entity_id');
		let entity_type = $form.data('entity_type');

		// formData.append('upload', fileLoader.file, fileLoader.fileName);
		formData.append('blob', fileLoader.file);
		formData.append('filename', fileLoader.fileName);
		formData.append('entity_type', entity_type);
		formData.append('entity_id', entity_id);

		fileLoader.xhr.send(formData);

		// Prevented the default behavior.
		evt.stop();
	}

	static replaceWysiwygImages(aConform) // eslint-disable-line
	{
		if (typeof CKEDITOR != 'undefined')
		{
			$('textarea, div[wysiwyg = "1"]').each(function(){
				var elementId = this.id,
					editor = CKEDITOR.instances[elementId];

				if (editor != null)
				{
					var content = editor.getData();

					$.each(aConform, function(index, object){
						content = content.replace(object.source, object.destination);
					});

					editor.setData(content);
				}
			});
		}
	}

	static frontendInit($parent)
	{
		// console.log('frontendInit');

		$parent.ckeditor({
			versionCheck: false,
			language: backendLng,
			removeButtons: 'Source,Save,NewPage,ExportPdf,Preview,Print,Templates,PasteText,PasteFromWord,Find,Replace,SelectAll,Scayt,Form,Checkbox,Radio,TextField,Textarea,Select,Button,ImageButton,HiddenField,Subscript,Superscript,CopyFormatting,Outdent,Indent,Blockquote,CreateDiv,BidiLtr,BidiRtl,Language,Link,Unlink,Anchor,Image,Table,HorizontalRule,Smiley,SpecialChar,PageBreak,Iframe,Format,Styles,Font,FontSize,TextColor,BGColor,Maximize,ShowBlocks,About',
			toolbarGroups: [
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
			],
			on: {
				instanceReady: function(evt) {
					var editor = evt.editor;

					editor.on('blur', function (e) {
						e.stopImmediatePropagation();
						editor.destroy();
						$parent.css('visibility', '');
						$parent.removeClass('editing');
					});
				}
			}
		});
	}

	static frontendDbl($parent, settings, aCss)
	{
		// console.log('frontendDbl');

		$parent.ckeditor(hQuery.extend({
			versionCheck: false,
			language: backendLng,
			removeButtons: 'Source,Save,NewPage,ExportPdf,Preview,Print,Templates,PasteText,PasteFromWord,Find,Replace,SelectAll,Scayt,Form,Checkbox,Radio,TextField,Textarea,Select,Button,ImageButton,HiddenField,Subscript,Superscript,CopyFormatting,Outdent,Indent,Blockquote,CreateDiv,BidiLtr,BidiRtl,Language,Link,Unlink,Anchor,Image,Table,HorizontalRule,Smiley,SpecialChar,PageBreak,Iframe,Format,Styles,Font,FontSize,TextColor,BGColor,Maximize,ShowBlocks,About',
			toolbarGroups: [
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
			],
			on: {
				instanceReady: function(evt) {
					var editor = evt.editor;

					editor.on('blur', function (e) {
						// console.log('frontendDbl blur');
						settings.blur($parent);
						editor.destroy();
					});
				}
			},
			stylesSet: [],
			contentsCss: aCss
		}, settings.wysiwygConfig));
	}
}

// https://ckeditor.com/docs/ckeditor4/latest/guide/dev_file_browser_api.html
function wysiwygFileManager() // eslint-disable-line
{
	this.getUrlParam = function(openedWindow, paramName)
	{
		var reParam = new RegExp('(?:[\?&]|&)' + paramName + '=([^&]+)', 'i');
		var match = openedWindow.location.search.match(reParam);

		return (match && match.length > 1) ? match[1] : null;
    }

	this.insertFile = function(url, openedWindow)
	{
		url = decodeURIComponent(url);
		url = url.replace(new RegExp(/\\/g), '/');

		var funcNum = this.getUrlParam(openedWindow, 'CKEditorFuncNum');

		window/*.opener*/.CKEDITOR.tools.callFunction(funcNum, url);
		openedWindow.close();
	}
}

wysiwygFileManager = new wysiwygFileManager();