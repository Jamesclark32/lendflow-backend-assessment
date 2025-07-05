<?php

declare(strict_types=1);

namespace App\Actions\Products;

use App\Models\Product;
use Illuminate\Support\Collection;
use Meilisearch\Endpoints\Indexes;

class IndexAction
{
    public function getProducts(string $searchTerm, Collection $filters): Collection
    {
        return Product::search($searchTerm,

            function (Indexes $meiliSearch, string $query, array $options) use ($filters) {

                $options['limit'] = 1000; // override meilisearch default of 20

                $filterParts = [];

                if ($filters->has('categories')) {
                    $categoryFilters = [];
                    foreach ($filters->get('categories') as $category) {
                        $categoryFilters[] = "categories IN [\"{$category}\"]";
                    }
                    $filterParts[] = '('.implode(' AND ', $categoryFilters).')';
                }

                if ($filters->has('price')) {
                    $maxPrice = $filters->get('price');
                    $priceFilter = "(on_sale = true AND sale_price <= {$maxPrice}) OR (on_sale = false AND price <= {$maxPrice})";
                    $filterParts[] = "({$priceFilter})";
                }

                if ($filters->has('color')) {
                    $colorFilter = "(color =  {$filters->get('color')})";
                    $filterParts[] = "({$colorFilter})";
                }

                if ($filters->has('on_sale')) {
                    $onSale = "(on_sale =  {$filters->get('on_sale')})";
                    $filterParts[] = "({$onSale})";
                }

                if ($filterParts !== []) {
                    $options['filter'] = implode(' AND ', $filterParts);
                }

                return $meiliSearch->search($query, $options);
            })
            ->get()
            ->load('categories:id,slug')
            ->map(function (Product $item): array {
                return [
                    'name' => $item->name,
                    'slug' => $item->slug,
                    'price' => $item->price,
                    'on_sale' => $item->on_sale,
                    'sale_price' => $item->sale_price,
                    'color' => $item->color,
                    'upc' => $item->upc,
                    'categories' => $item->categories->pluck('slug')->toArray(),
                ];
            });
    }
}
