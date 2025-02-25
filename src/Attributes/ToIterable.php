<?php

declare(strict_types=1);

namespace Pepperfm\Ssd\Attributes;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class ToIterable
{
    public function __construct(
        public string $type,
        public ?string $castType = null,
    ) {
    }
}
