<?php

namespace App\Providers;

use App\Models\AdminSetting;
use App\Models\Category;
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
        // Share category and site settings with customer/user views
        View::composer(['user.*', 'user.components.*'], function ($view) {
            try {
                $navcategories = Category::root()->active()->with(['children' => function ($query) {
                    $query->active()->orderBy('sort_order', 'asc');
                }])->orderBy('sort_order', 'asc')->take(6)->get();
            } catch (\Throwable $e) {
                $navcategories = collect();
            }

            $searchSuggestions = [
                'Fashion',
                'Electronics',
                'Mobiles',
                'Home & Kitchen',
                'Beauty & Personal Care',
                'Grocery',
                'Sports',
            ];

            $isDarkMode = AdminSetting::isDarkMode();
            $currentTheme = AdminSetting::currentTheme();

            $view->with([
                'navcategories' => $navcategories,
                'searchSuggestions' => $searchSuggestions,
                'homepageTitle' => config('app.name', 'Shopy'),
                'homepageSubtitle' => null,
                'homepageLogoLink' => route('home'),
                'logoMedia' => null,
                'isDarkMode' => $isDarkMode,
                'currentTheme' => $currentTheme,
            ]);
        });
    }
}
