<?php

declare(strict_types=1);

namespace App\Actions\Products;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Meilisearch\Endpoints\Indexes;

class IndexAction
{
    public function getProducts(string $searchTerm, Collection $filters): LengthAwarePaginator
    {
        return Product::search($searchTerm,

            function (Indexes $meiliSearch, string $query, array $options) use ($filters) {

                $filterParts = [];

                if ($filters->has('categories')) {
                    $filterParts[] = $this->buildCategoryFilter($filters->get('categories'));
                }

                if ($filters->has('price')) {
                    $filterParts[] = $this->buildPriceFilter((int) $filters->get('price'));
                }

                if ($filters->has('color')) {
                    $filterParts[] = $this->buildColorFilter((string) $filters->get('color'));
                }

                if ($filters->has('on_sale')) {
                    $filterParts[] = $this->buildOnSaleFilter((string) $filters->get('on_sale'));
                }

                if ($filterParts !== []) {
                    $options['filter'] = implode(' AND ', $filterParts);
                }

                return $meiliSearch->search($query, $options);
            })
            ->orderBy('name')
            ->paginateRaw(50);
    }

    public function buildCategoryFilter(array $categories): string
    {
        $categoryFilters = [];
        foreach ($categories as $category) {
            $categoryFilters[] = "categories IN [\"{$category}\"]";
        }

        return '('.implode(' AND ', $categoryFilters).')';

    }

    public function buildPriceFilter(int $maxPrice): string
    {
        return "((on_sale = true AND sale_price <= {$maxPrice}) OR (on_sale = false AND price <= {$maxPrice}))";
    }

    public function buildColorFilter(string $color): string
    {
        return "(color = \"$color\")";
    }

    public function buildOnSaleFilter(string|bool|int $onSale): string
    {
        $bool = filter_var($onSale, FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false';

        return "(on_sale = $bool)";
    }
}
