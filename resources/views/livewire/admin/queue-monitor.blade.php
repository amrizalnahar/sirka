@section('page-title', 'Queue Monitor')

<div>
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Queue Monitor</h1>
            <p class="text-sm text-gray-500 mt-1">Monitoring antrian jobs, batches, dan failed jobs.</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="bg-blue-100 text-blue-700 text-xs font-bold px-3 py-1 rounded-full">
                {{ count($this->jobs) }} Pending
            </span>
            <span class="bg-yellow-100 text-yellow-700 text-xs font-bold px-3 py-1 rounded-full">
                {{ count($this->jobBatches) }} Batches
            </span>
            <span class="bg-red-100 text-red-700 text-xs font-bold px-3 py-1 rounded-full">
                {{ count($this->failedJobs) }} Failed
            </span>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="flex border-b border-gray-200">
            <button wire:click="setTab('jobs')"
                    class="px-6 py-4 text-sm font-semibold transition-all focus:outline-none border-b-2 {{ $tab === 'jobs' ? 'text-primary border-primary bg-primary-light/30' : 'text-gray-500 border-transparent hover:text-primary hover:bg-gray-50' }}">
                <span class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Pending Jobs
                </span>
            </button>
            <button wire:click="setTab('batches')"
                    class="px-6 py-4 text-sm font-semibold transition-all focus:outline-none border-b-2 {{ $tab === 'batches' ? 'text-primary border-primary bg-primary-light/30' : 'text-gray-500 border-transparent hover:text-primary hover:bg-gray-50' }}">
                <span class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    Job Batches
                </span>
            </button>
            <button wire:click="setTab('failed')"
                    class="px-6 py-4 text-sm font-semibold transition-all focus:outline-none border-b-2 {{ $tab === 'failed' ? 'text-primary border-primary bg-primary-light/30' : 'text-gray-500 border-transparent hover:text-primary hover:bg-gray-50' }}">
                <span class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    Failed Jobs
                </span>
            </button>
        </div>

        {{-- Tab: Pending Jobs --}}
        <div x-show="$wire.tab === 'jobs'" x-cloak class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-gray-800">Pending Jobs</h2>
                @if(count($this->jobs) > 0)
                    <button wire:click="flushPendingJobs" wire:confirm="Yakin ingin menghapus semua pending jobs?"
                            class="text-red-600 hover:text-red-700 text-sm font-semibold flex items-center gap-1.5 bg-red-50 hover:bg-red-100 border border-red-200 rounded-lg px-3 py-1.5 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Hapus Semua
                    </button>
                @endif
            </div>

            @if(count($this->jobs) > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 text-gray-600 uppercase text-xs font-semibold">
                                <th class="px-4 py-3 text-left rounded-tl-lg">ID</th>
                                <th class="px-4 py-3 text-left">Queue</th>
                                <th class="px-4 py-3 text-left">Job</th>
                                <th class="px-4 py-3 text-center">Attempts</th>
                                <th class="px-4 py-3 text-left">Dibuat</th>
                                <th class="px-4 py-3 text-center rounded-tr-lg">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($this->jobs as $job)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-4 py-3 text-gray-500 font-mono">{{ $job['id'] }}</td>
                                    <td class="px-4 py-3">
                                        <span class="bg-blue-50 text-blue-700 text-xs font-bold px-2 py-1 rounded">{{ $job['queue'] }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-gray-800 font-medium">{{ $job['display_name'] }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="bg-gray-100 text-gray-600 text-xs font-bold px-2 py-1 rounded-full">{{ $job['attempts'] }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-gray-500" title="{{ $job['created_at_exact'] }}">{{ $job['created_at'] }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <button wire:click="deletePendingJob({{ $job['id'] }})" wire:confirm="Hapus job ini?"
                                                class="text-gray-400 hover:text-red-500 transition-colors p-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-12">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-100 rounded-full mb-4">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-700">Tidak ada pending jobs</h3>
                    <p class="text-gray-500 text-sm mt-1">Semua job sudah diproses.</p>
                </div>
            @endif
        </div>

        {{-- Tab: Job Batches --}}
        <div x-show="$wire.tab === 'batches'" x-cloak class="p-6">
            <h2 class="text-lg font-bold text-gray-800 mb-4">Job Batches</h2>

            @if(count($this->jobBatches) > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 text-gray-600 uppercase text-xs font-semibold">
                                <th class="px-4 py-3 text-left rounded-tl-lg">ID</th>
                                <th class="px-4 py-3 text-left">Nama</th>
                                <th class="px-4 py-3 text-center">Total</th>
                                <th class="px-4 py-3 text-center">Pending</th>
                                <th class="px-4 py-3 text-center">Failed</th>
                                <th class="px-4 py-3 text-left">Progress</th>
                                <th class="px-4 py-3 text-left rounded-tr-lg">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($this->jobBatches as $batch)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-4 py-3 text-gray-500 font-mono text-xs">{{ Str::limit($batch['id'], 12) }}</td>
                                    <td class="px-4 py-3 text-gray-800 font-medium">{{ $batch['name'] }}</td>
                                    <td class="px-4 py-3 text-center text-gray-600">{{ $batch['total_jobs'] }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="bg-yellow-50 text-yellow-700 text-xs font-bold px-2 py-1 rounded-full">{{ $batch['pending_jobs'] }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if($batch['failed_jobs'] > 0)
                                            <span class="bg-red-50 text-red-700 text-xs font-bold px-2 py-1 rounded-full">{{ $batch['failed_jobs'] }}</span>
                                        @else
                                            <span class="text-gray-400">0</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            <div class="w-24 h-2 bg-gray-200 rounded-full overflow-hidden">
                                                <div class="h-full bg-primary rounded-full transition-all" style="width: {{ $batch['progress'] }}%"></div>
                                            </div>
                                            <span class="text-xs text-gray-500 font-medium">{{ $batch['progress'] }}%</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($batch['cancelled_at'])
                                            <span class="bg-red-100 text-red-700 text-xs font-bold px-2.5 py-1 rounded-full">Dibatalkan</span>
                                        @elseif($batch['finished_at'])
                                            <span class="bg-green-100 text-green-700 text-xs font-bold px-2.5 py-1 rounded-full">Selesai</span>
                                        @else
                                            <span class="bg-blue-100 text-blue-700 text-xs font-bold px-2.5 py-1 rounded-full">Berjalan</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-12">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-100 rounded-full mb-4">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-700">Tidak ada job batches</h3>
                    <p class="text-gray-500 text-sm mt-1">Belum ada batch job yang dibuat.</p>
                </div>
            @endif
        </div>

        {{-- Tab: Failed Jobs --}}
        <div x-show="$wire.tab === 'failed'" x-cloak class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-gray-800">Failed Jobs</h2>
                @if(count($this->failedJobs) > 0)
                    <div class="flex items-center gap-2">
                        <button wire:click="retryAllFailedJobs" wire:confirm="Retry semua failed jobs?"
                                class="text-green-700 hover:text-green-800 text-sm font-semibold flex items-center gap-1.5 bg-green-50 hover:bg-green-100 border border-green-200 rounded-lg px-3 py-1.5 transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Retry Semua
                        </button>
                        <button wire:click="flushFailedJobs" wire:confirm="Yakin ingin menghapus semua failed jobs?"
                                class="text-red-600 hover:text-red-700 text-sm font-semibold flex items-center gap-1.5 bg-red-50 hover:bg-red-100 border border-red-200 rounded-lg px-3 py-1.5 transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Hapus Semua
                        </button>
                    </div>
                @endif
            </div>

            @if(count($this->failedJobs) > 0)
                <div class="space-y-4">
                    @foreach($this->failedJobs as $job)
                        <div class="bg-red-50 border border-red-100 rounded-xl p-4 hover:shadow-sm transition-shadow">
                            <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-3">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="bg-red-100 text-red-700 text-xs font-bold px-2 py-0.5 rounded">{{ $job['queue'] }}</span>
                                        <span class="text-xs text-gray-400 font-mono">{{ Str::limit($job['uuid'], 8) }}</span>
                                    </div>
                                    <h4 class="font-bold text-gray-800 text-sm">{{ $job['display_name'] }}</h4>
                                    <p class="text-xs text-red-600 font-mono mt-1 bg-red-100/50 rounded px-2 py-1 truncate" title="{{ $job['exception_preview'] }}">
                                        {{ $job['exception_preview'] }}
                                    </p>
                                    <p class="text-xs text-gray-400 mt-1" title="{{ $job['failed_at_exact'] }}">{{ $job['failed_at'] }}</p>
                                </div>
                                <div class="flex items-center gap-2 shrink-0">
                                    <button wire:click="retryFailedJob({{ $job['id'] }})"
                                            class="bg-primary hover:bg-primary-dark text-white text-xs font-bold rounded-lg px-3 py-1.5 transition-colors flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                        </svg>
                                        Retry
                                    </button>
                                    <button wire:click="deleteFailedJob({{ $job['id'] }})" wire:confirm="Hapus failed job ini?"
                                            class="text-gray-400 hover:text-red-500 transition-colors p-1.5 hover:bg-red-50 rounded">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-100 rounded-full mb-4">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-700">Tidak ada failed jobs</h3>
                    <p class="text-gray-500 text-sm mt-1">Semua job berhasil diproses tanpa error.</p>
                </div>
            @endif
        </div>
    </div>
</div>
