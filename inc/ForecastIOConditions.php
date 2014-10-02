<?php
/**
* Wrapper for get data by getters
*/
class ForecastIOConditions {
	private $raw_data;

	function __construct($raw_data) {
		$this->raw_data = $raw_data;
	}

	/**
	* Will return the temperature
	* 
	* @return String
	*/
	function getTemperature() {
		if(isset($this->raw_data->temperature)) {
			return $this->raw_data->temperature;
		}
		else {
			return '';
		}
	}

	/**
	* get the min temperature
	* 
	* only available for week forecast
	* 
	* @return type
	*/
	function getMinTemperature() {
		return $this->raw_data->temperatureMin;
	}

	/**
	* get max temperature
	* 
	* only available for week forecast
	* 
	* @return type
	*/
	function getMaxTemperature() {
		return $this->raw_data->temperatureMax;
	}

	/**
	* get apparent temperature (heat index/wind chill)
	* 
	* only available for current conditions
	* 
	* @return type
	*/
	function getApparentTemperature() {
		return $this->raw_data->apparentTemperature;
	}

	/**
	* Get the summary of the conditions
	* 
	* @return String
	*/
	function getSummary() {
		return $this->raw_data->summary;
	}

	/**
	* Get the icon of the conditions
	* 
	* @return String
	*/
	function getIcon() {
		if(isset($this->raw_data->icon)) {
			return $this->raw_data->icon;
		}
		else {
			return '';
		}
	}

	/**
	* Get the time, when $format not set timestamp else formatted time
	* 
	* @param String $format
	* @return String
	*/
	function getTime($format = null) {
		if (!isset($format)) {
			return $this->raw_data->time;
		} else {
			return date($format, $this->raw_data->time);
		}
	}

	/**
	* Get the pressure
	* 
	* @return String
	*/
	function getPressure() {
		return $this->raw_data->pressure;
	}

	/**
	* Get the dew point
	* 
	* Available in the current conditions
	* 
	* @return String
	*/
	function getDewPoint() {
		return $this->raw_data->dewPoint;
	}

	/**
	* get humidity
	* 
	* @return String
	*/
	function getHumidity() {
		return $this->raw_data->humidity;
	}

	/**
	* Get the wind speed
	* 
	* @return String
	*/
	function getWindSpeed() {
		return $this->raw_data->windSpeed;
	}

	/**
	* Get wind direction
	* 
	* @return type
	*/
	function getWindBearing() {
		return $this->raw_data->windBearing;
	}

	/**
	* get precipitation type
	* 
	* @return type
	*/
	function getPrecipitationType() {
		if(isset($this->raw_data->precipType))
			return $this->raw_data->precipType;
		else
			return '';
	}

	/**
	* get the probability 0..1 of precipitation type
	* 
	* @return type
	*/
	function getPrecipitationProbability() {
		if(isset($this->raw_data->precipProbability))
			return $this->raw_data->precipProbability;
		else
			return -1;
	}
	
	/**
	 * get the intensity of precipitation
	 *
	 * @return type
	 */
	function getPrecipitationIntensity() {
		if(isset($this->raw_data->precipIntensity))
			return $this->raw_data->precipIntensity;
		else
			return -1;
	}

	/**
	* Get the cloud cover
	* 
	* @return type
	*/
	function getCloudCover() {
		return $this->raw_data->cloudCover;
	}

	/**
	* get sunrise time
	* 
	* only available for week forecast
	* 
	* @return type
	*/
	function getSunrise($format=null) {
		if (!isset($format)) {
			return $this->raw_data->sunriseTime;
		} else {
			return date($format, $this->raw_data->sunriseTime);
		}
	}

	/**
	* get sunset time
	* 
	* only available for week forecast
	* 
	* @return type
	*/
	function getSunset($format=null) {
		if (!isset($format)) {
			return $this->raw_data->sunsetTime;
		} else {
			return date($format, $this->raw_data->sunsetTime);
		}
	}
	
	/**
	 * Conditionally returns the string.
	 * @return String the original string if non-empty/null/undefined,
	 * 	blank string ('') otherwise.
	 */
	private function cond_format($str) {
		if(!isset($str) || empty($str) || is_null($str)) {
			return '';
		}
		return $str;
	}
}
?>