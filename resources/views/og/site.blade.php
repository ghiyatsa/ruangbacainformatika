<svg width="1200" height="600" viewBox="0 0 1200 600" fill="none" xmlns="http://www.w3.org/2000/svg">
    <rect width="1200" height="600" fill="#F8FAFC" />
    <rect x="0" y="584" width="1200" height="16" fill="{{ $themeColor }}" />
    <rect x="72" y="72" width="1056" height="456" rx="36" fill="white" stroke="#E2E8F0" />

    <g transform="translate(96 96)">
        <rect x="0" y="0" width="152" height="152" rx="32" fill="#FFFFFF" />
        {!! $logoMarkup !!}
    </g>

    <text x="96" y="338" fill="#0F172A" font-size="72" font-weight="700" font-family="Inter, Segoe UI, Arial, sans-serif">
        <tspan x="96" dy="0">{{ $title }}</tspan>
        <tspan x="96" dy="64" fill="#475569" font-size="30" font-weight="500">{{ $subtitle }}</tspan>
    </text>
</svg>
