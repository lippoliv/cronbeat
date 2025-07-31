<?php

namespace Cronbeat\Tests;

use PHPUnit\Framework\Assert;
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
        Assert::assertEquals(5, $result);
    }

    public function testStringConcatenation(): void
    {
        // Given
        $firstString = "Hello, ";
        $secondString = "World!";

        // When
        $result = $firstString . $secondString;

        // Then
        Assert::assertEquals("Hello, World!", $result);
    }
}
