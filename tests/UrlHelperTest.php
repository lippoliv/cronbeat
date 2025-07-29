<?php

namespace Cronbeat\Tests;

use Cronbeat\UrlHelper;
use PHPUnit\Framework\TestCase;

class UrlHelperTest extends TestCase
{
    public function testParseControllerFromUrlWithEmptyUri()
    {
        // Given
        $_SERVER['REQUEST_URI'] = '/';
        
        // When
        $result = UrlHelper::parseControllerFromUrl();
        
        // Then
        $this->assertEquals('login', $result);
    }
    
    public function testParseControllerFromUrlWithControllerOnly()
    {
        // Given
        $_SERVER['REQUEST_URI'] = '/setup';
        
        // When
        $result = UrlHelper::parseControllerFromUrl();
        
        // Then
        $this->assertEquals('setup', $result);
    }
    
    public function testParseControllerFromUrlWithControllerAndPath()
    {
        // Given
        $_SERVER['REQUEST_URI'] = '/dashboard/stats';
        
        // When
        $result = UrlHelper::parseControllerFromUrl();
        
        // Then
        $this->assertEquals('dashboard', $result);
    }
    
    public function testParseControllerFromUrlWithTrailingSlash()
    {
        // Given
        $_SERVER['REQUEST_URI'] = '/login/';
        
        // When
        $result = UrlHelper::parseControllerFromUrl();
        
        // Then
        $this->assertEquals('login', $result);
    }
}