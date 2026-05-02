<?php

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    #[Locked]
    public string $token = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $encryptionKeyId = '';

    public bool $isSuccess = false;
    public string $statusMessage = '';
    public string $statusType = '';

    public function mount(string $token): void
    {
        $this->token = $token;
        $this->email = request()->string('email');
    }

    public function resetPassword(): void
    {
        $this->statusMessage = '';
        $this->statusType = '';

        try {
            $this->validate([
                'token' => ['required'],
                'email' => ['required', 'string', 'email'],
                'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
            ], [
                'email.required' => 'Alamat email wajib diisi.',
                'email.email' => 'Format email tidak valid.',
                'password.required' => 'Password wajib diisi.',
                'password.confirmed' => 'Konfirmasi password tidak cocok.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->password = '';
            $this->password_confirmation = '';
            $this->encryptionKeyId = '';
            throw $e;
        }

        $status = Password::reset(
            $this->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) {
                $user->forceFill([
                    'password' => Hash::make($this->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status != Password::PASSWORD_RESET) {
            $this->password = '';
            $this->password_confirmation = '';
            $this->encryptionKeyId = '';
            $this->statusType = 'error';
            $this->statusMessage = match ($status) {
                Password::INVALID_TOKEN => 'Link reset password sudah tidak berlaku atau tidak valid. Silakan minta link baru melalui halaman Lupa Password.',
                Password::INVALID_USER => 'Kami tidak menemukan akun dengan alamat email tersebut.',
                Password::RESET_THROTTLED => 'Terlalu banyak percobaan reset password. Silakan tunggu beberapa saat sebelum mencoba lagi.',
                default => 'Terjadi kesalahan saat mereset password. Silakan coba lagi.',
            };
            return;
        }

        $this->isSuccess = true;
        $this->statusType = 'success';
        $this->statusMessage = 'Password berhasil diubah! Silakan login menggunakan password baru Anda.';
    }

    public function goToLogin(): void
    {
        $this->redirectRoute('login', navigate: true);
    }
}; ?>

<div class="text-center">
    {{-- Header --}}
    <div class="mb-6">

        <h2 class="text-2xl font-bold text-gray-800">
            @if($isSuccess)
                Yeay, Password Berhasil Diubah!
            @elseif($statusType === 'error')
                Oops, Terjadi Kesalahan
            @else
                Buat Password Baru
            @endif
        </h2>
        <p class="mt-2 text-sm text-gray-600">
            @if($isSuccess)
                Password Anda telah berhasil direset. Sekarang Anda dapat masuk ke akun menggunakan password baru.
            @elseif($statusType === 'error')
                {{ $statusMessage }}
            @else
                Masukkan password baru yang kuat untuk akun <span class="font-medium text-gray-800">{{ $email }}</span>.
            @endif
        </p>
    </div>

    {{-- Status Alert --}}
    @if($statusType === 'error' && !$isSuccess)
        <div class="mb-6 rounded-lg px-4 py-3 text-sm bg-red-50 text-red-700 border border-red-200 flex items-start gap-3 text-left">
            <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span>{{ $statusMessage }}</span>
        </div>
    @endif

    @if($isSuccess)
        {{-- Success State --}}
        <div class="mb-6 rounded-lg px-4 py-3 text-sm bg-green-50 text-green-700 border border-green-200 flex items-start gap-3 text-left">
            <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span>{{ $statusMessage }}</span>
        </div>

        <x-primary-button wire:click="goToLogin" class="w-full justify-center">
            Login Sekarang
        </x-primary-button>

        <p class="mt-4 text-xs text-gray-500">
            Anda akan dialihkan ke halaman login.
        </p>
    @else
        {{-- Error recovery link for invalid token --}}
        @if($statusType === 'error')
            <div class="mb-6">
                <a href="{{ route('password.request') }}" wire:navigate class="text-sm text-[#1A6FAA] hover:underline font-medium">
                    Minta Link Reset Baru
                </a>
            </div>
        @endif

        {{-- Reset Form --}}
        <form x-data="{
            plainPassword: '',
            plainPasswordConfirmation: '',
            loading: false,
            error: '',
            async doReset() {
                this.error = '';
                this.loading = true;
                try {
                    const encryptor = new RsaEncryptor();
                    const result = await encryptor.encryptPassword(this.plainPassword);
                    $wire.set('password', result.encrypted, false);
                    $wire.set('encryptionKeyId', result.keyId, false);

                    if (this.plainPasswordConfirmation) {
                        const resultConfirm = await encryptor.encryptPassword(this.plainPasswordConfirmation);
                        $wire.set('password_confirmation', resultConfirm.encrypted, false);
                    }

                    await $wire.resetPassword();
                } catch (e) {
                    console.error('Encryption failed', e);
                    this.error = 'Gagal mengamankan password. Silakan coba lagi.';
                } finally {
                    this.loading = false;
                }
            }
        }" x-on:submit.prevent="doReset">
            <!-- Hidden encrypted fields -->
            <input type="hidden" wire:model="password" name="password" id="encrypted-password">
            <input type="hidden" wire:model="password_confirmation" name="password_confirmation" id="encrypted-password-confirmation">
            <input type="hidden" wire:model="encryptionKeyId" name="encryption_key_id" id="encryption-key-id">

            <!-- Email Address -->
            <div class="text-left">
                <x-input-label for="email" :value="'Alamat Email'" />
                <x-text-input wire:model="email" id="email" class="block mt-1 w-full bg-gray-100" type="text" name="email" readonly autofocus autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <!-- Password -->
            <div class="mt-4 text-left">
                <x-input-label for="password" :value="'Password Baru'" />
                <div class="relative mt-1" x-data="{ show: false }">
                    <input x-model="plainPassword"
                           id="password"
                           class="block w-full pr-10 rounded-md border-gray-300 shadow-sm focus:border-[#1A6FAA] focus:ring-[#1A6FAA]"
                           :type="show ? 'text' : 'password'"
                           name="password" autocomplete="new-password" />
                    <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 cursor-pointer">
                        <svg x-show="!show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        <svg x-show="show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M12 12l-4.242-4.242M12 12l4.242 4.242M3 3l3.59 3.59m11.46 11.46l3.59 3.59"/></svg>
                    </button>
                </div>
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <!-- Confirm Password -->
            <div class="mt-4 text-left">
                <x-input-label for="password_confirmation" :value="'Konfirmasi Password'" />
                <div class="relative mt-1" x-data="{ show: false }">
                    <input x-model="plainPasswordConfirmation"
                           id="password_confirmation"
                           class="block w-full pr-10 rounded-md border-gray-300 shadow-sm focus:border-[#1A6FAA] focus:ring-[#1A6FAA]"
                           :type="show ? 'text' : 'password'"
                           name="password_confirmation" autocomplete="new-password" />
                    <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 cursor-pointer">
                        <svg x-show="!show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        <svg x-show="show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M12 12l-4.242-4.242M12 12l4.242 4.242M3 3l3.59 3.59m11.46 11.46l3.59 3.59"/></svg>
                    </button>
                </div>
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                <p x-show="error" x-text="error" class="mt-2 text-sm text-red-600" style="display: none;"></p>
            </div>

            <div class="flex items-center justify-end mt-6">
                <x-primary-button ::disabled="loading">
                    <span x-show="loading">Memproses...</span>
                    <span x-show="!loading">Simpan Password Baru</span>
                </x-primary-button>
            </div>
        </form>
    @endif
</div>
