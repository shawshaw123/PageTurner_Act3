<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'PageTurner Bookstore')</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'brand-amber': '#FFBF00',
                        'brand-darkgreen': '#31472E',
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': { opacity: '0' },
                            '100%': { opacity: '1' },
                        }
                    },
                    animation: {
                        fadeIn: 'fadeIn 0.4s ease-in forwards',
                    }
                }
            }
        };
    </script>
    <style>
        html {
            scroll-behavior: smooth;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            text-rendering: optimizeLegibility;
        }
        body {
            overflow-x: hidden;
        }
        
        /* Smooth image loading without flicker */
        img[loading="lazy"] {
            animation: fadeIn 0.4s ease-in forwards;
            opacity: 0;
        }
        
        img[loading="lazy"].loaded {
            opacity: 1;
            animation: none;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const images = document.querySelectorAll('img[loading="lazy"]');
            images.forEach(img => {
                // If image is already cached/loaded
                if (img.complete) {
                    img.classList.add('loaded');
                    img.style.opacity = '1';
                }
                // When image finishes loading
                img.addEventListener('load', function() {
                    this.classList.add('loaded');
                    this.style.opacity = '1';
                });
                // If image fails to load
                img.addEventListener('error', function() {
                    this.classList.add('loaded');
                    this.style.opacity = '1';
                });
            });
        });
    </script>
    
    <!-- Scripts -->
    @if(file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    @stack('styles')
</head>
<body class="font-sans antialiased bg-white text-gray-900">
    <div class="min-h-screen relative">
        @include('partials.navigation')
        
        <!-- Page Heading -->
        @hasSection('header')
        <header class="bg-white border-b border-gray-100">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                @yield('header')
            </div>
        </header>
        @endif
        
        <!-- Flash Messages -->
        @include('partials.flash-messages')
        
        <!-- Page Content -->
        <main class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                @yield('content')
            </div>
        </main>
        
        @include('partials.footer')
    </div>
    
    @stack('scripts')
</body>
</html>
