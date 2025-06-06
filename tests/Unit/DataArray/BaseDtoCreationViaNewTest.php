<?php

use Pepperfm\Ssd\Tests\Fixtures\ResponseWrapperWithDataArrayDto;
use Pepperfm\Ssd\Tests\Fixtures\LinksDto;
use Pepperfm\Ssd\Tests\Fixtures\MetaDto;
use Pepperfm\Ssd\Tests\Fixtures\DataDto;

test('object creation 123123', function ($testResponse) {
    $data = new ResponseWrapperWithDataArrayDto($testResponse);
    expect($data)->toBeInstanceOf(ResponseWrapperWithDataArrayDto::class)
        ->and($data->data)->toBeArray()
        ->and($data->data[0])->toBeInstanceOf(DataDto::class)
        ->and($data->links)->toBeInstanceOf(LinksDto::class)
        ->and($data->meta)->toBeInstanceOf(MetaDto::class);
})->with(function () {
    $testResponse = [
        'data' => [
            [
                "id" => fake()->uuid(),
                "name" => fake()->name(),
                "username" => fake()->username(),
                "message" => fake()->text(),
                "amount" => fake()->numberBetween(1, 100),
                "currency" => fake()->currencyCode(),
                "created_at" => fake()->dateTime()->format('Y-m-d H:i:s'),
            ],
            [
                "id" => fake()->uuid(),
                "name" => fake()->name(),
                "username" => fake()->username(),
                "message" => fake()->text(),
                "amount" => fake()->numberBetween(1, 100),
                "currency" => fake()->currencyCode(),
                "created_at" => fake()->dateTime()->format('Y-m-d H:i:s'),
            ],
        ],
        'links' => [
            "first" => "https://www.donationalerts.com/api/v1/alerts/donations?page=1",
            "last" => "https://www.donationalerts.com/api/v1/alerts/donations?page=1",
            "prev" => null,
            "next" => null
        ],
        'meta' => [
            "current_page" => 1,
            "from" => 1,
            "last_page" => 1,
            "path" => "https://www.donationalerts.com/api/v1/alerts/donations",
            "per_page" => 30,
            "to" => 1,
            "total" => 1
        ],
    ];

    return [
        'object of std class' => fn() => literal(
            data: $testResponse['data'],
            links: $testResponse['links'],
            meta: $testResponse['meta'],
        ),
        'array' => fn() => [
            'data' => $testResponse['data'],
            'links' => $testResponse['links'],
            'meta' => $testResponse['meta'],
        ]
    ];
});

test('object creation unwrapped', function ($testResponse) {
    $data = new ResponseWrapperWithDataArrayDto(
        data: data_get($testResponse, 'data'),
        links: data_get($testResponse, 'links'),
        meta: data_get($testResponse, 'meta'),
    );
    expect($data)->toBeInstanceOf(ResponseWrapperWithDataArrayDto::class)
        ->and($data->data)->toBeArray()
        ->and($data->data[0])->toBeInstanceOf(DataDto::class)
        ->and($data->links)->toBeInstanceOf(LinksDto::class)
        ->and($data->meta)->toBeInstanceOf(MetaDto::class);
})->with(function () {
    $testResponse = [
        'data' => [
            [
                "id" => fake()->uuid(),
                "name" => fake()->name(),
                "username" => fake()->username(),
                "message" => fake()->text(),
                "amount" => fake()->numberBetween(1, 100),
                "currency" => fake()->currencyCode(),
                "created_at" => fake()->dateTime()->format('Y-m-d H:i:s'),
            ],
            [
                "id" => fake()->uuid(),
                "name" => fake()->name(),
                "username" => fake()->username(),
                "message" => fake()->text(),
                "amount" => fake()->numberBetween(1, 100),
                "currency" => fake()->currencyCode(),
                "created_at" => fake()->dateTime()->format('Y-m-d H:i:s'),
            ],
        ],
        'links' => [
            "first" => "https://www.donationalerts.com/api/v1/alerts/donations?page=1",
            "last" => "https://www.donationalerts.com/api/v1/alerts/donations?page=1",
            "prev" => null,
            "next" => null
        ],
        'meta' => [
            "current_page" => 1,
            "from" => 1,
            "last_page" => 1,
            "path" => "https://www.donationalerts.com/api/v1/alerts/donations",
            "per_page" => 30,
            "to" => 1,
            "total" => 1
        ],
    ];

    return [
        'object of std class' => fn() => literal(
            data: $testResponse['data'],
            links: $testResponse['links'],
            meta: $testResponse['meta'],
        ),
        'array' => fn() => [
            'data' => $testResponse['data'],
            'links' => $testResponse['links'],
            'meta' => $testResponse['meta'],
        ]
    ];
});
