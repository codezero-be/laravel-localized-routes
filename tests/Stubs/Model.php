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

    /**
     * Fake route model binding.
     *
     * @param string $slug
     *
     * @return mixed
     */
    public function resolveRouteBinding($slug)
    {
        $validSlug = $this->slugs[App::getLocale()];

        if ($validSlug !== $slug) {
            abort(404);
        }

        return $this;
    }
}
