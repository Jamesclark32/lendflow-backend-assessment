<?php

declare(strict_types=1);

namespace App\Actions\Categories;

use App\Models\Category;
use Illuminate\Pagination\LengthAwarePaginator;

class IndexAction
{
    public function getCategories(): LengthAwarePaginator
    {
        return Category::query()
            ->select([
                'id',
                'name',
                'slug',
            ])
            ->orderBy('name')
            ->paginate(50);
    }
}
