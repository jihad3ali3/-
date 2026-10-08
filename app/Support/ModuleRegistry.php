<?php

namespace App\Support;

use Illuminate\Support\ServiceProvider;
use RuntimeException;

/**
 * Turns the enabled module list in config/modules.php into service providers.
 *
 * It is referenced from bootstrap/providers.php, which runs before the
 * configuration repository exists, so the file is read directly.
 */
final class ModuleRegistry
{
    /**
     * @return list<class-string<ServiceProvider>>
     */
    public static function providers(): array
    {
        $config = require base_path('config/modules.php');

        $providers = [];

        foreach ($config['enabled'] ?? [] as $module) {
            if (! preg_match('/^[A-Z][A-Za-z0-9]*$/', $module)) {
                throw new RuntimeException("Invalid module name [{$module}] in config/modules.php.");
            }

            $provider = "Modules\\{$module}\\Providers\\{$module}ServiceProvider";

            if (! class_exists($provider)) {
                throw new RuntimeException("Module [{$module}] is enabled but [{$provider}] does not exist.");
            }

            $providers[] = $provider;
        }

        return $providers;
    }
}
