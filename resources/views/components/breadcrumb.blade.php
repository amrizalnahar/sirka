@props(['items' => []])

<nav aria-label="Breadcrumb" class="text-sm text-gray-500 mb-6 font-medium">
    <ol class="flex flex-wrap items-center">
        @foreach($items as $index => $item)
            <li class="flex items-center">
                @if($index > 0)
                    <span class="mx-2">&gt;</span>
                @endif

                @if(isset($item['url']) && $index < count($items) - 1)
                    <a href="{{ $item['url'] }}" class="text-primary hover:text-primary-dark transition-colors">
                        {{ $item['name'] }}
                    </a>
                @else
                    <span class="truncate" title="{{ $item['name'] }}">{{ Str::limit($item['name'], 40) }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
