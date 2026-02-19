/* global hostcmsBackend Notify i18n */
(function($){
	$.extend({
		unavailableWebauthn: function()
		{
			return !window.isSecureContext && location.protocol !== 'https:'
				|| !window.fetch || !navigator.credentials || !navigator.credentials.create;
		},
	});
})(jQuery);

/**
 * creates a new FIDO2 registration
 * @returns {undefined}
 */
async function createRegistration(location) { // eslint-disable-line
	try {
		// get create args
		let rep = await window.fetch(hostcmsBackend + '/index.php?webauthnRegisterList', { method:'GET', cache:'no-cache' });
		const createArgs = await rep.json();

		// error handling
		if (createArgs.success === false) {
			throw new Error(createArgs.msg || 'unknown error occured');
		}

		// replace binary base64 data with ArrayBuffer. a other way to do this
		// is the reviver function of JSON.parse()
		$.recursiveBase64StrToArrayBuffer(createArgs);

		// create credentials
		const cred = await navigator.credentials.create(createArgs);

		// create object
		const authenticatorAttestationResponse = {
			transports: cred.response.getTransports  ? cred.response.getTransports() : null,
			clientDataJSON: cred.response.clientDataJSON  ? $.arrayBufferToBase64(cred.response.clientDataJSON) : null,
			attestationObject: cred.response.attestationObject ? $.arrayBufferToBase64(cred.response.attestationObject) : null
		};

		// check auth on server side
		rep = await window.fetch(hostcmsBackend + '/index.php?webauthnRegister', {
			method  : 'POST',
			body    : JSON.stringify(authenticatorAttestationResponse),
			cache   : 'no-cache'
		});
		const authenticatorAttestationServerResponse = await rep.json();

		// prompt server response
		if (authenticatorAttestationServerResponse.success) {
			// console.log('register success');
			Notify('<span>' + i18n['webauth_register_success'] + '</span>', '', 'top-left', '5000', 'success', 'fa-fingerprint', true);
		}
	} catch (err) {
		console.log(err.message || 'unknown error occured');
		Notify('<span>Error: ' + (err.message || 'unknown error occured') + '</span>', '', 'top-left', '5000', 'danger', 'fa-fingerprint', true);
	}

	$.setCookie('_h_webauthn_show_modal', false, 2592000 * 12);
}

/**
 * checks a FIDO2 registration
 * @returns {undefined}
 */
async function checkRegistration(location) { // eslint-disable-line
	// console.log(location);

	try {
		// get check args
		let rep = await window.fetch(hostcmsBackend + '/index.php?webauthnLoadList', { method:'GET',cache:'no-cache' });
		const getArgs = await rep.json();

		// error handling
		if (getArgs.success === false) {
			throw new Error(getArgs.msg);
		}

		// replace binary base64 data with ArrayBuffer. a other way to do this
		// is the reviver function of JSON.parse()
		$.recursiveBase64StrToArrayBuffer(getArgs);

		// check credentials with hardware
		const cred = await navigator.credentials.get(getArgs);

		// create object for transmission to server
		const authenticatorAttestationResponse = {
			id: cred.rawId ? $.arrayBufferToBase64(cred.rawId) : null,
			clientDataJSON: cred.response.clientDataJSON  ? $.arrayBufferToBase64(cred.response.clientDataJSON) : null,
			authenticatorData: cred.response.authenticatorData ? $.arrayBufferToBase64(cred.response.authenticatorData) : null,
			signature: cred.response.signature ? $.arrayBufferToBase64(cred.response.signature) : null,
			userHandle: cred.response.userHandle ? $.arrayBufferToBase64(cred.response.userHandle) : null
		};

		// send to server
		rep = await window.fetch(hostcmsBackend + '/index.php?webauthnCheck', {
			method: 'POST',
			body: JSON.stringify(authenticatorAttestationResponse),
			cache: 'no-cache'
		});
		const authenticatorAttestationServerResponse = await rep.json();

		// check server response
		if (authenticatorAttestationServerResponse.success) {
			// console.log(authenticatorAttestationServerResponse.msg || 'login success');

			$.loadingScreen('show');

			$(window).off('beforeunload');

			window.location.href = location.href != ''
				? location.href
				: hostcmsBackend + '/index.php';

			window.location.reload();

			return true;
		} else {
			// throw new Error(authenticatorAttestationServerResponse.msg);
			// $('#authorizationError').html(authenticatorAttestationServerResponse.msg);

			return authenticatorAttestationServerResponse.msg;
		}
	} catch (err) {
		console.log(err.message || 'unknown error occured');
	}

	return false;
}

document.addEventListener('auxclick', function(event) {
	if (event.button === 1 && event.target.closest('[data-confirm-message]')) // средняя кнопка
	{
		event.preventDefault();

		var target = event.target.tagName === 'A' ? event.target : event.target.parentNode;

		const message = target.getAttribute('data-confirm-message') || 'Вы уверены?';

		if (confirm(message))
		{
			if (target.tagName === 'A')
			{
				window.open(target.href, '_blank');
			}
		}
	}
});