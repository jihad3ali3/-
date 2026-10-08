<?php

namespace Tests\Unit;

use App\Support\ModuleRegistry;
use Illuminate\Support\ServiceProvider;
use Tests\TestCase;

class ModuleRegistryTest extends TestCase
{
    public function test_every_enabled_module_resolves_to_a_service_provider(): void
    {
        $providers = ModuleRegistry::providers();

        $this->assertCount(count(config('modules.enabled')), $providers);

        foreach ($providers as $provider) {
            $this->assertTrue(is_subclass_of($provider, ServiceProvider::class), "{$provider} is not a service provider.");
        }
    }
}
