<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Account type -->
        <fieldset class="mb-4">
            <legend class="block font-medium text-sm text-gray-700 mb-2">I want to</legend>
            <div class="grid grid-cols-2 gap-3">
                <label class="flex items-start gap-2 border rounded-md p-3 cursor-pointer has-[:checked]:border-brand-darkgreen has-[:checked]:bg-green-50">
                    <input type="radio" name="account_type" value="buyer" class="mt-1 text-brand-darkgreen focus:ring-brand-darkgreen" {{ old('account_type', 'buyer') === 'buyer' ? 'checked' : '' }}>
                    <span>
                        <span class="block font-semibold text-sm text-gray-900">Buy books</span>
                        <span class="block text-xs text-gray-500">Browse, order and review</span>
                    </span>
                </label>
                <label class="flex items-start gap-2 border rounded-md p-3 cursor-pointer has-[:checked]:border-brand-darkgreen has-[:checked]:bg-green-50">
                    <input type="radio" name="account_type" value="seller" class="mt-1 text-brand-darkgreen focus:ring-brand-darkgreen" {{ old('account_type') === 'seller' ? 'checked' : '' }}>
                    <span>
                        <span class="block font-semibold text-sm text-gray-900">Sell books</span>
                        <span class="block text-xs text-gray-500">Independent seller or bookstore</span>
                    </span>
                </label>
            </div>
            @error('account_type')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </fieldset>

        <!-- Store details (sellers only) -->
        <div id="store-fields" class="mb-4 space-y-4 {{ old('account_type') === 'seller' ? '' : 'hidden' }}">
            <div>
                <label for="store_name" class="block font-medium text-sm text-gray-700">Store name</label>
                <input id="store_name" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-darkgreen focus:border-brand-darkgreen" type="text" name="store_name" value="{{ old('store_name') }}" maxlength="255" />
                @error('store_name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="store_description" class="block font-medium text-sm text-gray-700">About your store <span class="text-gray-400">(optional)</span></label>
                <textarea id="store_description" name="store_description" rows="2" maxlength="1000" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-darkgreen focus:border-brand-darkgreen">{{ old('store_description') }}</textarea>
            </div>
            <p class="text-xs text-gray-500">An administrator reviews new seller accounts before your listings go live.</p>
        </div>

        <!-- Name -->
        <div>
            <label for="name" class="block font-medium text-sm text-gray-700">Name</label>
            <input id="name" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-darkgreen focus:border-brand-darkgreen" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" />
            @error('name')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <label for="email" class="block font-medium text-sm text-gray-700">Email</label>
            <input id="email" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-darkgreen focus:border-brand-darkgreen" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" />
            @error('email')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Password -->
        <div class="mt-4">
            <label for="password" class="block font-medium text-sm text-gray-700">Password</label>
            <input id="password" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-darkgreen focus:border-brand-darkgreen" type="password" name="password" required autocomplete="new-password" />
            @error('password')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <label for="password_confirmation" class="block font-medium text-sm text-gray-700">Confirm Password</label>
            <input id="password_confirmation" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-darkgreen focus:border-brand-darkgreen" type="password" name="password_confirmation" required autocomplete="new-password" />
            @error('password_confirmation')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-brand-darkgreen rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-darkgreen" href="{{ route('login') }}">
                Already registered?
            </a>

            <button type="submit" class="ml-4 inline-flex items-center px-4 py-2 bg-brand-darkgreen border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-brand-amber hover:text-brand-darkgreen active:bg-brand-darkgreen focus:outline-none focus:ring-2 focus:ring-brand-darkgreen focus:ring-offset-2 transition ease-in-out duration-150">
                Register
            </button>
        </div>
    </form>

    {{-- Google sign-up creates a buyer account; sellers register with the form above. --}}
    @include('auth.partials.google-button', ['label' => 'Sign up as a buyer with Google'])

    <script>
        // Show the store fields only for seller sign-ups.
        document.querySelectorAll('input[name="account_type"]').forEach(function (radio) {
            radio.addEventListener('change', function () {
                var isSeller = this.value === 'seller';
                document.getElementById('store-fields').classList.toggle('hidden', !isSeller);
                document.getElementById('store_name').required = isSeller;
            });
        });
    </script>
</x-guest-layout>
