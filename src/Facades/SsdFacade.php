<?php

declare(strict_types=1);

namespace Pepperfm\Ssd\Facades;

use Illuminate\Support\Facades\Facade;
use Pepperfm\Ssd\BaseDto;

class SsdFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return BaseDto::class;
    }
}
