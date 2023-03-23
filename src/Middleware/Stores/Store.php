<?php

namespace CodeZero\LocalizedRoutes\Middleware\Stores;

interface Store
{
    /**
     * Store the given locale.
     *
     * @param string $locale
     *
     * @return void
     */
    public function store($locale);
}
