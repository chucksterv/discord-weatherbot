<?php


require_once 'database.php';

try {

    $sql = "CREATE TABLE IF NOT EXISTS daily_weather (
      id SERIAL PRIMARY KEY,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      latitude DOUBLE PRECISION NOT NULL,
      longitude DOUBLE PRECISION NOT NULL,
      timezone VARCHAR (50) NOT NULL,
      city VARCHAR (50) NOT NULL,
      max_temp FLOAT NOT NULL,
      min_temp FLOAT NOT NULL,
      max_temp_f FLOAT GENERATED ALWAYS AS (max_temp * 9 / 5) STORED,
      min_temp_f FLOAT GENERATED ALWAYS AS (min_temp * 9 / 5) STORED,
      uv_index FLOAT NOT NULL
    );";

    $pdo->exec($sql);
    echo "Table daily_weather created successfully.";

} catch (PDOException $e) {
    echo "Error creating daily_weather: " . $e->getMessage();
}
