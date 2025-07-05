<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasDefaultSlug;
use App\Policies\CategoryPolicy;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[UsePolicy(CategoryPolicy::class)]
class Category extends Model
{
    use HasDefaultSlug;
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
    ];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class);
    }
}
