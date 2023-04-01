<?php

namespace CodeZero\LocalizedRoutes\Tests\Stubs\Models;

use Illuminate\Database\Eloquent\Model as BaseModel;
use Illuminate\Support\Facades\App;

class ModelOneWithRouteBinding extends BaseModel
{
    protected $guarded = [];

    /**
     * Get the (fake) slug attribute.
     *
     * @return string
     */
    protected function getSlugAttribute(): string
    {
        return $this->getSlug();
    }

    /**
     * Get the localized slug.
     *
     * @param string|null $locale
     *
     * @return string
     */
    public function getSlug(?string $locale = null): string
    {
        return $this->attributes['slug'][$locale ?: App::getLocale()];
    }

    /**
     * Fake route model binding.
     *
     * @param mixed $value
     * @param string|null $field
     *
     * @return mixed
     */
    public function resolveRouteBinding($value, $field = null)
    {
        $field = $field ?: $this->getRouteKeyName();

        // Bypass the database for testing purpose and return
        // the current model as if it was found in the database.
        if ($this->getRouteKeyName() === 'id' && $field === 'id') {
            return $this;
        }

        // If the parameter is a slug, check if it is in the right language
        // and return the current model as if it was found in the database.
        $validSlug = $this->attributes[$field][App::getLocale()];

        if ($validSlug !== $value) {
            abort(404);
        }

        return $this;
    }
}
