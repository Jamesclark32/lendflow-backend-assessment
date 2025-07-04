<?php

declare(strict_types=1);

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

test('to array', function () {

    $category = App\Models\Category::factory()->create();
    $product = App\Models\Product::factory()->create();

    $instance = App\Models\Pivot\CategoryProduct::create([
        'product_id' => $product->id,
        'category_id' => $category->id,
    ]);

    expect(array_keys($instance->toArray()))
        ->toBe([
            'product_id',
            'category_id',
            'updated_at',
            'created_at',
        ]);
});
