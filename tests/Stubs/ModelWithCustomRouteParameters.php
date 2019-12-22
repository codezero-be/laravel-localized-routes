<?php

namespace CodeZero\LocalizedRoutes\Tests\Stubs;

use CodeZero\LocalizedRoutes\ProvidesRouteParameters;
use Illuminate\Database\Eloquent\Model as BaseModel;
use Illuminate\Support\Facades\App;

class ModelWithCustomRouteParameters extends BaseModel implements ProvidesRouteParameters
{
    protected $guarded = [];

    /**
     * Get the route parameters for this model.
     *
     * @param string|null $locale
     *
     * @return array
     */
    public function getRouteParameters($locale = null)
    {
        return [
            $this->id,
            $this->attributes['slug'][$locale ?: App::getLocale()]
        ];
    }

    /**
     * Fake route model binding (avoid database for test purpose).
     *
     * @param int $id
     *
     * @return mixed
     */
    public function resolveRouteBinding($id)
    {
        return $this;
    }
}
