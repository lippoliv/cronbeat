<?php

namespace Cronbeat\Tests;

use Cronbeat\UI;
use PHPUnit\Framework\TestCase;

class UITest extends TestCase
{
    private $ui;
    
    protected function setUp(): void
    {
        $this->ui = new UI();
    }
    
    public function testRenderSetupForm()
    {
        // Given a UI instance
        
        // When rendering the setup form
        $html = $this->ui->renderSetupForm();
        
        // Then it should contain the setup form elements
        $this->assertStringContainsString('<h1>CronBeat Setup</h1>', $html);
        $this->assertStringContainsString('<form method="post" action="index.php" class="hash-password">', $html);
        $this->assertStringContainsString('<input type="hidden" name="action" value="setup">', $html);
        $this->assertStringContainsString('<input type="text" id="username" name="username" required>', $html);
        $this->assertStringContainsString('<input type="password" id="password" name="password" required>', $html);
        $this->assertStringContainsString('<button type="submit">Create Account</button>', $html);
    }
    
    public function testRenderSetupFormWithError()
    {
        // Given a UI instance and an error message
        $errorMessage = 'Test error message';
        
        // When rendering the setup form with an error
        $html = $this->ui->renderSetupForm($errorMessage);
        
        // Then it should contain the error message
        $this->assertStringContainsString("<div class='error'>{$errorMessage}</div>", $html);
    }
    
    public function testRenderLoginForm()
    {
        // Given a UI instance
        
        // When rendering the login form
        $html = $this->ui->renderLoginForm();
        
        // Then it should contain the login form elements
        $this->assertStringContainsString('<h1>CronBeat Login</h1>', $html);
        $this->assertStringContainsString('<form method="post" action="index.php" class="hash-password">', $html);
        $this->assertStringContainsString('<input type="hidden" name="action" value="login">', $html);
        $this->assertStringContainsString('<input type="text" id="username" name="username" required>', $html);
        $this->assertStringContainsString('<input type="password" id="password" name="password" required>', $html);
        $this->assertStringContainsString('<button type="submit">Login</button>', $html);
    }
    
    public function testRenderLoginFormWithError()
    {
        // Given a UI instance and an error message
        $errorMessage = 'Test error message';
        
        // When rendering the login form with an error
        $html = $this->ui->renderLoginForm($errorMessage);
        
        // Then it should contain the error message
        $this->assertStringContainsString("<div class='error'>{$errorMessage}</div>", $html);
    }
    
    public function testRenderDashboard()
    {
        // Given a UI instance
        
        // When rendering the dashboard
        $html = $this->ui->renderDashboard();
        
        // Then it should contain the dashboard elements
        $this->assertStringContainsString('<h1>CronBeat Dashboard</h1>', $html);
        $this->assertStringContainsString('<p>Welcome to CronBeat! This is a placeholder for the dashboard.</p>', $html);
    }
    
    public function testRenderPageIncludesContentAndTitle()
    {
        // Given a UI instance and content
        $content = '<p>Test content</p>';
        $title = 'Test Title';
        
        // When rendering a page
        $html = $this->ui->renderPage($content, $title);
        
        // Then it should contain the content and title
        $this->assertStringContainsString("<title>{$title}</title>", $html);
        $this->assertStringContainsString($content, $html);
    }
    
    public function testRenderPageIncludesPasswordHashingJavaScript()
    {
        // Given a UI instance and content
        $content = '<p>Test content</p>';
        $title = 'Test Title';
        
        // When rendering a page
        $html = $this->ui->renderPage($content, $title);
        
        // Then it should include the JavaScript for password hashing
        $this->assertStringContainsString('function sha256(str)', $html);
        $this->assertStringContainsString('document.querySelectorAll(\'form.hash-password\')', $html);
    }
}