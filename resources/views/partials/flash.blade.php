@php
    // Error yang tidak menempel ke field form (dari service: payment, role, product, ...)
    $general = collect($errors->getMessages())
        ->only(['payment', 'role', 'product', 'user', 'data'])
        ->flatten();
    $alert = 'mb-5 flex items-start gap-3 rounded-2xl border px-4 py-3 text-sm';
@endphp

@if (session('success'))
    <div role="status" class="{{ $alert }} border-emerald-200 bg-emerald-50 text-emerald-900">
        <svg class="size-5 shrink-0 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
        <div>{{ session('success') }}</div>
    </div>
@endif
@if (session('warning'))
    <div role="status" class="{{ $alert }} border-amber-200 bg-amber-50 text-amber-900">
        <svg class="size-5 shrink-0 text-amber-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 9v4m0 4h.01M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z"/></svg>
        <div>{{ session('warning') }}</div>
    </div>
@endif
@if (session('error'))
    <div role="alert" class="{{ $alert }} border-red-200 bg-red-50 text-red-900">
        <svg class="size-5 shrink-0 text-red-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 8v5m0 3h.01"/></svg>
        <div>{{ session('error') }}</div>
    </div>
@endif
@if ($general->isNotEmpty())
    <div role="alert" class="{{ $alert }} border-red-200 bg-red-50 text-red-900">
        <svg class="size-5 shrink-0 text-red-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 8v5m0 3h.01"/></svg>
        <div>@foreach ($general as $msg)<div>{{ $msg }}</div>@endforeach</div>
    </div>
@endif
