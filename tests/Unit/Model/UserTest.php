<?php

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

test('to array', function () {

    $instance = App\Models\User::factory()->create()->refresh();

    expect(array_keys($instance->toArray()))
        ->toBe([
            'id',
            'name',
            'email',
            'email_verified_at',
            'created_at',
            'updated_at',
        ]);
});
