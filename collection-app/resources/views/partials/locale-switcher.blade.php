@php
    $localeLabels = config('locales.labels', []);
    $supported = config('locales.supported', ['en', 'th']);
@endphp
<details class="nav-lang">
    <summary class="nav-pill">{{ __('ui.language') }}: {{ $localeLabels[app()->getLocale()] ?? strtoupper(app()->getLocale()) }}</summary>
    <div class="nav-lang-menu" role="menu">
        @foreach ($supported as $loc)
            <a
                href="{{ route('locale.switch', $loc) }}"
                role="menuitem"
                @class(['nav-lang-item', 'nav-lang-item--active' => app()->getLocale() === $loc])
            >{{ $localeLabels[$loc] ?? $loc }}</a>
        @endforeach
    </div>
</details>
