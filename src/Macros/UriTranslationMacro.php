<?php

namespace CodeZero\LocalizedRoutes\Macros;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;

class UriTranslationMacro
{
    /**
     * Register the macro.
     *
     * @return void
     */
    public static function register()
    {
        Lang::macro('uri', function ($uri, $locale = null) {
            // Split the URI into a Collection of segments.
            $segments = new Collection(explode('/', trim($uri, '/')));

            // Attempt to translate each segment. If there is no translation
            // for a specific segment, then its original value will be used.
            $translations = $segments->map(function ($segment) use ($locale) {
                $translationKey = "routes.{$segment}";

                // If the segment is not a placeholder and the segment
                // has a translation, then update the segment.
                if ( ! Str::startsWith($segment, '{') && Lang::has($translationKey, $locale)) {
                    $segment = Lang::get($translationKey, [], $locale);
                }

                return $segment;
            });

            // Rebuild the URI from the translated segments.
            return $translations->implode('/');
        });
    }
}
