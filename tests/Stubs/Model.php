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
        return $this->attributes['slug'][App::getLocale()];
    }

    /**
     * Fake route model binding.
     *
     * @param string $slug
     *
     * @return mixed
     */
    public function resolveRouteBinding($slug)
    {
        $validSlug = $this->attributes['slug'][App::getLocale()];

        if ($validSlug !== $slug) {
            abort(404);
        }

        return $this;
    }
}
