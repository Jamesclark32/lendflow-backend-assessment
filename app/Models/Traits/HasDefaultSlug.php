<?php

declare(strict_types=1);

namespace App\Models\Traits;

use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

trait HasDefaultSlug
{
    use HasSlug;

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
