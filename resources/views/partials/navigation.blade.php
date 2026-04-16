<nav class="bg-brand-darkgreen text-white shadow-md sticky top-0 z-[100]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <div class="flex items-center">
                <!-- Logo -->
                <a href="{{ route('home') }}" class="text-xl font-bold">
                    <span class="text-brand-amber">Page</span>Turner
                </a>
                
                <!-- Navigation Links -->
                <div class="hidden md:flex ml-10 space-x-4">
                    <a href="{{ route('home') }}" class="hover:bg-brand-amber hover:text-brand-darkgreen px-3 py-2 rounded-md transition-colors">
                        Home
                    </a>
                    <a href="{{ route('books.index') }}" class="hover:bg-brand-amber hover:text-brand-darkgreen px-3 py-2 rounded-md transition-colors">
                        Books
                    </a>
                    <a href="{{ route('categories.index') }}" class="hover:bg-brand-amber hover:text-brand-darkgreen px-3 py-2 rounded-md transition-colors">
                        Categories
                    </a>
                    
                    @auth
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="hover:bg-brand-amber hover:text-brand-darkgreen px-3 py-2 rounded-md transition-colors font-medium">
                                Dashboard
                            </a>
                            <a href="{{ route('books.create') }}" class="hover:bg-brand-amber hover:text-brand-darkgreen px-3 py-2 rounded-md transition-colors">
                                Add Book
                            </a>
                            <a href="{{ route('categories.create') }}" class="hover:bg-brand-amber hover:text-brand-darkgreen px-3 py-2 rounded-md transition-colors">
                                Add Category
                            </a>
                        @else
                            <a href="{{ route('user.dashboard') }}" class="hover:bg-brand-amber hover:text-brand-darkgreen px-3 py-2 rounded-md transition-colors font-medium">
                                Dashboard
                            </a>
                        @endif
                    @endauth
                </div>
            </div>
            
            <!-- Right Side -->
            <div class="flex items-center space-x-4">
                @guest
                    <a href="{{ route('login') }}" class="hover:bg-brand-amber hover:text-brand-darkgreen px-3 py-2 rounded-md transition-colors">
                        Login
                    </a>
                    <a href="{{ route('register') }}" class="bg-brand-amber text-brand-darkgreen px-4 py-2 rounded-md font-medium hover:bg-white transition-colors">
                        Register
                    </a>
                @endguest
                
                @auth
                    <!-- Cart Link -->
                    <a href="{{ route('cart.index') }}" class="hover:bg-brand-amber hover:text-brand-darkgreen px-3 py-2 rounded-md transition-colors relative">
                        <svg class="w-5 h-5 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        Cart
                        @php
                            $cartCount = \App\Http\Controllers\CartController::getCartCount();
                        @endphp
                        @if($cartCount > 0)
                            <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                                {{ $cartCount }}
                            </span>
                        @endif
                    </a>
                    
                    <a href="{{ route('orders.index') }}" class="hover:bg-brand-amber hover:text-brand-darkgreen px-3 py-2 rounded-md transition-colors">
                        My Orders
                    </a>

                    @if(auth()->user()->isAdmin())
                        <!-- Notifications Link -->
                        <a href="{{ route('notifications.index') }}" class="hover:bg-brand-amber hover:text-brand-darkgreen px-3 py-2 rounded-md transition-colors relative">
                            <svg class="w-5 h-5 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                            Notifications
                            @php
                                $unreadCount = auth()->user()->unreadNotifications->count();
                            @endphp
                            @if($unreadCount > 0)
                                <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                                    {{ $unreadCount }}
                                </span>
                            @endif
                        </a>
                    @endif

                    <!-- Profile Dropdown with Logout -->
                    <div class="relative">
                        <button onclick="toggleDropdown()" class="flex items-center space-x-2 hover:bg-brand-amber hover:text-brand-darkgreen px-3 py-2 rounded-md transition-colors">
                            <span class="text-white/80">{{ auth()->user()->name }}</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div id="profileDropdown" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-[9999] border border-gray-200">
                            <!-- User Info Header -->
                            <div class="px-4 py-2 border-b border-gray-100">
                                <p class="text-sm font-medium text-gray-900">{{ auth()->user()->name }}</p>
                                <p class="text-xs text-gray-500">{{ auth()->user()->email }}</p>
                                @if(auth()->user()->isAdmin())
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800 mt-1">
                                        Admin
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 mt-1">
                                        Customer
                                    </span>
                                @endif
                            </div>
                            
                            <!-- Menu Items -->
                            <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">
                                Profile & Security
                            </a>
                            <a href="{{ route('two-factor.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">
                                Two-Factor Auth
                            </a>
                            
                            @if(auth()->user()->isAdmin())
                                <a href="{{ route('orders.statistics') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">
                                    Order Statistics
                                </a>
                            @endif
                            
                            <div class="border-t border-gray-100"></div>
                            
                            <!-- Logout Button in Dropdown -->
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors font-medium">
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                @endauth
            </div>
        </div>
    </div>
</nav>

<script>
    // Toggle dropdown function
    function toggleDropdown() {
        const dropdown = document.getElementById('profileDropdown');
        if (dropdown) {
            dropdown.classList.toggle('hidden');
        }
    }
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        const dropdown = document.getElementById('profileDropdown');
        const button = e.target.closest('button[onclick="toggleDropdown()"]');
        
        if (dropdown && !button && !dropdown.contains(e.target)) {
            dropdown.classList.add('hidden');
        }
    });
    
    // Close dropdown when pressing Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const dropdown = document.getElementById('profileDropdown');
            if (dropdown) {
                dropdown.classList.add('hidden');
            }
        }
    });
</script>
