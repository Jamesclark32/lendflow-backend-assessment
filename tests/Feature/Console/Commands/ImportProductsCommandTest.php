<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Commands;

use App\Console\Commands\ImportProductsCommand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ImportProductsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_success_exit_code(): void
    {
        $this->mockData();

        $this->artisan('db:import')
            ->assertExitCode(ImportProductsCommand::SUCCESS);
    }

    public function test_imports_categories(): void
    {
        $this->mockData();

        $this->assertDatabaseMissing('categories',
            ['name' => 'shirts'],
        );

        $this->assertDatabaseCount('categories', 0);

        $this->artisan('db:import')
            ->assertExitCode(ImportProductsCommand::SUCCESS);

        $this->assertDatabaseHas('categories',
            ['name' => 'shirts'],
        );

        $this->assertDatabaseCount('categories', 3);
    }

    public function test_imports_products(): void
    {
        $this->mockData();

        $this->assertDatabaseMissing('products',
            ['name' => 'Women\'s Tunic'],
        );

        $this->assertDatabaseCount('products', 0);

        $this->artisan('db:import')
            ->assertExitCode(ImportProductsCommand::SUCCESS);

        $this->assertDatabaseHas('products',
            ['name' => 'Women\'s Tunic'],
        );

        $this->assertDatabaseCount('products', 2);
    }

    public function test_imports_category_to_product_relationships(): void
    {
        $this->mockData();

        $this->assertDatabaseCount('category_product', 0);

        $this->artisan('db:import')
            ->assertExitCode(ImportProductsCommand::SUCCESS);

        $this->assertDatabaseHas('category_product',
            [
                'category_id' => Category::where('name', '=', 'shirts')->first()->id,
                'product_id' => Product::where('name', '=', 'Women\'s Tunic')->first()->id,
            ],
        );

        $this->assertDatabaseCount('category_product', 4);
    }

    public function test_handles_empty_data_gracefully(): void
    {
        File::shouldReceive('get')
            ->once()
            ->with(base_path('database/import.json'))
            ->andReturn('');

        $this->artisan('db:import')
            ->assertExitCode(ImportProductsCommand::FAILURE);
    }

    public function test_handles_missing_products_array_gracefully(): void
    {
        File::shouldReceive('get')
            ->once()
            ->with(base_path('database/import.json'))
            ->andReturn(json_encode(['fruits' => ['apples', 'bananas', 'oranges', 'pears']]));

        $this->artisan('db:import')
            ->assertExitCode(ImportProductsCommand::FAILURE);
    }

    protected function mockData(): void
    {
        File::shouldReceive('get')
            ->once()
            ->with(base_path('database/import.json'))
            ->andReturn(json_encode(['products' => [
                [
                    'name' => 'Women\'s Tunic',
                    'categories' => ['shirts', 'women'],
                    'price' => 3999,
                    'on_sale' => false,
                    'sale_price' => 2999,
                    'color' => 'red',
                    'upc' => '012345679061',
                ],
                [
                    'name' => 'Men\'s Classic T-Shirt',
                    'categories' => ['shirts', 'men'],
                    'price' => 1999,
                    'on_sale' => false,
                    'sale_price' => 1499,
                    'color' => 'black',
                    'upc' => '012345678901',
                ],
            ]]));
    }
}
