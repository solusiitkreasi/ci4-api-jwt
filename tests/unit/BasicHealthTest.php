<?php

use CodeIgniter\Test\CIUnitTestCase;

/**
 * Basic Health Tests for CI/CD Pipeline
 * @internal
 */
final class BasicHealthTest extends CIUnitTestCase
{
    public function testAppPathIsDefined(): void
    {
        $this->assertTrue(defined('APPPATH'), 'APPPATH should be defined');
    }

    public function testSystemPathIsDefined(): void  
    {
        $this->assertTrue(defined('SYSTEMPATH'), 'SYSTEMPATH should be defined');
    }

    public function testWritableDirectoryExists(): void
    {
        $this->assertTrue(is_dir(WRITEPATH), 'Writable directory should exist');
    }

    public function testWritableDirectoryIsWritable(): void
    {
        $this->assertTrue(is_writable(WRITEPATH), 'Writable directory should be writable');
    }

    public function testEnvironmentIsSet(): void
    {
        $this->assertNotEmpty(ENVIRONMENT, 'Environment should be set');
    }

    public function testBasicMath(): void
    {
        $this->assertEquals(4, 2 + 2, 'Basic math should work');
    }

    public function testPHPVersion(): void
    {
        $this->assertGreaterThanOrEqual('8.0', PHP_VERSION, 'PHP version should be 8.0 or higher');
    }

    public function testCodeIgniterFrameworkExists(): void
    {
        $this->assertTrue(class_exists('CodeIgniter\CodeIgniter'), 'CodeIgniter framework should be loaded');
    }
}
