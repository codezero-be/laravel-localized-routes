<?php

namespace CodeZero\LocalizedRoutes\Tests\Stubs;

use Illuminate\Database\Eloquent\Model as BaseModel;
use Illuminate\Support\Facades\App;

class Model extends BaseModel
{
    protected $guarded = [];

    /**
     * Get the (fake) slug attribute.
     *
     * @return string
     */
    protected function getSlugAttribute()
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
    public function getSlug($locale = null)
    {
        return $this->attributes['slug'][$locale ?: App::getLocale()];
    }

    /**
     * Fake route model binding.
     *
     * @param string $parameter
     * @param string|null $field
     *
     * @return mixed
     */
    public function resolveRouteBinding($parameter, $field = null)
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

        if ($validSlug !== $parameter) {
            abort(404);
        }

        return $this;
    }
}
