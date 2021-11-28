<?php

namespace CodeZero\LocalizedRoutes\Controller;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;

class FallbackController extends Controller
{
    /**
     * Handle the fallback route.
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     */
    public function __invoke()
    {
        $shouldRedirect = Config::get('localized-routes.redirect_to_localized_urls', false);

        if ($shouldRedirect) {
            $localizedUrl = Route::localizedUrl();
            $route = $this->findRouteByUrl($localizedUrl);

            if ( ! $route->isFallback) {
                return Redirect::to($localizedUrl, Config::get('localized-routes.redirect_status_code', 301))
                    ->header('Cache-Control', 'no-store, no-cache, must-revalidate');
            }
        }

        return $this->NotFoundResponse();
    }

    /**
     * Find a Route by its URL.
     *
     * @param string $url
     *
     * @return \Illuminate\Routing\Route
     */
    protected function findRouteByUrl($url)
    {
        return Collection::make(Route::getRoutes())->first(function ($route) use ($url) {
            return $route->matches(Request::create($url));
        });
    }

    /**
     * Return a 404 view or throw a 404 error if the view doesn't exist.
     *
     * @return \Illuminate\Http\Response
     */
    protected function NotFoundResponse()
    {
        $view = Config::get('localized-routes.404_view');

        if (View::exists($view)) {
            return Response::view($view, [], 404);
        }

        abort(404);
    }
}
