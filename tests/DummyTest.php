<?php

namespace Cronbeat\Tests;

use PHPUnit\Framework\TestCase;

class DummyTest extends TestCase
{
    public function testSimpleAddition(): void
    {
        // Given
        $firstNumber = 2;
        $secondNumber = 3;
        
        // When
        $result = $firstNumber + $secondNumber;
        
        // Then
        $this->assertEquals(5, $result);
    }
    
    public function testStringConcatenation(): void
    {
        // Given
        $firstString = "Hello, ";
        $secondString = "World!";
        
        // When
        $result = $firstString . $secondString;
        
        // Then
        $this->assertEquals("Hello, World!", $result);
    }
}