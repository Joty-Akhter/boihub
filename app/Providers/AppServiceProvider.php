<?php

namespace App\Providers;

use App\Models\Category;
use App\Support\Cart;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::defaultView('partials.pagination');

        // Trimmed query-string value, or '' when missing or not a string (e.g. ?q[]=x).
        Request::macro('queryText', function (string $key): string {
            $value = $this->query($key);

            return is_string($value) ? trim($value) : '';
        });

        View::composer('layouts.shop', function ($view) {
            $view->with([
                'cartCount' => app(Cart::class)->count(),
                'navCategories' => Category::active()->ordered()->take(6)->get(),
            ]);
        });
    }
}
