<?php

namespace CodeZero\LocalizedRoutes\Middleware;

use Closure;
use CodeZero\LocalizedRoutes\LocalizedUrlGenerator;
use CodeZero\LocalizedRoutes\Middleware\Detectors\RouteActionDetector;
use CodeZero\LocalizedRoutes\Middleware\Detectors\UrlDetector;
use CodeZero\LocalizedRoutes\RouteHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;

class SetLocale
{
    /**
     * LocaleHandler.
     *
     * @var \CodeZero\LocalizedRoutes\Middleware\LocaleHandler
     */
    protected $handler;

    /**
     * Create a new SetLocale instance.
     *
     * @param \CodeZero\LocalizedRoutes\Middleware\LocaleHandler $handler
     */
    public function __construct(LocaleHandler $handler)
    {
        $this->handler = $handler;
    }

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $originalRequest = $this->useOriginalRequestDuringLivewireRequests($request);

        $locale = $this->handler->detect();

        if ($locale) {
            $this->handler->store($locale);
        }

        // This is rom the SubstituteBindings middleware, but
        // it needs to run on the request we created,
        // after the locale is updated.
        Route::substituteBindings($originalRequest->route());
        Route::substituteImplicitBindings($originalRequest->route());

        return $next($request);
    }

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return mixed
     */
    public function useOriginalRequestDuringLivewireRequests(Request $request)
    {
        $url = $request->fullUrl();// Livewire::originalUrl();
        $originalRequest = Request::create($url);

        $originalRequest->setRouteResolver(function () use ($originalRequest) {
            return Route::getRoutes()->match($originalRequest);
        });

        App::bind(LocalizedUrlGenerator::class, function () use ($originalRequest) {
            return new LocalizedUrlGenerator($originalRequest);
        });

        App::bind(RouteActionDetector::class, function () use ($originalRequest) {
            return new RouteActionDetector($originalRequest);
        });

        App::bind(UrlDetector::class, function () use ($originalRequest) {
            return new UrlDetector($originalRequest);
        });

        App::bind(RouteHelper::class, function () use ($originalRequest) {
            return new RouteHelper($originalRequest);
        });

        return  $originalRequest;
    }
}
