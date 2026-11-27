<x-guest-layout>
    <div class="mb-6">
        <h1 class="font-serif text-2xl font-bold text-brand-ink">Create an account</h1>
        <p class="text-sm text-brand-muted mt-1">Join as a reader or register an independent bookshop.</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <fieldset>
            <legend class="block text-sm font-medium text-brand-ink mb-2">I want to</legend>
            <div class="grid grid-cols-2 gap-3">
                <label class="flex items-start gap-2.5 border border-brand-border rounded-xl p-3 cursor-pointer hover:bg-brand-paper has-[:checked]:border-brand-darkgreen has-[:checked]:bg-brand-cream/70 transition-colors">
                    <input type="radio" name="account_type" value="buyer" class="mt-0.5 text-brand-darkgreen focus:ring-brand-darkgreen" @checked(old('account_type', 'buyer') === 'buyer')>
                    <span>
                        <span class="block font-semibold text-sm text-brand-ink">Buy books</span>
                        <span class="block text-xs text-brand-muted">Browse, order &amp; review</span>
                    </span>
                </label>
                <label class="flex items-start gap-2.5 border border-brand-border rounded-xl p-3 cursor-pointer hover:bg-brand-paper has-[:checked]:border-brand-darkgreen has-[:checked]:bg-brand-cream/70 transition-colors">
                    <input type="radio" name="account_type" value="seller" class="mt-0.5 text-brand-darkgreen focus:ring-brand-darkgreen" @checked(old('account_type') === 'seller')>
                    <span>
                        <span class="block font-semibold text-sm text-brand-ink">Sell books</span>
                        <span class="block text-xs text-brand-muted">Independent bookstore</span>
                    </span>
                </label>
            </div>
            @error('account_type')
                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
            @enderror
        </fieldset>

        {{-- Seller Store Fields --}}
        <div id="store-fields" class="space-y-4 p-4 rounded-xl bg-brand-paper border border-brand-border {{ old('account_type') === 'seller' ? '' : 'hidden' }}">
            <div>
                <label for="store_name" class="block text-sm font-medium text-brand-ink mb-1">Store Name <span class="text-red-500">*</span></label>
                <input id="store_name" type="text" name="store_name" value="{{ old('store_name') }}" maxlength="255"
                       class="w-full rounded-lg border-brand-border text-sm focus:border-brand-darkgreen focus:ring-brand-darkgreen">
                @error('store_name')
                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="store_description" class="block text-sm font-medium text-brand-ink mb-1">About your store <span class="text-brand-muted font-normal">(optional)</span></label>
                <textarea id="store_description" name="store_description" rows="2" maxlength="1000"
                          class="w-full rounded-lg border-brand-border text-sm focus:border-brand-darkgreen focus:ring-brand-darkgreen">{{ old('store_description') }}</textarea>
            </div>
            <p class="text-xs text-brand-muted">An administrator reviews new seller accounts before listings go live.</p>
        </div>

        <div>
            <label for="name" class="block text-sm font-medium text-brand-ink mb-1">Full Name <span class="text-red-500">*</span></label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name"
                   class="w-full rounded-lg border-brand-border text-sm focus:border-brand-darkgreen focus:ring-brand-darkgreen @error('name') border-red-400 @enderror">
            @error('name')
                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-brand-ink mb-1">Email Address <span class="text-red-500">*</span></label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username"
                   class="w-full rounded-lg border-brand-border text-sm focus:border-brand-darkgreen focus:ring-brand-darkgreen @error('email') border-red-400 @enderror">
            @error('email')
                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
                <label for="password" class="block text-sm font-medium text-brand-ink mb-1">Password <span class="text-red-500">*</span></label>
                <input id="password" type="password" name="password" required autocomplete="new-password"
                       class="w-full rounded-lg border-brand-border text-sm focus:border-brand-darkgreen focus:ring-brand-darkgreen @error('password') border-red-400 @enderror">
                @error('password')
                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-brand-ink mb-1">Confirm <span class="text-red-500">*</span></label>
                <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                       class="w-full rounded-lg border-brand-border text-sm focus:border-brand-darkgreen focus:ring-brand-darkgreen">
            </div>
        </div>

        <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-brand-darkgreen text-white text-sm font-semibold hover:bg-brand-forest transition-colors">
            Create Account
        </button>
    </form>

    @include('auth.partials.google-button', ['label' => 'Sign up as a buyer with Google'])

    <p class="mt-6 text-center text-sm text-brand-muted">
        Already have an account?
        <a href="{{ route('login') }}" class="font-semibold text-brand-darkgreen hover:text-brand-amber">Sign in</a>
    </p>

    <script>
        document.querySelectorAll('input[name="account_type"]').forEach(function (radio) {
            radio.addEventListener('change', function () {
                var isSeller = this.value === 'seller';
                document.getElementById('store-fields').classList.toggle('hidden', !isSeller);
                document.getElementById('store_name').required = isSeller;
            });
        });
    </script>
</x-guest-layout>
