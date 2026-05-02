<?php

namespace App\Livewire\Admin;

use App\Models\AuditTrail;
use App\Models\Post;
use Illuminate\Support\Facades\Schema;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class Dashboard extends Component
{
    public function render()
    {
        $stats = [
            'posts' => 0,
        ];

        $latestActivities = collect();

        if (Schema::hasTable('posts')) {
            $stats['posts'] = Post::where('status', 'published')->count();
        }
        if (Schema::hasTable('audit_trails')) {
            $latestActivities = AuditTrail::with('user')->latest()->limit(10)->get();
        }

        return view('livewire.admin.dashboard', compact('stats', 'latestActivities'));
    }
}
