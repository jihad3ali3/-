<?php

use App\Providers\AppServiceProvider;
use App\Support\ModuleRegistry;

return [
    AppServiceProvider::class,

    // Service providers of every module listed in config/modules.php.
    ...ModuleRegistry::providers(),
];
