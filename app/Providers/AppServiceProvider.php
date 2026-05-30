<?php

namespace App\Providers;

use App\Models\Category;
use Illuminate\Support\Facades\Blade;
use Illuminate\Session\Store as SessionStore;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if (! SessionStore::hasMacro('boolean')) {
            SessionStore::macro('boolean', function (string $key, bool $default = false): bool {
                return filter_var($this->get($key, $default), FILTER_VALIDATE_BOOLEAN);
            });
        }

        Blade::if('admin', fn (): bool => (bool) session('is_admin', false));

        View::composer('*', function ($view): void {
            static $navCategories = null;

            if ($navCategories === null) {
                try {
                    if (Schema::hasTable('categories')) {
                        $categoryQuery = Category::query()
                            ->with(['activeSubcategories' => fn ($query) => $query->orderBy('name')])
                            ->orderBy('id');

                        if (Schema::hasColumn('categories', 'is_active')) {
                            $categoryQuery->where('is_active', true);
                        }

                        $navCategories = $categoryQuery->get();
                    } else {
                        $navCategories = collect();
                    }
                } catch (\Throwable) {
                    $navCategories = collect();
                }
            }

            $view->with('navCategories', $navCategories);
        });
    }
}
