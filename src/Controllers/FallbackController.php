<?php

namespace CodeZero\LocalizedRoutes\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
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
        return $this->redirectResponse() ?: $this->notFoundResponse();
    }

    /**
     * Return a redirect response if needed.
     *
     * @return \Illuminate\Http\RedirectResponse|false
     */
    protected function redirectResponse()
    {
        if ( ! $this->shouldRedirect()) {
            return false;
        }

        $localizedUrl = Route::localizedUrl();
        $route = $this->findRouteByUrl($localizedUrl);

        if ($route->isFallback) {
            return false;
        }

        return Redirect::to($localizedUrl, $this->getRedirectStatusCode())
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate');
    }

    /**
     * Find a Route by its URL.
     *
     * @param string $url
     *
     * @return \Illuminate\Routing\Route
     */
    protected function findRouteByUrl(string $url)
    {
        return Route::getRoutes()->match(Request::create($url));
    }

    /**
     * Return a 404 view or throw a 404 error if the view doesn't exist.
     *
     * @return \Illuminate\Http\Response
     */
    protected function notFoundResponse()
    {
        $view = Config::get('localized-routes.404_view');

        if (View::exists($view)) {
            return Response::view($view, [], 404);
        }

        abort(404);
    }

    /**
     * Determine if we need to redirect to a localized version of this route.
     *
     * @return bool
     */
    protected function shouldRedirect()
    {
        return Config::get('localized-routes.redirect_to_localized_urls');
    }

    /**
     * Get the redirect status code from config.
     *
     * @return int
     */
    protected function getRedirectStatusCode()
    {
        return Config::get('localized-routes.redirect_status_code', 301);
    }
}
