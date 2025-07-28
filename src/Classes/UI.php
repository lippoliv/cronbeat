<?php

namespace Cronbeat;

class UI
{
    /**
     * Render the HTML header
     *
     * @param string $title The page title
     * @return string The HTML header
     */
    public static function renderHeader(string $title): string
    {
        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$title} - CronBeat</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .container {
            width: 100%;
            max-width: 400px;
            padding: 2rem;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        h1 {
            margin-top: 0;
            color: #2c3e50;
            text-align: center;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
            box-sizing: border-box;
        }
        button {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: #2980b9;
        }
        .error {
            color: #e74c3c;
            margin-top: 1rem;
            text-align: center;
        }
        .success {
            color: #2ecc71;
            margin-top: 1rem;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
HTML;
    }

    /**
     * Render the HTML footer
     *
     * @return string The HTML footer
     */
    public static function renderFooter(): string
    {
        return <<<HTML
    </div>
</body>
</html>
HTML;
    }

    /**
     * Render the setup form
     *
     * @param string|null $error Error message to display, if any
     * @return string The HTML for the setup form
     */
    public static function renderSetupForm(?string $error = null): string
    {
        $errorHtml = $error ? "<div class='error'>{$error}</div>" : '';
        
        return self::renderHeader('Setup') . <<<HTML
        <h1>CronBeat Setup</h1>
        <p>Welcome to CronBeat! Please create an admin user to get started.</p>
        {$errorHtml}
        <form id="setupForm" method="post" action="index.php?action=setup">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="confirmPassword">Confirm Password</label>
                <input type="password" id="confirmPassword" name="confirmPassword" required>
            </div>
            <button type="submit">Create Account</button>
        </form>
        
        <script>
            document.getElementById('setupForm').addEventListener('submit', async function(e) {
                e.preventDefault();
                
                // Get form values
                const username = document.getElementById('username').value;
                const password = document.getElementById('password').value;
                const confirmPassword = document.getElementById('confirmPassword').value;
                
                // Validate passwords match
                if (password !== confirmPassword) {
                    alert('Passwords do not match');
                    return;
                }
                
                try {
                    // Hash the password with SHA-256
                    const hashedPassword = await sha256(password);
                    
                    // Create a hidden field for the hashed password
                    const hiddenField = document.createElement('input');
                    hiddenField.type = 'hidden';
                    hiddenField.name = 'hashedPassword';
                    hiddenField.value = hashedPassword;
                    this.appendChild(hiddenField);
                    
                    // Remove the plain text password and confirmation fields
                    document.getElementById('password').value = '';
                    document.getElementById('confirmPassword').value = '';
                    
                    // Submit the form
                    this.submit();
                } catch (error) {
                    alert('Error hashing password: ' + error.message);
                }
            });
            
            // SHA-256 implementation
            async function sha256(str) {
                // Convert string to ArrayBuffer
                const buffer = new TextEncoder().encode(str);
                
                // Use the SubtleCrypto API to hash the password
                const hashBuffer = await crypto.subtle.digest('SHA-256', buffer);
                
                // Convert hash to hex string
                return Array.from(new Uint8Array(hashBuffer))
                    .map(b => b.toString(16).padStart(2, '0'))
                    .join('');
            }
        </script>
HTML . self::renderFooter();
    }

    /**
     * Render the login form
     *
     * @param string|null $error Error message to display, if any
     * @return string The HTML for the login form
     */
    public static function renderLoginForm(?string $error = null): string
    {
        $errorHtml = $error ? "<div class='error'>{$error}</div>" : '';
        
        return self::renderHeader('Login') . <<<HTML
        <h1>CronBeat Login</h1>
        {$errorHtml}
        <form id="loginForm" method="post" action="index.php?action=login">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Login</button>
        </form>
        
        <script>
            // This is a dummy login form with no functionality as per requirements
            document.getElementById('loginForm').addEventListener('submit', function(e) {
                e.preventDefault();
                alert('Login functionality is not implemented as per requirements.');
            });
        </script>
HTML . self::renderFooter();
    }
}