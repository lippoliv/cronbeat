<?php

namespace Cronbeat;

class UI {
    public function renderPage($content, $title = 'CronBeat') {
        $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$title}</title>
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
            background-color: white;
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
            font-weight: 500;
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
    </style>
</head>
<body>
    <div class="container">
        {$content}
    </div>
    
    <script>
        function sha256(str) {
            // This is a simple polyfill for the SHA-256 algorithm
            // In a real application, you would use a more robust library
            return crypto.subtle.digest('SHA-256', new TextEncoder().encode(str))
                .then(buffer => {
                    return Array.from(new Uint8Array(buffer))
                        .map(b => b.toString(16).padStart(2, '0'))
                        .join('');
                });
        }
        
        // Find all forms with the class 'hash-password'
        document.querySelectorAll('form.hash-password').forEach(form => {
            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const passwordField = this.querySelector('input[name="password"]');
                if (passwordField && passwordField.value) {
                    // Hash the password
                    const hashedPassword = await sha256(passwordField.value);
                    
                    // Create a hidden field for the hashed password
                    const hiddenField = document.createElement('input');
                    hiddenField.type = 'hidden';
                    hiddenField.name = 'password_hash';
                    hiddenField.value = hashedPassword;
                    this.appendChild(hiddenField);
                    
                    // Clear the original password field
                    passwordField.value = '';
                    
                    // Submit the form
                    this.submit();
                }
            });
        });
    </script>
</body>
</html>
HTML;
        return $html;
    }

    public function renderSetupForm($error = null) {
        $errorHtml = $error ? "<div class='error'>{$error}</div>" : '';
        
        $content = <<<HTML
<h1>CronBeat Setup</h1>
{$errorHtml}
<form method="post" action="index.php" class="hash-password">
    <input type="hidden" name="action" value="setup">
    <div class="form-group">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" required>
    </div>
    <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>
    </div>
    <button type="submit">Create Account</button>
</form>
HTML;
        
        return $this->renderPage($content, 'CronBeat Setup');
    }

    public function renderLoginForm($error = null) {
        $errorHtml = $error ? "<div class='error'>{$error}</div>" : '';
        
        $content = <<<HTML
<h1>CronBeat Login</h1>
{$errorHtml}
<form method="post" action="index.php" class="hash-password">
    <input type="hidden" name="action" value="login">
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
HTML;
        
        return $this->renderPage($content, 'CronBeat Login');
    }

    public function renderDashboard() {
        $content = <<<HTML
<h1>CronBeat Dashboard</h1>
<p>Welcome to CronBeat! This is a placeholder for the dashboard.</p>
HTML;
        
        return $this->renderPage($content, 'CronBeat Dashboard');
    }
}