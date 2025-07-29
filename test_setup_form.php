<?php
// This script simulates a form submission to test the setup form

// Define the URL to submit to
$url = 'http://localhost:8080/setup';

// Define the form data
$data = [
    'username' => 'admin',
    'password_hash' => hash('sha256', 'password123')
];

// Initialize cURL session
$ch = curl_init($url);

// Set cURL options
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

// Execute cURL session and get the response
$response = curl_exec($ch);

// Check for cURL errors
if (curl_errno($ch)) {
    echo 'cURL error: ' . curl_error($ch) . "\n";
} else {
    // Get the HTTP status code
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $finalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    
    echo "HTTP status code: $httpCode\n";
    echo "Final URL: $finalUrl\n";
    
    // Check if the response contains an error message
    if (strpos($response, 'class="error"') !== false) {
        preg_match('/<div class=\'error\'>(.*?)<\/div>/s', $response, $matches);
        if (isset($matches[1])) {
            echo "Error message: " . trim($matches[1]) . "\n";
        } else {
            echo "Error div found but couldn't extract the message\n";
        }
    } else if ($finalUrl === 'http://localhost:8080/login') {
        echo "Success! Redirected to login page\n";
    } else {
        echo "No error message found in the response\n";
    }
}

// Close cURL session
curl_close($ch);

echo "Test completed\n";