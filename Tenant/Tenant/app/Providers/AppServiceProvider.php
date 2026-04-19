<?php

namespace App\Providers;

use App\Services\TenantCustomizationService;
use App\Support\TenantConfig;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(TenantCustomizationService::class);

        $this->app->bind('currentSchool', function () {
            if (! app()->bound('request')) {
                return null;
            }

            return request()->attributes->get('tenant');
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Blade::if('tenantModule', fn (string $moduleKey): bool => TenantConfig::moduleEnabled($moduleKey));

        Blade::if('tenantFeature', fn (string $featureKey): bool => TenantConfig::featureActive($featureKey));

        Blade::directive('tenantSetting', function (string $expression): string {
            return "<?php echo e(\\App\\Support\\TenantConfig::setting(...[{$expression}])); ?>";
        });
    }
}
