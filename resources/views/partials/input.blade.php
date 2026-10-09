@php
    $type = $type ?? 'text';
    $val = $type === 'password' ? '' : old($name, $value ?? '');
@endphp
<div>
    <label for="{{ $name }}" class="label">{{ $label }}</label>
    <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" value="{{ $val }}"
           @if (! empty($required)) required @endif
           @if (isset($min)) min="{{ $min }}" @endif
           @if (isset($placeholder)) placeholder="{{ $placeholder }}" @endif
           @error($name) aria-invalid="true" @enderror
           class="field @error($name) !border-red-400 focus:!ring-red-500/15 @enderror">
    @error($name)<p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
</div>
