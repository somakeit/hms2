<?php

namespace App\Providers;

use HMS\Auth\PasswordStore;
use HMS\Auth\PasswordStoreManager;
use HMS\Facades\Features;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Stripe\Stripe;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(PasswordStore::class, function ($app) {
            $passwordStoreManager = new PasswordStoreManager($app);

            return $passwordStoreManager->driver();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Globally set the money format
        setlocale(LC_MONETARY, 'en_GB.UTF-8');

        if (config('services.stripe.secret')) {
            Stripe::setApiKey(config('services.stripe.secret'));
        }

        Blade::if('feature', function ($value) {
            return Features::isEnabled($value);
        });

        Blade::directive('fatureState', function ($value) {
            return "<?php echo Features::isEnabled({$value}) ? 'true' : 'false' ?>";
        });

        // @content('view', 'block')
        Blade::directive('content', function ($expression) {
            [$view, $block] = explode(', ', str_replace('\'', '', $expression));

            return "<?php echo Content::get('{$view}', '{$block}', 'ContentBlock missing for {$view}:{$block}  '); ?>";
        });

        RateLimiter::for('membership.retention', function (object $job) {
            return Limit::perDay(1);
        });

        Paginator::useBootstrap();
    }
}
