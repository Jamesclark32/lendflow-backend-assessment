<?php

declare(strict_types=1);

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

test('to array', function () {

    $instance = App\Models\Category::factory()->create()->refresh();

    expect(array_keys($instance->toArray()))
        ->toBe([
            'id',
            'name',
            'slug',
            'created_at',
            'updated_at',
        ]);
});

test('has expected slug key', function () {
    $instance = App\Models\Category::factory()->create()->refresh();
    \PHPUnit\Framework\assertSame($instance->getRouteKeyName(), 'slug');
});
