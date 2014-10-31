<?php
	ini_set('display_errors',1);
	error_reporting(E_ALL);
	require_once 'ForecastIO.php';
	require_once 'API_KEY.php';
	
	/*
	 * At what percentage chance of rain should we say "It's gonna rain?"
	 */
	define("PRECIP_THRESHOLD", 0.50);
	/*
	 * How far should we look ahead to say "it's gonna rain?"
	 */
	define("HOURS_IN_FUTURE", 24);
	/*
	 * Should we consider all forms of precipitation (snow, sleet) as rain [true],
	 * 	or just rain [false]?
	 */
	define("ALL_PRECIP", false);
	/*
	 * Should we check by the minute, if rain is expected < 1 hour from the current time?
	 */
	define("CHECK_MINUTELY", true);
	/*
	 * Set this to TRUE if you want to see a tabular output of the weather data. NOTE: This will
	 * break the AJAX request
	 */
	define("DEBUG", false);
	
	/*
	 * Your API key
	 */
	define("API_KEY", $API_KEY);

	define("LAT", isset($_REQUEST['lat']) ? $_REQUEST['lat'] : 0);
	define("LONG", isset($_REQUEST['lon']) ? $_REQUEST['lon'] : 0);

	$forecast = new ForecastIO(API_KEY, LAT, LONG);
	
	/*
	 * Retrieve conditions
	 */
	$its_gonna_rain = false;
	
	$precip_stats = array(
		'when' => -1, // occurrence minutes from now. -1 if it will not occur within HOURS_IN_FUTURE hours
		'chance' => 0, // decimal chance of rain, 0..1
		'intensity' => 0, // intensity of rain, in inches / hour (can be converted on front-end)
		'type' => '', // type of precip. (rain, snow, sleet)
	);
	$min = 0;
	
	if(DEBUG) :
	?>
	<table border='1'>
	<thead>
	<tr>
		<th>when</th>
		<th>type</th>
		<th>prob</th>
		<th>int</th>
		<th>icon</th>
	</tr>
	</thead>
	<tbody>
	<?php
	endif;
	
	/*
	 * check current conditions
	 */
	$curr = $forecast->getCurrentConditions();
	if($curr != false && !$its_gonna_rain) {
		$precip_chance = $curr->getPrecipitationProbability();
		$precip_type = $curr->getPrecipitationType();
		$precip_intensity = $curr->getPrecipitationIntensity();
		$precip_icon = $curr->getIcon();
		
		if($precip_chance >= PRECIP_THRESHOLD && !$its_gonna_rain && (ALL_PRECIP ? true : $precip_type === "rain")) {
			$its_gonna_rain = true;
			$precip_stats['when'] = 0;
			$precip_stats['chance'] = $precip_chance;
			$precip_stats['intensity'] = $precip_intensity;
			$precip_stats['type'] = $precip_icon;
			$precip_stats['checked'] = 'current';
		}
	}
	
	/*
	 * Check minutely conditions
	 */
	$conditions_minutely = $forecast->getForecastMinutely(60);
	if(CHECK_MINUTELY && $conditions_minutely != false && !$its_gonna_rain) {
		foreach($conditions_minutely as $cond) {
			$precip_chance = $cond->getPrecipitationProbability();
			$precip_type = $cond->getPrecipitationType();
			$precip_intensity = $cond->getPrecipitationIntensity();
			$precip_icon = $cond->getIcon();
			
			if($precip_chance >= PRECIP_THRESHOLD && !$its_gonna_rain && (ALL_PRECIP ? true : $precip_type === "rain")) {
				$its_gonna_rain = true;
				
				$precip_stats['when'] = $min;
				$precip_stats['chance'] = $precip_chance;
				$precip_stats['intensity'] = $precip_intensity;
				$precip_stats['type'] = $precip_icon;
				$precip_stats['checked'] = 'minutely';
				
				break;
			}
			
			$min++;
		}
	}
	
	/*
	 * Check conditions by hour
	 */
	$min = 0;
	$conditions_hourly = $forecast->getForecast(HOURS_IN_FUTURE);
	if($conditions_hourly != false && !$its_gonna_rain) {
		foreach($conditions_hourly as $cond) {
			if($its_gonna_rain) break;
		
			$precip_chance = $cond->getPrecipitationProbability();
			$precip_type = $cond->getPrecipitationType();
			$precip_intensity = $cond->getPrecipitationIntensity();
			$precip_icon = $cond->getIcon();
			
			if(DEBUG) : // show debug information
			?>
				<tr <?php echo (($precip_chance >= PRECIP_THRESHOLD) ? 'style="background-color: #f67;"' : ''); ?>>
					<td><?php echo $min; ?></td>
					<td><?php echo $precip_type; ?></td>
					<td><?php echo $precip_chance; ?></td>
					<td><?php echo $precip_intensity; ?></td>
					<td><?php echo $precip_icon; ?></td>
				</tr>
			<?php
			endif;
			
			if($precip_chance >= PRECIP_THRESHOLD && !$its_gonna_rain && (ALL_PRECIP ? true : $precip_type === "rain")) {
				$its_gonna_rain = true;
				
				$precip_stats['when'] = $min; // represented in MINUTES
				$precip_stats['chance'] = $precip_chance;
				$precip_stats['intensity'] = $precip_intensity;
				$precip_stats['type'] = $precip_icon;
				$precip_stats['checked'] = 'hourly';
				
				break;
			}
			
		$min += 60;
		}
	}
	if(DEBUG) :
	?>
	</tbody>
	</table>
	<?php
	endif;
	
	// No rain. Get current conditions icon
	if(!$its_gonna_rain) {
		$precip_stats['type'] = $curr->getIcon();
	}
	
	// TODO: Possible fencepost issue?
	$precip_stats['minutesChecked'] = $min;
	
	echo json_encode($precip_stats);
?>