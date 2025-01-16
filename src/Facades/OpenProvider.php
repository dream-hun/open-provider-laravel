<?php

namespace jacktalkc\LaravelOpenProvider\Facades;

use Illuminate\Support\Facades\Facade;

class OpenProvider extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'openprovider';
    }
}