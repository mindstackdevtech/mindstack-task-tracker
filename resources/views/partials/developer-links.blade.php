{{-- GitHub, email and Facebook buttons. The addresses come from config/mindstack.php. --}}
@php $dev = config('mindstack.developer', []); @endphp

<div class="dev-links">
    @if (! empty($dev['github']))
        <a class="btn" href="{{ $dev['github'] }}" target="_blank" rel="noopener noreferrer">
            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 7l-5 5 5 5M16 7l5 5-5 5M14 4l-4 16"/></svg>
            GitHub
        </a>
    @endif

    @if (! empty($dev['email']))
        <a class="btn" href="mailto:{{ $dev['email'] }}">
            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18v12H3zM3 7l9 7 9-7"/></svg>
            Email
        </a>
    @endif

    @if (! empty($dev['facebook']))
        <a class="btn" href="{{ $dev['facebook'] }}" target="_blank" rel="noopener noreferrer">
            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="8" r="3.5"/><path d="M2.5 20a6.5 6.5 0 0 1 13 0M16 4.8a3.5 3.5 0 0 1 0 6.4M18.5 14.5A6.5 6.5 0 0 1 21.5 20"/></svg>
            Facebook
        </a>
    @endif
</div>
