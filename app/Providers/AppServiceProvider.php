<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::define('admin', function ($user) {
            return $user->role === 'admin';
        });

        // Blade directive @rupiah untuk format Rupiah konsisten
        Blade::directive('rupiah', function ($expression) {
            return "<?php echo formatRupiah($expression); ?>";
        });
    }
}
