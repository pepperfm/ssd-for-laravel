<?php

declare(strict_types=1);

namespace Pepperfm\Ssd\Tests\Fixtures;

use Pepperfm\Ssd\BaseDto;

class DataDto extends BaseDto
{
    public string $id = '30530030';

    public string $name = 'donation';

    public string $username = 'Ivan';

    public string $message = 'Hello!';

    public int $amount= 500;

    public string $currency = 'RUB';

    public string $createdAt = '2019-09-29 09:00:00';
}
