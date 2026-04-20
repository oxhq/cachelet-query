<?php

namespace Oxhq\Cachelet\Query\Facades;

use Illuminate\Support\Facades\Facade;

class CacheletQuery extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'cachelet.query';
    }
}
