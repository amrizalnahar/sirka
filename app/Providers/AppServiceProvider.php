<?php

namespace App\Providers;

use App\Models\SiteSetting;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

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
        $this->applySiteSettings();

        // Refresh config sebelum setiap queue job diproses
        // agar perubahan di database langsung terlihat tanpa restart worker
        Queue::before(function (JobProcessing $event) {
            $this->applySiteSettings();
        });
    }

    private function applySiteSettings(): void
    {
        try {
            // Check if the table exists before doing anything else
            if (!Schema::hasTable('site_settings')) {
                return;
            }
        } catch (\Exception $e) {
            // Database not available (e.g., during build time or before migration)
            return;
        }

        $fromAddress = SiteSetting::getValue('mail_from_address');

        if ($fromAddress) {
            config(['mail.from.address' => $fromAddress]);
        }

        // Override SEO config from SiteSetting
        if ($seoSiteName = SiteSetting::getValue('seo_site_name')) {
            config(['seo.site_name' => $seoSiteName]);
        }
        if ($seoDescription = SiteSetting::getValue('seo_description')) {
            config(['seo.description' => $seoDescription]);
        }
        if ($seoAuthor = SiteSetting::getValue('seo_author')) {
            config(['seo.author' => $seoAuthor]);
        }
        if ($ga4MeasurementId = SiteSetting::getValue('ga4_measurement_id')) {
            config(['services.ga4.measurement_id' => $ga4MeasurementId]);
        }
    }
}
