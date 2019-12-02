<?php

namespace CodeZero\LocalizedRoutes\Tests\Stubs;

use Illuminate\Database\Eloquent\Model as BaseModel;
use Illuminate\Support\Facades\App;

class Model extends BaseModel
{
    /**
     * Fake localized slugs.
     *
     * @var array
     */
    protected $slugs = [
        'en' => 'en-slug',
        'nl' => 'nl-slug',
    ];

    /**
     * Fake localized route key.
     *
     * @return mixed
     */
    public function getRouteKey()
    {
        return $this->slugs[App::getLocale()];

    }
}
