<?php

declare(strict_types=1);

namespace Tests\Feature\Controllers\Api\Categories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class IndexControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_200(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->get(route('api.categories.index'));

        $response->assertStatus(200);
    }

    public function test_returns_expected_data(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $categories = Category::factory()->count(3)->create();

        $response = $this->get(route('api.categories.index'));

        $response->assertStatus(200)
            ->assertJsonCount(3, 'categories')
            ->assertJsonStructure([
                'categories' => [],
            ]);

        foreach ($categories as $category) {
            $response->assertJsonFragment(['name' => $category->name]);
        }
    }

    public function test_returns_redirect_if_not_authenticated(): void
    {
        $response = $this->get(route('api.categories.index'));
        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }
}
