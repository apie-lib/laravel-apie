<?php

use Apie\LaravelApie\Wrappers\Routing\ApieRouteLoader;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\Route;

$group = Route::withoutMiddleware(SubstituteBindings::class);
resolve(ApieRouteLoader::class)->loadRoutes($group);
