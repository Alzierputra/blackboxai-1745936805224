<?php
$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'futsal_rental';

try {
    // Create connection
    $conn = new PDO("mysql:host=$host", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create database if it doesn't exist
    $sql = "CREATE DATABASE IF NOT EXISTS $dbname";
    $conn->exec($sql);
    echo "Database created successfully\n";

    // Select the database
    $conn->exec("USE $dbname");

    // Import schema from database.sql
    $sql = file_get_contents('database.sql');
    $conn->exec($sql);
    echo "Database schema imported successfully\n";

    echo "Database initialization completed successfully!\n";
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
