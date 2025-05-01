<?php
// Simple script to check database connection status

// Load the environment variables
require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Get database configuration from .env
$host = $_ENV['DB_HOST'] ?? 'localhost';
$port = $_ENV['DB_PORT'] ?? '3306';
$database = $_ENV['DB_DATABASE'] ?? 'theonedesk';
$username = $_ENV['DB_USERNAME'] ?? 'root';
$password = $_ENV['DB_PASSWORD'] ?? '';

// Set content type to JSON
header('Content-Type: application/json');

// Check if MySQL service is running
$mysqlRunning = false;
try {
    $socket = @fsockopen($host, $port, $errno, $errstr, 5);
    if ($socket) {
        $mysqlRunning = true;
        fclose($socket);
    }
} catch (Exception $e) {
    // Do nothing, we'll report the error in the response
}

// Try to connect to the database
$dbConnected = false;
$error = '';
try {
    $dsn = "mysql:host={$host};port={$port};dbname={$database}";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 5,
    ];
    
    $pdo = new PDO($dsn, $username, $password, $options);
    $dbConnected = true;
} catch (PDOException $e) {
    $error = $e->getMessage();
}

// Prepare the response
$response = [
    'status' => $dbConnected ? 'connected' : 'disconnected',
    'mysql_service' => $mysqlRunning ? 'running' : 'not running',
    'host' => $host,
    'port' => $port,
    'database' => $database,
    'error' => $error,
    'timestamp' => date('Y-m-d H:i:s'),
    'suggestions' => []
];

// Add suggestions based on the error
if (!$mysqlRunning) {
    $response['suggestions'][] = 'Make sure MySQL service is running on your server';
    $response['suggestions'][] = 'Check if MySQL is installed and properly configured';
    $response['suggestions'][] = 'Verify that the MySQL port is not blocked by a firewall';
} elseif (!$dbConnected) {
    if (strpos($error, 'Access denied') !== false) {
        $response['suggestions'][] = 'Check your database username and password';
        $response['suggestions'][] = 'Verify that the user has permissions to access the database';
    } elseif (strpos($error, 'Unknown database') !== false) {
        $response['suggestions'][] = 'The database does not exist. Create it or check the database name';
    } else {
        $response['suggestions'][] = 'Check your database configuration in the .env file';
        $response['suggestions'][] = 'Verify that the database server is accepting connections';
    }
}

// Output the response
echo json_encode($response, JSON_PRETTY_PRINT);
