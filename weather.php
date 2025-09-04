<?php

/**
 * WeatherAPI Class
 *
 * This class fetches and formats weather data from the Open-Meteo API
 * for use in a Discord bot. It provides temperature conversions, UV index
 * assessments, and rain probability insights.
 *
 * PHP version 8.3
 *
 * @category Weather
 * @package  WeatherBot
 * @author   D Diwelgama <20625456+chucksterv@users.noreply.github.com>
 * @license  MIT License
 */
class WeatherAPI
{
    // API endpoint
    private $_api_url = "https://api.open-meteo.com/v1/forecast";

    // Location and timezone properties
    public $latitude;
    public $longitude;
    public $timezone;
    public $city;

    // Temperature format (Celsius or Fahrenheit)
    private $_temp_format;

    // Properties returned from API call
    public $min_temp;
    public $max_temp;
    public $uv_index;
    public $rain_probability;

    private $_api_time;

    /**
     * Constructor to initialize the WeatherAPI class.
     *
     * @param float  $latitude    Default latitude of the location.
     * @param float  $longitude   Default longitude of the location.
     * @param string $timezone    Default timezone for the location.
     * @param string $city        Name of the city for the weather report.
     * @param string $temp_format Tempe format, "C" (Celsius) or "F" (Fahrenheit).
     */
    public function __construct(
        $latitude = -37.9694,
        $longitude = 145.0481,
        $timezone = "Australia/Sydney",
        $city = "Cheltenham",
        string $temp_format = "C"
    ) {
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->timezone = $timezone;
        $this->city = $city;
        $this->_temp_format = $temp_format;
    }

    /**
     * Fetches weather data from the API and assembles a formatted weather message.
     *
     * @return string Formatted weather update message.
     */
    public function getWeatherData()
    {
        // Parameters for the API request
        $params = [
          "latitude" => $this->latitude,
          "longitude" => $this->longitude,
          "current" => "temperature_2m",
          "hourly" => "temperature_2m",
          "daily" =>
              "weather_code,temperature_2m_max,temperature_2m_min,".
              "apparent_temperature_max,apparent_temperature_min,sunrise,".
              "sunset,daylight_duration,sunshine_duration,uv_index_max,".
              "uv_index_clear_sky_max,precipitation_sum,rain_sum,showers_sum,".
              "snowfall_sum,precipitation_hours,precipitation_probability_max,".
              "wind_speed_10m_max,wind_gusts_10m_max,wind_direction_10m_dominant,".
              "shortwave_radiation_sum,et0_fao_evapotranspiration",
          "timezone" => $this->timezone,
          "forecast_days" => 3,
        ];
        // Build query from params
        $query_string = http_build_query($params);

        // Initialize Curl Handler
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->_api_url . "?" . $query_string);
        // Stops the response from being printed by default
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);

        curl_close($ch);

        // Parse and store weather data in properties
        $weather_data = json_decode($response, true);

        $this->min_temp = $weather_data["daily"]["temperature_2m_min"][0];
        $this->max_temp = $weather_data["daily"]["temperature_2m_max"][0];
        $this->uv_index = $weather_data["daily"]["uv_index_max"][0];
        $this->rain_probability
            = $weather_data["daily"]["precipitation_probability_max"][0] ;
        $this->_api_time = $weather_data["daily"]["time"][0];

        // Assemble weather message for Discord
        return $this->_assembleWeatherMessage(
            json_decode($response, true),
        );

    }

    /**
     * Assembles the weather update message for the given data and format.
     *
     * @return string Formatted weather update message.
     */
    private function _assembleWeatherMessage()
    {
        // Extract and interpret UV index with descriptions
        $uv_index = $this->uv_index;
        $uv_text = match (true) {
            $uv_index <= 2 => "Low - you should be good",
            $uv_index <= 5 => "Moderate - sunscreen recommended!",
            $uv_index <= 8 => "High - wear sunscreen and stay in shade",
            default => "Very high - avoid outdoor exposure",
        };

        // Convert temperature based on the selected format
        $temp_format = $this->_temp_format;
        $max_temp = $temp_format == "F"
        ? ($this->max_temp * 9 / 5) + 32 : $this->max_temp;

        $min_temp = $temp_format == "F"
        ? ($this->min_temp * 9 / 5) + 32 : $this->min_temp;

        // Determine if shorts are recommended based on temperature
        if ($temp_format == "C") {
            $shorts = $max_temp >= 26 ?
            "Absolutely! It's a shorts kind of day!" : "Nah, stick to pants!";
        } else {

            $shorts = $max_temp >= 78 ?
            "Absolutely! It's a shorts kind of day!" : "Nah, stick to pants!";
        }

        // Assemble the message in a Discord-friendly format
        $message = "🏖️ **Weather Update for {$this->city}!** 🌦️\n";

        $message .= "🕕 *{$this->_api_time}: ";
        $message .= "Here's what you need to know for today!*\n\n";

        $message .= "- 🌡️ **Max Temp:** `{$max_temp}°";
        $message .= ($temp_format == "C" ? "C" : "F")."`\n";

        $message .= "- ❄️ **Min Temp:** `{$min_temp}°";
        $message .= ($temp_format == "C" ? "C" : "F")."`\n";

        $message .= "- 🌞 **UV Index:** `{$uv_index}` *({$uv_text})*\n";

        $message .= "- ☂️ **Chance of rain?** ";
        $message .= "{$this->rain_probability}% 🌧️\n";

        $message .= "- 🩳 **Wear Shorts?** {$shorts}\n\n";

        $message .= "Stay safe and enjoy your day! 😊\n";

        $message .= ($temp_format == "C"
          ? ($_ENV['DISC_ID_ME'] ?? getenv('DISC_ID_ME'))
          : ($_ENV['DISC_ID_TAY'] ?? getenv('DISC_ID_TAY'));

        return $message;
    }
}
