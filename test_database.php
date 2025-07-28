<?php
// Include the autoloader
require_once __DIR__ . '/vendor/autoload.php';

use Cronbeat\Database;

// Test database functionality
echo "Testing Database functionality...\n";

// Check if the database exists
echo "Database exists: " . (Database::exists() ? "Yes" : "No") . "\n";

// Create a new database instance
$db = new Database();

// Initialize the database
echo "Initializing database...\n";
$result = $db->initialize();
echo "Database initialized: " . ($result ? "Success" : "Failed") . "\n";

// Create a test user
$username = "testuser";
$password = hash('sha256', 'testpassword'); // Simulate client-side SHA-256 hashing
echo "Creating test user...\n";
$result = $db->createUser($username, $password);
echo "User created: " . ($result ? "Success" : "Failed") . "\n";

// Check if the database exists now
echo "Database exists: " . (Database::exists() ? "Yes" : "No") . "\n";

// Clean up (remove the database file for testing purposes)
echo "Cleaning up...\n";
if (file_exists(__DIR__ . '/db/db.sqlite')) {
    unlink(__DIR__ . '/db/db.sqlite');
    echo "Database file removed.\n";
}

echo "Test completed.\n";