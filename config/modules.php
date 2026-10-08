<?php

/*
|--------------------------------------------------------------------------
| Modules
|--------------------------------------------------------------------------
|
| Every business capability lives in its own module under Modules/<Name>.
| A module is only loaded when it is listed in "enabled". Each enabled
| module must provide Modules\<Name>\Providers\<Name>ServiceProvider.
|
| Remove a name from this list to switch a module off without deleting it.
|
*/

return [
    'enabled' => [
        'Catalog',
        'Orders',
    ],
];
