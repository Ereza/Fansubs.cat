var currentLinkId=-1;
var currentPlayId="";
var lastWindowWidth=0;
var baseUrl='';
var reportTimer;
//New player
var player = null;
var streamer = null;
var currentMegaFile = null;
var currentVideoTitle = null;
var currentMethod = null;
var currentSourceData = null;
var lastErrorTimestamp = null;
var lastErrorReported = null;
var playedMediaTimer = null;
var playedMediaSeconds = 0;
var enableDebug = false;
var loggedMessages = "";
var pageLoadedDate = Date.now();
var playerWasFullscreen = false;

var cookieOptions = {
	expires: 3650,
	path: baseUrl+'/',
	domain: 'anime.fansubs.cat'
};

nanoid=(t=21)=>{let e="",r=crypto.getRandomValues(new Uint8Array(t));for(;t--;){let n=63&r[t];e+=n<36?n.toString(36):n<62?(n-26).toString(36).toUpperCase():n<63?"_":"-"}return e};

function getNewPlayId(){
	if (crypto) {
		return nanoid(24);
	} else {
		return 'JSR-'+Math.random().toString(36).substr(2, 5)+Math.random().toString(36).substr(2, 5)+Math.random().toString(36).substr(2, 5)+Math.random().toString(36).substr(2, 5)+Math.random().toString(36);
	}
}

function isEmbedPage(){
	return $('#embed-page').length!=0;
}

function addLog(message){
	console.debug(message);
	var playerTime = '--:--:--';
	try{
		if (player && (player.currentTime() || player.currentTime()===0)) {
			playerTime = Math.floor(player.currentTime());
			var ptHours = Math.floor(player.currentTime() / 3600);
			var ptMinutes = Math.floor(player.currentTime() / 60) - (ptHours * 60);
			var ptSeconds = Math.floor(player.currentTime()) % 60;
			playerTime = ptHours.toString().padStart(2, '0') + ':' + ptMinutes.toString().padStart(2, '0') + ':' + ptSeconds.toString().padStart(2, '0');
		}
	} catch (error) {
		playerTime = '--:--:--';
	}
	loggedMessages+=new Date().toLocaleTimeString()+": ["+playerTime+"] "+message+"\n";
}

function beginVideoTracking(linkId, method){
	markLinkAsViewed(linkId);
	currentLinkId=linkId;
	currentMethod=method;
	//The chances of collision of this is so low that if we get a collision, it's no problem at all.
	currentPlayId=getNewPlayId();
	if (!enableDebug) {
		var xhr = new XMLHttpRequest();
		xhr.open("GET", baseUrl+"/counter.php?play_id="+currentPlayId+"&link_id="+currentLinkId+"&method="+currentMethod+"&action=open", true);
		xhr.send(null);
		gtag('event', 'Open link', {
			'event_category': "Playback",
			'event_label': currentLinkId
		});
	} else {
		console.debug('Would have requested: '+baseUrl+'/counter.php?play_id='+currentPlayId+'&link_id='+currentLinkId+"&method="+currentMethod+"&action=open");
	}
	reportTimer = setInterval(function tick() {
		if (!enableDebug) {
			var xhr = new XMLHttpRequest();
			xhr.open("POST", baseUrl+"/counter.php?play_id="+currentPlayId+"&link_id="+currentLinkId+"&method="+currentMethod+"&action=notify&time_spent="+Math.floor(playedMediaSeconds), true);
			xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
			xhr.send("log="+encodeURIComponent(loggedMessages));
		} else {
			console.debug('Would have requested: '+baseUrl+'/counter.php?play_id='+currentPlayId+"&method="+currentMethod+'&link_id='+currentLinkId+"&action=notify&time_spent="+Math.floor(playedMediaSeconds));
		}
	}, 60000);
}

function reportErrorToServer(error_type, error_text){
	if (currentLinkId!=-1){
		if (!lastErrorReported || lastErrorReported<=Date.now()-2000) {
			addLog("Error reported");
			lastErrorReported = Date.now();
			var playerTime = 0;
			try {
				if (player && (player.currentTime() || player.currentTime()===0)) {
					playerTime = player.currentTime();
				}
			} catch (error) {
				playerTime = 0;
			}
			if (!enableDebug) {
				var xhr = new XMLHttpRequest();
				xhr.open("POST", baseUrl+'/report_error.php', true);
				xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
				xhr.send("link_id="+currentLinkId+"&play_time="+playerTime+"&type="+encodeURIComponent(error_type)+"&text="+encodeURIComponent(error_text));
			} else {
				console.debug('Would have sent error via POST: '+"link_id="+currentLinkId+"&play_time="+playerTime+"&type="+encodeURIComponent(error_type)+"&text="+encodeURIComponent(error_text));
			}
		} else {
			addLog("Error repeated (not reported).");
		}
	}
}

function sendVideoTrackingEndAjax(){
	if (currentLinkId!=-1){
		clearInterval(reportTimer);
		if (!enableDebug) {
			var xhr = new XMLHttpRequest();
			xhr.open("POST", baseUrl+"/counter.php?play_id="+currentPlayId+"&link_id="+currentLinkId+"&method="+currentMethod+"&action=close&time_spent="+Math.floor(playedMediaSeconds), true);
			xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
			xhr.send("log="+encodeURIComponent(loggedMessages));
			gtag('event', 'Close link', {
				'event_category': "Playback",
				'event_label': currentLinkId + " / " + Math.floor(playedMediaSeconds)
			});
		} else {
			console.debug('Would have requested: '+baseUrl+'/counter.php?play_id='+currentPlayId+'&link_id='+currentLinkId+"&method="+currentMethod+"&action=close&time_spent="+Math.floor(playedMediaSeconds));
		}
		currentLinkId=-1;
		currentPlayId="";
		lastErrorReported=null;
		loggedMessages="";
		playedMediaSeconds=0;
	}
}

function sendVideoTrackingEndBeacon(){
	if (currentLinkId!=-1){
		clearInterval(reportTimer);
		if (!enableDebug) {
			var formData = new FormData();
			formData.append("log", loggedMessages);
			navigator.sendBeacon(baseUrl+'/counter.php?play_id='+currentPlayId+'&link_id='+currentLinkId+"&method="+currentMethod+"&action=close&time_spent="+Math.floor(playedMediaSeconds), formData);
			gtag('event', 'Close link', {
				'event_category': "Playback",
				'event_label': currentLinkId + " / " + Math.floor(playedMediaSeconds)
			});
		} else {
			console.debug('Would have requested: '+baseUrl+'/counter.php?play_id='+currentPlayId+'&link_id='+currentLinkId+"&method="+currentMethod+"&action=close&time_spent="+Math.floor(playedMediaSeconds));
		}
		currentLinkId=-1;
		currentPlayId="";
		lastErrorReported=null;
		playedMediaSeconds=0;
	}
}

function markLinkAsViewed(link_id){
	var current = Cookies.get('viewed_links', cookieOptions);
	if (current){
		var links = current.split(',');
		if (!links.includes(link_id)){
			links.push(link_id);
			Cookies.set('viewed_links', links.join(','), cookieOptions);
		}
	} else {
		var links = [];
		links.push(link_id);
		Cookies.set('viewed_links', links.join(','), cookieOptions);
	}
	$('.viewed-indicator[data-link-id='+link_id+']').attr('title','Ja l\'has vist: prem per a marcar-lo com a no vist');
	$('.viewed-indicator[data-link-id='+link_id+']').removeClass('not-viewed');
	$('.viewed-indicator[data-link-id='+link_id+']').addClass('viewed');
	$('.viewed-indicator[data-link-id='+link_id+'] span').removeClass('fa-eye-slash');
	$('.viewed-indicator[data-link-id='+link_id+'] span').addClass('fa-eye');
	$('.new-episode[data-link-id='+link_id+']').addClass('hidden');
}

function markLinkAsNotViewed(link_id){
	var current = Cookies.get('viewed_links', cookieOptions);
	if (current){
		var links = current.split(',');
		if (links.includes(link_id)){
			var result = links.filter(function(elem){
				return elem != link_id; 
			});
			Cookies.set('viewed_links', result.join(','), cookieOptions);
		}
	}
	$('.viewed-indicator[data-link-id='+link_id+']').attr('title','Encara no l\'has vist: prem per a marcar-lo com a vist');
	$('.viewed-indicator[data-link-id='+link_id+']').removeClass('viewed');
	$('.viewed-indicator[data-link-id='+link_id+']').addClass('not-viewed');
	$('.viewed-indicator[data-link-id='+link_id+'] span').removeClass('fa-eye');
	$('.viewed-indicator[data-link-id='+link_id+'] span').addClass('fa-eye-slash');
	$('.new-episode[data-link-id='+link_id+']').removeClass('hidden');
}

function getPlayerErrorEvent() {
	var error = "";
	if (player && player.error() && player.error().code) {
		var message = "";
		if (player.error().message) {
			message = " - "+player.error().message;
		}
		switch (player.error().code) {
			case 1:
				error+='1/ABORTED_BY_USER'+message;
				break;
			case 2:
				error+='2/NETWORK_ERROR'+message;
				break;
			case 3:
				error+='3/DECODER_ERROR'+message;
				break;
			case 4:
				error+='4/NOT_SUPPORTED'+message;
				break;
			default:
				error+=player.error().code+'/UNKNOWN_ERROR'+message;
		}
	} else {
		error+="Error desconegut";
	}
	return error;
}

function replayCurrentVideo() {
	player.play();
}

function hasNextVideo() {
	if (isEmbedPage()) {
		return false;
	}
	var position  = parseInt($('.video-player[data-link-id="'+currentLinkId+'"]').first().attr('data-position'));
	var results = $('.video-player').filter(function(){
		return parseInt($(this).attr('data-position')) > position;
	});

	if (results.length>0) {
		return true;
	}
	return false;
}

function playNextVideo() {
	playerWasFullscreen = player.isFullscreen();
	var position  = parseInt($('.video-player[data-link-id="'+currentLinkId+'"]').first().attr('data-position'));
	var results = $('.video-player').filter(function(){
		return parseInt($(this).attr('data-position')) > position;
	});

	if (results.length>0) {
		//In case of multiple links for one episode, only the first will be played
		closeOverlay();
		results.first().click();
	}
}

function getTitleForChromecast() {
	if (isEmbedPage()) {
		return $('#data-series').val();
	} else {
		return $('.series_title').first().text();
	}
}

function getSubtitleForChromecast() {
	if (isEmbedPage()) {
		return $('#data-episode-title').val() + " | " + $('#data-fansub').val();
	} else {
		return $('.video-player[data-link-id="'+currentLinkId+'"]').first().text() + " | " + $('.video-player[data-link-id="'+currentLinkId+'"]').first().attr('data-fansub');
	}
}

function getCoverImageUrlForChromecast() {
	if (isEmbedPage()) {
		return $('#data-cover').val();
	} else {
		return $('.video-player[data-link-id="'+currentLinkId+'"]').first().attr('data-cover');
	}
}

function initializePlayer(title, method, sourceData){
	currentVideoTitle = title;
	currentSourceData = sourceData;
	var sources = JSON.parse(sourceData);
	var start='<div class="white-popup"><div style="display: flex; height: 100%; width: 100%; justify-content: center; align-items: center;">';
	var end='</div></div>';

	if (method=='storage' && Date.now()-pageLoadedDate>=48*3600*1000) {
		parsePlayerError('PAGE_TOO_OLD_ERROR');
	} else {
		switch (method) {
			case 'mega':
				$('#overlay-content').html(start+'<video id="player" playsinline controls disableRemotePlayback class="video-js vjs-default-skin vjs-big-play-centered"></video>'+end);
				break;
			case 'youtube':
			case 'google-drive':
			case 'direct-video':
			case 'storage':
			default:
				var sourcesCode = "";
				for(var i=0;i<sources.length;i++) {
					if (sourcesCode!='') {
						sourcesCode+="\n";
					}
					if (!enableDebug) {
						sourcesCode+='<source type="'+(method=='youtube' ? 'video/youtube' : 'video/mp4')+'" src="'+sources[i].url+(sources[i].url.includes('?') ? '&amp;play_id=' : '?play_id=')+currentPlayId+'&amp;link_id='+currentLinkId+'" size="'+sources[i].resolution+'"/>';
					} else {
						sourcesCode+='<source type="'+(method=='youtube' ? 'video/youtube' : 'video/mp4')+'" src="'+sources[i].url+'"/>';
					}
				}
				$('#overlay-content').html(start+'<video id="player" playsinline controls disableRemotePlayback class="video-js vjs-default-skin vjs-big-play-centered">'+sourcesCode+'</video>'+end);
				break;
		}
	}

	var highestQuality = 0;
	var allQualities = [];
	for(var i=0;i<sources.length;i++) {
		if (!allQualities.includes(sources[i].resolution)) {
			allQualities.push(parseInt(sources[i].resolution));
		}
		if (sources[i].resolution>highestQuality){
			highestQuality=sources[i].resolution;
		}
	}

	allQualities = allQualities.sort(function(a, b){return b-a});

	var techOrders = ['chromecast', 'html5'];

	if (method=='mega') {
		if (window.chrome && window.chrome.cast && window.cast) {
			cast.framework.CastContext.getInstance().endCurrentSession(true);
		}
		techOrders = ['html5'];
	} else if (method=='youtube') {
		if (window.chrome && window.chrome.cast && window.cast) {
			cast.framework.CastContext.getInstance().endCurrentSession(true);
		}
		techOrders = ['youtube'];
	}

	var options = {
		controls: true,
		language: 'ca',
		errorDisplay: false,
		controlBar: {
			children: [
				"playToggle",
				"progressControl",
				"currentTimeDisplay",
				"timeDivider",
				"durationDisplay",
				"muteToggle",
				"volumeControl",
				"fullscreenToggle"
			]
		},
		techOrder: techOrders,
		chromecast: {
			requestTitleFn: getTitleForChromecast,
			requestSubtitleFn: getSubtitleForChromecast,
			requestCoverImageUrlFn: getCoverImageUrlForChromecast
		},
		youtube: {
			modestbranding: 1,
			iv_load_policy: 3
		},
		plugins: {
			chromecast: {
				buttonPositionIndex: -1
			},
			landscapeFullscreen: {
				fullscreen: {
					enterOnRotate: true,
					alwaysInLandscapeMode: true,
					iOS: true
				}
			},
			hotkeys: {
				enableModifiersForNumbers: false
			}
		}
	};
	$('video').on('contextmenu', function(e) {
		e.preventDefault();
	});
	player = videojs("player", options, function(){
		// Player (this) is initialized and ready.
		if (method=='mega') {
			this.currentSrc = function() {
				return 'mega';
			};
		}
	});

	//Recover from errors if needed
	player.one('canplay', event => {
		if (lastErrorTimestamp) {
			player.currentTime(lastErrorTimestamp);
			lastErrorTimestamp = null;
		}
	});
	setTimeout(function(){
		if (playerWasFullscreen) {
			playerWasFullscreen = false;
			player.requestFullscreen();
		}
	}, 0);
	player.on('ready', function(){
		if ($('.player_extra_upper').length==0) {
			$('<div class="player_extra_upper"><div class="player_extra_title">'+new Option(currentVideoTitle).innerHTML+'</div>'+((isEmbedPage() && self==top) ? '' : '<button class="player_extra_close vjs-button" title="Tanca" type="button" onclick="closeOverlay();"><svg aria-hidden="true" focusable="false" height="24" viewBox="4 4 16 16" width="24"><path d="M0 0h24v24H0z" fill="none"></path><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"></path></svg></button>')+'</div><div class="player_extra_ended"><div id="player_extra_ended_buttons"><button id="player_extra_ended_replay" class="player_extra_ended_button" onclick="replayCurrentVideo();"><span class="fa fa-undo"></span></button>' + (hasNextVideo() ? '<button id="player_extra_ended_next" class="player_extra_ended_button" onclick="playNextVideo();"><span class="fa fa-step-forward"></span></button>' : '') + '</div></div>').appendTo(".video-js");
			if (player.techName_=='Html5') {
				setTimeout(function(){
					if (player) {
						player.play();
					}
				}, 0);
			}
		}
	});
	
	player.on('playing', function(){
		addLog('Playing');
		playedMediaTimer = setInterval(function tick() {
			if (player) {
				playedMediaSeconds+=player.playbackRate();
				//addLog('playedMediaSeconds: '+playedMediaSeconds);
			}
		}, 1000);
	});
	player.on('pause', function(){
		addLog('Paused');
		clearInterval(playedMediaTimer);
	});
	player.on('ended', function(){
		addLog('Ended');
		clearInterval(playedMediaTimer);
	});
	player.on('stalled', function(){
		addLog('Stalled (informative)');
		//Do not clear: on iOS, this is triggered while the video keeps playing...
	});
	player.on('waiting', function(){
		addLog('Waiting');
		clearInterval(playedMediaTimer);
	});
	player.on('error', function(){
		parsePlayerError((currentMethod=='mega' ? 'E_MEGA_PLAYER_ERROR' : 'E_DIRECT_PLAYER_ERROR')+': '+getPlayerErrorEvent());
	});

	if (method=='mega') {
		//No multi-source support: show the first one
		loadMegaStream(sources[0].url);
	}
}

function parsePlayerError(error){
	var title = null;
	var message = null;
	var critical = false;
	var forceRefresh = false;
	switch (true) {
		case /EINTERNAL \(\-1\)/.test(error):
		case /EARGS \(\-2\)/.test(error):
		case /EAGAIN \(\-3\)/.test(error):
		case /ERATELIMIT \(\-4\)/.test(error):
		case /EFAILED \(\-5\)/.test(error):
		case /ETOOMANY \(\-6\)/.test(error):
		case /ERANGE \(\-7\)/.test(error):
		case /EEXPIRED \(\-8\)/.test(error):
		case /ECIRCULAR \(\-10\)/.test(error):
		case /EACCESS \(\-11\)/.test(error):
		case /EEXIST \(\-12\)/.test(error):
		case /EINCOMPLETE \(\-13\)/.test(error):
		case /EKEY \(\-14\)/.test(error):
		case /ESID \(\-15\)/.test(error):
		case /EBLOCKED \(\-16\)/.test(error):
		case /ETEMPUNAVAIL \(\-18\)/.test(error):
			title = "S'ha produït un error";
			message = "S'ha produït un error desconegut durant la reproducció del vídeo.<br>Torna-ho a provar, i si continua sense funcionar, prova de tornar a carregar la pàgina.<br>Si el problema persisteix, contacta amb nosaltres, si us plau.";
			reportErrorToServer('mega-unknown', error);
			break;
		case /ENOENT \(\-9\)/.test(error):
			critical = true;
			title = "El fitxer no existeix";
			message = "Sembla que el fitxer ja no existeix al proveïdor del vídeo en streaming.<br>Mirarem de corregir-ho ben aviat, disculpa les molèsties.";
			reportErrorToServer('mega-unavailable', error);
			break;
		case /EOVERQUOTA \(\-17\)/.test(error):
		case /Bandwidth limit reached/.test(error):
			forceRefresh = true;
			title = "Límit de MEGA superat";
			message = "Has superat el límit d'ample de banda del proveïdor del vídeo en streaming (MEGA).<br>Segurament estàs provant de mirar un vídeo que s'ha publicat fa molt poc.<br>L'estem copiant automàticament a un servidor alternatiu i d'aquí a poca estona estarà disponible i no veuràs aquest error.<br>Torna a carregar la pàgina d'aquí a una estona i torna-ho a provar.";
			reportErrorToServer('mega-quota-exceeded', error);
			break;
		case /E_MEGA_LOAD_ERROR/.test(error):
			if (/web browser lacks/.test(error) || /Streamer is not defined/.test(error)) {
				critical = true;
				title = "Navegador no compatible";
				message = "Sembla que el teu navegador no és compatible amb el reproductor.<br>Els dispositius iPhone i iPad no admeten la reproducció de vídeos de MEGA.<br>Prova de fer servir un altre navegador o un altre dispositiu.";
				reportErrorToServer('mega-incompatible-browser', error);
			} else if (/NetworkError/.test(error)){
				title = "No hi ha connexió";
				message = "S'ha produït un error de xarxa durant la reproducció del vídeo.<br>Assegura't que tinguis una connexió estable a Internet i torna-ho a provar.";
				reportErrorToServer('mega-connection-error', error);
			} else {
				title = "No s'ha pogut carregar";
				message = "S'ha produït un error durant la càrrega del vídeo.<br>Torna-ho a provar, i si continua sense funcionar, prova de recarregar la pàgina.<br>Si el problema persisteix, contacta amb nosaltres, si us plau.";
				reportErrorToServer('mega-load-failed', error);
			}
			break;
		case /PLAYER_ERROR/.test(error):
			switch (true) {
				case /NETWORK_ERROR/.test(error):
					title = "No hi ha connexió";
					message = "S'ha produït un error de xarxa durant la reproducció del vídeo.<br>Assegura't que tinguis una connexió estable a Internet i torna-ho a provar.";
					break;
				case /DECODER_ERROR/.test(error):
					title = "S'ha produït un error";
					message = "S'ha produït un error durant la decodificació del vídeo.<br>Torna-ho a provar, i si continua sense funcionar, prova de tornar a carregar la pàgina.<br>Si el problema persisteix, contacta amb nosaltres, si us plau.";
					break;
				case /NOT_SUPPORTED/.test(error):
					title = "No s'ha pogut carregar";
					message = "S'ha produït un error durant la càrrega del vídeo.<br>Torna-ho a provar, i si continua sense funcionar, prova de tornar a carregar la pàgina.<br>Si el problema persisteix, contacta amb nosaltres, si us plau.";
					break;
				case /ABORTED_BY_USER/.test(error):
				default:
					title = "S'ha produït un error";
					message = "S'ha produït un error desconegut durant la reproducció del vídeo.<br>Torna-ho a provar, i si continua sense funcionar, prova de tornar a carregar la pàgina.<br>Si el problema persisteix, contacta amb nosaltres, si us plau.";
			}
			reportErrorToServer(/E_MEGA_PLAYER_ERROR/.test(error) ? 'mega-player-failed' : 'direct-player-failed', error);
			break;
		case /PAGE_TOO_OLD_ERROR/.test(error):
			forceRefresh = true;
			title = "Cal que actualitzis la pàgina";
			message = "Fa més de 48 hores que vas obrir la pàgina i els enllaços de visualització han caducat.<br>Torna a carregar la pàgina i torna-ho a provar.";
			reportErrorToServer('page-too-old', error);
			break;
		default:
			title = "S'ha produït un error";
			message = "S'ha produït un error desconegut durant la reproducció del vídeo.<br>Torna-ho a provar, i si continua sense funcionar, prova de tornar a carregar la pàgina.<br>Si el problema persisteix, contacta amb nosaltres, si us plau.";
			reportErrorToServer('unknown', error);
			break;
	}
	lastErrorTimestamp = player ? player.currentTime() : 0;
	shutdownVideoPlayer();
	var start = '<div class="white-popup"><div style="justify-content: center; align-items: center; width: 100%; height: 100%; display: flex; flex-direction: column;" class="video-js"><div class="player_extra_upper" style="box-sizing: border-box;"><div class="player_extra_title">'+new Option(currentVideoTitle).innerHTML+'</div><button class="player_extra_close vjs-button" title="Tanca" type="button" onclick="closeOverlay();"><svg aria-hidden="true" focusable="false" height="24" viewBox="4 4 16 16" width="24"><path d="M0 0h24v24H0z" fill="none"></path><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"></path></svg></button></div>';
	var buttons = forceRefresh ? '<div class="player_error_buttons"><button class="error-close-button" onclick="location.reload();">Torna a carregar la pàgina</button></div>' : (critical ? '<div class="player_error_buttons"><button class="error-close-button" onclick="closeOverlay();">Tanca</button></div>' : '<div class="player_error_buttons"><button class="error-close-button" onclick="initializePlayer(currentVideoTitle, currentMethod, currentSourceData);">Torna-ho a provar</button></div>');
	var end='</div></div>';
	$('#overlay-content').html(start + '<div class="player_error_title"><span class=\"fa fa-exclamation-circle player_error_icon\"></span><br>' + title + '</div><div class="player_error_details">' + message + '</div>' + buttons + '<br><details style="color: #888; font-size: 1.3em; line-height: normal;"><summary style="cursor: pointer;"><strong><u>Detalls tècnics de l\'error</u></strong></summary>' + new Option(error).innerHTML + '<br>Reproducció / Enllaç / Instant: ' + currentPlayId + ' / ' + currentLinkId + ' / ' + lastErrorTimestamp + '</details>' + end);
}

function loadMegaStream(url){
	currentMegaFile = mega.file(url);
	currentMegaFile.loadAttributes((error, file) => {
		if (error){
			parsePlayerError('E_MEGA_LOAD_ERROR: '+error);
		} else {
			addLog('MEGA file loaded: ' + file.name + ', size: ' + file.size);
			streamer = new Streamer(file.downloadId, document.getElementById('player_html5_api'), {type: 'isom'});
			streamer.play();
		}
	});
}

function shutdownVideoPlayer() {
	clearInterval(playedMediaTimer);
	if (player!=null){
		try {
			player.dispose();
		} catch (error) {
			console.log("Error while stopping player: "+error);
		}
		player = null;
	}
	if (streamer!=null){
		streamer.destroy();
		streamer = null;
	}
	$('#overlay-content').html('');
}

function showContactScreen(reason) {
	$('body').addClass('no-overflow');
	$('#contact-overlay').removeClass('hidden');

	if (reason=='version_lost') {
		$('#contact-explanation').text("Hi ha capítols de fansubs antics que sabem que van ser subtitulats, però que actualment no estan disponibles. Si saps on els podem aconseguir, o si ens els pots fer arribar, si us plau, escriu-nos fent servir aquest formulari:");
	} else {
		$('#contact-explanation').text("Per a temes relacionats amb els fansubs, és recomanable que escriguis directament al fansub en qüestió fent servir el seu web o Twitter. En cas contrari, ens pots fer arribar comentaris, avisar-nos d'errors o de qualsevol problema o suggeriment per al web fent servir aquest formulari:");
	}
}

function showAlert(title, message, showRefresh=false) {
	if (document.fullscreenElement) {
		document.exitFullscreen();
	}
	$('#alert-overlay').removeClass('hidden');
	$('#alert-title').text(title);
	$('#alert-message').text(message);
	if (showRefresh) {
		$('#alert-refresh-button').removeClass('hidden');
		$('#alert-ok-button').text('Ignora');
	} else {
		$('#alert-refresh-button').addClass('hidden');
		$('#alert-ok-button').text('D\'acord');
	}
}

function closeOverlay() {
	addLog('Closed');
	shutdownVideoPlayer();
	sendVideoTrackingEndAjax();
	if (!isEmbedPage()) {
		$('#overlay').addClass('hidden');
		$('body').removeClass('no-overflow');
	} else {
		window.parent.postMessage('embedClosed', '*');
	}
}

$(document).ready(function() {
	if (!isEmbedPage()) {
		$(".video-player").click(function(){
			$('body').addClass('no-overflow');
			$('#overlay').removeClass('hidden');
			beginVideoTracking($(this).attr('data-link-id'), $(this).attr('data-method'));
			initializePlayer($(this).attr('data-title'), $(this).attr('data-method'), atob($(this).attr('data-sources')));
		});
		$(".viewed-indicator").click(function(){
			if ($(this).hasClass('not-viewed')){
				markLinkAsViewed($(this).attr('data-link-id'));
			} else {
				markLinkAsNotViewed($(this).attr('data-link-id'));
			}
		});
		$(".contact-link").click(function(){
			showContactScreen('generic');
		});
		$(".fansub-downloads").click(function(){
			window.open(atob($(this).attr('data-url')));
		});
		$(".version-lost").click(function(){
			showContactScreen('version_lost');
		});
		$(".version-missing-links-link").click(function(){
			showContactScreen('version_lost');
		});
		$(".version_tab").click(function(){
			$(".version_tab").each(function(){
				$(this).removeClass("version_tab_selected");
			});
			$(".version_content").each(function(){
				$(this).addClass("hidden");
			});
			$(this).addClass("version_tab_selected");
			$("#version_content_"+$(this).attr('data-version-id')).removeClass("hidden");
		});
		$('#search_form').submit(function(){
			if ($('#search_query').val()!=''){
				window.location.href=baseUrl+'/cerca/' + encodeURIComponent(encodeURIComponent($('#search_query').val()));
			}
			else{
				window.location.href=baseUrl+'/';
			}
			return false;
		});
		$('#search_button').click(function(){
			$('#search_form').submit();
		});
		$('#options-button').click(function(){
			$('body').addClass('no-overflow');
			$('#options-overlay').removeClass('hidden');
			$('#options-tooltip').attr('style','');
			Cookies.set('tooltip_closed', '1', cookieOptions);
		});
		$('#options-tooltip-close').click(function(){
			$('#options-tooltip').attr('style','');
			Cookies.set('tooltip_closed', '1', cookieOptions);
		});
		$('#options-cancel-button').click(function(){
			$('#options-form').trigger("reset");
			$('#options-overlay').addClass('hidden');
			$('body').removeClass('no-overflow');
		});
		$('#options-save-button').click(function(){
			Cookies.set('show_missing', $('#show_missing').prop('checked') ? '1' : '0', cookieOptions);
			Cookies.set('show_cancelled', $('#show_cancelled').prop('checked') ? '1' : '0', cookieOptions);
			Cookies.set('show_hentai', $('#show_hentai').prop('checked') ? '1' : '0', cookieOptions);
			var hiddenFansubs = $('#options-fansubs input:not(:checked)');
			var values = [];
			
			for (var i=0;i<hiddenFansubs.length;i++){
				values.push(hiddenFansubs[i].value);
			}
			Cookies.set('hidden_fansubs', values.join(','), cookieOptions);

			location.reload();
		});
		$('#options-select-all').click(function(){
			$('[id^=show_fansub_]').each(function(){
				$(this).prop('checked',true);
			});
		});
		$('#options-unselect-all').click(function(){
			$('[id^=show_fansub_]').each(function(){
				$(this).prop('checked',false);
			});
		});
		$('#contact-cancel-button').click(function(){
			$('#contact-form').trigger("reset");
			$('#contact-overlay').addClass('hidden');
			$('body').removeClass('no-overflow');
		});
		$('#alert-ok-button').click(function(){
			$('#alert-overlay').addClass('hidden');
		});
		$('#alert-refresh-button').click(function(){
			window.location.reload();
		});
		$('#contact-send-button').click(function(){
			if (!/\S+@\S+\.\S+/.test($('#contact_address').val())) {
				alert('Introdueix una adreça de resposta vàlida.');
				return;
			}
			if ($('#contact_message').val()=='') {
				alert('Introdueix un missatge.');
				return;
			}
			$('#contact-send-button').addClass('hidden');
			$('#contact-send-button-loading').removeClass('hidden');
			var xhr = new XMLHttpRequest();
			xhr.open("POST", baseUrl+'/contact.php', true);
			xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
			xhr.onreadystatechange = function() {
				if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
					$('#contact-send-button-loading').addClass('hidden');
					$('#contact-send-button-done').removeClass('hidden');
					setTimeout(function(){
						$('#contact-send-button-done').addClass('hidden');
						$('#contact-send-button').removeClass('hidden');
						$('#contact-form').trigger("reset");
						$('#contact-overlay').addClass('hidden');
						$('body').removeClass('no-overflow');
					}, 4000);
				} else if (this.readyState === XMLHttpRequest.DONE) {
					alert("S'ha produït un error en enviar el missatge. Torna-ho a provar.");
					$('#contact-send-button-loading').addClass('hidden');
					$('#contact-send-button').removeClass('hidden');
				}
			}
			xhr.send("address="+encodeURIComponent($('#contact_address').val())+"&message="+encodeURIComponent($('#contact_message').val())+"&magic=1714");
		});
		$('.select-genre').click(function(){
			$('.select-genre').removeClass('select-genre-selected');
			$(this).addClass('select-genre-selected');
			var genreId = $(this).attr("data-genre-id");
			if (genreId==-1) {
				$('.catalog > div').removeClass('hidden');
			} else {
				$('.catalog > div').addClass('hidden');
				$('.catalog > div.genre-'+genreId).removeClass('hidden');
			}
		});

		var size = Math.max(parseInt($('.carousel').width()/($(window).width()>650 ? 184 : 122)),1);
		var genresSize = Math.max(parseInt($('.genres-carousel').width()/($(window).width()>650 ? 100 : 100)),1);

		$('.carousel').slick({
			speed: 300,
			infinite: false,
			slidesToShow: size,
			slidesToScroll: size,
			variableWidth: true,
			prevArrow: '<button data-nosnippet class="slick-prev" aria-label="Anterior" type="button">Anterior</button>',
			nextArrow: '<button data-nosnippet class="slick-next" aria-label="Següent" type="button">Següent</button>'
		});

		$('.recommendations').slick({
			dots: true,
			appendDots: '.recommendations',
			speed: 600,
			infinite: true,
			autoplay: true,
			autoplaySpeed: 10000,
			slidesToShow: 1,
			slidesToScroll: 1,
			prevArrow: '<button data-nosnippet class="slick-prev" aria-label="Anterior" type="button">Anterior</button>',
			nextArrow: '<button data-nosnippet class="slick-next" aria-label="Següent" type="button">Següent</button>'
		});

		$('.genres-carousel').slick({
			speed: 300,
			infinite: false,
			slidesToShow: genresSize,
			slidesToScroll: genresSize,
			variableWidth: true,
			prevArrow: '<button data-nosnippet class="slick-prev" aria-label="Anterior" type="button">Anterior</button>',
			nextArrow: '<button data-nosnippet class="slick-next" aria-label="Següent" type="button">Següent</button>'
		});

		if ($('.synopsis-content').height()>=154) {
			$(".show-more").removeClass('hidden');
			$('.synopsis-content').addClass('expandable-content-hidden');
			$(".show-more a").on("click", function() {
				var linkText = $(this).text();    

				if(linkText === "Mostra'n més..."){
					linkText = "Mostra'n menys";
					$(".synopsis-content").switchClass("expandable-content-hidden", "expandable-content-shown", 400);
				} else {
					linkText = "Mostra'n més...";
					$(".synopsis-content").switchClass("expandable-content-shown", "expandable-content-hidden", 400);
				};

				$(this).text(linkText);
			});
		}

		$("[class*='trackable-']").click(function () {
			var type="";
			var event="";
			var label="";
			if ($(this).hasClass('trackable-films-catalog')){
				type="Click series";
				event="Click series on films catalog";
				label=$(this).attr('data-series-id');
			} else if ($(this).hasClass('trackable-series-catalog')){
				type="Click series";
				event="Click series on series catalog";
				label=$(this).attr('data-series-id');
			} else if ($(this).hasClass('trackable-search-results')){
				type="Click series";
				event="Click series on search results";
				label=$(this).attr('data-series-id');
			} else if ($(this).hasClass('trackable-featured')){
				type="Click series";
				event="Click series on featured section";
				label=$(this).attr('data-series-id');
			} else if ($(this).hasClass('trackable-latest')){
				type="Click series";
				event="Click series on latest updates section";
				label=$(this).attr('data-series-id');
			} else if ($(this).hasClass('trackable-random')){
				type="Click series";
				event="Click series on random section";
				label=$(this).attr('data-series-id');
			} else if ($(this).hasClass('trackable-popular')){
				type="Click series";
				event="Click series on most popular section";
				label=$(this).attr('data-series-id');
			} else if ($(this).hasClass('trackable-newest')){
				type="Click series";
				event="Click series on newest section";
				label=$(this).attr('data-series-id');
			} else if ($(this).hasClass('trackable-toprated')){
				type="Click series";
				event="Click series on top rated section";
				label=$(this).attr('data-series-id');
			} else if ($(this).hasClass('trackable-related-anime')){
				type="Click series";
				event="Click series on related anime";
				label=$(this).attr('data-series-id');
			} else if ($(this).hasClass('trackable-related-manga')){
				type="Click manga";
				event="Click related manga";
				label=$(this).attr('data-manga-id');
			} else if ($(this).hasClass('trackable-search-results-manga')){
				type="Click manga";
				event="Click manga on search results";
				label=$(this).attr('data-manga-id');
			} else if ($(this).hasClass('trackable-advent')){
				type="Click advent calendar";
				event="Click advent calendar";
				label="Click advent calendar";
			}
			if (type!='' && event!='' && label!='') {
				gtag('event', event, {
					'event_category': type,
					'event_label': label
				});
			}
		});

		if (Cookies.get('tooltip_closed', cookieOptions)!='1') {
			$("#options-tooltip").fadeIn("slow");
		}

		$(window).resize(function() {
			if ($(window).width()!=lastWindowWidth) {
				var size = Math.max(parseInt($('.carousel').width()/($(window).width()>650 ? 184 : 122)),1);
				var genresSize = Math.max(parseInt($('.genres-carousel').width()/($(window).width()>650 ? 100 : 100)),1);

				$('.carousel').slick('unslick');
				$('.carousel').slick({
					speed: 300,
					infinite: false,
					slidesToShow: size,
					slidesToScroll: size,
					variableWidth: true,
					prevArrow: '<button data-nosnippet class="slick-prev" aria-label="Anterior" type="button">Anterior</button>',
					nextArrow: '<button data-nosnippet class="slick-next" aria-label="Següent" type="button">Següent</button>'
				});

				$('.genres-carousel').slick('unslick');
				$('.genres-carousel').slick({
					speed: 300,
					infinite: false,
					slidesToShow: genresSize,
					slidesToScroll: genresSize,
					variableWidth: true,
					prevArrow: '<button data-nosnippet class="slick-prev" aria-label="Anterior" type="button">Anterior</button>',
					nextArrow: '<button data-nosnippet class="slick-next" aria-label="Següent" type="button">Següent</button>'
				});

				lastWindowWidth=$(window).width();
			}
		});

		lastWindowWidth=$(window).width();
	} else {
		$('body').addClass('no-overflow');
		beginVideoTracking($('#data-link-id').val(), $('#data-method').val());
		initializePlayer($('#data-title').val(), $('#data-method').val(), atob($('#data-sources').val()));
		window.parent.postMessage('embedInitialized', '*');
	}

	$(window).on('unload', function() {
		addLog('Navigated away');
		sendVideoTrackingEndBeacon();
		shutdownVideoPlayer();
	});
});

//Google Analytics
window.dataLayer = window.dataLayer || [];
function gtag(){
	dataLayer.push(arguments);
}
gtag('js', new Date());
gtag('config', 'UA-628107-14', {'transport_type': 'beacon'});
