<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>{{ $title ?? 'MRM System' }}</title>
    
    <!-- Tailwind CSS with Plugins -->
    <script src="{{ asset('js/tailwind.js') }}"></script>
    
    <!-- Google Fonts & Material Symbols -->
    <link href="{{ asset('css/material-symbols-outlined.css') }}" rel="stylesheet"/>
    <link href="{{ asset('css/geist-jetbrains.css') }}" rel="stylesheet"/>
    
    <!-- Tailwind Configuration from Design Spec -->
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "secondary-fixed": "#d3e4fe",
                        "surface-container-low": "#f2f4f6",
                        "surface-bright": "#f7f9fb",
                        "tertiary-fixed": "#ffdbce",
                        "surface-dim": "#d8dadc",
                        "on-error-container": "#93000a",
                        "on-secondary-fixed-variant": "#38485d",
                        "surface-variant": "#e0e3e5",
                        "inverse-surface": "#2d3133",
                        "outline-variant": "#c4c5d5",
                        "on-primary-fixed": "#001453",
                        "primary-fixed": "#dde1ff",
                        "surface-container-highest": "#e0e3e5",
                        "secondary": "#505f76",
                        "surface-tint": "#3755c3",
                        "on-primary-container": "#a8b8ff",
                        "on-primary": "#ffffff",
                        "on-tertiary-fixed": "#380d00",
                        "inverse-primary": "#b8c4ff",
                        "on-secondary": "#ffffff",
                        "surface-container-lowest": "#ffffff",
                        "error-container": "#ffdad6",
                        "secondary-container": "#d0e1fb",
                        "on-secondary-fixed": "#0b1c30",
                        "primary-container": "#1e40af",
                        "secondary-fixed-dim": "#b7c8e1",
                        "error": "#ba1a1a",
                        "on-secondary-container": "#54647a",
                        "on-tertiary": "#ffffff",
                        "background": "#f7f9fb",
                        "surface-container": "#eceef0",
                        "on-primary-fixed-variant": "#173bab",
                        "outline": "#757684",
                        "on-background": "#191c1e",
                        "on-surface": "#191c1e",
                        "on-tertiary-container": "#ffa583",
                        "on-tertiary-fixed-variant": "#802a00",
                        "surface": "#f7f9fb",
                        "primary-fixed-dim": "#b8c4ff",
                        "surface-container-high": "#e6e8ea",
                        "tertiary": "#611e00",
                        "inverse-on-surface": "#eff1f3",
                        "tertiary-container": "#872d00",
                        "tertiary-fixed-dim": "#ffb59a",
                        "on-error": "#ffffff",
                        "primary": "#00288e",
                        "on-surface-variant": "#444653"
                    },
                    "borderRadius": {
                        "DEFAULT": "0.125rem",
                        "lg": "0.25rem",
                        "xl": "0.5rem",
                        "full": "0.75rem"
                    },
                    "spacing": {
                        "container-max-width": "1440px",
                        "gutter": "16px",
                        "margin-mobile": "16px",
                        "unit": "4px",
                        "margin-desktop": "32px"
                    },
                    "fontFamily": {
                        "label-sm": ["JetBrains Mono"],
                        "headline-md": ["Geist"],
                        "headline-lg-mobile": ["Geist"],
                        "body-lg": ["Geist"],
                        "label-md": ["JetBrains Mono"],
                        "body-md": ["Geist"],
                        "headline-sm": ["Geist"],
                        "body-sm": ["Geist"],
                        "headline-lg": ["Geist"]
                    },
                    "fontSize": {
                        "label-sm": ["11px", {"lineHeight": "14px", "letterSpacing": "0.03em", "fontWeight": "500"}],
                        "headline-md": ["24px", {"lineHeight": "32px", "letterSpacing": "-0.01em", "fontWeight": "600"}],
                        "headline-lg-mobile": ["24px", {"lineHeight": "32px", "fontWeight": "600"}],
                        "body-lg": ["16px", {"lineHeight": "24px", "fontWeight": "400"}],
                        "label-md": ["12px", {"lineHeight": "16px", "letterSpacing": "0.02em", "fontWeight": "500"}],
                        "body-md": ["14px", {"lineHeight": "20px", "fontWeight": "400"}],
                        "headline-sm": ["20px", {"lineHeight": "28px", "fontWeight": "600"}],
                        "body-sm": ["13px", {"lineHeight": "18px", "fontWeight": "400"}],
                        "headline-lg": ["30px", {"lineHeight": "38px", "letterSpacing": "-0.02em", "fontWeight": "600"}]
                    }
                },
            },
        }
    </script>
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            display: inline-block;
            vertical-align: middle;
        }
        body { font-family: 'Geist', sans-serif; }
        .mono { font-family: 'JetBrains Mono', monospace; }
        .hide-scrollbar::-webkit-scrollbar { display: none; }
        .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
    @stack('styles')
</head>
<body class="bg-background text-on-surface selection:bg-primary-container selection:text-on-primary-container">

    @if ($sidebar ?? true)
        <!-- Sidebar Navigation -->
        <x-sidebar />
    @endif

    @if ($topbar ?? true)
        <!-- Top App Bar -->
        <x-topbar :title="$topbarTitle ?? ''" :subnav="$subnav ?? []" />
    @endif

    <!-- Content Area -->
    <main class="{{ ($sidebar ?? true) ? 'ml-52' : '' }} {{ ($topbar ?? true) ? 'mt-16' : '' }} p-margin-desktop max-w-[1440px] mx-auto">
        {{ $slot }}
    </main>

    @stack('scripts')
</body>
</html>
