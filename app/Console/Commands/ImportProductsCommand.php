<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Pivot\CategoryProduct;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Str;

class ImportProductsCommand extends Command
{
    protected $signature = 'db:import';

    protected $description = 'Imports products and categories from the JSON file located at database/import.json';

    protected ?Carbon $now = null;

    protected Collection $categories;

    protected Collection $products;

    public function handle(): int
    {
        $this->now = now();

        $importData = File::get(base_path('database/import.json'));

        if (!$importData) {
            $this->error('Error reading source file');

            return self::FAILURE;
        }

        $importData = json_decode($importData, true);
        $importData = data_get($importData, 'products');

        if (!is_array($importData)) {
            $this->error('Invalid or missing products data in import.json');

            return self::FAILURE;
        }

        $importData = collect($importData);

        $this->info('Processing categories...');
        $this->storeCategories($importData);
        $this->categories = Category::get();

        $this->info('Processing products...');
        $this->storeProducts($importData);
        $this->products = Product::get();

        $this->info('Processing relationships...');
        $this->storeCategoryProductRelationships($importData);

        $this->info('Updating search index...');
        Artisan::call('scout:import', ['model' => Product::class]);

        $this->info('Clearing response cache...');
        Artisan::call('responsecache:clear');

        $this->info('Import completed');

        return self::SUCCESS;
    }

    protected function storeCategories(Collection $importData): void
    {
        $categories = $importData
            ->pluck('categories')
            ->flatten()
            ->unique()
            ->values()
            ->map(function (string $name): array {
                return [
                    'name' => $name,
                    'slug' => Str::slug($name),
                    'created_at' => $this->now,
                    'updated_at' => $this->now,
                ];
            })->toArray();

        Category::InsertOrIgnore($categories);
    }

    protected function storeProducts(Collection $importData): void
    {
        $products = $importData
            ->map(function (array $product): array {

                $name = data_get($product, 'name');

                return [
                    'name' => $name,
                    'slug' => Str::slug($name),
                    'price' => data_get($product, 'price'),
                    'on_sale' => data_get($product, 'on_sale'),
                    'sale_price' => data_get($product, 'sale_price'),
                    'color' => data_get($product, 'color'),
                    'upc' => data_get($product, 'upc'),
                    'created_at' => $this->now,
                    'updated_at' => $this->now,
                ];
            })->toArray();

        Product::InsertOrIgnore($products);
    }

    protected function storeCategoryProductRelationships(Collection $importData): void
    {
        $categoryProducts = $importData
            ->map(function (array $product) {
                $results = [];

                $categories = data_get($product, 'categories');

                foreach ($categories as $category) {

                    $productId = $this->products
                        ->where('upc', '=', data_get($product, 'upc'))
                        ->first()
                        ->id;

                    $categoryId = $this->categories
                        ->where('name', '=', $category)
                        ->first()
                        ->id;

                    if ($productId && $categoryId) {
                        $results[] = [
                            'product_id' => $productId,
                            'category_id' => $categoryId,
                            'created_at' => $this->now,
                            'updated_at' => $this->now,
                        ];
                    }
                }

                return $results;
            })
            ->flatten(1)
            ->toArray();

        CategoryProduct::InsertOrIgnore($categoryProducts);
    }
}
