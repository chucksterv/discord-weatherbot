<?php

use Discord\Builders\MessageBuilder;
use Discord\Discord;

require_once __DIR__.'/vendor/autoload.php';
require_once 'weather.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$current_hour = date('H');

if ($current_hour >= 6 && $current_hour < 7) {
    $weather_api = new WeatherAPI();
    $weather_data = $weather_api->getWeatherData();

} elseif ($current_hour >= 1 && $current_hour < 2) {
    $weather_api = new WeatherAPI(latitude : 36.309384, longitude : -115.294567, timezone : "America/Los_Angeles", city : "Las Vegas", temp_format : "F");
    $weather_data = $weather_api->getWeatherData();
}
//Todo Add error handling
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
