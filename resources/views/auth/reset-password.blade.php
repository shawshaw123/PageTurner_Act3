<x-guest-layout>
    <form method="POST" action="{{ route('password.store') }}">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Email Address -->
        <div>
            <label for="email" class="block font-medium text-sm text-gray-700">Email</label>
            <input id="email" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-darkgreen focus:border-brand-darkgreen" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username" />
            @error('email')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Password -->
        <div class="mt-4">
            <label for="password" class="block font-medium text-sm text-gray-700">New Password</label>
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
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-brand-darkgreen border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-brand-amber hover:text-brand-darkgreen transition ease-in-out duration-150">
                Reset Password
            </button>
        </div>
    </form>
</x-guest-layout>
