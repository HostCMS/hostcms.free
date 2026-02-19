/* global */
(function($) {
	"use strict";

	// Кешируем регулярные выражения и карты символов
	const HTML_ENTITY_MAP = {
		'&': '&amp;',
		'<': '&lt;',
		'>': '&gt;',
		'"': '&quot;',
		"'": '&#39;',
		'/': '&#x2F;',
		'`': '&#x60;',
		'=': '&#x3D;'
	};
	const REGEX_HTML_CHARS = /[&<>"'`=\/]/g; // eslint-disable-line
	const REGEX_ESCAPE_SELECTOR = /([ #;&,.+*~\':"@!^$[\]()<=>|\/\{\}\?])/g; // eslint-disable-line
	const REGEX_RGB = /^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/;

	// iOS платформы в Set для быстрого поиска
	const IOS_PLATFORMS = new Set([
		'iPad Simulator', 'iPhone Simulator', 'iPod Simulator',
		'iPad', 'iPhone', 'iPod'
	]);

	$.extend({
		escapeHtml: function(str) {
			if (typeof str === 'number') {
				return str;
			}
			if (!str) return '';
			return String(str).replace(REGEX_HTML_CHARS, (s) => HTML_ENTITY_MAP[s]);
		},

		isiOS: function() {
			return IOS_PLATFORMS.has(navigator.platform) ||
				// iPad on iOS 13 detection (Mac + Touch)
				(navigator.userAgent.includes("Mac") && "ontouchend" in document);
		},

		mathRound: function(value, decimals = 2) {
			// Number.EPSILON помогает избежать ошибок плавающей запятой (например, 1.005 -> 1.01)
			const multiplier = Math.pow(10, decimals);
			return Math.round((value + Number.EPSILON) * multiplier) / multiplier;
		},

		rgb2hex: function(rgb) {
			if (!rgb) return undefined;

			const match = rgb.match(REGEX_RGB);
			if (!match) return undefined;

			// Битовый сдвиг и toString(16) часто быстрее
			// (1 << 24) нужен для паддинга нулями слева, затем slice
			const r = parseInt(match[1], 10);
			const g = parseInt(match[2], 10);
			const b = parseInt(match[3], 10);

			return "#" + ((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1);
		},

		escapeSelector: function(selector) {
			if (!selector) return '';
			// Сначала экранируем слэши, потом спецсимволы
			return selector.replace(/\\/g, '\\\\').replace(REGEX_ESCAPE_SELECTOR, '\\$1');
		},

		copyToClipboard: function(selector) {
			const copyText = document.querySelector(selector);
			if (!copyText) return;

			copyText.select();

			try {
				// execCommand устарел, но для совместимости часто оставляют.
				// В новых браузерах лучше navigator.clipboard.writeText,
				// но он асинхронный и требует HTTPS. Оставляем fallback логику.
				document.execCommand('copy');

				const $wrapper = $('.error-clipboard-wrapper');
				$wrapper.append('<i class="fa-solid fa-check success clipboard-success margin-left-5"></i>');

				setTimeout(() => {
					$wrapper.find('.clipboard-success').remove();
					$(selector).blur(); // Убираем фокус jQuery методом
				}, 5000);

			} catch (err) {
				console.error('Copy failed: ', err);
			}
		},

		/**
		 * convert RFC 1342-like base64 strings to array buffer
		 * Optimized recursive traversal
		 */
		recursiveBase64StrToArrayBuffer: function(obj) {
			const prefix = '=?BINARY?B?';
			const suffix = '?=';

			if (typeof obj === 'object' && obj !== null) {
				// Object.keys быстрее и безопаснее for...in (не цепляет прототипы)
				Object.keys(obj).forEach(key => {
					const val = obj[key];

					if (typeof val === 'string') {
						if (val.startsWith(prefix) && val.endsWith(suffix)) {
							const str = val.substring(prefix.length, val.length - suffix.length);

							const binary_string = window.atob(str);
							const len = binary_string.length;
							const bytes = new Uint8Array(len);

							for (let i = 0; i < len; i++) {
								bytes[i] = binary_string.charCodeAt(i);
							}
							obj[key] = bytes.buffer;
						}
					} else if (typeof val === 'object') {
						$.recursiveBase64StrToArrayBuffer(val);
					}
				});
			}
		},

		/**
		 * Convert a ArrayBuffer to Base64
		 * OPTIMIZED: Avoids O(n^2) string concatenation loop
		 */
		arrayBufferToBase64: function(buffer) {
			let binary = '';
			const bytes = new Uint8Array(buffer);
			const len = bytes.byteLength;
			const CHUNK_SIZE = 0x8000; // 32k chunk size to avoid stack overflow

			// Используем String.fromCharCode.apply для чанков, это намного быстрее цикла
			for (let i = 0; i < len; i += CHUNK_SIZE) {
				// subarray создает view, не копируя память
				binary += String.fromCharCode.apply(null, bytes.subarray(i, Math.min(i + CHUNK_SIZE, len)));
			}

			return window.btoa(binary);
		},

		setCookie: function(name, value, expires, path, domain, secure) {
			let expiresString = "";
			if (expires) {
				const date = new Date();
				date.setTime(date.getTime() + (expires * 1000));
				expiresString = "; expires=" + date.toUTCString(); // toGMTString is deprecated
			}

			document.cookie = name + "=" + encodeURIComponent(value) +
				expiresString +
				(path ? "; path=" + path : "") +
				(domain ? "; domain=" + domain : "") +
				(secure ? "; secure" : "");
		}
	});
})(jQuery);

// --- Global Helpers ---

if (!Number.isInteger) {
	Number.isInteger = function(value) {
		return typeof value === 'number' &&
			isFinite(value) &&
			Math.floor(value) === value;
	};
}

// eslint-disable-next-line
function isEmpty(str) {
	return (!str || str.length === 0);
}

// eslint-disable-next-line
function uuidv4() {
	// Если есть crypto API, используем его (быстрее и уникальнее)
	if (typeof crypto !== 'undefined' && crypto.randomUUID) {
		return crypto.randomUUID();
	}

	return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
		const r = Math.random() * 16 | 0;
		const v = c === 'x' ? r : (r & 0x3 | 0x8);
		return v.toString(16);
	});
}

/**
 * Склонение после числительных
 */
// eslint-disable-next-line
function declension(number, nominative, genitive_singular, genitive_plural) {
	const last_digit = number % 10;
	const last_two_digits = number % 100;

	if (last_digit === 1 && last_two_digits !== 11) {
		return nominative;
	}

	if ((last_digit === 2 && last_two_digits !== 12) ||
		(last_digit === 3 && last_two_digits !== 13) ||
		(last_digit === 4 && last_two_digits !== 14)) {
		return genitive_singular;
	}

	return genitive_plural;
}

/**
 * jQuery Cookie plugin (Refactored)
 */
jQuery.cookie = function(key, value, options) {
	// Case 1: Set cookie
	if (arguments.length > 1 && String(value) !== "[object Object]") {
		options = jQuery.extend({}, options);

		if (value === null || value === undefined) {
			options.expires = -1;
		}

		if (typeof options.expires === 'number') {
			const days = options.expires;
			const t = options.expires = new Date();
			t.setDate(t.getDate() + days);
		}

		value = String(value);

		return (document.cookie = [
			encodeURIComponent(key), '=',
			options.raw ? value : cookie_encode(value),
			options.expires ? '; expires=' + options.expires.toUTCString() : '',
			options.path ? '; path=' + options.path : '',
			options.domain ? '; domain=' + options.domain : '',
			options.secure ? '; secure' : ''
		].join(''));
	}

	// Case 2: Get cookie
	options = value || {};
	const decode = options.raw ? (s) => s : decodeURIComponent;

	// Кешируем регулярку нельзя, т.к. key меняется, но можно экранировать key
	const parts = document.cookie.split('; ');
	const encodedKey = encodeURIComponent(key);

	for (let i = 0; i < parts.length; i++) {
		const part = parts[i];
		const separatorIndex = part.indexOf('=');
		if (separatorIndex !== -1 && part.substring(0, separatorIndex) === encodedKey) {
			return decode(part.substring(separatorIndex + 1));
		}
	}

	return null;
};

function cookie_encode(string) {
	// Encode value but allow specific chars for human readability
	return encodeURIComponent(string)
		.replace(/%(7B|7D|3A|22|23|5B|5D)/g, decodeURIComponent);
}