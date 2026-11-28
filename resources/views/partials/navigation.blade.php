@php
    $navLink = function (bool $active): string {
        return $active
            ? 'px-3 py-2 rounded-lg text-sm font-semibold bg-white/15 text-brand-gold transition-colors'
            : 'px-3 py-2 rounded-lg text-sm font-medium text-white/85 hover:text-white hover:bg-white/10 transition-colors';
    };
    $mobileLink = function (bool $active): string {
        return $active
            ? 'block px-3 py-2.5 rounded-lg text-sm font-semibold bg-white/15 text-brand-gold'
            : 'block px-3 py-2.5 rounded-lg text-sm font-medium text-white/90 hover:bg-white/10 hover:text-white';
    };
    $isAdminSection = request()->routeIs('admin.*', 'books.create', 'categories.create', 'orders.statistics');
@endphp

<nav class="bg-brand-darkgreen text-white border-b border-white/10 sticky top-0 z-50 shadow-xs" aria-label="Primary">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16 gap-4">
            <!-- Left: Brand + Primary Links -->
            <div class="flex items-center gap-6 min-w-0">
                <a href="{{ route('home') }}" class="flex items-center gap-2 shrink-0 group">
                    <span class="w-8 h-8 rounded-lg bg-brand-gold text-brand-darkgreen flex items-center justify-center font-serif font-bold text-lg shadow-xs">
                        L
                    </span>
                    <span class="font-serif text-xl font-bold tracking-tight">
                        <span class="text-white">LEBR</span><span class="text-brand-gold">On</span>
                    </span>
                </a>

                <!-- Desktop Navigation Links -->
                <div class="hidden lg:flex items-center gap-1">
                    <a href="{{ route('home') }}" class="{{ $navLink(request()->routeIs('home')) }}" @if(request()->routeIs('home')) aria-current="page" @endif>
                        Home
                    </a>
                    <a href="{{ route('books.index') }}" class="{{ $navLink(request()->routeIs('books.index', 'books.show')) }}" @if(request()->routeIs('books.index', 'books.show')) aria-current="page" @endif>
                        Books
                    </a>
                    <a href="{{ route('categories.index') }}" class="{{ $navLink(request()->routeIs('categories.index', 'categories.show')) }}" @if(request()->routeIs('categories.index', 'categories.show')) aria-current="page" @endif>
                        Categories
                    </a>
                    <a href="{{ route('ai.chat') }}" class="{{ $navLink(request()->routeIs('ai.chat')) }}" @if(request()->routeIs('ai.chat')) aria-current="page" @endif>
                        AI Concierge
                    </a>

                    @auth
                        <span class="mx-1.5 h-5 w-px bg-white/15" aria-hidden="true"></span>

                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="{{ $navLink(request()->routeIs('admin.dashboard')) }}" @if(request()->routeIs('admin.dashboard')) aria-current="page" @endif>
                                Dashboard
                            </a>

                            <!-- Admin Management Dropdown -->
                            <div class="relative" data-dropdown>
                                <button type="button"
                                        data-dropdown-toggle="adminDropdown"
                                        aria-expanded="false"
                                        aria-controls="adminDropdown"
                                        class="{{ $navLink($isAdminSection && !request()->routeIs('admin.dashboard')) }} inline-flex items-center gap-1">
                                    <span>Manage</span>
                                    <svg class="w-4 h-4 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>
                                <div id="adminDropdown" class="hidden absolute left-0 mt-2 w-64 bg-white text-brand-ink rounded-xl shadow-xl border border-brand-border py-2 z-[90]">
                                    <div class="px-3.5 py-1.5 text-[11px] font-bold uppercase tracking-wider text-brand-muted">Catalog & Marketplace</div>
                                    <a href="{{ route('books.create') }}" class="flex items-center gap-2.5 px-3.5 py-2 text-sm hover:bg-brand-cream transition-colors">Add Book</a>
                                    <a href="{{ route('categories.create') }}" class="flex items-center gap-2.5 px-3.5 py-2 text-sm hover:bg-brand-cream transition-colors">Add Category</a>
                                    <a href="{{ route('admin.sellers.index') }}" class="flex items-center gap-2.5 px-3.5 py-2 text-sm hover:bg-brand-cream transition-colors">Seller Approvals</a>
                                    <a href="{{ route('orders.statistics') }}" class="flex items-center gap-2.5 px-3.5 py-2 text-sm hover:bg-brand-cream transition-colors">Order Statistics</a>
                                    <a href="{{ route('admin.paymongo.index') }}" class="flex items-center gap-2.5 px-3.5 py-2 text-sm hover:bg-brand-cream transition-colors">PayMongo Payments</a>

                                    <div class="my-1.5 border-t border-brand-border"></div>
                                    <div class="px-3.5 py-1.5 text-[11px] font-bold uppercase tracking-wider text-brand-muted">Operations & Monitoring</div>
                                    <a href="{{ route('admin.import-export.index') }}" class="flex items-center gap-2.5 px-3.5 py-2 text-sm hover:bg-brand-cream transition-colors">Import & Export</a>
                                    <a href="{{ route('admin.backup.index') }}" class="flex items-center gap-2.5 px-3.5 py-2 text-sm hover:bg-brand-cream transition-colors">Database Backups</a>
                                    <a href="{{ route('admin.audit.index') }}" class="flex items-center gap-2.5 px-3.5 py-2 text-sm hover:bg-brand-cream transition-colors">Audit Logs</a>
                                    <a href="{{ route('admin.api-rate-limits.index') }}" class="flex items-center gap-2.5 px-3.5 py-2 text-sm hover:bg-brand-cream transition-colors">API Rate Limits</a>
                                    <a href="{{ route('admin.ai.dashboard') }}" class="flex items-center gap-2.5 px-3.5 py-2 text-sm hover:bg-brand-cream transition-colors">AI Usage & Monitoring</a>
                                    <a href="{{ route('admin.api-docs') }}" class="flex items-center gap-2.5 px-3.5 py-2 text-sm hover:bg-brand-cream transition-colors">REST API Docs (Swagger)</a>
                                    <a href="{{ route('admin.integration-preview') }}" class="flex items-center gap-2.5 px-3.5 py-2 text-sm hover:bg-brand-cream transition-colors">Integration Preview (Mock)</a>
                                </div>
                            </div>
                        @elseif(auth()->user()->isSeller())
                            <a href="{{ route('seller.dashboard') }}" class="{{ $navLink(request()->routeIs('seller.dashboard')) }}" @if(request()->routeIs('seller.dashboard')) aria-current="page" @endif>
                                Seller Hub
                            </a>
                            @if(auth()->user()->isApprovedSeller())
                                <a href="{{ route('seller.books') }}" class="{{ $navLink(request()->routeIs('seller.books')) }}" @if(request()->routeIs('seller.books')) aria-current="page" @endif>
                                    My Listings
                                </a>
                                <a href="{{ route('books.create') }}" class="{{ $navLink(request()->routeIs('books.create')) }}" @if(request()->routeIs('books.create')) aria-current="page" @endif>
                                    Add Listing
                                </a>
                            @endif
                        @else
                            <a href="{{ route('user.dashboard') }}" class="{{ $navLink(request()->routeIs('user.dashboard')) }}" @if(request()->routeIs('user.dashboard')) aria-current="page" @endif>
                                Dashboard
                            </a>
                        @endif
                    @endauth
                </div>
            </div>

            <!-- Right: Actions & User Menu -->
            <div class="flex items-center gap-2 sm:gap-3">
                @guest
                    <a href="{{ route('login') }}" class="hidden sm:inline-flex px-3.5 py-2 rounded-lg text-sm font-medium text-white/90 hover:text-white hover:bg-white/10 transition-colors">
                        Sign in
                    </a>
                    <a href="{{ route('register') }}" class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-semibold bg-brand-gold text-brand-darkgreen hover:bg-white transition-colors shadow-xs">
                        Get Started
                    </a>
                @endguest

                @auth
                    @unless(auth()->user()->isSeller())
                        @php $cartCount = \App\Http\Controllers\CartController::getCartCount(); @endphp
                        <a href="{{ route('cart.index') }}"
                           class="relative inline-flex items-center gap-1.5 px-2.5 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('cart.*') ? 'bg-white/15 text-brand-gold' : 'text-white/90 hover:bg-white/10 hover:text-white' }} transition-colors"
                           aria-label="Shopping cart{{ $cartCount > 0 ? ', ' . $cartCount . ' items' : '' }}">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.85" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            <span class="hidden sm:inline">Cart</span>
                            @if($cartCount > 0)
                                <span class="inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1 rounded-full bg-brand-gold text-brand-darkgreen text-[11px] font-bold">
                                    {{ $cartCount }}
                                </span>
                            @endif
                        </a>
                    @endunless

                    <a href="{{ route('orders.index') }}"
                       class="hidden md:inline-flex {{ $navLink(request()->routeIs('orders.index', 'orders.show', 'orders.track')) }}"
                       @if(request()->routeIs('orders.index', 'orders.show', 'orders.track')) aria-current="page" @endif>
                        {{ auth()->user()->isBuyer() ? 'My Orders' : 'Orders' }}
                    </a>

                    @if(auth()->user()->isAdmin() || auth()->user()->isSeller())
                        @php $unreadCount = auth()->user()->unreadNotifications->count(); @endphp
                        <a href="{{ route('notifications.index') }}"
                           class="relative inline-flex items-center p-2 rounded-lg {{ request()->routeIs('notifications.*') ? 'bg-white/15 text-brand-gold' : 'text-white/85 hover:bg-white/10 hover:text-white' }} transition-colors"
                           aria-label="Notifications{{ $unreadCount > 0 ? ', ' . $unreadCount . ' unread' : '' }}">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.85" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                            @if($unreadCount > 0)
                                <span class="absolute -top-0.5 -right-0.5 min-w-[1.15rem] h-[1.15rem] px-1 rounded-full bg-red-500 text-white text-[10px] font-bold flex items-center justify-center">
                                    {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                                </span>
                            @endif
                        </a>
                    @endif

                    <!-- Profile Dropdown -->
                    <div class="relative" data-dropdown>
                        <button type="button"
                                data-dropdown-toggle="profileDropdown"
                                aria-expanded="false"
                                aria-controls="profileDropdown"
                                class="flex items-center gap-2 pl-2 pr-2.5 py-1.5 rounded-lg hover:bg-white/10 transition-colors">
                            <span class="w-7 h-7 rounded-full bg-brand-gold/20 border border-brand-gold/40 text-brand-gold text-xs font-bold flex items-center justify-center uppercase">
                                {{ substr(auth()->user()->name, 0, 1) }}
                            </span>
                            <span class="hidden sm:inline text-sm font-medium text-white/95 max-w-[120px] truncate">{{ auth()->user()->name }}</span>
                            <svg class="w-4 h-4 text-white/70" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <div id="profileDropdown" class="hidden absolute right-0 mt-2 w-60 bg-white text-brand-ink rounded-xl shadow-xl border border-brand-border py-2 z-[90]">
                            <div class="px-4 py-2.5 border-b border-brand-border">
                                <p class="text-sm font-semibold text-brand-ink truncate">{{ auth()->user()->name }}</p>
                                <p class="text-xs text-brand-muted truncate">{{ auth()->user()->email }}</p>
                                <div class="mt-1.5 flex flex-wrap gap-1.5">
                                    <x-badge :variant="match(auth()->user()->role) { 'admin' => 'brand', 'seller' => 'warning', default => 'info' }">
                                        {{ auth()->user()->role_label }}
                                    </x-badge>
                                    @if(auth()->user()->isSeller() && !auth()->user()->isApprovedSeller())
                                        <x-badge variant="neutral">{{ ucfirst(auth()->user()->seller_status ?? 'pending') }}</x-badge>
                                    @endif
                                </div>
                            </div>

                            <div class="py-1">
                                <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-brand-ink hover:bg-brand-cream transition-colors">Profile & Security</a>
                                <a href="{{ route('two-factor.index') }}" class="block px-4 py-2 text-sm text-brand-ink hover:bg-brand-cream transition-colors">Two-Factor Auth</a>
                                <a href="{{ route('api-tokens.index') }}" class="block px-4 py-2 text-sm text-brand-ink hover:bg-brand-cream transition-colors">API Tokens</a>
                                <a href="{{ route('user.data-portability.index') }}" class="block px-4 py-2 text-sm text-brand-ink hover:bg-brand-cream transition-colors">Data Portability</a>
                                @if(auth()->user()->isAdmin())
                                    <a href="{{ route('orders.statistics') }}" class="block px-4 py-2 text-sm text-brand-ink hover:bg-brand-cream transition-colors">Order Statistics</a>
                                @endif
                            </div>

                            <div class="border-t border-brand-border pt-1">
                                <form method="POST" action="{{ route('logout') }}" data-no-loading>
                                    @csrf
                                    <button type="submit" class="block w-full text-left px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-50 transition-colors">
                                        Sign out
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endauth

                <!-- Mobile Menu Toggle -->
                <button type="button"
                        id="mobileMenuBtn"
                        aria-expanded="false"
                        aria-controls="mobileMenu"
                        aria-label="Toggle navigation menu"
                        class="lg:hidden inline-flex items-center justify-center p-2 rounded-lg text-white/90 hover:bg-white/10 hover:text-white transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Navigation Panel -->
    <div id="mobileMenu" class="hidden lg:hidden bg-brand-forest border-t border-white/10 px-4 pt-3 pb-5 space-y-1">
        <a href="{{ route('home') }}" class="{{ $mobileLink(request()->routeIs('home')) }}">Home</a>
        <a href="{{ route('books.index') }}" class="{{ $mobileLink(request()->routeIs('books.index', 'books.show')) }}">Books</a>
        <a href="{{ route('categories.index') }}" class="{{ $mobileLink(request()->routeIs('categories.index', 'categories.show')) }}">Categories</a>
        <a href="{{ route('ai.chat') }}" class="{{ $mobileLink(request()->routeIs('ai.chat')) }}">AI Concierge</a>

        @guest
            <div class="pt-3 mt-3 border-t border-white/10 flex flex-col gap-2">
                <a href="{{ route('login') }}" class="block text-center px-4 py-2.5 rounded-lg text-sm font-medium bg-white/10 text-white">Sign in</a>
                <a href="{{ route('register') }}" class="block text-center px-4 py-2.5 rounded-lg text-sm font-semibold bg-brand-gold text-brand-darkgreen">Create Account</a>
            </div>
        @endguest

        @auth
            <div class="pt-3 mt-3 border-t border-white/10 space-y-1">
                <a href="{{ route('orders.index') }}" class="{{ $mobileLink(request()->routeIs('orders.*')) }}">{{ auth()->user()->isBuyer() ? 'My Orders' : 'Orders' }}</a>

                @if(auth()->user()->isAdmin())
                    <p class="px-3 pt-2 text-[11px] font-bold uppercase tracking-wider text-white/50">Administration</p>
                    <a href="{{ route('admin.dashboard') }}" class="{{ $mobileLink(request()->routeIs('admin.dashboard')) }}">Admin Dashboard</a>
                    <a href="{{ route('books.create') }}" class="{{ $mobileLink(request()->routeIs('books.create')) }}">Add Book</a>
                    <a href="{{ route('categories.create') }}" class="{{ $mobileLink(request()->routeIs('categories.create')) }}">Add Category</a>
                    <a href="{{ route('admin.sellers.index') }}" class="{{ $mobileLink(request()->routeIs('admin.sellers.*')) }}">Seller Approvals</a>
                    <a href="{{ route('admin.paymongo.index') }}" class="{{ $mobileLink(request()->routeIs('admin.paymongo.*')) }}">PayMongo Payments</a>
                    <a href="{{ route('admin.import-export.index') }}" class="{{ $mobileLink(request()->routeIs('admin.import-export.*', 'admin.import.*', 'admin.export.*')) }}">Import & Export</a>
                    <a href="{{ route('admin.backup.index') }}" class="{{ $mobileLink(request()->routeIs('admin.backup.*')) }}">Database Backups</a>
                    <a href="{{ route('admin.audit.index') }}" class="{{ $mobileLink(request()->routeIs('admin.audit.*')) }}">Audit Logs</a>
                    <a href="{{ route('admin.api-rate-limits.index') }}" class="{{ $mobileLink(request()->routeIs('admin.api-rate-limits.*')) }}">API Rate Limits</a>
                    <a href="{{ route('admin.ai.dashboard') }}" class="{{ $mobileLink(request()->routeIs('admin.ai.dashboard')) }}">AI Monitoring</a>
                    <a href="{{ route('admin.api-docs') }}" class="{{ $mobileLink(request()->routeIs('admin.api-docs')) }}">REST API Docs</a>
                    <a href="{{ route('admin.integration-preview') }}" class="{{ $mobileLink(request()->routeIs('admin.integration-preview')) }}">Integration Preview</a>
                @elseif(auth()->user()->isSeller())
                    <a href="{{ route('seller.dashboard') }}" class="{{ $mobileLink(request()->routeIs('seller.dashboard')) }}">Seller Hub</a>
                    @if(auth()->user()->isApprovedSeller())
                        <a href="{{ route('seller.books') }}" class="{{ $mobileLink(request()->routeIs('seller.books')) }}">My Listings</a>
                        <a href="{{ route('books.create') }}" class="{{ $mobileLink(request()->routeIs('books.create')) }}">Add Listing</a>
                    @endif
                @else
                    <a href="{{ route('user.dashboard') }}" class="{{ $mobileLink(request()->routeIs('user.dashboard')) }}">My Dashboard</a>
                @endif
            </div>
        @endauth
    </div>
</nav>

<script>
    (function () {
        const mobileBtn = document.getElementById('mobileMenuBtn');
        const mobileMenu = document.getElementById('mobileMenu');

        if (mobileBtn && mobileMenu) {
            mobileBtn.addEventListener('click', function () {
                const open = !mobileMenu.classList.toggle('hidden');
                mobileBtn.setAttribute('aria-expanded', String(open));
            });
        }

        function closeAllDropdowns(exceptId) {
            document.querySelectorAll('[data-dropdown-toggle]').forEach(function (btn) {
                const targetId = btn.getAttribute('data-dropdown-toggle');
                const menu = document.getElementById(targetId);
                if (menu && targetId !== exceptId) {
                    menu.classList.add('hidden');
                    btn.setAttribute('aria-expanded', 'false');
                }
            });
        }

        document.querySelectorAll('[data-dropdown-toggle]').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.stopPropagation();
                const targetId = btn.getAttribute('data-dropdown-toggle');
                const menu = document.getElementById(targetId);
                if (!menu) return;
                const willOpen = menu.classList.contains('hidden');
                closeAllDropdowns(willOpen ? targetId : null);
                menu.classList.toggle('hidden', !willOpen);
                btn.setAttribute('aria-expanded', String(willOpen));
            });
        });

        document.addEventListener('click', function (e) {
            if (!e.target.closest('[data-dropdown]')) {
                closeAllDropdowns(null);
            }
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeAllDropdowns(null);
                if (mobileMenu && !mobileMenu.classList.contains('hidden')) {
                    mobileMenu.classList.add('hidden');
                    if (mobileBtn) mobileBtn.setAttribute('aria-expanded', 'false');
                }
            }
        });
    })();
</script>
