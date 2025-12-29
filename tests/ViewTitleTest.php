<?php

namespace Cronbeat\Tests;

use PHPUnit\Framework\TestCase;
use Cronbeat\Views\LoginView;
use Cronbeat\Views\SetupView;
use Cronbeat\Views\MigrateView;

class ViewTitleTest extends TestCase {

    public function testLoginViewRendersDocumentTitleAndH1(): void {
        // Given
        $view = new LoginView();

        // When
        $html = $view->render();

        // Then
        $this->assertStringContainsString('<title>Login - CronBeat</title>', $html);
        $this->assertStringContainsString('<h1>Login</h1>', $html);
    }

    public function testSetupViewUsesCronBeatInTitleAndH1(): void {
        // Given
        $view = new SetupView();

        // When
        $html = $view->render();

        // Then
        $this->assertStringContainsString('<title>CronBeat Setup - CronBeat</title>', $html);
        $this->assertStringContainsString('<h1>CronBeat Setup</h1>', $html);
    }

    public function testMigrateViewUsesCronBeatInTitleAndH1(): void {
        // Given
        $view = new MigrateView();

        // When
        $html = $view->render();

        // Then
        $this->assertStringContainsString('<title>CronBeat Database Migration - CronBeat</title>', $html);
        $this->assertStringContainsString('<h1>CronBeat Database Migration</h1>', $html);
    }
}
