<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Login | MRM System</title>
    
    <!-- Vite Entrypoint & Global JS Modules -->
    @vite(['resources/js/app.js'])
    
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
                        "on-surface-variant": "#43474e",
                        "on-surface": "#191c20",
                        "outline": "#73777f",
                        "on-tertiary-container": "#ffdbce",
                        "primary": "#3856c5",
                        "on-error": "#ffffff",
                        "tertiary": "#8a4f38",
                        "on-tertiary": "#ffffff",
                        "surface-container-high": "#e6e8ea",
                        "error": "#ba1a1a",
                        "surface-container": "#eceef0",
                        "surface": "#f7f9fb",
                        "surface-container-lowest-dark": "#0f1318",
                        "surface-container-dark": "#1b2024",
                        "on-surface-dark": "#e2e2e5"
                    }
                }
            }
        };
    </script>
    <style>
        body {
            font-family: 'Geist Sans', sans-serif;
        }
    </style>
</head>
<body class="bg-gradient-to-tr from-surface-container-low via-surface-bright to-primary-fixed/20 min-h-screen flex items-center justify-center p-4">

    <div class="max-w-md w-full bg-surface-container-lowest/80 backdrop-blur-md border border-outline-variant rounded-2xl shadow-xl p-8 space-y-6">
        
        <!-- Header -->
        <div class="text-center space-y-2">
            <div class="w-16 h-16 bg-primary-fixed text-primary rounded-2xl flex items-center justify-center mx-auto shadow-sm">
                <span class="material-symbols-outlined text-[36px]" data-icon="precision_manufacturing">precision_manufacturing</span>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-on-surface tracking-tight">MRM System</h1>
                <p class="text-xs font-semibold tracking-widest text-primary uppercase">Clinical Precision</p>
            </div>
        </div>

        @if(session('success'))
            <div class="p-3 bg-secondary-container text-on-secondary-fixed border border-outline-variant rounded-xl text-xs text-center font-medium">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="p-3 bg-error-container text-on-error-container border border-error rounded-xl text-xs">
                <ul class="list-disc pl-4 space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Login Form -->
        <form action="{{ route('login') }}" method="POST" class="space-y-4">
            @csrf
            
            <div>
                <label for="email" class="block text-xs font-semibold text-on-surface-variant mb-1 flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[14px]">mail</span>
                    Username / Email
                </label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                       placeholder="nama@peroniks.com"
                       class="w-full px-4 py-2.5 bg-surface-container border border-outline-variant rounded-xl text-sm text-on-surface placeholder-outline/50 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all"/>
            </div>

            <div>
                <label for="password" class="block text-xs font-semibold text-on-surface-variant mb-1 flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[14px]">lock</span>
                    Password
                </label>
                <input type="password" name="password" id="password" required
                       placeholder="••••••••"
                       class="w-full px-4 py-2.5 bg-surface-container border border-outline-variant rounded-xl text-sm text-on-surface placeholder-outline/50 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all"/>
            </div>

            <button type="submit" class="w-full py-3 bg-primary hover:bg-primary/90 text-on-primary rounded-xl font-semibold text-sm transition-all shadow-md hover:shadow-lg flex items-center justify-center gap-2">
                <span class="material-symbols-outlined text-[18px]">login</span>
                Masuk ke Sistem
            </button>
        </form>

        <!-- Footer -->
        <div class="text-center pt-4 border-t border-outline-variant text-[10px] text-on-surface-variant/70 space-y-1">
            <p class="font-bold">v1.0.0-MVP</p>
            <p>&copy; 2026 PT Peroni Karya Sentra. All rights reserved.</p>
        </div>

    </div>

</body>
</html>
