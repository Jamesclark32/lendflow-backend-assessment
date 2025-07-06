<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasDefaultSlug;
use App\Policies\ProductPolicy;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Laravel\Scout\Searchable;

#[UsePolicy(ProductPolicy::class)]
class Product extends Model
{
    use HasDefaultSlug;
    use HasFactory;
    use Searchable;

    protected $fillable = [
        'name',
        'slug',
        'price',
        'on_sale',
        'sale_price',
        'color',
        'upc',
    ];

    protected $casts = [
        'on_sale' => 'boolean',
    ];

    public function toSearchableArray(): array
    {
        return [
            'name' => $this->name,
            'color' => $this->color,
            'on_sale' => $this->on_sale,
            'price' => $this->price,
            'sale_price' => $this->sale_price,
            'categories' => $this->categories->pluck('name')->toArray(),
        ];
    }

    /*****************
     * Relationships
     ****************/

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }
}
