const SyntaxHighlighter = (function() {
	let completionProviderRegistered = false;
	let lastSqlTables = null;
	let activeEditorCount = 0;

	const STATIC_KEYWORDS = ["ABORT","ABSOLUTE","ACTION","ADA","ADD","AFTER","ALL","ALLOCATE","ALTER","ALWAYS","ANALYZE","AND","ANY","ARE","AS","ASC","ASSERTION","AT","ATTACH","AUTHORIZATION","AUTOINCREMENT","AVG","BACKUP","BEFORE","BEGIN","BETWEEN","BIT","BIT_LENGTH","BOTH","BREAK","BROWSE","BULK","BY","CASCADE","CASCADED","CASE","CAST","CATALOG","CHAR","CHARACTER","CHARACTER_LENGTH","CHAR_LENGTH","CHECK","CHECKPOINT","CLOSE","CLUSTERED","COALESCE","COLLATE","COLLATION","COLUMN","COMMIT","COMPUTE","CONFLICT","CONNECT","CONNECTION","CONSTRAINT","CONSTRAINTS","CONTAINS","CONTAINSTABLE","CONTINUE","CONVERT","CORRESPONDING","COUNT","CREATE","CROSS","CURRENT","CURRENT_DATE","CURRENT_TIME","CURRENT_TIMESTAMP","CURRENT_USER","CURSOR","DATABASE","DATE","DAY","DBCC","DEALLOCATE","DEC","DECIMAL","DECLARE","DEFAULT","DEFERRABLE","DEFERRED","DELETE","DENY","DESC","DESCRIBE","DESCRIPTOR","DETACH","DIAGNOSTICS","DISCONNECT","DISK","DISTINCT","DISTRIBUTED","DO","DOMAIN","DOUBLE","DROP","DUMP","EACH","ELSE","END","END-EXEC","ERRLVL","ESCAPE","EXCEPT","EXCEPTION","EXCLUDE","EXCLUSIVE","EXEC","EXECUTE","EXISTS","EXIT","EXPLAIN","EXTERNAL","EXTRACT","FAIL","FALSE","FETCH","FILE","FILLFACTOR","FILTER","FIRST","FLOAT","FOLLOWING","FOR","FOREIGN","FORTRAN","FOUND","FREETEXT","FREETEXTTABLE","FROM","FULL","FUNCTION","GENERATED","GET","GLOB","GLOBAL","GO","GOTO","GRANT","GROUP","GROUPS","HAVING","HOLDLOCK","HOUR","IDENTITY","IDENTITYCOL","IDENTITY_INSERT","IF","IGNORE","IMMEDIATE","IN","INCLUDE","INDEX","INDEXED","INDICATOR","INITIALLY","INNER","INPUT","INSENSITIVE","INSERT","INSTEAD","INT","INTEGER","INTERSECT","INTERVAL","INTO","IS","ISNULL","ISOLATION","JOIN","KEY","KILL","LANGUAGE","LAST","LEADING","LEFT","LEVEL","LIKE","LIMIT","LINENO","LOAD","LOCAL","LOWER","MATCH","MATERIALIZED","MAX","MERGE","MIN","MINUTE","MODULE","MONTH","NAMES","NATIONAL","NATURAL","NCHAR","NEXT","NO","NOCHECK","NONCLUSTERED","NONE","NOT","NOTHING","NOTNULL","NULL","NULLIF","NULLS","NUMERIC","OCTET_LENGTH","OF","OFF","OFFSET","OFFSETS","ON","ONLY","OPEN","OPENDATASOURCE","OPENQUERY","OPENROWSET","OPENXML","OPTION","OR","ORDER","OTHERS","OUTER","OUTPUT","OVER","OVERLAPS","PAD","PARTIAL","PARTITION","PASCAL","PERCENT","PIVOT","PLAN","POSITION","PRAGMA","PRECEDING","PRECISION","PREPARE","PRESERVE","PRIMARY","PRINT","PRIOR","PRIVILEGES","PROC","PROCEDURE","PUBLIC","QUERY","RAISE","RAISERROR","RANGE","READ","READTEXT","REAL","RECONFIGURE","RECURSIVE","REFERENCES","REGEXP","REINDEX","RELATIVE","RELEASE","RENAME","REPLACE","REPLICATION","RESTORE","RESTRICT","RETURN","RETURNING","REVERT","REVOKE","RIGHT","ROLLBACK","ROW","ROWCOUNT","ROWGUIDCOL","ROWS","RULE","SAVE","SAVEPOINT","SCHEMA","SCROLL","SECOND","SECTION","SECURITYAUDIT","SELECT","SEMANTICKEYPHRASETABLE","SEMANTICSIMILARITYDETAILSTABLE","SEMANTICSIMILARITYTABLE","SESSION","SESSION_USER","SET","SETUSER","SHUTDOWN","SIZE","SMALLINT","SOME","SPACE","SQL","SQLCA","SQLCODE","SQLERROR","SQLSTATE","SQLWARNING","STATISTICS","SUBSTRING","SUM","SYSTEM_USER","TABLE","TABLESAMPLE","TEMP","TEMPORARY","TEXTSIZE","THEN","TIES","TIME","TIMESTAMP","TIMEZONE_HOUR","TIMEZONE_MINUTE","TO","TOP","TRAILING","TRAN","TRANSACTION","TRANSLATE","TRANSLATION","TRIGGER","TRIM","TRUE","TRUNCATE","TRY_CONVERT","TSEQUAL","UNBOUNDED","UNION","UNIQUE","UNKNOWN","UNPIVOT","UPDATE","UPDATETEXT","UPPER","USAGE","USE","USER","USING","VACUUM","VALUE","VALUES","VARCHAR","VARYING","VIEW","VIRTUAL","WAITFOR","WHEN","WHENEVER","WHERE","WHILE","WINDOW","WITH","WITHIN GROUP","WITHOUT","WORK","WRITE","WRITETEXT","YEAR","ZONE"];

	// Убираем дубликаты
	const UNIQUE_KEYWORDS = [...new Set(STATIC_KEYWORDS)];

	let cachedKeywordSuggestions = null;

	class SyntaxHighlighter {
		static save($textarea) {
			const editor = this._getEditorInstance($textarea);
			if (editor) {
				$textarea.val(editor.getValue());
			}
		}

		static saveAll(settings) {
			if (typeof monaco === 'undefined') {
				console.warn('Monaco not available for saveAll');
				return;
			}

			jQuery("#" + settings.windowId + " .monaco-editor").each(function() {
				const editor = monaco.editor.getEditors().find(ed => ed.getDomNode() === this);
				if (editor) {
					const $container = $(this).parent();
					const $textarea = $container.prev('textarea');
					if ($textarea.length) {
						$textarea.val(editor.getValue());
					}
				}
			});
		}

		static loadAutosave($textarea) {
			const editor = this._getEditorInstance($textarea);
			if (editor) {
				const content = $textarea.val();
				if (content) {
					editor.setValue(content);
				}
			}
		}

		static remove($parent) {
			const $monacoEditors = $parent.find('.monaco-editor');
			const removedCount = $monacoEditors.length;

			if (removedCount > 0) {
				// Уменьшаем счётчик активных редакторов
				activeEditorCount = Math.max(0, activeEditorCount - removedCount);

				// Если редакторов больше нет, сбрасываем провайдер
				if (activeEditorCount === 0) {
					this._resetCompleter();
				}
			}

			$monacoEditors.remove();
		}

		static addCompleter(sqlTables) {
			lastSqlTables = sqlTables;
			activeEditorCount++; // Увеличиваем счётчик при добавлении редактора

			this._waitForMonaco(() => {
				// Если провайдер уже зарегистрирован, но таблицы изменились
				if (completionProviderRegistered) {
					// Проверяем, изменились ли таблицы
					if (JSON.stringify(lastSqlTables) !== JSON.stringify(sqlTables)) {
						// Перерегистрируем провайдер с новыми таблицами
						this._registerCompleter(sqlTables);
					}
					return;
				}

				this._registerCompleter(sqlTables);
				completionProviderRegistered = true;
			});
		}

		static _registerCompleter(sqlTables) {
			monaco.languages.registerCompletionItemProvider('sql', {
				provideCompletionItems: (model, position) => {
					const word = model.getWordUntilPosition(position);
					const range = {
						startLineNumber: position.lineNumber,
						endLineNumber: position.lineNumber,
						startColumn: word.startColumn,
						endColumn: word.endColumn
					};

					// Ленивая инициализация кэша ключевых слов
					if (!cachedKeywordSuggestions) {
						cachedKeywordSuggestions = UNIQUE_KEYWORDS.map(word => ({
							label: word,
							kind: monaco.languages.CompletionItemKind.Keyword,
							insertText: word,
							detail: 'Keyword'
						}));
					}

					// Клонируем и добавляем range
					const suggestions = cachedKeywordSuggestions.map(s => ({
						...s,
						range: range
					}));

					// Добавляем таблицы
					this._addTableSuggestions(suggestions, sqlTables, range);

					return { suggestions };
				}
			});
		}

		static _resetCompleter() {
			completionProviderRegistered = false;
			lastSqlTables = null;
			cachedKeywordSuggestions = null; // Очищаем кэш при полном сбросе
			console.log('SyntaxHighlighter: Completer reset');
		}

		static _waitForMonaco(callback, attempts = 0) {
			const maxAttempts = 50; // 5 секунд при интервале 100мс

			if (typeof monaco !== 'undefined' && monaco.languages) {
				callback();
			} else if (attempts < maxAttempts) {
				setTimeout(() => this._waitForMonaco(callback, attempts + 1), 100);
			} else {
				console.error('Monaco Editor failed to load within timeout');
			}
		}

		static _getEditorInstance($textarea) {
			const $next = $textarea.next();
			if (!$next.length) return null;

			const monacoElement = $next.find('.monaco-editor').get(0);
			if (!monacoElement) return null;

			if (typeof monaco === 'undefined') {
				console.warn('Monaco not loaded yet');
				return null;
			}

			return monaco.editor.getEditors().find(editor => editor.getDomNode() === monacoElement);
		}

		static _addTableSuggestions(suggestions, sqlTables, range) {
			if (!sqlTables) return;

			let tableNames = [];
			if (Array.isArray(sqlTables)) {
				tableNames = sqlTables;
			} else if (typeof sqlTables === 'object') {
				tableNames = Object.keys(sqlTables);
			}

			tableNames.forEach(name => {
				// Проверяем, нет ли уже такого suggestion
				const exists = suggestions.some(s => s.label === name);
				if (!exists) {
					suggestions.push({
						label: name,
						kind: monaco.languages.CompletionItemKind.Class,
						insertText: `\`${name}\``,
						range: range,
						detail: 'Table',
						sortText: '0' + name
					});
				}
			});
		}

		static forceReset() {
			activeEditorCount = 0;
			this._resetCompleter();
		}

		static getStatus() {
			return {
				providerRegistered: completionProviderRegistered,
				activeEditors: activeEditorCount,
				cachedKeywords: cachedKeywordSuggestions ? cachedKeywordSuggestions.length : 0,
				uniqueKeywords: UNIQUE_KEYWORDS.length
			};
		}
	}

	return SyntaxHighlighter;
})();

window.syntaxhighlighter = SyntaxHighlighter;