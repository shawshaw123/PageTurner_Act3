<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'LEBROn')</title>

    <!-- Fonts: Editorial Serif (Lora) + Clean UI Sans (Plus Jakarta Sans) -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=lora:500,600,700|plus-jakarta-sans:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Tailwind CSS CDN (fallback when Vite build manifest is absent) -->
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
                        },
                        boxShadow: {
                            'xs': '0 1px 2px 0 rgba(28, 33, 30, 0.04)',
                        }
                    }
                }
            };
        </script>
    @else
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

    <style>
        html {
            scroll-behavior: smooth;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            text-rendering: optimizeLegibility;
        }
        body {
            background-color: #FAF8F4;
            color: #1C211E;
            overflow-x: hidden;
        }
        :focus-visible {
            outline: 2px solid #243B2E;
            outline-offset: 2px;
        }
        img[loading="lazy"] {
            transition: opacity 0.25s ease-out;
        }
    </style>

    @stack('styles')
</head>
<body class="font-sans antialiased bg-brand-paper text-brand-ink flex flex-col min-h-screen">
    <!-- Skip Navigation Link (Accessibility) -->
    <a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-[200] focus:px-4 focus:py-2 focus:rounded-lg focus:bg-brand-darkgreen focus:text-white focus:font-semibold focus:shadow-lg">
        Skip to main content
    </a>

    @include('partials.navigation')

    <!-- Page Heading -->
    @hasSection('header')
        <header class="bg-white border-b border-brand-border">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                @yield('header')
            </div>
        </header>
    @endif

    <!-- Flash Messages -->
    @include('partials.flash-messages')

    <!-- Page Content -->
    <main id="main-content" class="flex-1 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @yield('content')
        </div>
    </main>

    @include('partials.footer')

    <!-- Subtle Floating AI Book Concierge (hidden on AI chat page itself) -->
    @unless(request()->routeIs('ai.chat'))
        <a href="{{ route('ai.chat') }}"
           class="fixed bottom-5 right-5 z-40 inline-flex items-center gap-2 rounded-full bg-brand-darkgreen text-white pl-3.5 pr-4 py-2.5 shadow-lg border border-white/10 hover:bg-brand-forest transition-colors group"
           aria-label="Open LEBROn AI Book Assistant">
            <span class="w-7 h-7 rounded-full bg-brand-gold/20 text-brand-gold flex items-center justify-center">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                </svg>
            </span>
            <span class="text-xs font-semibold tracking-wide hidden sm:inline">Ask AI Concierge</span>
        </a>
    @endunless

    <!-- Accessible Confirmation Modal -->
    <div id="pt-confirm-modal" class="hidden fixed inset-0 z-[150] flex items-center justify-center p-4 bg-black/50 backdrop-blur-[1px]" role="dialog" aria-modal="true" aria-labelledby="pt-confirm-title">
        <div class="bg-white rounded-2xl border border-brand-border shadow-xl max-w-md w-full p-6">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-full bg-red-50 text-red-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 id="pt-confirm-title" class="font-serif text-lg font-bold text-brand-ink">Confirm action</h3>
                    <p id="pt-confirm-message" class="mt-1 text-sm text-brand-muted"></p>
                </div>
            </div>
            <div class="mt-6 flex items-center justify-end gap-2.5">
                <button type="button" id="pt-confirm-cancel" class="px-4 py-2 rounded-lg border border-brand-border bg-white text-sm font-medium text-brand-ink hover:bg-brand-cream transition-colors">
                    Cancel
                </button>
                <button type="button" id="pt-confirm-ok" class="px-4 py-2 rounded-lg bg-red-600 text-sm font-semibold text-white hover:bg-red-700 transition-colors">
                    Confirm
                </button>
            </div>
        </div>
    </div>

    <script>
        (function () {
            // 1. Accessible Confirmation Modal for forms with data-confirm
            let pendingForm = null;
            const modal = document.getElementById('pt-confirm-modal');
            const msgEl = document.getElementById('pt-confirm-message');
            const okBtn = document.getElementById('pt-confirm-ok');
            const cancelBtn = document.getElementById('pt-confirm-cancel');

            function closeModal() {
                modal.classList.add('hidden');
                pendingForm = null;
            }

            if (modal) {
                cancelBtn.addEventListener('click', closeModal);
                modal.addEventListener('click', function (e) {
                    if (e.target === modal) closeModal();
                });
                document.addEventListener('keydown', function (e) {
                    if (e.key === 'Escape' && !modal.classList.contains('hidden')) closeModal();
                });
                okBtn.addEventListener('click', function () {
                    if (pendingForm) {
                        const formToSubmit = pendingForm;
                        formToSubmit.dataset.confirmed = 'true';
                        closeModal();
                        formToSubmit.submit();
                    }
                });
            }

            document.addEventListener('submit', function (e) {
                const form = e.target;
                if (!(form instanceof HTMLFormElement)) return;

                // Confirmation guard
                if (form.dataset.confirm && form.dataset.confirmed !== 'true') {
                    e.preventDefault();
                    pendingForm = form;
                    msgEl.textContent = form.dataset.confirm;
                    modal.classList.remove('hidden');
                    cancelBtn.focus();
                    return;
                }

                // Duplicate submission prevention on mutating forms
                const method = (form.getAttribute('method') || 'GET').toUpperCase();
                if (method !== 'GET' && !form.hasAttribute('data-no-loading')) {
                    if (form.dataset.submitting === 'true') {
                        e.preventDefault();
                        return;
                    }
                    form.dataset.submitting = 'true';
                    const btn = form.querySelector('button[type="submit"]:not([disabled])');
                    if (btn) {
                        btn.disabled = true;
                        btn.classList.add('opacity-70', 'cursor-wait');
                    }
                }
            });
        })();
    </script>

    @stack('scripts')
</body>
</html>
