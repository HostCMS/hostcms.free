async function getMedia() {
if(navigator.mediaDevices === undefined) {
navigator.mediaDevices={};
}
if(navigator.mediaDevices.getUserMedia === undefined) {
navigator.mediaDevices.getUserMedia=function(constraints) {
var getUserMedia=navigator.webkitGetUserMedia||navigator.mozGetUserMedia;
if(!getUserMedia) {
return Promise.reject(
new Error("getUserMedia is not implemented in this browser"),
);
}
return new Promise(function(resolve, reject) {
getUserMedia.call(navigator, constraints, resolve, reject);
});
};
}
let stream=null;
try {
stream=await navigator.mediaDevices.getUserMedia({ audio: true });
if(stream !== null)
{
return true;
}
} catch (err) {
console.error('Softophone: microphone is disabled! Check browser settings');
return false;
}
}
var session=null;
var remoteAudio=new window.Audio();
remoteAudio.autoplay=true;
export default async function initSoftophone(line, data) {
if(location.protocol!='https:') {
console.log('Softophone: HTTPS connection required!');
return false;
}
const bMicro=await getMedia();
data=$.extend({
server: '',
login: '',
password: '',
register_server: '',
display_name: ''||data.login,
register_expires: 180,
session_timers: false,
rington: 'ringback.aac',
userAgent: 'HostCMS-JsSip-'+JsSIP.version
}, data);
if(bMicro)
{
JsSIP.debug.disable();
var socket=new JsSIP.WebSocketInterface(data.server);
socket.via_transport="tcp";
var configuration={
sockets  : [ socket ],
password : data.password,
uri: 'sip:'+data.login+'@'+data.register_server,
display_name: data.display_name,
session_timers: data.session_timers,
register_expires: data.register_expires,
user_agent: data.userAgent
};
var phone=new JsSIP.UA(configuration);
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
console.log('Registering on SIP server failed with error: '+ev.cause);
configuration.uri=null;
configuration.password=null;
$('.telephony-name-wrapper i').removeClass('palegreen').addClass('darkorange');
$('.phone-action-buttons .call').addClass('hidden');
});
var options={
mediaConstraints : {
audio: true,
video: false
},
'pcConfig': {
'rtcpMuxPolicy': 'require',
}
};
phone.on("newRTCSession", function(response) {
var newSession=response.session;
if(session){
session=null;
}
session=newSession;
var completeSession=function(){
session=null;
hideTimer();
};
if(session.direction === 'outgoing')
{
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
if(session.direction === "incoming")
{
playSound(data.rington, true);
$('.phone-action-buttons .call').find('i').removeClass('fa-phone').addClass('fa-phone-volume').addClass('fa-shake');
$('.navbar li#softophone .dropdown-toggle').dropdown('toggle');
$('.caller-name span').text(session.remote_identity.display_name);
$('.caller-name').removeClass('hidden');
$('.phone-action-buttons .hangup').removeClass('hidden');
$('.phone-action-buttons .call').off('click').click(function(){
session.answer(options);
});
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
add_stream();
stopSound();
showTimer();
});
session.on("confirmed", function(){
console.log('CONFIRM STREAM');
stopSound();
});
session.on("ended", function(e){
console.log('call ended with cause: '+e.cause);
cancelCall();
});
session.on("failed",function(e){
console.log('call failed with cause: '+e.cause);
cancelCall();
});
}
});
phone.start();
$('.phone-action-buttons .call').off('click').on('click', function() {
console.log('call');
var callOptions={
'mediaConstraints' : {
'audio': true,
'video': false
},
'pcConfig': {
'rtcpMuxPolicy': 'require',
'iceServers': []
},
};
session=phone.call($('.phone-number').val(), callOptions);
add_stream();
$('.phone-action-buttons .call').addClass('hidden');
$('.phone-action-buttons .hangup').removeClass('hidden');
});
$('.phone-action-buttons .hangup').off('click').on('click', function() {
console.log('hangup', session);
if(session) {
session.terminate();
}
cancelCall();
});
$('.phone-action-buttons .microphone').off('click').click(function(){
if(session) {
if(session.isMuted().audio)
{
session.unmute();
}else
{
session.mute();
}
}
$(this).find('i').toggleClass('fa-microphone gray fa-microphone-slash darkorange');
})
}
}
function add_stream(){
session.connection.addEventListener('addstream', function(e) { // eslint-disable-line
console.log('addstream', e);
const audioElement=document.createElement('audio');
audioElement.srcObject=e.stream;
audioElement.play();
document.body.appendChild(audioElement);
});
session.connection.addEventListener('track', function(e) { // eslint-disable-line
console.log('Add stream track on connection');
remoteAudio.srcObject=e.streams[0]; // eslint-disable-line
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
Howler.autoUnlock=true;
Howler.html5PoolSize=10000;
const sound=new Howl({
src: ['/modules/skin/bootstrap/sound/'+soundName], // for safari, .webm doesn`t support safari less than 17
autoplay: true,
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
var minutes=0,
seconds=0;
function showTimer()
{
window.timex=setTimeout(function() {
seconds++;
if(seconds>59)
{
seconds=0;
minutes++;
if(minutes>59) {
minutes=0;
minutes++;
}
$('.phone-action-buttons #minutes').text(minutes<10 ? "0"+minutes : minutes);
}
$('.phone-action-buttons #seconds').text(seconds<10 ? "0"+seconds : seconds);
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
if(!hostcmsBackendCompression) {
import('./softophone.js').then(module => {
window.initSoftophone=module.default;
});
}
$(function(){
$.extend({
softophonePrepare: function() {
var jPhoneListBox=$('.navbar-account #phoneListBox');
jPhoneListBox.on({
'click': function(event){
event.stopPropagation();
},
'touchstart': function() {
$(this).data({'isTouchStart': true});
}
});
$('.phone-number').on('keyup', function() {
$.toggleBackspace();
});
$('.backspace-button').on('click', function() {
var phone=$('.phone-number').val();
$('.phone-number').val(phone.substring(0, phone.length-1));
if(phone.length==1)
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
var phone=$('.phone-number').val();
$('.phone-number').val(phone+$(this).text());
$.toggleBackspace();
$('.phone-number').focus();
});
$('.navbar li#softophone').on('shown.bs.dropdown', function() {
$('.phone-number').focus();
});
},
initSoftophone: async function(line, data) {
if(!hostcmsBackendCompression) {
const { default: initSoftophone }=await import('./softophone.js');
initSoftophone(line, data);
}else
{
initSoftophone(line, data); // eslint-disable-line
}
}
});
});

//# sourceMappingURL=85685326fee5ce6c76458991d2c5510e.js.map