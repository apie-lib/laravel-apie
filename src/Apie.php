<?php
namespace Apie\LaravelApie;

use Illuminate\Support\Facades\Facade;

class Apie extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'apie';
    }
}