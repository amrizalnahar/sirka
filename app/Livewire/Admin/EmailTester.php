<?php

namespace App\Livewire\Admin;

use App\Jobs\FailEmailTestJob;
use App\Mail\TestEmail;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class EmailTester extends Component
{
    public string $email = '';
    public ?string $result = null;
    public bool $success = false;

    protected function rules(): array
    {
        return [
            'email' => ['required', 'email'],
        ];
    }

    protected function messages(): array
    {
        return [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
        ];
    }

    public function sendSuccess(): void
    {
        $this->validate();
        $this->reset(['result', 'success']);

        try {
            Mail::to($this->email)->queue(new TestEmail());
            $this->result = 'Email berhasil dikirim ke ' . $this->email;
            $this->success = true;
            $this->dispatch('notify', type: 'success', message: 'Email tes berhasil dikirim.');
        } catch (\Exception $e) {
            $this->result = 'Gagal mengirim email: ' . $e->getMessage();
            $this->success = false;
            $this->dispatch('notify', type: 'error', message: 'Gagal mengirim email tes.');
        }
    }

    public function simulateFail(): void
    {
        $this->validate();
        $this->reset(['result', 'success']);

        FailEmailTestJob::dispatch($this->email);

        $this->result = 'Job simulasi gagal telah didispatch ke queue. Jalankan "php artisan queue:work" agar job diproses dan masuk ke failed_jobs.';
        $this->success = false;
        $this->dispatch('notify', type: 'warning', message: 'Simulasi gagal didispatch ke queue.');
    }

    public function render()
    {
        return view('livewire.admin.email-tester');
    }
}
