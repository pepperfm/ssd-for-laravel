<?php

declare(strict_types=1);

namespace Pepperfm\Ssd\Tests\Fixtures;

use Pepperfm\Ssd\BaseDto;

class MetaDto extends BaseDto
{
    public int $currentPage;

    public ?int $from;

    public int $lastPage;

    /** @var array{array{array-key, url: string, label: string, active: bool}} $links */
    public array $links;

    public string $path;

    public int $perPage;

    public ?int $to;

    public int $total;
}
