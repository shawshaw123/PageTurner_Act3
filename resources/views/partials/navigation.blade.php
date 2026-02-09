<nav class="bg-brand-darkgreen text-white shadow-lg">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center">
                <!-- Logo -->
                <a href="{{ route('home') }}" class="text-xl font-bold">
                    PageTurner
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
                            <a href="{{ route('books.create') }}" class="hover:bg-brand-amber hover:text-brand-darkgreen px-3 py-2 rounded-md transition-colors">
                                Add Book
                            </a>
                            <a href="{{ route('categories.create') }}" class="hover:bg-brand-amber hover:text-brand-darkgreen px-3 py-2 rounded-md transition-colors">
                                Add Category
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
                    <a href="{{ route('orders.index') }}" class="hover:bg-brand-amber hover:text-brand-darkgreen px-3 py-2 rounded-md transition-colors">
                        My Orders
                    </a>
                    <span class="text-white/80">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="hover:bg-brand-amber hover:text-brand-darkgreen px-3 py-2 rounded-md transition-colors">
                            Logout
                        </button>
                    </form>
                @endauth
            </div>
        </div>
    </div>
</nav>
