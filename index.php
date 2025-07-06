<?php

use Discord\Builders\MessageBuilder;
use Discord\Discord;

require_once __DIR__.'/vendor/autoload.php';
require_once 'database.php';
require_once 'weather.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$current_hour = date('H');

// if ($current_hour >= 6 && $current_hour < 7) {
$weather_api = new WeatherAPI();

// } elseif ($current_hour >= 1 && $current_hour < 2) {
//     $weather_api = new WeatherAPI(latitude : 36.309384, longitude : -115.294567, timezone : "America/Los_Angeles", city : "Las Vegas", temp_format : "F");
//
// }
// Todo Add error handling
if (isset($weather_api)) {
    $weather_data = $weather_api->getWeatherData();

    try {

        $sql = "INSERT INTO daily_weather(
        latitude,
        longitude,
        timezone,
        city,
        max_temp,
        min_temp,
        uv_index,
        rain_probability
      )
      values (?, ?, ?, ?, ?, ?, ?, ?);";

        $prepared = $pdo->prepare($sql);
        $prepared->execute(
            [
              $weather_api->latitude,
              $weather_api->longitude,
              $weather_api->timezone,
              $weather_api->city,
              $weather_api->max_temp,
              $weather_api->min_temp,
              $weather_api->uv_index,
              $weather_api->rain_probability
             ]
        );
        if ($prepared->rowCount() > 0) {
            echo "Inserted data into daily_weather.", PHP_EOL;
        } else {
            echo "No data was inserted.", PHP_EOL;
        }

    } catch (PDOException $e) {
        echo "DB Error. Error inserting into daily_weather: " . $e->getMessage();
    }
}

$discord = new Discord(
    [
      'token' => $_ENV['DISCORD_TOKEN'],
    ]
);

$discord -> on(
    'init',
    function (Discord $discord) use ($weather_data) {
        echo "Bot is ready!", PHP_EOL;
        $channelId = $_ENV['DISCORD_CHANNEL'];

        $message = MessageBuilder::new()
          ->setContent($weather_data ?? "Your code is running at a time it's not supposed to. Check the server. (cronjobs and server time)");
        $channel = $discord->getChannel($channelId);
        $channel->sendMessage($message)->then(
            function () use ($discord) {
                echo "Message sent successfully !\n";
                $discord->close();
            },
            function ($error) use ($discord) {
                echo "Failed to send message: {$error->getMessage()}\n";
                $discord->close();
            }
        );
    }
);

// $discord->run();
// $discord->getLoop()->stop();
// $discord->removeAllListeners();
// $discord->logger->setDebug(false);
// $discord->close();
