<form method="POST" action="{{ route('admin.agents.' . $action, $agent) }}"
      @if (! empty($confirm)) onsubmit="return confirm('{{ $confirm }}')" @endif>
    @csrf
    <button class="{{ $class }}">{{ $label }}</button>
</form>
