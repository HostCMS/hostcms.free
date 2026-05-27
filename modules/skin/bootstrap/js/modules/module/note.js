/*global hostcmsBackend tinymce i18n */

/**
 * API и СЕТЕВЫЕ ЗАПРОСЫ
 */
const CrmNoteApi = {
	async request(paramsObject) {
		try {
			const params = new URLSearchParams(paramsObject);
			const response = await fetch(hostcmsBackend + '/crm/note/index.php', {
				method: 'POST',
				body: params
			});

			if (!response.ok) throw new Error(`Ошибка HTTP: ${response.status}`);

			const result = await response.json();
			if (!result.success) throw new Error(result.error || 'unknown error');

			return result;
		} catch (error) {
			console.error('[Сетевая ошибка]:', error);
			throw error;
		}
	},
	sendReaction: (crm_note_id, user_id, emoji, action) =>
		CrmNoteApi.request({ sendReaction: 1, crm_note_id, user_id, emoji, action }),
	toggleSubscription: (crm_note_id, user_id, action) =>
		CrmNoteApi.request({ toggleSubscription: 1, crm_note_id, user_id, action }),
	getReactionUsers: (crm_note_id, emoji) =>
		CrmNoteApi.request({ getReactionUsers: 1, crm_note_id, emoji }),
	sendAiRequest: (messageId, ai_prompt_id, model) =>
		CrmNoteApi.request({ sendAiRequest: 1, messageId, ai_prompt_id, model })
};

/**
 * УТИЛИТЫ ДЛЯ МЕДИА И БУФЕРА ОБМЕНА
 */
const MediaUtils = {
	async getMedia(constraints, errorMessage) {
		if (navigator.mediaDevices === undefined) navigator.mediaDevices = {};

		if (navigator.mediaDevices.getUserMedia === undefined) {
			navigator.mediaDevices.getUserMedia = function (c) {
				const getUserMedia = navigator.webkitGetUserMedia || navigator.mozGetUserMedia;
				if (!getUserMedia) return Promise.reject(new Error("getUserMedia is not implemented"));
				return new Promise((resolve, reject) => getUserMedia.call(navigator, c, resolve, reject));
			};
		}
		try {
			const stream = await navigator.mediaDevices.getUserMedia(constraints);
			return stream !== null ? stream : false;
		} catch (err) {
			console.error(errorMessage);
			return false;
		}
	},
	getAllMedia: () => MediaUtils.getMedia({ video: true, audio: true }, 'Timeline: microphone or camera is disabled!'),
	getAudioMedia: () => MediaUtils.getMedia({ audio: true }, 'Timeline: microphone is disabled!')
};

const ClipboardUtils = {
	async copyHtmlToClipboard(htmlContent, textContent) {
		if (navigator.clipboard && window.ClipboardItem && window.isSecureContext) {
			try {
				const data = [new ClipboardItem({
					"text/html": new Blob([htmlContent], { type: "text/html" }),
					"text/plain": new Blob([textContent], { type: "text/plain" })
				})];
				await navigator.clipboard.write(data);
				return;
			} catch (err) {
				console.warn('Современный API не сработал, пробуем fallback', err);
			}
		}
		return ClipboardUtils.fallbackCopy(htmlContent);
	},
	fallbackCopy(htmlContent) {
		return new Promise((resolve, reject) => {
			const tempDiv = document.createElement('div');
			tempDiv.innerHTML = htmlContent;
			tempDiv.style.position = 'fixed';
			tempDiv.style.left = '-9999px';
			tempDiv.style.top = '0';
			tempDiv.contentEditable = true;
			document.body.appendChild(tempDiv);

			const range = document.createRange();
			range.selectNodeContents(tempDiv);
			const sel = window.getSelection();
			sel.removeAllRanges();
			sel.addRange(range);

			try {
				document.execCommand('copy') ? resolve() : reject(new Error('Команда copy не удалась'));
			} catch (err) {
				reject(err);
			} finally {
				sel.removeAllRanges();
				document.body.removeChild(tempDiv);
			}
		});
	}
};

/**
 * БАЗОВЫЙ КЛАСС КОМПОНЕНТА
 */
class UIComponent {
	constructor(wrapper) {
		this.wrapper = wrapper;
	}

	delegate(eventName, selector, handler) {
		this.wrapper.addEventListener(eventName, (e) => {
			const target = e.target.closest(selector);
			if (target) {
				handler.call(this, e, target);
			}
		});
	}

	// Поиск элемента: сначала внутри wrapper, потом глобально
	querySelector(selector) {
		return this.wrapper.querySelector(selector) || document.querySelector(selector);
	}
}

/**
 * КОНТРОЛЛЕР АУДИО
 */
class AudioController extends UIComponent {
	constructor(wrapper) {
		super(wrapper);
		this.MAX_SECONDS = 600;
		this.stream = null;
		this.recorder = null;
		this.timerInterval = null;
		this.secondsRecorded = 0;
		this.currentId = '';
		this.currentExtension = '';
		this.chunks = [];

		this.modal = this.querySelector('#audio-record-modal');
		this.indicator = this.querySelector('#audio-recording-indicator');
		this.timerEl = this.querySelector('#audio-record-timer');
		this.btnStart = this.querySelector('#btn-start-audio-record');
		this.btnStop = this.querySelector('#btn-stop-audio-record');
		this.btnSend = this.querySelector('#btn-send-audio');
		this.btnCancel = this.querySelector('#btn-cancel-audio');

		this.bindEvents();
	}

	bindEvents() {
		this.delegate('click', '#start-audio-record-btn', this.initMedia);
		this.delegate('click', '#btn-start-audio-record', this.startRecording);
		this.delegate('click', '#btn-stop-audio-record', this.stopRecording);
		this.delegate('click', '#btn-send-audio', this.sendAudio);
		this.delegate('click', '#close-audio-modal, #btn-cancel-audio', this.closeModal);

		document.addEventListener('keydown', (e) => {
			if ((e.key === 'Escape' || e.key === 'Esc') && this.modal && !this.modal.classList.contains('hidden')) {
				this.closeModal();
			}
		});
	}

	async initMedia(e) {
		e.preventDefault();
		if (location.protocol != 'https:') return console.log('Timeline: HTTPS connection required!');

		try {
			this.stream = await MediaUtils.getAudioMedia();
			if (this.stream) {
				if (this.modal) this.modal.classList.remove('hidden');
				this.resetUI();
			}
		} catch (err) {
			this.handleMediaError(err);
		}
	}

	startRecording(e) {
		e.preventDefault();
		this.currentId = 'audio_' + Date.now();
		let options = { mimeType: 'audio/webm; codecs=opus' };
		this.currentExtension = 'weba';

		if (!MediaRecorder.isTypeSupported(options.mimeType)) {
			options = { mimeType: 'audio/mp4' };
			this.currentExtension = 'mp4';
			if (!MediaRecorder.isTypeSupported(options.mimeType)) {
				options = {};
				this.currentExtension = 'weba';
			}
		}

		this.recorder = new MediaRecorder(this.stream, options);
		this.chunks = [];

		this.recorder.ondataavailable = (event) => {
			if (event.data && event.data.size > 0) this.chunks.push(event.data);
		};

		this.recorder.start(1000);

		if (this.btnStart) this.btnStart.classList.add('hidden');
		if (this.btnStop) this.btnStop.classList.remove('hidden');
		if (this.btnCancel) this.btnCancel.classList.remove('hidden');
		if (this.indicator) this.indicator.classList.remove('hidden');

		this.secondsRecorded = 0;
		this.timerInterval = setInterval(() => {
			this.secondsRecorded++;
			this.updateTimerUI();
			if (this.secondsRecorded >= this.MAX_SECONDS) this.stopRecording(e);
		}, 1000);
	}

	stopRecording(e) {
		if (e) e.preventDefault();
		if (this.recorder && this.recorder.state !== 'inactive') this.recorder.stop();
		clearInterval(this.timerInterval);
		if (this.indicator) this.indicator.classList.add('hidden');
		if (this.btnStop) this.btnStop.classList.add('hidden');
		if (this.btnSend) this.btnSend.classList.remove('hidden');
	}

	sendAudio(e) {
		e.preventDefault();
		if (this.chunks.length === 0) return console.log('Голосовое сообщение пустое или запись не удалась.');

		const blob = new Blob(this.chunks, { type: 'audio/' + this.currentExtension });
		const file = new File([blob], `${this.currentId}.${this.currentExtension}`, {
			type: 'audio/' + this.currentExtension,
			lastModified: new Date().getTime()
		});

		const dropzoneForm = this.querySelector('.dropzone-form-timeline');
		if (dropzoneForm && dropzoneForm.dropzone) {
			const dzContainer = this.querySelector('.crm-note-attachments-dropzone');
			if (dzContainer) dzContainer.classList.remove('hidden');
			dropzoneForm.dropzone.addFile(file);
			this.closeModal(e);
		} else {
			console.log('Не удалось найти область загрузки файлов (Dropzone).');
		}
	}

	closeModal(e) {
		if (e) e.preventDefault();
		if (this.stream) {
			this.stream.getTracks().forEach(track => track.stop());
			this.stream = null;
		}
		if (this.recorder && this.recorder.state !== 'inactive') this.recorder.stop();
		clearInterval(this.timerInterval);
		if (this.modal) this.modal.classList.add('hidden');
	}

	resetUI() {
		if (this.btnStart) this.btnStart.classList.remove('hidden');
		if (this.btnStop) this.btnStop.classList.add('hidden');
		if (this.btnSend) {
			this.btnSend.classList.add('hidden');
			this.btnSend.innerHTML = 'Прикрепить файл';
			this.btnSend.disabled = false;
		}
		if (this.btnCancel) this.btnCancel.classList.remove('hidden');
		if (this.indicator) this.indicator.classList.add('hidden');
		if (this.timerEl) this.timerEl.innerText = "00:00 / 10:00";
	}

	updateTimerUI() {
		const m = String(Math.floor(this.secondsRecorded / 60)).padStart(2, '0');
		const s = String(this.secondsRecorded % 60).padStart(2, '0');
		if (this.timerEl) this.timerEl.innerText = `${m}:${s} / 10:00`;
	}

	handleMediaError(err) {
		if (err.name === 'NotAllowedError' || err.name === 'PermissionDeniedError') {
			console.log('Доступ заблокирован. Разрешите доступ в настройках браузера.');
		} else if (err.name === 'NotFoundError' || err.name === 'DevicesNotFoundError') {
			console.log('Устройство не найдено. Убедитесь, что оно подключено.');
		} else if (err.name === 'NotReadableError' || err.name === 'TrackStartError') {
			console.log('Устройство уже используется другой программой.');
		} else {
			console.error('Ошибка медиаустройств: ' + err.message);
		}
	}
}

/**
 * КОНТРОЛЛЕР ВИДЕО
 */
class VideoController extends AudioController {
	constructor(wrapper) {
		super(wrapper);
		this.modal = this.querySelector('#video-record-modal');
		this.videoPreview = this.querySelector('#video-preview');
		this.indicator = this.querySelector('#recording-indicator');
		this.timerEl = this.querySelector('#record-timer');
		this.btnStart = this.querySelector('#btn-start-record');
		this.btnStop = this.querySelector('#btn-stop-record');
		this.btnSend = this.querySelector('#btn-send-video');
		this.btnCancel = this.querySelector('#btn-cancel-video');
	}

	bindEvents() {
		this.delegate('click', '#start-video-record-btn', this.initMedia);
		this.delegate('click', '#btn-start-record', this.startRecording);
		this.delegate('click', '#btn-stop-record', this.stopRecording);
		this.delegate('click', '#btn-send-video', this.sendVideo);
		this.delegate('click', '#close-video-modal, #btn-cancel-video', this.closeModal);

		document.addEventListener('keydown', (e) => {
			if ((e.key === 'Escape' || e.key === 'Esc') && this.modal && !this.modal.classList.contains('hidden')) {
				this.closeModal();
			}
		});
	}

	async initMedia(e) {
		e.preventDefault();
		if (location.protocol != 'https:') return console.log('Timeline: HTTPS connection required!');

		try {
			this.stream = await MediaUtils.getAllMedia();
			if (this.stream) {
				if (this.videoPreview) this.videoPreview.srcObject = this.stream;
				if (this.modal) this.modal.classList.remove('hidden');
				this.resetUI();
			}
		} catch (err) {
			this.handleMediaError(err);
		}
	}

	startRecording(e) {
		e.preventDefault();
		this.currentId = 'video_' + Date.now();
		let options = { mimeType: 'video/webm; codecs=vp8,opus' };
		this.currentExtension = 'webm';

		if (!MediaRecorder.isTypeSupported(options.mimeType)) {
			options = { mimeType: 'video/mp4' };
			this.currentExtension = 'mp4';
		}

		this.recorder = new MediaRecorder(this.stream, options);
		this.chunks = [];

		this.recorder.ondataavailable = (event) => {
			if (event.data && event.data.size > 0) this.chunks.push(event.data);
		};

		this.recorder.start(1000);

		if (this.btnStart) this.btnStart.classList.add('hidden');
		if (this.btnStop) this.btnStop.classList.remove('hidden');
		if (this.btnCancel) this.btnCancel.classList.remove('hidden');
		if (this.indicator) this.indicator.classList.remove('hidden');

		this.secondsRecorded = 0;
		this.timerInterval = setInterval(() => {
			this.secondsRecorded++;
			this.updateTimerUI();
			if (this.secondsRecorded >= this.MAX_SECONDS) this.stopRecording();
		}, 1000);
	}

	sendVideo(e) {
		e.preventDefault();
		if (this.chunks.length === 0) return console.log('Видео пустое или запись не удалась.');

		const blob = new Blob(this.chunks, { type: 'video/' + this.currentExtension });
		const file = new File([blob], `${this.currentId}.${this.currentExtension}`, {
			type: 'video/' + this.currentExtension,
			lastModified: new Date().getTime()
		});

		const dropzoneForm = this.querySelector('.dropzone-form-timeline');
		if (dropzoneForm && dropzoneForm.dropzone) {
			const dzContainer = this.querySelector('.crm-note-attachments-dropzone');
			if (dzContainer) dzContainer.classList.remove('hidden');
			dropzoneForm.dropzone.addFile(file);
			this.closeModal();
		} else {
			console.log('Не удалось найти область загрузки файлов (Dropzone).');
		}
	}

	resetUI() {
		super.resetUI();
		if (this.btnSend) this.btnSend.innerHTML = 'Отправить в чат';
	}
}


/**
 * КОНТРОЛЛЕР ЦИТИРОВАНИЯ И ОТВЕТОВ
 */
class QuoteController extends UIComponent {
	constructor(wrapper) {
		super(wrapper);
		this.quotePreview = this.querySelector('#quote-preview');
		this.quoteAuthorName = this.querySelector('#quote-author-name');
		this.quoteTextPreview = this.querySelector('#quote-text-preview');
		this.cancelQuoteBtn = this.querySelector('#cancel-quote');

		// Ищем поле как в CRM Notes (wysiwyg="1"), так и в Helpdesk (name="message")
		this.textareaField = this.querySelector('textarea[wysiwyg="1"]') || this.querySelector('textarea[name="message"]') || this.querySelector('#message');

		this.bindEvents();
	}

	bindEvents() {
		this.wrapper.addEventListener('mousedown', (e) => {
			if (e.target.closest('.action-btn.quote-text') || e.target.closest('.action-btn.reply')) {
				e.preventDefault();
			}
		});

		this.delegate('click', '.action-btn.quote-text', this.handleQuote);
		this.delegate('click', '.action-btn.reply', this.handleReply);

		if (this.cancelQuoteBtn) {
			this.cancelQuoteBtn.addEventListener('click', () => {
				this.quotePreview.classList.add('hidden');
			});
		}
	}

	getCleanText(messageBody) {
		const bodyClone = messageBody.cloneNode(true);
		bodyClone.querySelectorAll('.quote, .message-topic, img, .crm-note-attachment-wrapper').forEach(el => el.remove());
		return bodyClone.innerText.trim();
	}

	insertToEditor(htmlContent, fallbackTextContent) {
		const editor = typeof tinymce !== 'undefined' && this.textareaField ? tinymce.get(this.textareaField.id) : null;

		if (editor) {
			editor.insertContent(htmlContent);
			editor.focus();
		} else if (this.textareaField) {
			this.textareaField.value += fallbackTextContent;
			this.textareaField.focus();
		}
	}

	setFormParentIds(form, parentId, timelineId) {
		if(!form) return;
		const parent_id_input = form.querySelector('input[name="parent_id"]');
		if (parent_id_input) parent_id_input.value = parentId;

		const parent_timeline_id = form.querySelector('input[name="parent_timeline_id"]');
		if (parent_timeline_id) parent_timeline_id.value = timelineId;
	}

	handleQuote(e, target) {
		const messageEl = target.closest('.message');
		let author = 'Аноним';
		let textToQuote = window.getSelection().toString().trim();
		let fallbackText = '';
		let quoteHTML = '';

		// Логика CRM Notes
		if (messageEl) {
			author = messageEl.querySelector('.author').textContent;
			const form = messageEl.closest('.timeline-wrapper').querySelector('form');
			if (!textToQuote) textToQuote = this.getCleanText(messageEl.querySelector('.message-body'));

			const messageId = messageEl.getAttribute('data-message-id');
			const parentId = messageEl.getAttribute('data-parent-id');
			this.setFormParentIds(form, parentId > 0 ? parentId : messageId, messageEl.getAttribute('data-timeline-id'));

		// Логика Helpdesk (из оригинального поля)
		} else {
			const originalMessageContainer = document.querySelector('textarea[name="original_message"]');
			if (originalMessageContainer) {
				// Извлекаем имя автора из тегов <acronym><b>...
				const authorEl = originalMessageContainer.closest('.form-group').querySelector('.caption acronym b');
				if (authorEl) author = authorEl.textContent;

				if (!textToQuote) textToQuote = originalMessageContainer.value;
			}
		}

		quoteHTML = `<blockquote class="quote"><span class="quote-author">${author}:</span><br/>${textToQuote.replace(/\n/g, '<br/>')}</blockquote><p>&nbsp;</p>`;
		fallbackText = `\n[Цитата ${author}]: ${textToQuote}\n`;

		this.insertToEditor(quoteHTML, fallbackText);
	}

	handleReply(e, target) {
		const messageEl = target.closest('.message');
		if(!messageEl) return; // Reply логика не применяется в форме Helpdesk

		const author = messageEl.querySelector('.author').textContent;
		const form = messageEl.closest('.timeline-wrapper').querySelector('form');
		let text = this.getCleanText(messageEl.querySelector('.message-body'));

		this.quoteAuthorName.textContent = author + ':';
		this.quoteTextPreview.textContent = text;
		this.quotePreview.classList.remove('hidden');

		const quoteHTML = `<span class="quote-author">${author}</span>,&nbsp;`;
		const fallbackText = `${author}, `;

		this.insertToEditor(quoteHTML, fallbackText);

		const messageId = messageEl.getAttribute('data-message-id');
		const parentId = messageEl.getAttribute('data-parent-id');
		this.setFormParentIds(form, parentId > 0 ? parentId : messageId, messageEl.getAttribute('data-timeline-id'));
	}
}

/**
 * КОНТРОЛЛЕР РЕАКЦИЙ
 */
class ReactionController extends UIComponent {
	constructor(wrapper) {
		super(wrapper);
		this.emojiPicker = this.querySelector('.js-emoji-picker') || this.querySelector('#emoji-picker');
		this.textareaField = this.querySelector('textarea[wysiwyg="1"]') || this.querySelector('textarea[name="message"]') || this.querySelector('#message');
		this.reactionsCache = {};
		this.tooltipTimeout = null;
		this.currentEmojiTarget = null;

		this.bindEvents();
	}

	bindEvents() {
		this.delegate('click', '#textarea-emoji-btn', this.openTextareaEmoji);

		// Разделяем логику в зависимости от того, где была вызвана кнопка эмодзи
		this.delegate('click', '.add-emoji', (e, target) => {
			const messageEl = target.closest('.message');
			if(messageEl) {
				this.openMessageEmoji(e, target);
			} else {
				this.openTextareaEmoji(e, target);
			}
		});

		this.delegate('click', '.reaction', this.toggleReaction);

		// this.delegate('click', '.emoji-option', this.selectEmoji);

		if (this.emojiPicker) {
			this.emojiPicker.addEventListener('click', (e) => {
				const target = e.target.closest('.emoji-option');
				if (target) {
					this.selectEmoji(e, target);
				}
			});
		}

		this.delegate('mouseover', '.reaction', this.showTooltip);
		this.delegate('mouseout', '.reaction', this.hideTooltip);

		// Закрытие пикера при клике вне
		document.addEventListener('click', (e) => {
			if (this.emojiPicker && !e.target.closest('.js-emoji-picker') && !e.target.closest('#emoji-picker') && !e.target.closest('.add-emoji') && !e.target.closest('#textarea-emoji-btn')) {
				this.emojiPicker.classList.add('hidden');
			}
		});
	}

	openTextareaEmoji(e, target) {
		e.stopPropagation();

		// Проверяем: если окно уже открыто именно для текстового редактора — закрываем его
		if (!this.emojiPicker.classList.contains('hidden') && this.currentEmojiTarget === 'tinymce') {
			this.emojiPicker.classList.add('hidden');
			return;
		}

		this.currentEmojiTarget = 'tinymce';
		this.positionPicker(target, -this.emojiPicker.offsetHeight - 5);
	}

	openMessageEmoji(e, target) {
		const newEmojiTarget = target.closest('.message-footer').querySelector('.reactions');

		// То же самое для сообщений: закрываем, если кликнули по той же кнопке повторно
		if (!this.emojiPicker.classList.contains('hidden') && this.currentEmojiTarget === newEmojiTarget) {
			this.emojiPicker.classList.add('hidden');
			return;
		}

		this.currentEmojiTarget = newEmojiTarget;
		this.positionPicker(target, target.offsetHeight + 5);
	}

	positionPicker(target, yOffset) {
		// Перемещаем в body для независимости от родительских контейнеров
		if (this.emojiPicker.parentNode !== document.body) {
			document.body.appendChild(this.emojiPicker);
		}

		this.emojiPicker.classList.remove('hidden');

		const rect = target.getBoundingClientRect();
		const pickerWidth = this.emojiPicker.offsetWidth;
		const pickerHeight = this.emojiPicker.offsetHeight;

		// Базовые расчеты
		let top = rect.top + window.scrollY + (yOffset > 0 ? yOffset : -pickerHeight - 5);
		let left = rect.left + window.scrollX;

		// Если правый край пикера уходит за правый край экрана
		if (rect.left + pickerWidth > window.innerWidth) {
			// Сдвигаем влево, оставляя отступ в 10px от края экрана
			left = window.innerWidth - pickerWidth - 10 + window.scrollX;
		}

		// Если левый край пикера уходит за левый край экрана
		if (rect.left < 0 || left < window.scrollX) {
			left = window.scrollX + 10;
		}

		// Если пикер должен открыться снизу, но там нет места
		if (yOffset > 0 && (rect.top + yOffset + pickerHeight > window.innerHeight)) {
			// Принудительно открываем его сверху над кнопкой
			top = rect.top + window.scrollY - pickerHeight - 5;
		}

		this.emojiPicker.style.position = 'absolute';
		this.emojiPicker.style.top = `${top}px`;
		this.emojiPicker.style.left = `${left}px`;
	}

	toggleReaction(e, target) {
		const messageEl = target.closest('.message');
		const messageId = messageEl.getAttribute('data-message-id');
		const userId = messageEl.getAttribute('data-user-id');
		const parts = target.textContent.trim().split(' ');
		const selectedEmoji = parts[0];
		let count = parseInt(parts[1]);
		const isMyReaction = target.classList.contains('user-reacted');

		target.style.pointerEvents = 'none';

		if (isMyReaction) {
			CrmNoteApi.sendReaction(messageId, userId, selectedEmoji, 'remove').then(() => {
				delete this.reactionsCache[`${messageId}_${selectedEmoji}`];
				count--;
				if (count <= 0) target.remove();
				else { target.textContent = `${selectedEmoji} ${count}`; target.classList.remove('user-reacted'); }
				target.style.pointerEvents = 'auto';
			}).catch(() => target.style.pointerEvents = 'auto');
		} else {
			const oldReactionBtn = messageEl.querySelector('.user-reacted');
			const oldEmoji = oldReactionBtn ? oldReactionBtn.textContent.trim().split(' ')[0] : null;
			const action = oldEmoji ? 'replace' : 'add';

			CrmNoteApi.sendReaction(messageId, userId, selectedEmoji, action).then(() => {
				delete this.reactionsCache[`${messageId}_${selectedEmoji}`];
				if (oldEmoji) delete this.reactionsCache[`${messageId}_${oldEmoji}`];

				if (oldReactionBtn) {
					let oldCount = parseInt(oldReactionBtn.textContent.trim().split(' ')[1]) - 1;
					if (oldCount <= 0) oldReactionBtn.remove();
					else { oldReactionBtn.textContent = `${oldEmoji} ${oldCount}`; oldReactionBtn.classList.remove('user-reacted'); }
				}

				count++;
				target.textContent = `${selectedEmoji} ${count}`;
				target.classList.add('user-reacted');
				target.style.pointerEvents = 'auto';
			}).catch(() => target.style.pointerEvents = 'auto');
		}
	}

	selectEmoji(e, target) {
		if (!this.currentEmojiTarget) return;
		const selectedEmoji = target.textContent;

		if (this.currentEmojiTarget === 'tinymce') {
			// Поддержка вставки в обычную Textarea, если TinyMCE не инициализирован
			const editor = typeof tinymce !== 'undefined' && this.textareaField ? tinymce.get(this.textareaField.id) : null;
			if (editor) {
				editor.insertContent(selectedEmoji);
				editor.focus();
			} else if (this.textareaField) {
				this.textareaField.value += selectedEmoji;
				this.textareaField.focus();
			}
		} else {
			const messageEl = this.currentEmojiTarget.closest('.message');
			const messageId = messageEl.getAttribute('data-message-id');
			const userId = messageEl.getAttribute('data-user-id');
			const oldReactionBtn = this.currentEmojiTarget.querySelector('.user-reacted');
			const oldEmoji = oldReactionBtn ? oldReactionBtn.textContent.trim().split(' ')[0] : null;

			if (oldEmoji === selectedEmoji) {
				if (this.emojiPicker) this.emojiPicker.classList.add('hidden');
				return;
			}

			const action = oldEmoji ? 'replace' : 'add';
			CrmNoteApi.sendReaction(messageId, userId, selectedEmoji, action).then(() => {
				delete this.reactionsCache[`${messageId}_${selectedEmoji}`];
				if (oldEmoji) delete this.reactionsCache[`${messageId}_${oldEmoji}`];

				if (oldReactionBtn) {
					let oldCount = parseInt(oldReactionBtn.textContent.trim().split(' ')[1]) - 1;
					if (oldCount <= 0) oldReactionBtn.remove();
					else { oldReactionBtn.textContent = `${oldEmoji} ${oldCount}`; oldReactionBtn.classList.remove('user-reacted'); }
				}

				let existingReaction = Array.from(this.currentEmojiTarget.querySelectorAll('.reaction'))
					.find(r => r.textContent.includes(selectedEmoji));

				if (existingReaction) {
					let textParts = existingReaction.textContent.trim().split(' ');
					existingReaction.textContent = `${selectedEmoji} ${parseInt(textParts[1]) + 1}`;
					existingReaction.classList.add('user-reacted');
				} else {
					const newReaction = document.createElement('span');
					newReaction.className = 'reaction user-reacted';
					newReaction.textContent = `${selectedEmoji} 1`;
					this.currentEmojiTarget.appendChild(newReaction);
				}
			});
		}

		if (this.emojiPicker) this.emojiPicker.classList.add('hidden');
	}

	showTooltip(e, target) {
		const messageEl = target.closest('.message');
		const messageId = messageEl.getAttribute('data-message-id');
		const tooltipEl = messageEl.querySelector('.reaction-tooltip');
		if (!tooltipEl) return;

		const selectedEmoji = target.textContent.trim().split(' ')[0];
		const cacheKey = `${messageId}_${selectedEmoji}`;

		const renderTooltip = (html) => {
			tooltipEl.innerHTML = html;
			tooltipEl.style.display = 'block';
			tooltipEl.classList.remove('visible');

			const btnRect = target.getBoundingClientRect();
			const tooltipRect = tooltipEl.getBoundingClientRect();
			const messageRect = messageEl.getBoundingClientRect();

			tooltipEl.style.left = `${(btnRect.left - messageRect.left) + (btnRect.width / 2) - (tooltipRect.width / 2)}px`;
			tooltipEl.style.top = `${(btnRect.top - messageRect.top) - tooltipRect.height - 8}px`;
			tooltipEl.classList.add('visible');
		};

		this.tooltipTimeout = setTimeout(() => {
			if (this.reactionsCache[cacheKey]) renderTooltip(this.reactionsCache[cacheKey]);
			else {
				CrmNoteApi.getReactionUsers(messageId, selectedEmoji).then(res => {
					this.reactionsCache[cacheKey] = res.html;
					renderTooltip(res.html);
				});
			}
		}, 300);
	}

	hideTooltip(e, target) {
		clearTimeout(this.tooltipTimeout);
		const tooltipEl = target.closest('.message').querySelector('.reaction-tooltip');
		if (tooltipEl) {
			tooltipEl.classList.remove('visible');
			setTimeout(() => { if (!tooltipEl.classList.contains('visible')) tooltipEl.removeAttribute('style'); }, 200);
		}
	}
}

/**
 * КОНТРОЛЛЕР AI
 */
class AiController extends UIComponent {
	constructor(wrapper) {
		super(wrapper);
		this.aiMenu = this.querySelector('#ai-menu');
		this.aiModal = this.querySelector('#ai-modal');
		this.aiModalText = this.querySelector('#ai-modal-text');
		this.aiLoading = this.querySelector('#ai-loading');
		this.aiCopyBtn = this.querySelector('#ai-copy-btn');
		this.currentAiTarget = null;

		this.bindEvents();
	}

	bindEvents() {
		this.delegate('click', '.ai-action-btn', this.openAiMenu);
		this.delegate('click', '.ai-menu-item', this.selectAiPrompt);
		this.delegate('click', '.ai-modal-close', this.closeModal);
		this.delegate('click', '#ai-copy-btn', this.copyAiResult);

		document.addEventListener('click', (e) => {
			if (!e.target.closest('#ai-menu') && !e.target.closest('.ai-action-btn') && this.aiMenu) {
				this.aiMenu.classList.add('hidden');
				this.aiMenu.style.display = '';
			}
		});

		document.addEventListener('keydown', (e) => {
			if (e.key === 'Escape' || e.key === 'Esc') {
				// Закрываем модальное окно результатов AI
				if (this.aiModal && !this.aiModal.classList.contains('hidden')) {
					this.closeModal();
				}
				// Закрываем меню выбора промптов AI
				if (this.aiMenu && !this.aiMenu.classList.contains('hidden')) {
					this.aiMenu.classList.add('hidden');
					this.aiMenu.style.display = '';
				}
			}
		});
	}

	openAiMenu(e, target) {
		this.currentAiTarget = target;
		this.aiMenu.classList.remove('hidden');
		this.aiMenu.style.display = 'block';

		const btnRect = target.getBoundingClientRect();
		const menuRect = this.aiMenu.getBoundingClientRect();
		const wrapperRect = this.wrapper.getBoundingClientRect();

		const relativeLeft = btnRect.left - wrapperRect.left + this.wrapper.scrollLeft;
		let relativeTop = btnRect.bottom - wrapperRect.top + this.wrapper.scrollTop + 5;

		if (btnRect.bottom + menuRect.height > window.innerHeight) {
			relativeTop = btnRect.top - wrapperRect.top + this.wrapper.scrollTop - menuRect.height - 5;
		}

		this.aiMenu.style.position = 'absolute';
		this.aiMenu.style.top = `${relativeTop}px`;
		this.aiMenu.style.left = `${relativeLeft}px`;
	}

	selectAiPrompt(e, target) {
		const messageEl = this.currentAiTarget.closest('.message');
		let messageId = 0;

		// Определяем ID сущности для AI-запроса: либо из сообщения (CRM Notes), либо из скрытых полей (Helpdesk)
		if (messageEl) {
			messageId = messageEl.getAttribute('data-message-id');
		} else {
			const parentIdSelect = document.querySelector('select[name="parent_id"]');
			const ticketIdInput = document.querySelector('input[name="helpdesk_ticket_id"]');

			if (parentIdSelect && parentIdSelect.value > 0) {
				messageId = parentIdSelect.value;
			} else if (ticketIdInput) {
				messageId = ticketIdInput.value;
			}
		}

		const promptId = target.getAttribute('data-ai-prompt-id');
		const model = target.getAttribute('data-ai-prompt-model');

		this.aiMenu.classList.add('hidden');
		this.aiModal.classList.remove('hidden');
		this.aiModalText.classList.add('hidden');
		this.aiLoading.classList.remove('hidden');
		this.aiCopyBtn.style.display = 'none';

		CrmNoteApi.sendAiRequest(messageId, promptId, model).then((response) => {
			this.aiLoading.classList.add('hidden');
			this.aiModalText.innerHTML = response.html;
			this.aiModalText.classList.remove('hidden');
			this.aiCopyBtn.style.display = 'block';
		}).catch((e) => {
			// this.aiModal.classList.add('hidden');

			this.aiLoading.classList.add('hidden');
			this.aiModalText.innerHTML = '<div class="ai-error"> ⚠️ ' + $.escapeHtml(e.message) + '</div>';
			this.aiModalText.classList.remove('hidden');
		});
	}

	closeModal(e, target) { // eslint-disable-line
		this.aiModal.classList.add('hidden');
	}

	copyAiResult() {
		ClipboardUtils.copyHtmlToClipboard(this.aiModalText.innerHTML, this.aiModalText.innerText).then(() => {
			const originalText = this.aiCopyBtn.innerText;
			this.aiCopyBtn.innerText = typeof i18n !== 'undefined' ? i18n['copy_success'] : 'Скопировано';
			this.aiCopyBtn.style.backgroundColor = "#10b981";
			this.aiCopyBtn.style.color = "#ffffff";

			setTimeout(() => {
				this.aiCopyBtn.innerText = originalText;
				this.aiCopyBtn.style.backgroundColor = "";
				this.aiCopyBtn.style.color = "";
			}, 2000);
		});
	}
}

class MiscUIController extends UIComponent {
	constructor(wrapper) {
		super(wrapper);
		this.topicInput = this.querySelector('#new-message-topic');
		this.bindEvents();
	}

	bindEvents() {
		this.delegate('click', '#toggle-topic-btn', (e) => {
			e.preventDefault();
			if(this.topicInput) {
				this.topicInput.classList.toggle('hidden');
				if (!this.topicInput.classList.contains('hidden')) this.topicInput.focus();
			}
		});

		this.delegate('click', '.action-btn.subscribe', (e, target) => {
			const messageEl = target.closest('.message');
			if(!messageEl) return;

			const messageId = messageEl.getAttribute('data-message-id') || 'temp-id';
			const userId = messageEl.getAttribute('data-user-id');
			const isActive = target.classList.contains('active');

			CrmNoteApi.toggleSubscription(messageId, userId, isActive ? 'unsubscribe' : 'subscribe').then(() => {
				if (isActive) {
					target.classList.remove('active');
					target.innerHTML = '<i class="fa-solid fa-bell"></i>';
					target.title = target.getAttribute('data-subscribe');
				} else {
					target.classList.add('active');
					target.innerHTML = '<i class="fa-solid fa-bell-slash"></i>';
					target.title = target.getAttribute('data-unsubscribe');
				}
			});
		});
	}
}

function crmNotesCallback(wrapper) { // eslint-disable-line
	new AudioController(wrapper);
	new VideoController(wrapper);
	new QuoteController(wrapper);
	new ReactionController(wrapper);
	new AiController(wrapper);
	new MiscUIController(wrapper);
}

function crmNotesOnDOMReady(crmNotesCallback, wrapper) { // eslint-disable-line
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', () => crmNotesCallback(wrapper));
	} else {
		crmNotesCallback(wrapper);
	}
}