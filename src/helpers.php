<?php

if (! function_exists('route')) {
    /**
     * Generate the URL to a named route.
     *
     * @param string $name
     * @param array $parameters
     * @param bool $absolute
     * @param null|string $locale
     *
     * @return string
     */
    function route($name, $parameters = [], $absolute = true, $locale = null)
    {
        return app('url')->route($name, $parameters, $absolute, $locale);
    }
}
