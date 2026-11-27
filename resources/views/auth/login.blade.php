<x-guest-layout>
    <div class="mb-6">
        <h1 class="font-serif text-2xl font-bold text-brand-ink">Sign in</h1>
        <p class="text-sm text-brand-muted mt-1">Welcome back to LEBROn.</p>
    </div>

    @if(session('status'))
        <x-alert type="success" class="mb-4">{{ session('status') }}</x-alert>
    @endif
    @if(session('error'))
        <x-alert type="error" class="mb-4">{{ session('error') }}</x-alert>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <div>
            <label for="email" class="block text-sm font-medium text-brand-ink mb-1">Email Address</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                   class="w-full rounded-lg border-brand-border text-sm focus:border-brand-darkgreen focus:ring-brand-darkgreen @error('email') border-red-400 @enderror">
            @error('email')
                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <div class="flex items-center justify-between mb-1">
                <label for="password" class="block text-sm font-medium text-brand-ink">Password</label>
                @if(Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-xs font-medium text-brand-darkgreen hover:text-brand-amber">
                        Forgot password?
                    </a>
                @endif
            </div>
            <input id="password" type="password" name="password" required autocomplete="current-password"
                   class="w-full rounded-lg border-brand-border text-sm focus:border-brand-darkgreen focus:ring-brand-darkgreen @error('password') border-red-400 @enderror">
            @error('password')
                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <label for="remember_me" class="inline-flex items-center gap-2 cursor-pointer">
            <input id="remember_me" type="checkbox" name="remember" class="rounded border-brand-border text-brand-darkgreen focus:ring-brand-darkgreen">
            <span class="text-sm text-brand-muted">Remember me</span>
        </label>

        <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-brand-darkgreen text-white text-sm font-semibold hover:bg-brand-forest transition-colors">
            Sign in
        </button>
    </form>

    @include('auth.partials.google-button', ['label' => 'Sign in with Google'])

    <p class="mt-6 text-center text-sm text-brand-muted">
        New to LEBROn?
        <a href="{{ route('register') }}" class="font-semibold text-brand-darkgreen hover:text-brand-amber">Create an account</a>
    </p>
</x-guest-layout>
