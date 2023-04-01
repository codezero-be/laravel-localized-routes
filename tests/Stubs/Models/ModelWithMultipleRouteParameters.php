<?php

namespace CodeZero\LocalizedRoutes\Tests\Stubs\Models;

use CodeZero\LocalizedRoutes\ProvidesRouteParameters;
use Illuminate\Database\Eloquent\Model as BaseModel;
use Illuminate\Support\Facades\App;

class ModelWithMultipleRouteParameters extends BaseModel implements ProvidesRouteParameters
{
    protected $guarded = [];

    /**
     * Get the route parameters for this model.
     *
     * @param string|null $locale
     *
     * @return array
     */
    public function getRouteParameters($locale = null): array
    {
        return [
            $this->id,
            $this->attributes['slug'][$locale ?: App::getLocale()]
        ];
    }

    /**
     * Fake route model binding (avoid database for test purpose).
     *
     * @param mixed $value
     * @param string|null $field
     *
     * @return mixed
     */
    public function resolveRouteBinding($value, $field = null)
    {
        return $this;
    }
}
