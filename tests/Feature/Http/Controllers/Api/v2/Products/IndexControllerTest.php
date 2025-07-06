<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api\v2\Products;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Sequence;
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
    }

    public function test_returns_v2_response_header_on_first_call(): void
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

    public function test_returns_v2_response_header_after_call_to_v2_is_cached(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->get(route('api.products.index'), [
            'x-api-version' => '1',
        ]);

        $response->assertStatus(200);
        $response->assertHeader('x-api-version', '1');

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

    public function test_returns_expected_data(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $category = Category::factory()->create([
            'name' => 'testing-category',
        ]);

        $products = Product::factory()->count(3)->create();

        $category->products()->attach($products);

        // reindex Meilisearch
        $products->each->searchable();
        sleep(2);

        $queryString = http_build_query([
            'categories' => ['testing-category'],
        ]);

        $response = $this->get(route('api.products.index').'?'.$queryString, [
            'x-api-version' => '2',
        ]);

        $response->assertStatus(200)
            ->assertJsonCount(3, 'products')
            ->assertJsonStructure([
                'products' => [],
            ]);

        foreach ($products as $product) {
            $response->assertJsonFragment(['name' => $product->name]);
        }
    }

    public function test_returns_redirect_if_not_authenticated(): void
    {
        $response = $this->get(route('api.products.index'), [
            'x-api-version' => '2',
        ]);
        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    public function test_search_parameter_returns_expected_results(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $category = Category::factory()->create([
            'name' => 'testing-category',
        ]);

        $products = Product::factory()->count(3)->create();

        $category->products()->attach($products);

        $product = Product::factory()->create([
            'name' => 'testing-search',
        ]);

        $category->products()->attach($product);
        // reindex Meilisearch
        $products->each->searchable();
        $product->searchable();
        sleep(2);

        $queryString = http_build_query([
            'search' => 'testing-search',
            'categories' => ['testing-category'],
        ]);

        $response = $this->get(route('api.products.index').'?'.$queryString, [
            'x-api-version' => '2',
        ]);

        $response->assertStatus(200)
            ->assertJsonCount(1, 'products')
            ->assertJsonStructure([
                'products' => [],
            ])
            ->assertJsonFragment(['name' => $product->name]);

        foreach ($products as $product) {
            $response->assertDontSeeText(['name' => $product->name]);
        }
    }

    public function test_category_filter_returns_expected_results(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $products = Product::factory()->count(3)->create();

        $category = Category::factory()->create([
            'name' => 'testing-category',
        ]);

        $product = Product::factory()->create();

        $product->categories()->attach($category);

        // reindex Meilisearch
        $products->each->searchable();
        $product->searchable();
        sleep(2);

        $queryString = http_build_query([
            'categories' => ['testing-category'],
        ]);

        $response = $this->get(route('api.products.index').'?'.$queryString, [
            'x-api-version' => '2',
        ]);

        $response->assertStatus(200)
            ->assertJsonCount(1, 'products')
            ->assertJsonStructure([
                'products' => [],
            ])
            ->assertJsonFragment(['name' => $product->name]);

        foreach ($products as $product) {
            $response->assertDontSeeText(['name' => $product->name]);
        }
    }

    public function test_category_uses_an_d_logic(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $products = Product::factory()->count(3)->create();

        $categoryOne = Category::factory()->create([
            'name' => 'testing-category-one',
        ]);

        $categoryTwo = Category::factory()->create([
            'name' => 'testing-category-two',
        ]);

        $productOne = Product::factory()->create();
        $productOne->categories()->attach([$categoryOne, $categoryTwo]);

        $productTwo = Product::factory()->create();
        $productTwo->categories()->attach($categoryOne);

        $productThree = Product::factory()->create();
        $productThree->categories()->attach($categoryTwo);

        // reindex Meilisearch
        $products->each->searchable();
        $productOne->searchable();
        $productTwo->searchable();
        $productThree->searchable();
        sleep(2);

        $queryString = http_build_query([
            'categories' => ['testing-category-one', 'testing-category-two'],
        ]);

        $response = $this->get(route('api.products.index').'?'.$queryString, [
            'x-api-version' => '2',
        ]);

        $response->assertStatus(200)
            ->assertJsonCount(1, 'products')
            ->assertJsonStructure([
                'products' => [],
            ])
            ->assertJsonFragment(['name' => $productOne->name])
            ->assertDontSeeText(['name' => $productTwo->name])
            ->assertDontSeeText(['name' => $productThree->name]);

        foreach ($products as $product) {
            $response->assertDontSeeText(['name' => $product->name]);
        }
    }

    public function test_price_filter_returns_expected_results(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $category = Category::factory()->create([
            'name' => 'testing-category',
        ]);

        $products = Product::factory()->count(3)->create([
            'price' => 3499,
            'on_sale' => false,
        ]);

        $product = Product::factory()->create([
            'price' => 2499,
            'on_sale' => false,
        ]);

        $category->products()->attach($products);
        $category->products()->attach($product);

        // reindex Meilisearch
        $products->each->searchable();
        $product->searchable();
        sleep(2);

        $queryString = http_build_query([
            'price' => 2500,
            'categories' => ['testing-category'],
        ]);

        $response = $this->get(route('api.products.index').'?'.$queryString, [
            'x-api-version' => '2',
        ]);

        $response->assertStatus(200)
            ->assertJsonCount(1, 'products')
            ->assertJsonStructure([
                'products' => [],
            ])
            ->assertJsonFragment(['name' => $product->name]);

        foreach ($products as $product) {
            $response->assertDontSeeText(['name' => $product->name]);
        }

        // Also check with query which would include all 4
        $queryString = http_build_query([
            'price' => 3500,
            'categories' => ['testing-category'],
        ]);

        $response = $this->get(route('api.products.index').'?'.$queryString, [
            'x-api-version' => '2',
        ]);

        $response->assertStatus(200)
            ->assertJsonCount(4, 'products')
            ->assertJsonStructure([
                'products' => [],
            ])
            ->assertJsonFragment(['name' => $product->name]);

        foreach ($products as $product) {
            $response->assertJsonFragment(['name' => $product->name]);
        }
    }

    public function test_price_filter_refers_sale_price_when_on_sale(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $category = Category::factory()->create([
            'name' => 'testing-category',
        ]);

        $products = Product::factory()->count(3)->create([
            'price' => 3499,
            'on_sale' => true,
            'sale_price' => 3449,
        ]);

        $product = Product::factory()->create([
            'price' => 2499,
            'on_sale' => true,
            'sale_price' => 1449,
        ]);

        $category->products()->attach($products);
        $category->products()->attach($product);

        // reindex Meilisearch
        $products->each->searchable();
        $product->searchable();
        sleep(2);

        $queryString = http_build_query([
            'price' => 1450,
            'categories' => ['testing-category'],
        ]);

        $response = $this->get(route('api.products.index').'?'.$queryString, [
            'x-api-version' => '2',
        ]);

        $response->assertStatus(200)
            ->assertJsonCount(1, 'products')
            ->assertJsonStructure([
                'products' => [],
            ])
            ->assertJsonFragment(['name' => $product->name]);

        foreach ($products as $product) {
            $response->assertDontSeeText(['name' => $product->name]);
        }

        // Also check with query which would include all 4
        $queryString = http_build_query([
            'price' => 3450,
            'categories' => ['testing-category'],
        ]);

        $response = $this->get(route('api.products.index').'?'.$queryString, [
            'x-api-version' => '2',
        ]);

        $response->assertStatus(200)
            ->assertJsonCount(4, 'products')
            ->assertJsonStructure([
                'products' => [],
            ])
            ->assertJsonFragment(['name' => $product->name]);

        foreach ($products as $product) {
            $response->assertJsonFragment(['name' => $product->name]);
        }
    }

    public function test_color_filter_returns_expected_results(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $category = Category::factory()->create([
            'name' => 'testing-category',
        ]);

        $products = Product::factory()->count(6)
            ->state(new Sequence(
                ['color' => 'red'],
                ['color' => 'green'],
                ['color' => 'blue'],
            ))
            ->create();

        $product = Product::factory()->create([
            'color' => 'orange',
        ]);

        $category->products()->attach($products);
        $category->products()->attach($product);

        // reindex Meilisearch
        $products->each->searchable();
        $product->searchable();
        sleep(2);

        $queryString = http_build_query([
            'color' => 'orange',
            'categories' => ['testing-category'],
        ]);

        $response = $this->get(route('api.products.index').'?'.$queryString, [
            'x-api-version' => '2',
        ]);

        $response->assertStatus(200)
            ->assertJsonCount(1, 'products')
            ->assertJsonStructure([
                'products' => [],
            ])
            ->assertJsonFragment(['name' => $product->name]);

        foreach ($products as $product) {
            $response->assertDontSeeText(['name' => $product->name]);
        }
    }

    public function test_on_sale_filter_returns_expected_results(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $category = Category::factory()->create([
            'name' => 'testing-category',
        ]);

        $products = Product::factory()->count(6)
            ->create([
                'on_sale' => false,
            ]);

        $product = Product::factory()->create([
            'on_sale' => true,
        ]);

        $category->products()->attach($products);
        $category->products()->attach($product);

        // reindex Meilisearch
        $products->each->searchable();
        $product->searchable();
        sleep(2);

        $queryString = http_build_query([
            'on_sale' => 'true',
            'categories' => ['testing-category'],
        ]);

        $response = $this->get(route('api.products.index').'?'.$queryString, [
            'x-api-version' => '2',
        ]);

        $response->assertStatus(200)
            ->assertJsonCount(1, 'products')
            ->assertJsonStructure([
                'products' => [],
            ])
            ->assertJsonFragment(['name' => $product->name]);

        foreach ($products as $product) {
            $response->assertDontSeeText(['name' => $product->name]);
        }
    }

    public function test_returns_422_for_invalid_search_or_filter(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $queryString = http_build_query([
            'on_sale' => 'orange',
        ]);

        $response = $this->get(route('api.products.index').'?'.$queryString, [
            'x-api-version' => '2',
        ]);

        $response->assertStatus(422);
    }

    public function test_filters_join_correctly_with_an_d_logic(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $category = Category::factory()->create([
            'name' => 'testing-category',
        ]);

        $products = Product::factory()->count(6)
            ->create([
                'on_sale' => false,
                'color' => 'red',
                'price' => 3500,
            ]);

        $product = Product::factory()->create([
            'on_sale' => true,
            'color' => 'orange',
            'price' => 3500,
            'sale_price' => 2000,
        ]);

        $category->products()->attach($products);
        $category->products()->attach($product);

        // reindex Meilisearch
        $products->each->searchable();
        $product->searchable();
        sleep(2);

        $queryString = http_build_query([
            'on_sale' => true,
            'color' => 'orange',
            'price' => 2005,
            'categories' => ['testing-category'],
        ]);

        $response = $this->get(route('api.products.index').'?'.$queryString, [
            'x-api-version' => '2',
        ]);

        $response->assertStatus(200)
            ->assertJsonCount(1, 'products')
            ->assertJsonStructure([
                'products' => [],
            ])
            ->assertJsonFragment(['name' => $product->name]);

        foreach ($products as $product) {
            $response->assertDontSeeText(['name' => $product->name]);
        }
    }

    public function test_categories_is_required_for_api_v1(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $products = Product::factory()->count(3)->create();

        // reindex Meilisearch
        $products->each->searchable();
        sleep(2);

        $response = $this->get(route('api.products.index'), [
            'x-api-version' => '2',
        ]);

        $response->assertStatus(422)
            ->assertJsonFragment(['errors' => ['categories' => ['The categories field is required.']]]);
    }
}
