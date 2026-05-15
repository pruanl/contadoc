@props(['status'])

@php
    $classes = [
        'active' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
        'pending' => 'bg-amber-50 text-amber-800 ring-amber-600/20',
        'new' => 'bg-sky-50 text-sky-700 ring-sky-600/20',
        'reviewed' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
        'archived' => 'bg-zinc-100 text-zinc-700 ring-zinc-600/20',
        'failed' => 'bg-red-50 text-red-700 ring-red-600/20',
    ][$status] ?? 'bg-zinc-100 text-zinc-700 ring-zinc-600/20';
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset {$classes}"]) }}>
    {{ $slot->isEmpty() ? $status : $slot }}
</span>
