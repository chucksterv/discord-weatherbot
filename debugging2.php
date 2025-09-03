<?php
// Test if we can create a PDO instance at all
try {
    echo "Testing basic PDO functionality..." . PHP_EOL;
    
    // Test with SQLite (which you have available)
    $pdo_sqlite = new PDO('sqlite::memory:');
    echo "SQLite PDO works!" . PHP_EOL;
    
    // Test PostgreSQL driver specifically
    $pdo_test = new PDO('pgsql:host=localhost;dbname=test', 'user', 'pass');
    
} catch (PDOException $e) {
    echo "PDO Error: " . $e->getMessage() . PHP_EOL;
    echo "Error details: ";
    var_dump($e);
}
