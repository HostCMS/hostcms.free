/*global Howler Howl JsSIP */
async function getMedia() {
	if (navigator.mediaDevices === undefined) {
		navigator.mediaDevices = {};
	}

	if (navigator.mediaDevices.getUserMedia === undefined) {
		navigator.mediaDevices.getUserMedia = function (constraints) {
			// Сначала, если доступно, получим устаревшее getUserMedia
			var getUserMedia = navigator.webkitGetUserMedia || navigator.mozGetUserMedia;

			// Некоторые браузеры не реализуют его, тогда вернём отменённый промис
			// с ошибкой для поддержания последовательности интерфейса
			if (!getUserMedia) {
				return Promise.reject(
					new Error("getUserMedia is not implemented in this browser"),
				);
			}

			// Иначе, обернём промисом устаревший navigator.getUserMedia
			return new Promise(function (resolve, reject) {
				getUserMedia.call(navigator, constraints, resolve, reject);
			});
		};
	}

	let stream = null;

	try {
		stream = await navigator.mediaDevices.getUserMedia({ audio: true });
		if (stream !== null)
		{
			return true;
		}
	} catch (err) {
		console.error('Softophone: microphone is disabled! Check browser settings');
		return false;
	}
}

var session = null;
var remoteAudio = new window.Audio();
remoteAudio.autoplay = true;

export default async function initSoftophone(line, data) {
	if (location.protocol != 'https:') {
		console.error('Softophone: HTTPS connection required!');
		return false;
	}

	const bMicro = await getMedia();
	// const bMicro = true; // !!!! TMP

	data = $.extend({
		server: '',
		login: '',
		password: '',
		register_server: '',
		display_name: '' || data.login,
		register_expires: 180,
		session_timers: false,
		rington: 'ringback.aac',
		userAgent: 'HostCMS-JsSip-' + JsSIP.version
	}, data);

	console.log('initSoftophone', line, data);

	if (bMicro)
	{
		// JsSIP.debug.enable('JsSIP:*');
		JsSIP.debug.disable();
		var socket = new JsSIP.WebSocketInterface(data.server);
		socket.via_transport = "tcp";

		var configuration = {
			sockets  : [ socket ],
			password : data.password,
			uri: 'sip:' + data.login + '@' + data.register_server,
			display_name: data.display_name,
			session_timers: data.session_timers,
			register_expires: data.register_expires,
			user_agent: data.userAgent
		};

		var phone = new JsSIP.UA(configuration);

		// События регистрации клиента
		phone.on('connected', function() {});
		phone.on('disconnected', function() {});
		phone.on('registered', function() {
			console.log('registered');
			$('.telephony-name-wrapper i').removeClass('darkorange').addClass('palegreen');
			$('.phone-action-buttons .call').removeClass('hidden');
		});
		phone.on('unregistered', function() {
			$('.telephony-name-wrapper i').removeClass('palegreen').addClass('darkorange');
			$('.phone-action-buttons .call').addClass('hidden');
		});
		phone.on('registrationFailed', function(ev){
			console.log('Registering on SIP server failed with error: ' + ev.cause);
			configuration.uri = null;
			configuration.password = null;
			$('.telephony-name-wrapper i').removeClass('palegreen').addClass('darkorange');
			$('.phone-action-buttons .call').addClass('hidden');
		});

		// Обработка событии исх. звонка
		/*var eventHandlers = {
			'progress': function(e) {
				console.log('call is in progress', e);
			},
			'failed': function(e) {
				console.log('call failed with cause: ' + e.cause);
			},
			'ended': function(e) {
				console.log('call ended with cause: ' + e.cause);
			},
			'confirmed': function(e) {
				console.log('call confirmed', e);
			}
		};*/

		var options = {
			// eventHandlers    : eventHandlers,
			mediaConstraints : {
				audio: true,
				video: false
			},
			'pcConfig': {
				'rtcpMuxPolicy': 'require',
				/*iceServers: [
					// { 'urls': 'stun:stun.stunprotocol.org:3478' },
					{ urls: 'stun:stun.l.google.com:19302' }
				]*/
			}
		};

		phone.on("newRTCSession", function(response) {
			var newSession = response.session;

			// console.log('newRTCSession', session);
			// console.log('newRTCSessionNew', newSession);

			// hangup any existing call
			if(session){
				session = null;
			}

			session = newSession;
			var completeSession = function(){
				session = null;
				hideTimer();
			};

			// console.log('newRTCSessionAfter', session);
			// console.log('incoming data_session: ', session);

			// Исходящий
			if(session.direction === 'outgoing')
			{
				// console.log('stream outgoing  -------->');

				session.on('connecting', function() {
					console.log('CONNECT');
				});
				session.on('peerconnection', function() {
					console.log('peerconnection outgoing');
				});
				session.on('ended', completeSession);
				session.on('failed', completeSession);
				session.on('accepted',function() {
					console.log('accepted outgoing');
					showTimer();
				});
				session.on('confirmed',function(){
					console.log('CONFIRM STREAM outgoing');
				});
			}

			if (session.direction === "incoming")
			{
				// console.log('stream incoming  <--------');

				playSound(data.rington, true);

				$('.phone-action-buttons .call').find('i').removeClass('fa-phone').addClass('fa-phone-volume').addClass('fa-shake');
				$('.navbar li#softophone .dropdown-toggle').dropdown('toggle');

				$('.caller-name span').text(session.remote_identity.display_name);
				$('.caller-name').removeClass('hidden');

				$('.phone-action-buttons .hangup').removeClass('hidden');

				$('.phone-action-buttons .call').off('click').click(function(){
					// console.log('answer click');
					session.answer(options);
				});

				// incoming call here
				session.on('connecting', function() {
					console.log('CONNECT');
				});
				session.on("accepted", function(){
					console.log('accepted');
					stopSound();
				});
				session.on('peerconnection', function() {
					console.log('peerconnection incoming');

					$('.phone-action-buttons .call').addClass('hidden');

					// the call has answered
					/*$('.phone-action-buttons .hangup')
						.off('click')
						.click(function(){
							// console.log('accepted', session);
							if (session) {
								session.terminate();
							}

							cancelCall();
						});*/

					add_stream();
					stopSound();
					showTimer();
				});
				session.on("confirmed", function(){
					console.log('CONFIRM STREAM');
					stopSound();
				});
				session.on("ended", function(e){
					console.log('call ended with cause: ' + e.cause);
					cancelCall();
				});
				session.on("failed",function(e){
					console.log('call failed with cause: ' + e.cause);
					cancelCall();
				});
				// session.on('addstream', function(){});
			}
		});

		// Запускаем
		phone.start();

		// var session;

		// Кнопка для звонка
		$('.phone-action-buttons .call').off('click').on('click', function() {
			console.log('call');
			var callOptions = {
				'mediaConstraints' : {
					'audio': true,
					'video': false
				},
				'pcConfig': {
					'rtcpMuxPolicy': 'require',
					'iceServers': []
				},
			};

			session = phone.call($('.phone-number').val(), callOptions);
			// console.log('phone', session);
			add_stream();

			$('.phone-action-buttons .call').addClass('hidden');
			$('.phone-action-buttons .hangup').removeClass('hidden');
		});

		// Кнопка для отбоя звонка
		$('.phone-action-buttons .hangup').off('click').on('click', function() {
			console.log('hangup', session);

			if (session) {
				session.terminate();
			}

			cancelCall();
		});

		// Mute
		$('.phone-action-buttons .microphone').off('click').click(function(){
			// console.log('mute', session.isMuted());

			if (session) {
				if (session.isMuted().audio)
				{
					session.unmute();
				}
				else
				{
					session.mute();
				}
			}

			$(this).find('i').toggleClass('fa-microphone gray fa-microphone-slash darkorange');
		})
	}
}

function add_stream(){
	// This is for Chrome, Firefox, etc.
	session.connection.addEventListener('addstream', function(e) { // eslint-disable-line
		console.log('addstream', e);
		// remoteAudio.src = (e.stream);
		// remoteAudio.play();

		const audioElement = document.createElement('audio');
		audioElement.srcObject = e.stream;
		audioElement.play();
		document.body.appendChild(audioElement);
	});

	// This is for Safari.
	session.connection.addEventListener('track', function(e) { // eslint-disable-line
		console.log('Add stream track on connection')
		remoteAudio.srcObject = e.streams[0]; // eslint-disable-line
		remoteAudio.play(); // eslint-disable-line
	});
}

function cancelCall() {
	$('.phone-action-buttons .call').find('i').removeClass('fa-phone-volume').addClass('fa-phone').removeClass('fa-shake');
	$('.phone-action-buttons .call').removeClass('hidden');
	$('.phone-action-buttons .hangup').addClass('hidden');
	$('.caller-name span').text('');
	$('.caller-name').addClass('hidden');
	stopSound();
	hideTimer();
}

function playSound(soundName, loop) {
	Howler.stop();
	Howler.unload();

	Howler.autoUnlock = true;
	// Howler.usingWebAudio = true;
	Howler.html5PoolSize = 10000;

	const sound = new Howl({
		src: ['/modules/skin/bootstrap/sound/' + soundName], // for safari, .webm doesn`t support safari less than 17
		// format: ['webm'],
		autoplay: true,
		// html5: true,
		loop: loop,
		volume: 1,
		preload: true,
		onend: () => { sound.unload(); }
	});

	sound.play();
}

function stopSound() {
	Howler.stop();
}

var minutes = 0,
	seconds = 0;

function showTimer()
{
	window.timex = setTimeout(function () {
		// console.log('seconds', seconds);
		seconds++;
		if (seconds > 59)
		{
			seconds = 0;
			minutes++;

			if (minutes > 59) {
				minutes = 0;
				minutes++;
			}

			$('.phone-action-buttons #minutes').text(minutes < 10 ? "0" + minutes : minutes);
		}

		$('.phone-action-buttons #seconds').text(seconds < 10 ? "0" + seconds : seconds);

		showTimer();
	}, 1000);

	$('.phone-action-buttons .timer').removeClass('hidden');
}

function hideTimer()
{
	clearTimeout(window.timex);
	$('.phone-action-buttons .timer').addClass('hidden');
	$('.phone-action-buttons .timer').text('00:00');
}