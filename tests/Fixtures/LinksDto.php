<?php

declare(strict_types=1);

namespace Pepperfm\Ssd\Tests\Fixtures;

use Pepperfm\Ssd\BaseDto;

class LinksDto extends BaseDto
{
    public string $first;

    public string $last;

    public ?string $prev = null;

    public ?string $next = null;
}
