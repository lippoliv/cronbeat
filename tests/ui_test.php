<?php

namespace Cronbeat\Tests;

use Cronbeat\UI;
use PHPUnit\Framework\TestCase;

class UITest extends TestCase
{
    /**
     * Test that renderHeader() returns valid HTML with the provided title
     */
    public function testRenderHeaderReturnsValidHtmlWithTitle(): void
    {
        // Given a page title
        $title = 'Test Page';
        
        // When rendering the header
        $result = UI::renderHeader($title);
        
        // Then it should contain the title in the expected format
        $this->assertStringContainsString("<title>{$title} - CronBeat</title>", $result);
        
        // And it should be valid HTML
        $this->assertStringStartsWith('<!DOCTYPE html>', $result);
        $this->assertStringContainsString('<html lang="en">', $result);
        $this->assertStringContainsString('<head>', $result);
        $this->assertStringContainsString('<body>', $result);
        $this->assertStringContainsString('<div class="container">', $result);
    }
    
    /**
     * Test that renderFooter() returns valid HTML closing tags
     */
    public function testRenderFooterReturnsValidHtmlClosingTags(): void
    {
        // Given we need to render the footer
        
        // When rendering the footer
        $result = UI::renderFooter();
        
        // Then it should contain the expected closing tags
        $this->assertStringContainsString('</div>', $result);
        $this->assertStringContainsString('</body>', $result);
        $this->assertStringContainsString('</html>', $result);
    }
    
    /**
     * Test that renderSetupForm() returns a form with no error message by default
     */
    public function testRenderSetupFormReturnsFormWithNoErrorByDefault(): void
    {
        // Given no error message
        
        // When rendering the setup form
        $result = UI::renderSetupForm();
        
        // Then it should contain the form elements
        $this->assertStringContainsString('<h1>CronBeat Setup</h1>', $result);
        $this->assertStringContainsString('<form id="setupForm"', $result);
        $this->assertStringContainsString('name="username"', $result);
        $this->assertStringContainsString('name="password"', $result);
        $this->assertStringContainsString('name="confirmPassword"', $result);
        
        // And it should not contain an error message
        $this->assertStringNotContainsString('<div class=\'error\'>', $result);
    }
    
    /**
     * Test that renderSetupForm() includes the error message when provided
     */
    public function testRenderSetupFormIncludesErrorMessageWhenProvided(): void
    {
        // Given an error message
        $errorMessage = 'Test error message';
        
        // When rendering the setup form with the error
        $result = UI::renderSetupForm($errorMessage);
        
        // Then it should contain the error message
        $this->assertStringContainsString("<div class='error'>{$errorMessage}</div>", $result);
    }
    
    /**
     * Test that renderSetupForm() includes the JavaScript for password hashing
     */
    public function testRenderSetupFormIncludesJavaScriptForPasswordHashing(): void
    {
        // Given we need to render the setup form
        
        // When rendering the setup form
        $result = UI::renderSetupForm();
        
        // Then it should contain the JavaScript for password hashing
        $this->assertStringContainsString('<script>', $result);
        $this->assertStringContainsString('document.getElementById(\'setupForm\').addEventListener(\'submit\'', $result);
        $this->assertStringContainsString('async function sha256(str)', $result);
        $this->assertStringContainsString('crypto.subtle.digest(\'SHA-256\'', $result);
    }
    
    /**
     * Test that renderLoginForm() returns a form with no error message by default
     */
    public function testRenderLoginFormReturnsFormWithNoErrorByDefault(): void
    {
        // Given no error message
        
        // When rendering the login form
        $result = UI::renderLoginForm();
        
        // Then it should contain the form elements
        $this->assertStringContainsString('<h1>CronBeat Login</h1>', $result);
        $this->assertStringContainsString('<form id="loginForm"', $result);
        $this->assertStringContainsString('name="username"', $result);
        $this->assertStringContainsString('name="password"', $result);
        
        // And it should not contain an error message
        $this->assertStringNotContainsString('<div class=\'error\'>', $result);
    }
    
    /**
     * Test that renderLoginForm() includes the error message when provided
     */
    public function testRenderLoginFormIncludesErrorMessageWhenProvided(): void
    {
        // Given an error message
        $errorMessage = 'Test error message';
        
        // When rendering the login form with the error
        $result = UI::renderLoginForm($errorMessage);
        
        // Then it should contain the error message
        $this->assertStringContainsString("<div class='error'>{$errorMessage}</div>", $result);
    }
    
    /**
     * Test that renderLoginForm() includes the JavaScript for form submission
     */
    public function testRenderLoginFormIncludesJavaScriptForFormSubmission(): void
    {
        // Given we need to render the login form
        
        // When rendering the login form
        $result = UI::renderLoginForm();
        
        // Then it should contain the JavaScript for form submission
        $this->assertStringContainsString('<script>', $result);
        $this->assertStringContainsString('document.getElementById(\'loginForm\').addEventListener(\'submit\'', $result);
    }
    
    /**
     * Test that renderHeader() and renderFooter() can be combined to create a complete page
     */
    public function testRenderHeaderAndFooterCanBeCombinedToCreateCompletePage(): void
    {
        // Given a page title and content
        $title = 'Test Page';
        $content = '<p>Test content</p>';
        
        // When combining header, content, and footer
        $result = UI::renderHeader($title) . $content . UI::renderFooter();
        
        // Then it should form a complete HTML page
        $this->assertStringStartsWith('<!DOCTYPE html>', $result);
        $this->assertStringContainsString($content, $result);
        $this->assertStringEndsWith('</html>', trim($result));
    }
}