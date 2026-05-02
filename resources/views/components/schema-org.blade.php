@props(['type' => 'WebSite', 'data' => []])

@php
$schema = array_merge([
    '@context' => 'https://schema.org',
    '@type' => $type,
], $data);
@endphp

<script type="application/ld+json">
{!! json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
