<?php

use Illuminate\Contracts\Support\Arrayable;
use Pepperfm\Ssd\BaseDto;

arch('debug')
    ->expect(['dd', 'dump', 'env', 'ray'])
    ->each->not->toBeUsed();

test('base dto')
    ->expect(BaseDto::class)->toBeAbstract()
    ->and(BaseDto::class)->toImplement(Arrayable::class)
    ->and(BaseDto::class)->toImplement(\JsonSerializable::class);
