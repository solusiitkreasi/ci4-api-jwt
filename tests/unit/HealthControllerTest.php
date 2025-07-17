<?php

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * Health Controller Feature Tests
 * @internal
 */
final class HealthControllerTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    public function testHealthEndpointExists(): void
    {
        $result = $this->get('/api/health');
        
        // Should not be 404
        $this->assertNotEquals(404, $result->getStatusCode());
    }

    public function testPingEndpointExists(): void
    {
        $result = $this->get('/api/ping');
        
        // Should not be 404
        $this->assertNotEquals(404, $result->getStatusCode());
    }

    public function testPingEndpointReturnsJson(): void
    {
        $result = $this->get('/api/ping');
        
        // Skip if endpoint doesn't exist yet
        if ($result->getStatusCode() === 404) {
            $this->markTestSkipped('Ping endpoint not implemented yet');
        }
        
        $this->assertEquals('application/json', $result->getHeaderLine('Content-Type'));
    }

    public function testHealthEndpointReturnsJson(): void
    {
        $result = $this->get('/api/health');
        
        // Skip if endpoint doesn't exist yet
        if ($result->getStatusCode() === 404) {
            $this->markTestSkipped('Health endpoint not implemented yet');
        }
        
        $this->assertEquals('application/json', $result->getHeaderLine('Content-Type'));
    }
}
