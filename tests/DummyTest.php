<?php

namespace Cronbeat\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Dummy test class to demonstrate the Given-When-Then pattern
 */
class DummyTest extends TestCase
{
    /**
     * Test that a simple addition works correctly
     */
    public function testSimpleAddition(): void
    {
        // Given two numbers
        $firstNumber = 2;
        $secondNumber = 3;
        
        // When we add them together
        $result = $firstNumber + $secondNumber;
        
        // Then the result should be their sum
        $this->assertEquals(5, $result);
    }
    
    /**
     * Test that a string concatenation works correctly
     */
    public function testStringConcatenation(): void
    {
        // Given two strings
        $firstString = "Hello, ";
        $secondString = "World!";
        
        // When we concatenate them
        $result = $firstString . $secondString;
        
        // Then the result should be the combined string
        $this->assertEquals("Hello, World!", $result);
    }
}