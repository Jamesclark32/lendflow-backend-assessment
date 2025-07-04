<?php

declare(strict_types=1);

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

test('to array', function () {

    $instance = App\Models\Product::factory()->create()->refresh();

    expect(array_keys($instance->toArray()))
        ->toBe([
            'id',
            'name',
            'slug',
            'price',
            'on_sale',
            'sale_price',
            'color',
            'upc',
            'created_at',
            'updated_at',
        ]);
});

test('has expected slug key', function () {
    $instance = App\Models\Product::factory()->create()->refresh();
    \PHPUnit\Framework\assertSame($instance->getRouteKeyName(), 'slug');
});
