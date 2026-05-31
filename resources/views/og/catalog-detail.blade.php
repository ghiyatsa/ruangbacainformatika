<svg width="1200" height="600" viewBox="0 0 1200 600" fill="none" xmlns="http://www.w3.org/2000/svg">
    <rect width="1200" height="600" fill="#F8FAFC" />
    <rect x="0" y="584" width="1200" height="16" fill="{{ $themeColor }}" />
    <rect x="72" y="72" width="1056" height="456" rx="36" fill="white" stroke="#E2E8F0" />

    <rect x="96" y="96" width="170" height="38" rx="19" fill="#E2E8F0" />
    <text x="181" y="120" text-anchor="middle" fill="#334155" font-size="18" font-weight="700" font-family="Inter, Segoe UI, Arial, sans-serif">{{ $label }}</text>

    <g>
        @php($titleY = 198)
        @foreach ($titleLines as $line)
            <text x="96" y="{{ $titleY + ($loop->index * 64) }}" fill="#111827" font-size="54" font-weight="700" font-family="Inter, Segoe UI, Arial, sans-serif">{{ $line }}</text>
        @endforeach
    </g>

    <g>
        @php($authorY = 430)
        @foreach ($authorLines as $line)
            <text x="96" y="{{ $authorY + ($loop->index * 34) }}" fill="#475569" font-size="28" font-weight="500" font-family="Inter, Segoe UI, Arial, sans-serif">{{ $line }}</text>
        @endforeach
    </g>

    <text x="96" y="520" fill="#94A3B8" font-size="22" font-weight="600" font-family="Inter, Segoe UI, Arial, sans-serif">{{ $siteName }}</text>

    <rect x="904" y="96" width="200" height="280" rx="28" fill="#F8FAFC" stroke="#E2E8F0" />
    <g transform="translate(904 136)">
        {!! $logoMarkup !!}
    </g>
</svg>
