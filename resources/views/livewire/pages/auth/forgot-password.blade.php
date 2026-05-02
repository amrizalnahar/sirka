<?php

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $email = '';

    public string $alertMessage = '';
    public string $alertType = '';

    public function sendPasswordResetLink(): void
    {
        $this->alertMessage = '';
        $this->alertType = '';

        $this->validate([
            'email' => ['required', 'string', 'email'],
        ], [
            'email.required' => 'Alamat email wajib diisi.',
            'email.email'    => 'Format email tidak valid.',
        ]);

        $status = Password::sendResetLink(
            $this->only('email')
        );

        if ($status != Password::RESET_LINK_SENT) {
            $this->alertMessage = match ($status) {
                Password::RESET_THROTTLED       => 'Terlalu banyak permintaan. Silakan tunggu beberapa saat sebelum mencoba lagi.',
                'passwords.user'                => 'Kami tidak menemukan pengguna dengan alamat email tersebut.',
                'passwords.token'               => 'Token reset password tidak valid.',
                default                         => 'Terjadi kesalahan. Silakan coba lagi.',
            };
            $this->alertType = 'error';

            return;
        }

        $this->reset('email');
        $this->alertMessage = 'Link reset password telah dikirim ke email Anda. Silakan periksa kotak masuk Anda.';
        $this->alertType = 'success';
    }
}; ?>

<div>
    <div class="text-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Lupa Password?</h2>
        <p class="mt-2 text-sm text-gray-600">
            Tidak masalah. Masukkan alamat email Anda dan kami akan mengirimkan link reset password agar Anda dapat membuat password baru.
        </p>
    </div>

    @if ($alertMessage)
        <div class="mb-4 rounded-lg px-4 py-3 text-sm flex items-start gap-3 {{ $alertType === 'success' ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200' }}">
            <span class="mt-0.5 shrink-0">
                @if ($alertType === 'success')
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                @else
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                @endif
            </span>
            <span>{{ $alertMessage }}</span>
        </div>
    @endif

    <form wire:submit="sendPasswordResetLink">
        <div>
            <x-input-label for="email" :value="'Alamat Email'" />
            <x-text-input
                wire:model="email"
                id="email"
                class="block mt-1 w-full {{ $errors->has('email') ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : '' }}"
                type="text"
                name="email"
                placeholder="nama@email.com"
                autofocus
            />
            @error('email')
                <p class="mt-1.5 text-sm text-red-600 flex items-center gap-1">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01"/></svg>
                    {{ $message }}
                </p>
            @enderror
        </div>

        <div class="flex items-center justify-between mt-6">
            <a href="{{ route('login') }}" wire:navigate class="text-sm text-gray-600 hover:text-gray-900 underline">
                Kembali ke login
            </a>

            <x-primary-button wire:loading.attr="disabled" class="whitespace-nowrap h-10">
                <span wire:loading class="whitespace-nowrap">Memproses...</span>
                <span wire:loading.remove class="whitespace-nowrap">Kirim Link Reset</span>
            </x-primary-button>
        </div>
    </form>
</div>
