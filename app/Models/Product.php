<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasDefaultSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    use HasDefaultSlug;
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'price',
        'on_sale',
        'sale_price',
        'color',
        'upc',
    ];

    public function category(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }
}
