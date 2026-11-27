<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'PageTurner Bookstore') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=lora:500,600,700|plus-jakarta-sans:400,500,600,700&display=swap" rel="stylesheet" />

    @if(!file_exists(public_path('build/manifest.json')))
        <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
        <script>
            tailwind.config = {
                theme: {
                    extend: {
                        fontFamily: {
                            sans: ['Plus Jakarta Sans', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                            serif: ['Lora', 'Georgia', 'Cambria', 'Times New Roman', 'serif'],
                        },
                        colors: {
                            'brand-amber': '#D99B26',
                            'brand-gold': '#F2B84B',
                            'brand-darkgreen': '#243B2E',
                            'brand-forest': '#1B2D23',
                            'brand-paper': '#FAF8F4',
                            'brand-cream': '#F3EFE7',
                            'brand-border': '#E6E0D4',
                            'brand-ink': '#1C211E',
                            'brand-muted': '#5C6660',
                        }
                    }
                }
            };
        </script>
    @else
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="font-sans text-brand-ink antialiased bg-brand-paper min-h-screen flex flex-col justify-center py-10 px-4 sm:px-6">
    <div class="w-full max-w-md mx-auto">
        <div class="text-center mb-6">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-2.5 group">
                <span class="w-10 h-10 rounded-xl bg-brand-darkgreen text-brand-gold flex items-center justify-center font-serif font-bold text-xl shadow-sm">P</span>
                <span class="font-serif text-3xl font-bold tracking-tight text-brand-darkgreen">
                    <span class="text-brand-amber">Page</span>Turner
                </span>
            </a>
            <p class="mt-2 text-sm text-brand-muted">Your neighborhood bookstore, online.</p>
        </div>

        <div class="bg-white border border-brand-border shadow-sm rounded-2xl p-6 sm:p-8">
            {{ $slot }}
        </div>

        <p class="mt-6 text-center text-xs text-brand-muted">
            <a href="{{ route('home') }}" class="hover:text-brand-darkgreen transition-colors">&larr; Back to Bookstore</a>
        </p>
    </div>
</body>
</html>
