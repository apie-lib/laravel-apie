<?php
namespace Apie\LaravelApie\Blade;

use Illuminate\Support\Facades\Facade;

class ApieRender extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ApieBladeRenderFunctions::class;
    }
}
