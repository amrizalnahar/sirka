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

    <div class="max-w-xl">
        <h1 class="text-xl font-bold text-gray-800 mb-6">Email SMTP Tester</h1>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email Penerima</label>
                <input wire:model="email" type="text" placeholder="email@example.com"
                       class="w-full border-gray-300 focus:border-[#1A6FAA] focus:ring-[#1A6FAA] rounded-lg text-sm">
                @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex gap-3">
                @can('system-email-tester')
                <button wire:click="sendSuccess" wire:loading.attr="disabled" wire:target="sendSuccess" class="flex-1 inline-flex items-center justify-center gap-2 px-4 h-10 bg-[#1A6FAA] text-white text-sm font-medium rounded-md hover:bg-[#155a8a] transition-colors disabled:opacity-70 disabled:cursor-not-allowed whitespace-nowrap">
                    <span wire:loading wire:target="sendSuccess" class="whitespace-nowrap">Memproses...</span>
                    <span wire:loading.remove wire:target="sendSuccess" class="whitespace-nowrap">Kirim Sukses</span>
                </button>
                <button wire:click="simulateFail" wire:loading.attr="disabled" wire:target="simulateFail" class="flex-1 inline-flex items-center justify-center gap-2 px-4 h-10 bg-red-50 text-red-600 text-sm font-medium rounded-md hover:bg-red-100 transition-colors disabled:opacity-70 disabled:cursor-not-allowed whitespace-nowrap">
                    <span wire:loading wire:target="simulateFail" class="whitespace-nowrap">Memproses...</span>
                    <span wire:loading.remove wire:target="simulateFail" class="whitespace-nowrap">Simulasi Gagal</span>
                </button>
                @endcan
            </div>

            @if($result)
                <div class="rounded-lg p-4 text-sm {{ $success ? 'bg-green-50 text-green-800 border border-green-200' : 'bg-red-50 text-red-800 border border-red-200' }}">
                    <p class="font-medium mb-1">{{ $success ? 'Berhasil' : 'Gagal' }}</p>
                    <p class="text-xs">{{ $result }}</p>
                </div>
            @endif
        </div>
    </div>
</div>
