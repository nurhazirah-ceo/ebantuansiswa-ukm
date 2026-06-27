@props(['active'])

@php
$classes = ($active ?? false)
    ? 'inline-flex items-center px-1 pt-1 border-b-2 border-blue-400 text-sm font-semibold text-white'
    : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium text-slate-300 hover:text-white hover:border-slate-500 transition';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
