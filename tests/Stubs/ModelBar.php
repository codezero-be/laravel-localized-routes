<?php

namespace CodeZero\LocalizedRoutes\Tests\Stubs;

use Illuminate\Support\Facades\App;
use Illuminate\Database\Eloquent\Model as BaseModel;
use CodeZero\LocalizedRoutes\ProvidesRouteParameters;

class ModelBar extends BaseModel implements ProvidesRouteParameters
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
            $this->attributes['slug'][$locale ?: App::getLocale()]
        ];
    }

    /**
     * Fake route model binding (avoid database for test purpose).
     *
     * @param int $id
     * @param string|null $field
     *
     * @return mixed
     */
    public function resolveRouteBinding($id, $field = null)
    {
        return $this;
    }
}
