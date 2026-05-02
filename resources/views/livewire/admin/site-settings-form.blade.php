<div>
    <!-- Toast -->
    <div x-data="{ show: false, message: '', type: 'success' }"
         x-on:notify.window="show = true; message = $event.detail.message; type = $event.detail.type; setTimeout(() => show = false, 3000)"
         x-show="show" x-transition
         class="fixed top-5 right-5 z-50 px-4 py-3 rounded-lg shadow-lg text-white text-sm font-medium"
         :class="type === 'success' ? 'bg-green-600' : 'bg-red-600'"
         style="display: none;">
        <span x-text="message"></span>
    </div>

    <div class="max-w-3xl">
        <h1 class="text-xl font-bold text-gray-800 mb-6">Pengaturan Situs</h1>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-8">
            <!-- Identitas Situs -->
            <div>
                <h2 class="text-sm font-semibold text-gray-700 mb-4 pb-2 border-b border-gray-100">Identitas Situs</h2>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Situs</label>
                        <input wire:model="siteName" type="text" class="w-full border-gray-300 focus:border-[#1A6FAA] focus:ring-[#1A6FAA] rounded-lg text-sm">
                        @error('siteName') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi Situs</label>
                        <textarea wire:model="siteDescription" rows="3" class="w-full border-gray-300 focus:border-[#1A6FAA] focus:ring-[#1A6FAA] rounded-lg text-sm"></textarea>
                        @error('siteDescription') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Logo</label>
                        @if($existingLogo && !$siteLogo)
                            <div class="mb-2">
                                <img src="{{ Storage::url($existingLogo) }}" alt="Logo" class="h-16 w-auto rounded-lg object-contain border border-gray-200">
                            </div>
                        @endif
                        @if($siteLogo)
                            <div class="mb-2">
                                <img src="{{ $siteLogo->temporaryUrl() }}" alt="Preview" class="h-16 w-auto rounded-lg object-contain border border-gray-200">
                            </div>
                        @endif
                        <input wire:model="siteLogo" type="file" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-[#1A6FAA] file:text-white hover:file:bg-[#155a8a]">
                        @error('siteLogo') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Favicon</label>
                        @if($existingFavicon && !$siteFavicon)
                            <div class="mb-2">
                                <img src="{{ Storage::url($existingFavicon) }}" alt="Favicon" class="h-8 w-8 rounded-lg object-contain border border-gray-200">
                            </div>
                        @endif
                        @if($siteFavicon)
                            <div class="mb-2">
                                <img src="{{ $siteFavicon->temporaryUrl() }}" alt="Preview" class="h-8 w-8 rounded-lg object-contain border border-gray-200">
                            </div>
                        @endif
                        <input wire:model="siteFavicon" type="file" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-[#1A6FAA] file:text-white hover:file:bg-[#155a8a]">
                        @error('siteFavicon') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <!-- Kontak -->
            <div>
                <h2 class="text-sm font-semibold text-gray-700 mb-4 pb-2 border-b border-gray-100">Kontak</h2>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email Kontak</label>
                        <input wire:model="contactEmail" type="text" class="w-full border-gray-300 focus:border-[#1A6FAA] focus:ring-[#1A6FAA] rounded-lg text-sm">
                        @error('contactEmail') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Telepon</label>
                        <input wire:model="contactPhone" type="text" class="w-full border-gray-300 focus:border-[#1A6FAA] focus:ring-[#1A6FAA] rounded-lg text-sm">
                        @error('contactPhone') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Alamat</label>
                        <textarea wire:model="contactAddress" rows="2" class="w-full border-gray-300 focus:border-[#1A6FAA] focus:ring-[#1A6FAA] rounded-lg text-sm"></textarea>
                        @error('contactAddress') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <!-- Email Sistem -->
            <div>
                <h2 class="text-sm font-semibold text-gray-700 mb-4 pb-2 border-b border-gray-100">Email Sistem</h2>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email Pengirim (MAIL_FROM_ADDRESS)</label>
                        <input wire:model="mailFromAddress" type="text" placeholder="admin@secret-campaign.test" class="w-full border-gray-300 focus:border-[#1A6FAA] focus:ring-[#1A6FAA] rounded-lg text-sm">
                        @error('mailFromAddress') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        <p class="mt-1 text-xs text-gray-500">Alamat email yang digunakan sebagai pengirim untuk semua notifikasi sistem.</p>
                    </div>
                </div>
            </div>

            <!-- Media Sosial -->
            <div>
                <h2 class="text-sm font-semibold text-gray-700 mb-4 pb-2 border-b border-gray-100">Media Sosial</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Facebook</label>
                        <input wire:model="socialFacebook" type="text" placeholder="https://facebook.com/..." class="w-full border-gray-300 focus:border-[#1A6FAA] focus:ring-[#1A6FAA] rounded-lg text-sm">
                        @error('socialFacebook') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Instagram</label>
                        <input wire:model="socialInstagram" type="text" placeholder="https://instagram.com/..." class="w-full border-gray-300 focus:border-[#1A6FAA] focus:ring-[#1A6FAA] rounded-lg text-sm">
                        @error('socialInstagram') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">WhatsApp</label>
                        <input wire:model="socialWhatsapp" type="text" placeholder="https://wa.me/..." class="w-full border-gray-300 focus:border-[#1A6FAA] focus:ring-[#1A6FAA] rounded-lg text-sm">
                        @error('socialWhatsapp') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">TikTok</label>
                        <input wire:model="socialTiktok" type="text" placeholder="https://tiktok.com/..." class="w-full border-gray-300 focus:border-[#1A6FAA] focus:ring-[#1A6FAA] rounded-lg text-sm">
                        @error('socialTiktok') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <!-- Setting SEO -->
            <div>
                <h2 class="text-sm font-semibold text-gray-700 mb-4 pb-2 border-b border-gray-100">Setting SEO</h2>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">SEO Site Name</label>
                        <input wire:model="seoSiteName" type="text" placeholder="Nama situs untuk SEO" class="w-full border-gray-300 focus:border-[#1A6FAA] focus:ring-[#1A6FAA] rounded-lg text-sm">
                        @error('seoSiteName') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">SEO Description</label>
                        <textarea wire:model="seoDescription" rows="3" placeholder="Deskripsi default untuk meta tag" class="w-full border-gray-300 focus:border-[#1A6FAA] focus:ring-[#1A6FAA] rounded-lg text-sm"></textarea>
                        @error('seoDescription') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">SEO Author</label>
                        <input wire:model="seoAuthor" type="text" placeholder="Nama author / tim" class="w-full border-gray-300 focus:border-[#1A6FAA] focus:ring-[#1A6FAA] rounded-lg text-sm">
                        @error('seoAuthor') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">GA4 Measurement ID</label>
                        <input wire:model="ga4MeasurementId" type="text" placeholder="G-XXXXXXXXXX" class="w-full border-gray-300 focus:border-[#1A6FAA] focus:ring-[#1A6FAA] rounded-lg text-sm">
                        @error('ga4MeasurementId') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        <p class="mt-1 text-xs text-gray-500">Contoh: G-ABC123DEF0. Kosongkan jika tidak menggunakan Google Analytics.</p>
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t border-gray-100">
                @can('settings-edit')
                <button wire:click="save" class="px-6 py-2.5 bg-[#1A6FAA] text-white text-sm font-medium rounded-lg hover:bg-[#155a8a] transition-colors shadow-sm">
                    Simpan Pengaturan
                </button>
                @endcan
            </div>
        </div>
    </div>
</div>
