<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Keeps a unique `slug` column in sync with the model's `name`.
 *
 * The slug is only generated when it is left empty, so an editor can always
 * override it by hand without it being rewritten on the next save.
 */
trait HasSlug
{
    public static function bootHasSlug(): void
    {
        static::saving(function (Model $model) {
            if (blank($model->slug) && filled($model->name)) {
                $model->slug = static::generateUniqueSlug($model->name, $model->getKey());
            }
        });
    }

    protected static function generateUniqueSlug(string $name, mixed $ignoreId = null): string
    {
        $base = Str::slug($name) ?: Str::random(8);
        $slug = $base;
        $suffix = 2;

        while (static::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->exists()
        ) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
