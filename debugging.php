<?php
require_once __DIR__.'/vendor/autoload.php';

if(file_exists(__DIR__ . '/.env')){
  $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
  $dotenv->load();
}

// Debug information
echo "=== DEBUG INFO ===" . PHP_EOL;
echo "Available PDO drivers: ";
print_r(PDO::getAvailableDrivers());
echo "PHP Version: " . phpversion() . PHP_EOL;

// Check environment variables
echo "Environment variables:" . PHP_EOL;
echo "DB_HOST: " . ($_ENV['DB_HOST'] ?? getenv('DB_HOST') ?? 'NOT SET') . PHP_EOL;
echo "DB_NAME: " . ($_ENV['DB_NAME'] ?? getenv('DB_NAME') ?? 'NOT SET') . PHP_EOL;
echo "DB_USER: " . ($_ENV['DB_USER'] ?? getenv('DB_USER') ?? 'NOT SET') . PHP_EOL;
echo "DB_PASSWORD: " . (($_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD')) ? 'SET' : 'NOT SET') . PHP_EOL;
echo "DB_PORT: " . ($_ENV['DB_PORT'] ?? getenv('DB_PORT') ?? '5432') . PHP_EOL;

try {
    $dsn = sprintf(
        "pgsql:host=%s;dbname=%s;port=%s",
        $_ENV['DB_HOST'] ?? getenv('DB_HOST'),
        $_ENV['DB_NAME'] ?? getenv('DB_NAME'),
        '5432'
    );
    
    echo "DSN: " . $dsn . PHP_EOL;
    echo "==================" . PHP_EOL;
    
    $pdo = new \PDO(
        $dsn,
        $_ENV['DB_USER'] ?? getenv('DB_USER'),
        $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD')
    );
    
    $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    echo "Database connection successful!" . PHP_EOL;
    
} catch (\PDOException $e) {
    echo "Database connection failed: " . $e->getMessage() . PHP_EOL;
    echo "Error Code: " . $e->getCode() . PHP_EOL;
}
