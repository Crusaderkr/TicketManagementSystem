<?php
// Define the path to the .env file relative to the project root
// Assuming the project structure is: root/config/load_env.php and root/.env
$envFile = __DIR__ . 'load.env'; 

if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        // Skip comments and ensure the line contains an '='
        if (strpos(trim($line), '#') === 0 || strpos($line, '=') === false) {
            continue;
        }

        list($key, $value) = explode('=', $line, 2);
        
        $key = trim($key);
        $value = trim($value);

        // Define the constant globally
        if (!defined($key)) {
            define($key, $value);
        }
    }
} else {
    // Optionally handle the case where the .env file is missing (e.g., on a production server)
    // error_log(".env file not found.");
}
?>