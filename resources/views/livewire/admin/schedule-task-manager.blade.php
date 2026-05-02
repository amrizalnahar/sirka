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

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <h1 class="text-xl font-bold text-gray-800">Schedule Tasks</h1>
        <button wire:click="refreshTasks" class="inline-flex items-center px-4 py-2 bg-[#1A6FAA] text-white text-sm font-medium rounded-lg hover:bg-[#155a8a] transition-colors shadow-sm">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
            Refresh Task
        </button>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
        <div class="flex flex-col sm:flex-row gap-4">
            <div class="flex-1">
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari task..."
                       class="w-full border-gray-300 focus:border-[#1A6FAA] focus:ring-[#1A6FAA] rounded-lg text-sm">
            </div>
            <div class="sm:w-48">
                <select wire:model.live="statusFilter" class="w-full border-gray-300 focus:border-[#1A6FAA] focus:ring-[#1A6FAA] rounded-lg text-sm">
                    <option value="">Semua Status</option>
                    <option value="active">Aktif</option>
                    <option value="inactive">Nonaktif</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Tasks Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nama</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Command</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Jadwal</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Terakhir dijalankan</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($tasks as $task)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ $task->name }}</div>
                                @if($task->description)
                                    <div class="text-xs text-gray-500 mt-0.5">{{ $task->description }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <code class="bg-gray-100 px-2 py-1 rounded text-xs text-gray-700">{{ $task->command }}</code>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                {{ $task->expression ?: 'Manual' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $task->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                    {{ $task->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                {{ $task->last_run_at ? $task->last_run_at->diffForHumans() : 'Belum pernah' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    @can('schedule-tasks-execute')
                                    <button wire:click="openExecuteModal({{ $task->id }})" class="px-3 py-1.5 bg-[#1A6FAA] text-white text-xs font-medium rounded-md hover:bg-[#155a8a] transition-colors">
                                        Jalankan
                                    </button>
                                    @endcan
                                    <button wire:click="openHistoryModal({{ $task->id }})" class="px-3 py-1.5 bg-gray-100 text-gray-600 text-xs font-medium rounded-md hover:bg-gray-200 transition-colors">
                                        Riwayat
                                    </button>
                                    @can('schedule-tasks-execute')
                                    <button wire:click="toggleActive({{ $task->id }})" class="text-xs text-gray-500 hover:text-[#1A6FAA] font-medium">
                                        {{ $task->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <p>Tidak ada schedule task.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Execute Modal -->
    @if($showExecuteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="fixed inset-0 bg-black/50" wire:click="closeModal"></div>
            <div class="bg-white rounded-xl shadow-xl max-w-md w-full mx-4 z-10 p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Jalankan Schedule Task</h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Dijadwalkan untuk</label>
                        <input type="datetime-local" wire:model="scheduledFor"
                               class="block w-full border-gray-300 focus:border-[#1A6FAA] focus:ring-[#1A6FAA] rounded-lg text-sm">
                        @error('scheduledFor') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-6">
                    <button wire:click="closeModal" class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">Batal</button>
                    <button wire:click="executeTask" class="px-4 py-2 bg-[#1A6FAA] text-white text-sm font-medium rounded-lg hover:bg-[#155a8a] transition-colors">Jalankan</button>
                </div>
            </div>
        </div>
    @endif

    <!-- History Modal -->
    @if($showHistoryModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="fixed inset-0 bg-black/50" wire:click="closeModal"></div>
            <div class="bg-white rounded-xl shadow-xl max-w-3xl w-full mx-4 z-10 p-6 max-h-[80vh] flex flex-col">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Riwayat Eksekusi</h3>

                <div class="overflow-y-auto flex-1">
                    @if(count($executionHistory) > 0)
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50 sticky top-0">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Dijalankan</th>
                                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Dijadwalkan</th>
                                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Exit Code</th>
                                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Output</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($executionHistory as $execution)
                                    <tr>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-600">{{ \Carbon\Carbon::parse($execution['executed_at'])->format('d M Y H:i') }}</td>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-600">{{ \Carbon\Carbon::parse($execution['scheduled_for'])->format('d M Y H:i') }}</td>
                                        <td class="px-4 py-2 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                                {{ $execution['status'] === 'completed' ? 'bg-green-100 text-green-700' : ($execution['status'] === 'failed' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') }}">
                                                {{ ucfirst($execution['status']) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-600">{{ $execution['exit_code'] ?? '-' }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-600 max-w-xs">
                                            @if($execution['output'])
                                                <pre class="text-xs bg-gray-100 p-2 rounded overflow-x-auto whitespace-pre-wrap">{{ \Illuminate\Support\Str::limit($execution['output'], 200) }}</pre>
                                            @else
                                                <span class="text-xs text-gray-400">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="text-center py-8 text-gray-500">
                            <p>Belum ada riwayat eksekusi.</p>
                        </div>
                    @endif
                </div>

                <div class="flex justify-end mt-4 pt-4 border-t border-gray-100">
                    <button wire:click="closeModal" class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">Tutup</button>
                </div>
            </div>
        </div>
    @endif
</div>
