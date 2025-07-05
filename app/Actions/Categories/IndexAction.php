<?php

declare(strict_types=1);

namespace App\Actions\Categories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;

class IndexAction
{
    public function getCategories(): Collection
    {
        return Category::query()
            ->select([
                'name',
                'slug',
            ])
            ->orderBy('name')
            ->get();
    }
}
