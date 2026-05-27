class SyntaxHighlighter {
	static _completers = new Map();
	static _completerInitialized = false;

	static save($textarea) {
		const editor = this._getEditorInstance($textarea);
		if (editor) {
			const code = editor.getSession().getValue();
			$textarea.val(code);
		}
	}

	static saveAll(settings) {
		if (typeof ace === 'undefined') {
			console.warn('Ace Editor not loaded');
			return;
		}

		jQuery("#" + settings.windowId + " .ace_editor").each(function() {
			const editor = ace.edit(this);
			if (editor) {
				const code = editor.getSession().getValue();
				jQuery(this).prev('textarea').val(code);
			}
		});
	}

	static loadAutosave($textarea) {
		const editor = this._getEditorInstance($textarea);
		if (editor) {
			const content = $textarea.val();
			if (content) {
				editor.getSession().setValue(content);
			}
		}
	}

	static remove($parent) {
		const $editors = $parent.find('.ace_editor');

		// Удаляем связанные completer'ы
		$editors.each((index, element) => {
			const editorId = element.id || `ace-editor-${index}`;
			this._removeCompleter(editorId);
		});

		$editors.remove();
	}

	static addCompleter(sqlTables, editorId = 'default') {
		// Проверяем доступность Ace и language_tools
		if (typeof ace === 'undefined') {
			console.error('Ace Editor not loaded');
			return;
		}

		try {
			const langTools = ace.require('ace/ext/language_tools');

			this._removeCompleter(editorId);

			// Нормализуем sqlTables
			const tableList = this._normalizeTables(sqlTables);

			// Создаем новый completer
			const completer = {
				id: editorId,
				getCompletions: function(editor, session, pos, prefix, callback) {
					callback(null, tableList.map(function(table) {
						return {
							value: table,
							meta: 'Table',
							score: 1000
						};
					}));
				}
			};

			langTools.addCompleter(completer);

			// Сохраняем ссылку
			this._completers.set(editorId, completer);
			this._completerInitialized = true;

		} catch (e) {
			console.error('Failed to add completer. Make sure ace/ext/language_tools is loaded:', e);
		}
	}

	static _getEditorInstance($textarea) {
		if (typeof ace === 'undefined') {
			console.warn('Ace Editor not loaded');
			return null;
		}

		const $editor = $textarea.siblings('.ace_editor').first();
		if (!$editor.length) return null;

		try {
			return ace.edit($editor.get(0));
		} catch (e) {
			console.error('Failed to get Ace editor instance:', e);
			return null;
		}
	}

	static _removeCompleter(editorId) {
		if (!this._completerInitialized) return;

		try {
			const langTools = ace.require('ace/ext/language_tools');
			const completer = this._completers.get(editorId);

			if (completer) {
				// Ace API не предоставляет прямого метода удаления,
				// поэтому используем внутренний массив
				if (langTools.completers) {
					const index = langTools.completers.indexOf(completer);
					if (index > -1) {
						langTools.completers.splice(index, 1);
					}
				}
				this._completers.delete(editorId);
			}
		} catch (e) {
			console.warn('Failed to remove completer:', e);
		}
	}

	static _normalizeTables(sqlTables) {
		if (!sqlTables) return [];

		if (Array.isArray(sqlTables)) {
			return sqlTables.filter(t => t && typeof t === 'string');
		} else if (typeof sqlTables === 'object') {
			return Object.keys(sqlTables).filter(k => k && typeof k === 'string');
		}

		return [];
	}

	static resetCompleters() {
		if (!this._completerInitialized) return;

		try {
			const langTools = ace.require('ace/ext/language_tools');

			// Удаляем все наши completer'ы
			for (const [id, completer] of this._completers) {
				if (langTools.completers) {
					const index = langTools.completers.indexOf(completer);
					if (index > -1) {
						langTools.completers.splice(index, 1);
					}
				}
			}

			this._completers.clear();
		} catch (e) {
			console.warn('Failed to reset completers:', e);
		}
	}

	static getStatus() {
		return {
			aceLoaded: typeof ace !== 'undefined',
			completersCount: this._completers.size,
			completerIds: Array.from(this._completers.keys())
		};
	}
}

window.syntaxhighlighter = SyntaxHighlighter;