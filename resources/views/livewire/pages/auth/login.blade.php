<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    public string $encryptionKeyId = '';

    public function login(): void
    {
        $this->validate();

        try {
            $this->form->authenticate();
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->form->password = '';
            $this->encryptionKeyId = '';
            throw $e;
        }

        Session::regenerate();

        $this->redirectIntended(default: route('admin.dashboard', absolute: false), navigate: true);
    }
}; ?>

<div class="text-center">
    {{-- Header --}}
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Selamat Datang Kembali</h2>
        <p class="mt-2 text-sm text-gray-600">
            Masuk ke panel admin untuk mengelola konten dan pengaturan situs.
        </p>
    </div>

    {{-- Session Status --}}
    @if (session('status'))
        <div class="mb-6 rounded-lg px-4 py-3 text-sm bg-green-50 text-green-700 border border-green-200 flex items-start gap-3 text-left">
            <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>{{ session('status') }}</span>
        </div>
    @endif

    <form x-data="{
        plainPassword: '',
        loading: false,
        error: '',
        async doLogin() {
            this.error = '';
            this.loading = true;
            try {
                const encryptor = new RsaEncryptor();
                const { encrypted, keyId } = await encryptor.encryptPassword(this.plainPassword);
                $wire.set('form.password', encrypted, false);
                $wire.set('encryptionKeyId', keyId, false);
                await $wire.login();
            } catch (e) {
                console.error('Encryption failed', e);
                this.error = 'Gagal mengamankan password. Silakan coba lagi.';
            } finally {
                this.loading = false;
            }
        }
    }" x-on:submit.prevent="doLogin">
        <!-- Hidden encrypted fields -->
        <input type="hidden" wire:model="form.password" name="password" id="encrypted-password">
        <input type="hidden" wire:model="encryptionKeyId" name="encryption_key_id" id="encryption-key-id">

        <!-- Email Address -->
        <div class="text-left">
            <x-input-label for="email" :value="'Alamat Email'" />
            <x-text-input wire:model="form.email" id="email" class="block mt-1 w-full" type="text" name="email" placeholder="nama@email.com" autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('form.email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4 text-left">
            <x-input-label for="password" :value="'Password'" />
            <div class="relative mt-1" x-data="{ show: false }">
                <input x-model="plainPassword"
                       id="password"
                       class="block w-full pr-10 rounded-md border-gray-300 shadow-sm focus:border-[#1A6FAA] focus:ring-[#1A6FAA]"
                       :type="show ? 'text' : 'password'"
                       name="password"
                       autocomplete="current-password" />
                <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 cursor-pointer">
                    <svg x-show="!show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    <svg x-show="show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M12 12l-4.242-4.242M12 12l4.242 4.242M3 3l3.59 3.59m11.46 11.46l3.59 3.59"/></svg>
                </button>
            </div>
            <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
            <p x-show="error" x-text="error" class="mt-2 text-sm text-red-600" style="display: none;"></p>
        </div>

        <!-- Remember Me & Forgot Password -->
        <div class="flex items-center justify-between mt-4">
            <label for="remember" class="inline-flex items-center">
                <input wire:model="form.remember" id="remember" type="checkbox" class="rounded border-gray-300 text-[#1A6FAA] shadow-sm focus:ring-[#1A6FAA]" name="remember">
                <span class="ms-2 text-sm text-gray-600">Ingat saya</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-sm text-[#1A6FAA] hover:underline font-medium" href="{{ route('password.request') }}" wire:navigate>
                    Lupa password?
                </a>
            @endif
        </div>

        <div class="mt-6">
            <x-primary-button class="w-full justify-center" ::disabled="loading">
                <span x-show="loading">Memproses...</span>
                <span x-show="!loading">Masuk</span>
            </x-primary-button>
        </div>
    </form>
</div>
