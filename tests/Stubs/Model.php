<?php

namespace CodeZero\LocalizedRoutes\Tests\Stubs;

use Illuminate\Database\Eloquent\Model as BaseModel;

class Model extends BaseModel
{
    public function getRouteKey()
    {
        $slugs = [
            'en' => 'en-slug',
            'nl' => 'nl-slug',
        ];

        return $slugs[app()->getLocale()];
    }
}
