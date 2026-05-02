<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;

abstract class ServiceTestCase extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Set up Laravel application context for facades
        $this->app->make('config');
        $this->app->make('db');
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    protected function mockRepository(string $repositoryClass)
    {
        return Mockery::mock($repositoryClass);
    }

    protected function createMockModel(string $modelClass, array $attributes = [])
    {
        $mock = Mockery::mock($modelClass);
        
        foreach ($attributes as $key => $value) {
            $mock->$key = $value;
        }
        
        return $mock;
    }

    protected function mockRepositoryMethod($mock, string $method, $return = null)
    {
        return $mock->shouldReceive($method)->andReturn($return);
    }
}
