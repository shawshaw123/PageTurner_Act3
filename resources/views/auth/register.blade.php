<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div>
            <label for="name" class="block font-medium text-sm text-gray-700">Name</label>
            <input id="name" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-darkgreen focus:border-brand-darkgreen" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            @error('name')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <label for="email" class="block font-medium text-sm text-gray-700">Email</label>
            <input id="email" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-darkgreen focus:border-brand-darkgreen" type="email" name="email" :value="old('email')" required autocomplete="username" />
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
</x-guest-layout>
