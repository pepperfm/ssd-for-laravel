<?php

declare(strict_types=1);

namespace Pepperfm\Ssd\Tests\Fixtures;

use Illuminate\Support\Collection;
use Pepperfm\Ssd\Attributes\ToIterable;
use Pepperfm\Ssd\BaseDto;

class ResponseWrapperWithDataCollectionDto extends BaseDto
{
    #[ToIterable(DataDto::class, \Illuminate\Database\Eloquent\Collection::class)]
    public Collection $data;

    public LinksDto $links;

    public MetaDto $meta;
}
