<?php
/**
 * Helper Class for forecast.io webservice
 */
class ForecastIO {

	private $api_key;
	private $lat;
	private $lon;
	
	private $data;
	
	/*
	 * The base URI to access the Forecast.IO API
	 */
	const API_ENDPOINT = 'https://api.forecast.io/forecast/';
	
	/*
	 * Whether to write to a file. Should be used for debugging purposes ONLY.
	 * Note that this option will be treated as false if READ_FROM_FILE is enabled.
	 * Should be used for debugging purposes ONLY.
	 */
	const WRITE_TO_FILE = false;
	/*
	 * Whether to read from a file. This will bypass the API call.
	 * If this is set to true, WRITE_TO_FILE will be ignored.
	 * Should be used for debugging purposes ONLY.
	 */
	const READ_FROM_FILE = false;
	/*
	 * Name of the file to read to / write from
	 */
	const DATA_FILE = 'data-minutely.json';

	/**
	* Create a new instance
	* 
	* @param String $api_key
	*/
	function __construct($api_key, $latitude = 0, $longitude = 0) {
		$latitude = (is_numeric($latitude) ? $latitude : 0);
		$longitude = (is_numeric($longitude) ? $longitude : 0);
	
		$this->api_key = $api_key;
		$this->lat = (abs($latitude) > 90 ? fmod($latitude, 90) : $latitude);
		$this->lon = (abs($longitude) > 180 ? fmod($longitude, 180) : $longitude);
		
		$this->requestData();
	}


	/**
	 * Requests data via cURL to the API
	 * @param String $timestamp the timestamp
	 * @param String $exclusions any data to exclude
	 * @return JSON string of data, or false if unsuccessful
	 */
	private function requestData($timestamp = false, $exclusions = false, $units = 'us') {
		if(!self::READ_FROM_FILE) {
			if(!isset($_SESSION['weather'])) {
				$request_url = self::API_ENDPOINT . $this->api_key . '/' . $this->lat . ',' . $this->lon . ( $timestamp ? ',' . $timestamp : '' ) . '?units=' . $units . ( $exclusions ? '&exclude=' . $exclusions : '' );

				$ch = curl_init($request_url);
				curl_setopt($ch, CURLOPT_HTTPGET, 1);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

				$content = curl_exec($ch);
				curl_close($ch);

				$content = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $content);
				$this->data = (!empty($content) ? json_decode($content) : false);
				$_SESSION['weather'] = $this->data;
				if(self::WRITE_TO_FILE) {
					$fh = fopen(self::DATA_FILE, 'w') or die("File I/O error");
					fwrite($fh, $content);
					fclose($fh);
				}
			}
			else {
				// read from $_SESSION
				$this->data = $_SESSION['weather'];
			}
		}
		// read from file
		else {
			$fh = fopen(self::DATA_FILE, 'r') or die("Could not open file for reading.");
			$content = fread($fh, filesize(self::DATA_FILE));
			$content = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $content);
			$this->data = (!empty($content) ? json_decode($content) : false);
			// var_dump($content);
			fclose($fh);
		}
	}

	/**
	* Will return the current location
	* 
	* @return Current location
	*/
	function getCurrentLocation() {
		$arr = array(
			'lat' => $this->lat,
			'lon' => $this->lon,
		);

		return $arr;
	}

	/**
	* Will return the current conditions
	* 
	* @return \ForecastIOConditions|boolean
	*/
	function getCurrentConditions() {
		if ($this->data !== false)
			return new ForecastIOConditions($this->data->currently);
		else
			return false;
	}

	/**
	* Will return historical conditions for day of given timestamp
	*
	* @param float $latitude
	* @param float $longitude
	* @param int $timestamp
	* @return \ForecastIOConditions|boolean
	*/
	function getHistoricalConditions($timestamp) {
		if ($this->data !== false)
			return new ForecastIOConditions($this->data->daily->data[0]);
		else
			return false;
	}
	
	/**
	 * Get forecast minute-by-minute for the current hour
	 * @param Integer $minutes number of minutes to look ahead. Max 60.
	 * @return \ForecastIOConditions|boolean
	 */
	function getForecastMinutely($minutes) {
		if($this-> data !== false && isset($this->data->minutely->data)) {
			$conditions = array();
			
			$minutes = min($minutes, 60);
			for($i = 0; $i <= $minutes; $i++) {
				if(!isset($this->data->minutely->data[$i])) break;
				$raw_data = $this->data->minutely->data[$i];
				$conditions[] = new ForecastIOConditions($raw_data);
			}
			
			return $conditions;
		}
		else {
			return false;
		}	
	}
	
	/**
	 * Retrieves the hourly forecast for the next user-defined number of hours
	 * @param Integer $hours the number of hours to look ahead. max 48
	 * @return \ForecastIOConditions|boolean
	 */
	function getForecast($hours) {
		if ($this->data !== false && isset($this->data->hourly->data)) {
			$conditions = array();
			
			$hours = min($hours, 48);
			for($i = 0; $i <= $hours; $i++) {
				if(!isset($this->data->hourly->data[$i])) break;
				$raw_data = $this->data->hourly->data[$i];
				$conditions[] = new ForecastIOConditions($raw_data);
			}

			return $conditions;
		}
		else {
			return false;
		}
	}

	/**
	* Will return conditions on hourly basis for the next 24 hours
	* 
	* @return \ForecastIOConditions|boolean
	*/
	function getForecastToday() {
		return $this->getForecast(24);
	}


	/**
	* Will return daily conditions for next seven days
	* 
	* @return \ForecastIOConditions|boolean
	*/
	function getForecastWeek() {
		if ($this->data !== false) {
			$conditions = array();

			foreach ($this->data->daily->data as $raw_data) {
				$conditions[] = new ForecastIOConditions($raw_data);
			}
			return $conditions;
		}
		else {
			return false;
		}
	}
}

include 'ForecastIOConditions.php';
?>
