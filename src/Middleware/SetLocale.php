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
    public function handle(Request $request, Closure $next)
    {
        // If this is a Livewire request, we have to recreate
        // the original Request and rebind the classes that need it.
        // This needs to be done before the locale is updated,
        // because some Detector classes use the Request.
        $originalRequest = $this->useOriginalRequestDuringLivewireRequests();

        $locale = $this->handler->detect();

        if ($locale) {
            $this->handler->store($locale);
        }

        if ($originalRequest) {
            // This is from the SubstituteBindings middleware.
            // If this is a Livewire request, this needs to run on
            // the request we created, after the locale is updated,
            // to enable localized route model binding.
            Route::substituteBindings($originalRequest->route());
            Route::substituteImplicitBindings($originalRequest->route());
        }

        return $next($request);
    }

    /**
     * If this is a Livewire request, recreate the original Request,
     * and use it in the classes that need it.
     *
     * @return \Illuminate\Http\Request|null
     */
    public function useOriginalRequestDuringLivewireRequests(): ?Request
    {
        if ( ! $this->isLivewireRequest()) {
            return null;
        }

        $url = \Livewire\Livewire::originalUrl();
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

    /**
     * Check if this is a Livewire ajax call.
     *
     * @return bool
     */
    protected function isLivewireRequest(): bool
    {
        return class_exists(\Livewire\LivewireManager::class)
            && App::make(\Livewire\LivewireManager::class)->isLivewireRequest();
    }
}
