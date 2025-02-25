<?php

declare(strict_types=1);

namespace Pepperfm\Ssd\Tests\Fixtures;

use Pepperfm\Ssd\Attributes\ToIterable;
use Pepperfm\Ssd\BaseDto;

class ResponseWrapperWithDataArrayDto extends BaseDto
{
    #[ToIterable(DataDto::class)]
    public array $data;

    public LinksDto $links;

    public MetaDto $meta;
}
