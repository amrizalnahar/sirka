@props(['messages'])

@if ($messages)
    <ul {{ $attributes->merge(['class' => 'text-sm text-red-600 space-y-1']) }}>
        @foreach (collect((array) $messages)->flatten()->filter() as $message)
            <li>{{ $message }}</li>
        @endforeach
    </ul>
@endif
