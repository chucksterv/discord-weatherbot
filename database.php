<?php
require_once __DIR__.'/vendor/autoload.php';

if(file_exists(__DIR__ . '/.env')){
  $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
  $dotenv->load();
}

try {
    $dsn = sprintf(
        "%s:host=%s;dbname=%s;port=%s",
        $_ENV['DB_TYPE'] ?? getenv('DB_TYPE'),
        $_ENV['DB_HOST'] ?? getenv('DB_HOST'),
        $_ENV['DB_NAME'] ?? getenv('DB_NAME'),
        $_ENV['DB_PORT'] ?? getenv('DB_PORT')
    );

    $username = $_ENV['DB_USER'] ?? getenv('DB_USER');
    $password = $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD');

    $pdo = new \PDO($dsn, $username, $password);
    $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

    echo "Database connection successful." . PHP_EOL;

} catch (\PDOException $e) {
    echo "Database connection failed: " . $e->getMessage() . PHP_EOL;
}

