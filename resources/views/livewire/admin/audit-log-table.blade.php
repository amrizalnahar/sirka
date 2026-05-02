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

    <h1 class="text-xl font-bold text-gray-800 mb-6">Audit Log</h1>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-4 flex flex-col lg:flex-row gap-3">
        <select wire:model.live="eventFilter" class="w-full lg:w-40 border-gray-300 focus:border-[#1A6FAA] focus:ring-[#1A6FAA] rounded-lg text-sm">
            <option value="">Semua Event</option>
            @foreach($events as $event)
                <option value="{{ $event }}">{{ $event }}</option>
            @endforeach
        </select>
        <select wire:model.live="modelFilter" class="w-full lg:w-52 border-gray-300 focus:border-[#1A6FAA] focus:ring-[#1A6FAA] rounded-lg text-sm">
            <option value="">Semua Model</option>
            @foreach($models as $model)
                <option value="{{ $model }}">{{ class_basename($model) }}</option>
            @endforeach
        </select>
        <select wire:model.live="userFilter" class="w-full lg:w-48 border-gray-300 focus:border-[#1A6FAA] focus:ring-[#1A6FAA] rounded-lg text-sm">
            <option value="">Semua User</option>
            @foreach($users as $user)
                <option value="{{ $user->id }}">{{ $user->name }}</option>
            @endforeach
        </select>
        <input wire:model.live="dateFrom" type="date" class="w-full lg:w-40 border-gray-300 focus:border-[#1A6FAA] focus:ring-[#1A6FAA] rounded-lg text-sm" placeholder="Dari">
        <input wire:model.live="dateTo" type="date" class="w-full lg:w-40 border-gray-300 focus:border-[#1A6FAA] focus:ring-[#1A6FAA] rounded-lg text-sm" placeholder="Sampai">
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 text-gray-600 font-medium border-b border-gray-200">
                    <tr>
                        <th class="px-5 py-3">Waktu</th>
                        <th class="px-5 py-3">User</th>
                        <th class="px-5 py-3">Event</th>
                        <th class="px-5 py-3">Model</th>
                        <th class="px-5 py-3">IP</th>
                        <th class="px-5 py-3 text-right"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($logs as $log)
                        <tr class="hover:bg-gray-50 transition-colors cursor-pointer" wire:click="toggleRow({{ $log->id }})" wire:key="log-{{ $log->id }}">
                            <td class="px-5 py-3.5 text-gray-600 whitespace-nowrap">{{ $log->created_at->format('d M Y H:i') }}</td>
                            <td class="px-5 py-3.5 font-medium text-gray-800">{{ $log->user?->name ?? 'System' }}</td>
                            <td class="px-5 py-3.5">
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium
                                    @if($log->event === 'created') bg-green-100 text-green-700
                                    @elseif($log->event === 'updated') bg-blue-100 text-blue-700
                                    @elseif($log->event === 'deleted') bg-red-100 text-red-700
                                    @else bg-gray-100 text-gray-600 @endif">
                                    {{ $log->event }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-gray-500">{{ class_basename($log->auditable_type) }} #{{ $log->auditable_id }}</td>
                            <td class="px-5 py-3.5 text-gray-500 text-xs font-mono">{{ $log->ip_address ?? '-' }}</td>
                            <td class="px-5 py-3.5 text-right">
                                <svg class="w-4 h-4 text-gray-400 inline-block transition-transform" :class="{{ $expandedRow === $log->id ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </td>
                        </tr>
                        @if($expandedRow === $log->id)
                            <tr wire:key="log-detail-{{ $log->id }}">
                                <td colspan="6" class="px-5 py-4 bg-gray-50">
                                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 text-xs">
                                        <div>
                                            <p class="font-semibold text-gray-700 mb-1">Data Lama</p>
                                            <pre class="bg-white border border-gray-200 rounded-lg p-3 overflow-x-auto text-gray-600">{{ json_encode($log->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?: '-' }}</pre>
                                        </div>
                                        <div>
                                            <p class="font-semibold text-gray-700 mb-1">Data Baru</p>
                                            <pre class="bg-white border border-gray-200 rounded-lg p-3 overflow-x-auto text-gray-600">{{ json_encode($log->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?: '-' }}</pre>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-gray-500">Tidak ada data audit log.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-5 py-3 border-t border-gray-200">
            {{ $logs->links() }}
        </div>
    </div>
</div>
