<?php

declare(strict_types=1);

namespace Pepperfm\Ssd\Attributes;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class MapName
{
    public function __construct(
        public string $name,
    ) {
    }
}
