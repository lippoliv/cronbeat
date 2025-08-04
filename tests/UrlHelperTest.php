<?php

namespace Cronbeat\Tests;

use Cronbeat\UrlHelper;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Assert;

class UrlHelperTest extends TestCase {
    public function testParseControllerFromUrlWithEmptyUri(): void {
        // Given
        $_SERVER['REQUEST_URI'] = '/';

        // When
        $result = UrlHelper::parseControllerFromUrl();

        // Then
        Assert::assertEquals('login', $result);
    }

    public function testParseControllerFromUrlWithControllerOnly(): void {
        // Given
        $_SERVER['REQUEST_URI'] = '/setup';

        // When
        $result = UrlHelper::parseControllerFromUrl();

        // Then
        Assert::assertEquals('setup', $result);
    }

    public function testParseControllerFromUrlWithControllerAndPath(): void {
        // Given
        $_SERVER['REQUEST_URI'] = '/dashboard/stats';

        // When
        $result = UrlHelper::parseControllerFromUrl();

        // Then
        Assert::assertEquals('dashboard', $result);
    }

    public function testParseControllerFromUrlWithTrailingSlash(): void {
        // Given
        $_SERVER['REQUEST_URI'] = '/login/';

        // When
        $result = UrlHelper::parseControllerFromUrl();

        // Then
        Assert::assertEquals('login', $result);
    }

    public function testParsePathWithoutControllerWithEmptyUri(): void {
        // Given
        $_SERVER['REQUEST_URI'] = '/';

        // When
        $result = UrlHelper::parsePathWithoutController();

        // Then
        Assert::assertEquals('', $result);
    }

    public function testParsePathWithoutControllerWithControllerOnly(): void {
        // Given
        $_SERVER['REQUEST_URI'] = '/setup';

        // When
        $result = UrlHelper::parsePathWithoutController();

        // Then
        Assert::assertEquals('', $result);
    }

    public function testParsePathWithoutControllerWithControllerAndPath(): void {
        // Given
        $_SERVER['REQUEST_URI'] = '/dashboard/stats';

        // When
        $result = UrlHelper::parsePathWithoutController();

        // Then
        Assert::assertEquals('stats', $result);
    }

    public function testParsePathWithoutControllerWithTrailingSlash(): void {
        // Given
        $_SERVER['REQUEST_URI'] = '/login/';

        // When
        $result = UrlHelper::parsePathWithoutController();

        // Then
        Assert::assertEquals('', $result);
    }

    public function testParsePathWithoutControllerWithMultiplePathSegments(): void {
        // Given
        $_SERVER['REQUEST_URI'] = '/dashboard/stats/monthly/2025';

        // When
        $result = UrlHelper::parsePathWithoutController();

        // Then
        Assert::assertEquals('stats/monthly/2025', $result);
    }
}
