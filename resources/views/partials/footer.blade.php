<footer class="bg-brand-darkgreen text-white/85 border-t border-white/10 mt-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-10">
            <div class="sm:col-span-2 lg:col-span-1">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-2">
                    <span class="w-8 h-8 rounded-lg bg-brand-gold text-brand-darkgreen flex items-center justify-center font-serif font-bold text-lg">P</span>
                    <span class="font-serif text-xl font-bold text-white"><span class="text-brand-gold">Page</span>Turner</span>
                </a>
                <p class="mt-3 text-sm text-white/70 leading-relaxed">
                    A curated online bookstore connecting readers with independent sellers, timeless literature, and modern non-fiction.
                </p>
            </div>

            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-brand-gold mb-4">Explore</h3>
                <ul class="space-y-2.5 text-sm">
                    <li><a href="{{ route('home') }}" class="text-white/75 hover:text-white transition-colors">Home</a></li>
                    <li><a href="{{ route('books.index') }}" class="text-white/75 hover:text-white transition-colors">Browse Catalog</a></li>
                    <li><a href="{{ route('categories.index') }}" class="text-white/75 hover:text-white transition-colors">Book Categories</a></li>
                    <li><a href="{{ route('ai.chat') }}" class="text-white/75 hover:text-white transition-colors">AI Book Concierge</a></li>
                </ul>
            </div>

            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-brand-gold mb-4">Account</h3>
                <ul class="space-y-2.5 text-sm">
                    @auth
                        <li><a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : (auth()->user()->isSeller() ? route('seller.dashboard') : route('user.dashboard')) }}" class="text-white/75 hover:text-white transition-colors">Dashboard</a></li>
                        <li><a href="{{ route('orders.index') }}" class="text-white/75 hover:text-white transition-colors">Orders</a></li>
                        <li><a href="{{ route('profile.edit') }}" class="text-white/75 hover:text-white transition-colors">Profile & Security</a></li>
                        <li><a href="{{ route('user.data-portability.index') }}" class="text-white/75 hover:text-white transition-colors">Data Portability</a></li>
                    @else
                        <li><a href="{{ route('login') }}" class="text-white/75 hover:text-white transition-colors">Sign in</a></li>
                        <li><a href="{{ route('register') }}" class="text-white/75 hover:text-white transition-colors">Create Account</a></li>
                    @endauth
                </ul>
            </div>

            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-brand-gold mb-4">Support</h3>
                <ul class="space-y-2.5 text-sm text-white/75">
                    <li>support@pageturner.com</li>
                    <li>(123) 456-7890</li>
                    <li class="text-white/60 text-xs pt-1">Mon–Sat, 9:00 AM – 6:00 PM</li>
                </ul>
            </div>
        </div>

        <div class="border-t border-white/10 mt-10 pt-6 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-white/60">
            <p>&copy; {{ date('Y') }} PageTurner Bookstore. All rights reserved.</p>
            <p>Crafted for readers and independent booksellers.</p>
        </div>
    </div>
</footer>
