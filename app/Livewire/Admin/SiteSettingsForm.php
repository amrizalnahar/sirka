<?php

namespace App\Livewire\Admin;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.admin')]
class SiteSettingsForm extends Component
{
    use WithFileUploads;

    public string $siteName = '';
    public string $siteDescription = '';
    public $siteLogo = null;
    public ?string $existingLogo = null;
    public $siteFavicon = null;
    public ?string $existingFavicon = null;

    public string $contactEmail = '';
    public string $contactPhone = '';
    public string $contactAddress = '';

    public string $mailFromAddress = '';

    public string $socialFacebook = '';
    public string $socialInstagram = '';
    public string $socialWhatsapp = '';
    public string $socialTiktok = '';

    public string $seoSiteName = '';
    public string $seoDescription = '';
    public string $seoAuthor = '';
    public string $ga4MeasurementId = '';

    public function mount(): void
    {
        $this->siteName = SiteSetting::getValue('site_name', config('app.name'));
        $this->siteDescription = SiteSetting::getValue('site_description', '');
        $this->existingLogo = SiteSetting::getValue('site_logo');
        $this->existingFavicon = SiteSetting::getValue('site_favicon');

        $this->contactEmail = SiteSetting::getValue('contact_email', '');
        $this->contactPhone = SiteSetting::getValue('contact_phone', '');
        $this->contactAddress = SiteSetting::getValue('contact_address', '');

        $this->mailFromAddress = SiteSetting::getValue('mail_from_address', config('mail.from.address', ''));

        $this->socialFacebook = SiteSetting::getValue('social_facebook', '');
        $this->socialInstagram = SiteSetting::getValue('social_instagram', '');
        $this->socialWhatsapp = SiteSetting::getValue('social_whatsapp', '');
        $this->socialTiktok = SiteSetting::getValue('social_tiktok', '');

        $this->seoSiteName = SiteSetting::getValue('seo_site_name', config('seo.site_name', config('app.name')));
        $this->seoDescription = SiteSetting::getValue('seo_description', config('seo.description', ''));
        $this->seoAuthor = SiteSetting::getValue('seo_author', config('seo.author', ''));
        $this->ga4MeasurementId = SiteSetting::getValue('ga4_measurement_id', env('GA4_MEASUREMENT_ID', ''));
    }

    public function save(): void
    {
        $this->validate([
            'siteName' => ['nullable', 'string', 'max:255'],
            'siteDescription' => ['nullable', 'string', 'max:1000'],
            'siteLogo' => ['nullable', 'image', 'max:2048', 'mimes:jpg,jpeg,png,webp'],
            'siteFavicon' => ['nullable', 'image', 'max:1024', 'mimes:png,ico'],
            'contactEmail' => ['nullable', 'email', 'max:255'],
            'contactPhone' => ['nullable', 'string', 'max:50'],
            'contactAddress' => ['nullable', 'string', 'max:500'],
            'mailFromAddress' => ['nullable', 'email', 'max:255'],
            'socialFacebook' => ['nullable', 'string', 'max:255'],
            'socialInstagram' => ['nullable', 'string', 'max:255'],
            'socialWhatsapp' => ['nullable', 'string', 'max:255'],
            'socialTiktok' => ['nullable', 'string', 'max:255'],
            'seoSiteName' => ['nullable', 'string', 'max:255'],
            'seoDescription' => ['nullable', 'string', 'max:1000'],
            'seoAuthor' => ['nullable', 'string', 'max:255'],
            'ga4MeasurementId' => ['nullable', 'string', 'max:50'],
        ]);

        $logoPath = $this->existingLogo;
        if ($this->siteLogo) {
            if ($this->existingLogo) {
                Storage::disk('public')->delete($this->existingLogo);
            }
            $logoPath = $this->siteLogo->store('settings', 'public');
        }

        $faviconPath = $this->existingFavicon;
        if ($this->siteFavicon) {
            if ($this->existingFavicon) {
                Storage::disk('public')->delete($this->existingFavicon);
            }
            $faviconPath = $this->siteFavicon->store('settings', 'public');
        }

        SiteSetting::setValue('site_name', $this->siteName ?: null);
        SiteSetting::setValue('site_description', $this->siteDescription ?: null);
        SiteSetting::setValue('site_logo', $logoPath);
        SiteSetting::setValue('site_favicon', $faviconPath);

        SiteSetting::setValue('contact_email', $this->contactEmail ?: null);
        SiteSetting::setValue('contact_phone', $this->contactPhone ?: null);
        SiteSetting::setValue('contact_address', $this->contactAddress ?: null);
        SiteSetting::setValue('mail_from_address', $this->mailFromAddress ?: null);

        SiteSetting::setValue('social_facebook', $this->socialFacebook ?: null);
        SiteSetting::setValue('social_instagram', $this->socialInstagram ?: null);
        SiteSetting::setValue('social_whatsapp', $this->socialWhatsapp ?: null);
        SiteSetting::setValue('social_tiktok', $this->socialTiktok ?: null);

        SiteSetting::setValue('seo_site_name', $this->seoSiteName ?: null);
        SiteSetting::setValue('seo_description', $this->seoDescription ?: null);
        SiteSetting::setValue('seo_author', $this->seoAuthor ?: null);
        SiteSetting::setValue('ga4_measurement_id', $this->ga4MeasurementId ?: null);

        $this->existingLogo = $logoPath;
        $this->existingFavicon = $faviconPath;
        $this->siteLogo = null;
        $this->siteFavicon = null;

        $this->dispatch('notify', type: 'success', message: 'Pengaturan berhasil disimpan.');
    }

    public function render()
    {
        return view('livewire.admin.site-settings-form');
    }
}
