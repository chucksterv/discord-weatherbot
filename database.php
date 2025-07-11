<?php
require_once __DIR__.'/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

try {
    $con_str =  sprintf(
        "%s:host=%s;dbname=%s;user=%s;password=%s",
        $_ENV['DB_TYPE'],
        $_ENV['DB_HOST'],
        $_ENV['DB_NAME'],
        $_ENV['DB_USER'],
        $_ENV['DB_PASSWORD']
    );

    $pdo = new \PDO($con_str);
    $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

    //    echo "Database connection successful." . PHP_EOL;

} catch (\PDOException $e) {
    echo "Database connection failed: " . $e->getMessage() . PHP_EOL;
}
