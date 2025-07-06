<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Middleware;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiVersionMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_correctly_direct_v1(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->get(route('api.products.index'), [
            'x-api-version' => '1',
        ]);

        $response->assertStatus(200);
        $response->assertHeader('x-api-version', '1');
    }

    public function test_correctly_direct_v2(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $category = Category::factory()->create([
            'name' => 'testing-category',
        ]);

        $queryString = http_build_query([
            'categories' => ['testing-category'],
        ]);

        $response = $this->get(route('api.products.index').'?'.$queryString, [
            'x-api-version' => '2',
        ]);

        $response->assertStatus(200);
        $response->assertHeader('x-api-version', '2');
    }

    public function test_correctly_errors_for_v3(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->get(route('api.products.index'), [
            'x-api-version' => '3',
        ]);

        $response->assertStatus(400);
    }
}
