@extends('layouts.app')

@section('title', 'Two-Factor Authentication - PageTurner')

@section('header')
<h1 class="text-3xl font-bold text-gray-900">Two-Factor Authentication</h1>
@endsection

@section('content')
<div class="max-w-2xl mx-auto">
    <!-- Status Messages -->
    @if(session('success'))
        <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    <!-- 2FA Status Card -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden mb-6">
        <div class="p-6 border-b border-gray-100">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="flex-shrink-0">
                        @if($enabled)
                            <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                            </div>
                        @else
                            <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center">
                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </div>
                        @endif
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">
                            Two-Factor Authentication
                        </h3>
                        <p class="text-sm {{ $enabled ? 'text-green-600' : 'text-gray-500' }}">
                            {{ $enabled ? '✓ Enabled' : '✗ Disabled' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="p-6">
            @if(!$enabled)
                <p class="text-gray-600 mb-4">
                    Add additional security to your account using two-factor authentication. When enabled, you will be prompted for a secure code during login sent to your email.
                </p>
                <form method="POST" action="{{ route('two-factor.enable') }}">
                    @csrf
                    <button type="submit" class="bg-brand-darkgreen text-white px-6 py-2 rounded-md hover:bg-brand-amber hover:text-brand-darkgreen transition-colors font-semibold">
                        Enable 2FA
                    </button>
                </form>
            @else
                <p class="text-gray-600 mb-4">
                    Two-factor authentication is currently <strong class="text-green-600">enabled</strong>. Each time you login, you will receive a verification code via email.
                </p>
                <form method="POST" action="{{ route('two-factor.disable') }}" onsubmit="return confirm('Are you sure you want to disable 2FA?')">
                    @csrf
                    @method('DELETE')
                    <div class="mb-4">
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                        <input type="password" name="password" id="password" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-darkgreen focus:border-brand-darkgreen" required>
                        @error('password')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <button type="submit" class="bg-red-600 text-white px-6 py-2 rounded-md hover:bg-red-700 transition-colors font-semibold">
                        Disable 2FA
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- Recovery Codes Section -->
    @if($enabled)
    <div class="bg-white rounded-lg shadow-lg overflow-hidden mb-6">
        <div class="p-6 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-gray-800">Recovery Codes</h3>
            <p class="text-sm text-gray-500 mt-1">Store these recovery codes in a secure location. They can be used to access your account if you lose access to your email.</p>
        </div>
        <div class="p-6">
            @if(session('recoveryCodes'))
                <div class="bg-gray-50 rounded-lg p-4 mb-4">
                    <div class="grid grid-cols-2 gap-2">
                        @foreach(session('recoveryCodes') as $code)
                            <code class="bg-white px-3 py-2 rounded border text-sm font-mono text-gray-700">{{ $code }}</code>
                        @endforeach
                    </div>
                </div>
                <p class="text-sm text-red-600 font-medium mb-4">
                    ⚠️ Save these codes now. You won't be able to see them again!
                </p>
            @else
                <p class="text-sm text-gray-500 mb-4">Recovery codes were shown when 2FA was enabled. If you need new codes, regenerate them below.</p>
            @endif

            <form method="POST" action="{{ route('two-factor.regenerate') }}">
                @csrf
                <div class="mb-4">
                    <label for="regen_password" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                    <input type="password" name="password" id="regen_password" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-darkgreen focus:border-brand-darkgreen" required>
                </div>
                <button type="submit" class="bg-gray-800 text-white px-6 py-2 rounded-md hover:bg-gray-700 transition-colors font-semibold text-sm">
                    Regenerate Recovery Codes
                </button>
            </form>
        </div>
    </div>
    @endif

    <div class="mt-6">
        <a href="{{ route('profile.edit') }}" class="text-brand-darkgreen hover:text-brand-amber font-medium transition-colors">
            &larr; Back to Profile
        </a>
    </div>
</div>
@endsection
