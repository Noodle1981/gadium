@props(['href', 'active', 'icon'])

@php
$classes = ($active ?? false)
            ? 'flex items-center px-4 py-3 text-white bg-orange-600 rounded-lg group transition-all duration-200 shadow-lg shadow-orange-900/20'
            : 'flex items-center px-4 py-3 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg group transition-all duration-200';
@endphp

<a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }} wire:navigate>
    @if(isset($icon))
        <div class="{{ ($active ?? false) ? 'text-white' : 'text-gray-500 group-hover:text-orange-500' }} transition-colors duration-200"
             :class="sidebarCollapsed ? 'mr-0' : 'mr-3'">
            {!! $icon !!}
        </div>
    @endif
    <span class="font-medium whitespace-nowrap" x-show="!sidebarCollapsed" x-transition.opacity>{{ $slot }}</span>
</a>
