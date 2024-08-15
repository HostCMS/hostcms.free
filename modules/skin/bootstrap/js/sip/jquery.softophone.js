import initSoftophone from './softophone.js';

$(function (){
	$.extend({
		softophonePrepare: function() {
			var jPhoneListBox = $('.navbar-account #phoneListBox');

			jPhoneListBox.on({
				'click': function (event){
					event.stopPropagation();
				},
				'touchstart': function () {
					$(this).data({'isTouchStart': true});
				}
			});

			$('.phone-number').on('keyup', function() {
				$.toggleBackspace();
			});

			$('.backspace-button').on('click', function() {
				var phone = $('.phone-number').val();
				$('.phone-number').val(phone.substring(0, phone.length - 1));

				if (phone.length == 1)
				{
					$('.backspace-button').addClass('hidden');
				}
			});

			$('.keyboard').on('click', function() {
				$('.pad').toggleClass('hidden');
				$(this).find('i').toggleClass('azure');
				$('.phone-action-buttons').toggleClass('padding-bottom-10');
				$('.phone-number').focus();
			});

			$('.dial-pad .number-dig').on('click', function(){
				var phone = $('.phone-number').val();
				$('.phone-number').val(phone + $(this).text());

				$.toggleBackspace();
				$('.phone-number').focus();
			});

			$('.navbar li#softophone').on('shown.bs.dropdown', function () {
				$('.phone-number').focus();
			});
		},
		initSoftophone: function (line, data) {
			initSoftophone(line, data);
		}
	});
});