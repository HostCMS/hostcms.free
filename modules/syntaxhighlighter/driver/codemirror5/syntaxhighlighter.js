class syntaxhighlighter {
	static save($textarea) {
		// Ищем CodeMirror либо рядом, либо в данных jQuery
		const $cmElement = $textarea.next('.CodeMirror');
		if ($cmElement.length) {
			// Стандартный способ получения инстанса
			const editor = $textarea.data('CodeMirrorInstance')
						|| $textarea.data('CodeMirror')
						|| $cmElement.get(0).CodeMirror;

			if (editor) {
				const code = editor.getDoc().getValue();
				$textarea.val(code);
			}
		}
	}

	static saveAll(settings) {
		jQuery("#" + settings.windowId + " .CodeMirror").each(function() {
			// this = DOM элемент .CodeMirror
			if (this.CodeMirror) {
				this.CodeMirror.save();
			}
		});
	}

	static loadAutosave($textarea) {
		if ($textarea.next().hasClass('CodeMirror')) {
			const editor = $textarea.data('CodeMirrorInstance')
						|| $textarea.data('CodeMirror')
						|| $textarea.next().get(0).CodeMirror;

			if (editor) {
				editor.getDoc().setValue($textarea.val() || '');
			}
		}
	}

	static remove($parent) {
		$parent.find('.CodeMirror').remove();
	}

	static addCompleter(sqlTables) {
		const oTables = {};

		// Обработка аргумента (объект или массив)
		if (Array.isArray(sqlTables)) {
			sqlTables.forEach(table => {
				oTables[table] = [];
			});
		} else if (typeof sqlTables === 'object') {
			Object.assign(oTables, sqlTables);
		}

		if (!CodeMirror.commands.autocomplete) {
			CodeMirror.commands.autocomplete = function(cm) {
				CodeMirror.showHint(cm, CodeMirror.hint.sql, {
					tables: oTables
				});
			};
		} else {
			CodeMirror.commands.autocomplete = function(cm) {
				CodeMirror.showHint(cm, CodeMirror.hint.sql, {
					tables: oTables
				});
			};
		}
	}
}