// Note: variable "messages" is from messages.js

var res; // the result
var isMetric;
var audio;

// Google Maps API settings
var API_KEY = "AIzaSyDJam7rRnpRPhNSsEMSih7Ox_DoKQrm1K0";
var ZOOM = 15;
var MAPTYPE = "roadmap";
var URL_START = "https://www.google.com/maps/embed/v1/view?key=" + API_KEY + "&center=";
var URL_END = "&zoom=" + (ZOOM ? ZOOM : 15) + "&maptype=" + (MAPTYPE ? MAPTYPE : "roadmap");

function callForecast(position) {
	var lat = position.coords.latitude;
	var lon = position.coords.longitude;

	$.get('inc/forecast.php?lat=' + lat + '&lon=' + lon, function(result, status) {
		return displayForecast(result, status);
	});
	
	// display user's location in a google map
	$("#map-wrapper").append($("<iframe></iframe>")
		.attr( { "src" : URL_START + lat + "," + lon + URL_END } )
	).hide();
}

/**
 * Displays the forecast
 */
function displayForecast(result, status) {
	$("#shut-up").val(messages.audioStop);
	$("#shut-up").removeAttr("disabled");
	// hide weather-info initially when retrieving results.
	res = $.parseJSON(result);
	
	var whenHTML, chanceHTML, intensityHTML, intensityUnitsHTML;
	
	if(res.when < 0) {
		playSound(false, true);
		$('#weather').html(messages.noRain);
		document.title = messages.noRain;
		$('body').css({ 'background-color': 'white' });
		
		whenHTML = "...in the next ";
		if(res.minutesChecked < 60) {
			whenHTML += res.minutesChecked + " minute" + ( res.minutesChecked != 1 ? "s" : "" );
		}
		else {
			var hoursAhead = res.minutesChecked / 60;
			whenHTML += hoursAhead + " hour" + ( hoursAhead != 1.0 ? "s" : "" );
		}
		
		chanceHTML = 0;
		intensityHTML = 0;
	}
	else {
		playSound(true, true);
		$('#weather').html(messages.rain);
		document.title = messages.rain;
		
		whenHTML = "";
		var rainTime = new Date();
		var now = new Date();
		if(res.when == 0) {
			whenHTML = messages.rainNow;
		}
		else {
			// show when it's gonna rain
			rainTime.setMinutes(now.getMinutes() + res.when);
			var dayDiff = daysBetween(now, rainTime);
			
			if(dayDiff == 0) {
				if(res.when < 60) { // display time in minutes
					whenHTML += res.when + " minute" + (res.when != 1 ? "s" : "") + " from now";
				}
				else {
					whenHTML += (res.when / 60) + " hour" + (res.when != 60 ? "s" : "") + " from now";
				}
			}
			else {
				whenHTML += dayDiff + " day" + (dayDiff !== 1 ? "s" : "") + " from now";
			}
			whenHTML += " at " + rainTime.toLocaleTimeString();
		}
		
		chanceHTML = Math.round(res.chance * 100);

		intensityHTML = (Math.round(res.intensity * 1000) / 1000) * (isMetric ? 2.54 : 1);
	}
	
	intensityUnitsHTML = (isMetric ? "cm/hr" : "in/hr");
	
	$('#when').html(whenHTML);
	$('#chance').html(chanceHTML);
	$('#intensity').html(intensityHTML);
	$('#intensity-units').html(intensityUnitsHTML);
	
	// need the precise intensity for conversions.
	$('#intensity-precise').val(res.intensity);
	
	// toggle background
	var icon = res.type;
	var bodyBG = "white"; var light = true;
	switch(icon) {
		case "clear-day": bodyBG = "#ff6644"; break;
		case "clear-night": bodyBG = "#224466"; light = false; break;
		case "rain": bodyBG = "lightblue"; break;
		case "snow": bodyBG = "#bbc;"; break;
		case "sleet":
		case "wind":
		case "fog": bodyBG = "#969696;"; break;
		case "cloudy": bodyBG = "gray"; light = false; break;
		case "partly-cloudy-day": bodyBG = "#aaa"; break;
		case "partly-cloudy-night": bodyBG = "#444"; light = false; break;
		default: bodyBG = "white";
	}
	changeFavicon(icon);
	
	$('#weather-info, #map-wrapper').show();
	
	$('body')
		.css({ 'background-color': bodyBG })
		.attr({ 'class': (light ? "light" : "dark") });
}

/**
 * Plays a sound, given the source
 * @param which whether it will rain or not
 * @param loop whether to loop the sound
 */
function playSound(rain, loop) {
	// stop the currently playing audio
	if(audio) {
		audio.pause();
		audio.currentTime = 0;
	}
	
	// start new audio
	audio = (rain ? $('#its-gonna-rain')[0] : $('#it-aint-gonna-rain')[0]);
	if(loop) {
		audio.addEventListener('ended', loopListener, false);
	}
	else {
		audio.removeEventListener('ended', loopListener);
	}
	audio.play();
}

var loopListener = function() {
	this.currentTime = 0;
	this.play();
}

/**
 * Changes the favicon of the page
 */
function changeFavicon(type) {
	if(!$('link[type="image/x-icon"').length) {
		var fav = $('<link></link>').attr( { 'type': 'image/x-icon', 'rel': 'icon' } );
		$('head').append(fav);
	}

	$('head link[type="image/x-icon"]').attr( { 'href': 'ico/' + type + '.ico' } );
	console.log('changed your favicon to ' + type);
}

/**
 * Converts from metric <--> imperial
 */
function convertUnits() {
	var intensity = $("#intensity-precise").val();
	if(isMetric) {
		// Metric -> US
		var intensityUSPrecise = parseFloat(intensity) / 2.54;
		var intensityUS = Math.round(intensityUSPrecise * 1000) / 1000;
		$("#intensity").html(intensityUS);
		$("#intensity-units").html("in/hr");
		$("#intensity-precise").val(intensityUSPrecise);
	}
	else {
		// US -> Metric
		var intensityMetricPrecise = parseFloat(intensity) * 2.54;
		var intensityMetric = Math.round(intensityMetricPrecise * 1000) / 1000;
		$("#intensity").html(intensityMetric);
		$("#intensity-units").html("cm/hr");
		$("#intensity-precise").val(intensityMetricPrecise);
	}
	isMetric = !isMetric;
}

/**
 * Gets the number of days between the first day and the second day
 * @param first the first Date object
 * @param second the second Date object
 * @return number of days between first and second. If first comes chronologically after second, may return a 
 * 	negative number.
 */
function daysBetween(first, second) {

	// Copy date parts of the timestamps, discarding the time parts.
	var one = new Date(first.getFullYear(), first.getMonth(), first.getDate());
	var two = new Date(second.getFullYear(), second.getMonth(), second.getDate());

	// Do the math.
	var millisecondsPerDay = 1000 * 60 * 60 * 24;
	var millisBetween = two.getTime() - one.getTime();
	var days = millisBetween / millisecondsPerDay;

	// Round down.
	return Math.floor(days);
}

$(document).ready(function() {
	$("#weather").html(messages.initial);
	$("#shut-up").attr("disabled", 'disabled');
	$('#weather-info, #map-wrapper').hide();
	// TODO: Better Metric/Imperial detection
	isMetric = (navigator.language === 'en-US' ? false : true);

	if (navigator.geolocation) {
		navigator.geolocation.getCurrentPosition(callForecast);
	}
	else {
		$("#weather").html(messages.noGeo);
	}
	
	$("#convert").click(function() {
		return convertUnits();
	});
	
	// button to stop/start playing audio
	$("#shut-up").click(function() {
		if(audio.paused) {
			audio.play();
			$(this).val(messages.audioStop);
		}
		else {
			audio.pause();
			$(this).val(messages.audioStopped);
		}
	});
	
	// credits, disclaimer, privacy-policy
	$("a").click(function(event) {
		event.preventDefault();
		var which = $(this).attr("href");
		$(which).fadeIn('fast');
		event.stopPropagation();
	});
	
	$(".close-button").click(function() {
		$(this).parent().fadeOut('fast');
	});
	
	$(document).keyup(function(event) {
		if(event.keyCode == 27) {
			console.log("You pressed ESCAPE");
			var containers = $(".popup");
			containers.each(function() {
				if($(this).is(':visible')) {
					$(this).fadeOut('fast');
				}
			});
		}
	});
	
	$(document).click(function (e) {
		var container = $(".popup");
		
		container.each(function() {
			if (!$(this).is(e.target) && $(this).has(e.target).length === 0) {
				$(this).fadeOut('fast');
			}
		});
	});
});