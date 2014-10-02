<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no" />
	<title>Steve Reich tells the weather</title>
	
	<link type="text/css" rel="stylesheet" href="css/style.css" />
	<script language="JavaScript" src="http://www.geoplugin.net/javascript.gp" type="text/javascript"></script>
</head>
<body>

	<noscript>Warning! This site will not work without JavaScript. Please access this site on a JavaScript-enabled browser.</noscript>

	<input type="button" id="convert" value="Convert" />
	<input type="button" id="shut-up" value="SHUT UP!" />
	
	<main>
	<div id="weather-wrapper">
		<h1 id="weather">Steve Reich Weather Forecast</h1>

		<section id="weather-info">
			<p><span id="when"></span></p>
			<p><span id="chance"></span>% chance</p>
			<p><span id="intensity"></span> <span id="intensity-units"></span> of rain</p>
			<input type="hidden" id="intensity-precise" />
		</section>
	</div>

	<audio id="it-aint-gonna-rain">
		<source src="audio/it-aint-gonna-rain.mp3" type="audio/mpeg" preload="auto" />
		<source src="audio/it-aint-gonna-rain.ogg" type="audio/ogg" preload="auto" />
	</audio>

	<audio id="its-gonna-rain">
		<source src="audio/its-gonna-rain.mp3" type="audio/mpeg" preload="auto" />
		<source src="audio/its-gonna-rain.ogg" type="audio/ogg" preload="auto" />
		Sorry, your browser does not support HTML5 audio.
	</audio>

	<div id="map-wrapper">

	</div>
	</main>

	<!-- stuff initially hidden -->
	<div id="credits" class="popup" style="display: none;">
		<ul>
			<li>Made by Geoffrey Liu</li>
			<li>Made with JQuery</li>
			<li>Forecast.io API</li>
		</ul>
		
		<input type="button" class="close-button" value="X" />
	</div>

	<div id="disclaimer" class="popup" style="display: none;">
		<p>I am not responsible for any rain-related deaths or squeaky floors pertaining to the use of this weather application. Remember: Weather forecasts are always subject to human error.</p>
		
		<input type="button" class="close-button" value="X" />
	</div>

	<div id="privacy" class="popup" style="display: none;">
		<p>We will absolutely positively one-hundred-percent certified double checked guaranteed for-sure not keep track of any of your personal data, EXCEPT... your location. That is required for an accurate weather forecast.</p>
		
		<p>If you do not agree, you can opt out of sharing your location, but we won't be able to share you your rain forecast. Sorry, you get what you put in.</p>
		
		<input type="button" class="close-button" value="X" />
	</div>

	<footer>
		<p><a href='#credits'>Credits</a> | <a href='#disclaimer'>Disclaimer</a> | <a href='#privacy'>Privacy Policy</a></p>
	</footer>

	<!-- SCRIPTS -->
	<script type="text/javascript" src="//code.jquery.com/jquery-2.1.0.min.js"></script>
	<script type="text/javascript" src="js/messages.js"></script>
	<script type="text/javascript" src="js/gonnaRain.js"></script>
</body>
</html>
