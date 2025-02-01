<?php


/**
 * WeatherAPI class
 * Handles retrieving and formatting weather data for use in a Discord bot.
 */
class WeatherAPI
{
    // API endpoint
    private $_api_url = "https://api.open-meteo.com/v1/forecast";

    // Location and timezone properties
    private $_latitude;
    private $_longitude;
    private $_timezone;
    private $_city;

    // Temperature format (Celsius or Fahrenheit)
    private $_temp_format;

    /**
       * Constructor to initialize the WeatherAPI class.
       *
       * @param float $latitude Default latitude of the location.
       * @param float $longitude Default longitude of the location.
       * @param string $timezone Default timezone for the location.
       * @param string $city Name of the city for the weather report.
       * @param string $temp_format Temperature format, either "C" (Celsius) or "F" (Fahrenheit).
       */
    public function __construct($latitude = -37.9694, $longitude = 145.0481, $timezone = "Australia/Sydney", $city = "Cheltenham", string $temp_format = "C")
    {
        $this->_latitude = $latitude;
        $this->_longitude = $longitude;
        $this->_timezone = $timezone;
        $this->_city = $city;
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
          "latitude" => $this->_latitude,
          "longitude" => $this->_longitude,
          "current" => "temperature_2m",
          "hourly" => "temperature_2m",
          "daily" => "weather_code,temperature_2m_max,temperature_2m_min,apparent_temperature_max,apparent_temperature_min,sunrise,sunset,daylight_duration,sunshine_duration,uv_index_max,uv_index_clear_sky_max,precipitation_sum,rain_sum,showers_sum,snowfall_sum,precipitation_hours,precipitation_probability_max,wind_speed_10m_max,wind_gusts_10m_max,wind_direction_10m_dominant,shortwave_radiation_sum,et0_fao_evapotranspiration",
          "timezone" => $this->_timezone,
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

        // Parse and assemble the weather data into a message
        return $this->_assembleWeatherMessage(json_decode($response, true), $this->_city, $this->_temp_format);

    }

    /**
     * Assembles the weather update message for the given data and format.
     *
     * @param array $weather_data Parsed weather data from the API response.
     * @param string $city Name of the city for the weather report.
     * @param string $temp_format Temperature format ("C" or "F").
     * @return string Formatted weather update message.
     */
    private function _assembleWeatherMessage($weather_data, $city, $temp_format)
    {

        // Extract and interpret UV index with descriptions
        $uv_index = $weather_data["daily"]["uv_index_max"][0];
        $uv_text = match (true) {
            $uv_index <= 2 => "Low - you should be good",
            $uv_index <= 5 => "Moderate - sunscreen recommended!",
            $uv_index <= 8 => "High - wear sunscreen and stay in shade",
            default => "Very high - avoid outdoor exposure",
        };

        // Convert temperature based on the selected format
        $max_temp = $temp_format == "F" ? ($weather_data["daily"]["temperature_2m_max"][0] * 9 / 5) + 32 : $weather_data["daily"]["temperature_2m_max"][0];
        $min_temp = $temp_format == "F" ? ($weather_data["daily"]["temperature_2m_min"][0] * 9 / 5) + 32 : $weather_data["daily"]["temperature_2m_min"][0];

        // Determine if shorts are recommended based on temperature
        if ($temp_format == "C") {
            $shorts = $max_temp >= 26 ? "Absolutely! It's a shorts kind of day!" : "Nah, stick to pants!";
        } else {

            $shorts = $max_temp >= 78 ? "Absolutely! It's a shorts kind of day!" : "Nah, stick to pants!";
        }

        // Assemble the message in a Discord-friendly format
        $message = "🏖️ **Weather Update for {$city}!** 🌦️\n";
        $message .= "🕕 *{$weather_data["daily"]["time"][0]}: Here's what you need to know for today!*\n\n";
        $message .= "- 🌡️ **Max Temp:** `{$max_temp}°".($temp_format == "C" ? "C" : "F")."`\n";
        $message .= "- ❄️ **Min Temp:** `{$min_temp}°".($temp_format == "C" ? "C" : "F")."`\n";
        $message .= "- 🌞 **UV Index:** `{$uv_index}` *({$uv_text})*\n";
        $message .= "- ☂️ **Chance of rain?** {$weather_data["daily"]["precipitation_probability_max"][0]}% 🌧️\n";
        $message .= "- 🩳 **Wear Shorts?** {$shorts}\n\n";
        $message .= "Stay safe and enjoy your day! 😊\n";
        $message .= ($temp_format == "C" ? $_ENV['DISC_ID_ME'] : $_ENV['DISC_ID_TAY']);
        return $message;
    }
}
