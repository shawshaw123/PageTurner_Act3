<footer class="bg-brand-darkgreen text-white py-12 mt-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
            <div>
                <h3 class="text-xl font-bold mb-4 text-brand-amber">PageTurner Bookstore</h3>
                <p class="text-gray-300">Your destination for quality books at great prices.</p>
            </div>
            
            <div>
                <h3 class="text-lg font-semibold mb-4 border-b border-brand-amber/30 pb-2 inline-block">Quick Links</h3>
                <ul class="space-y-2">
                    <li><a href="{{ route('home') }}" class="text-gray-300 hover:text-brand-amber transition-colors">Home</a></li>
                    <li><a href="{{ route('books.index') }}" class="text-gray-300 hover:text-brand-amber transition-colors">Browse Books</a></li>
                    <li><a href="{{ route('categories.index') }}" class="text-gray-300 hover:text-brand-amber transition-colors">Categories</a></li>
                </ul>
            </div>
            
            <div>
                <h3 class="text-lg font-semibold mb-4 border-b border-brand-amber/30 pb-2 inline-block">Contact</h3>
                <p class="text-gray-300">Email: support@pageturner.com</p>
                <p class="text-gray-300">Phone: (123) 456-7890</p>
            </div>
        </div>
        
        <div class="border-t border-brand-amber/20 mt-12 pt-8 text-center text-gray-400">
            <p>&copy; {{ date('Y') }} PageTurner Bookstore. All rights reserved.</p>
        </div>
    </div>
</footer>
