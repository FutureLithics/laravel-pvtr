@props([
    'text',
])

<span {{ $attributes->merge(['class' => 'group relative inline-flex cursor-help border-b border-dotted border-current']) }}>
    {{ $slot }}
    <span
        role="tooltip"
        class="pointer-events-none absolute bottom-full left-0 z-10 mb-2 w-56 rounded-md bg-slate-900 px-3 py-2 text-xs font-normal leading-relaxed text-white opacity-0 shadow-sm transition-opacity group-hover:opacity-100 group-focus-within:opacity-100"
    >
        {{ $text }}
    </span>
</span>
