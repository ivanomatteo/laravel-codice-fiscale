<?php

namespace IvanoMatteo\LaravelCodiceFiscale\Tests;

use Orchestra\Testbench\TestCase;
use IvanoMatteo\LaravelCodiceFiscale\LaravelCodiceFiscaleServiceProvider;

class ExampleTest extends TestCase
{

    protected function getPackageProviders($app)
    {
        return [LaravelCodiceFiscaleServiceProvider::class];
    }
    
    /** @test */
    public function true_is_true()
    {
        $this->assertTrue(true);
    }
}
