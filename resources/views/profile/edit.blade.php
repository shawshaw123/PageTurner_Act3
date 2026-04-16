@extends('layouts.app')

@section('title', 'Profile Settings - PageTurner')

@section('header')
<h1 class="text-3xl font-bold text-gray-900">Profile & Security Settings</h1>
@endsection

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    {{-- Profile Information --}}
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="p-6 border-b border-gray-100">
            <h2 class="text-lg font-semibold text-gray-800">Profile Information</h2>
            <p class="text-sm text-gray-500 mt-1">Update your account's profile information and email address.</p>
        </div>
        <div class="p-6">
            <form method="POST" action="{{ route('profile.update') }}">
                @csrf
                @method('PATCH')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                        <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-darkgreen focus:border-brand-darkgreen" required>
                        @error('name')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-darkgreen focus:border-brand-darkgreen" required>
                        @error('email')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                @if (session('status') === 'profile-updated')
                    <p class="text-sm text-green-600 mt-2">Profile saved successfully.</p>
                @endif

                <div class="mt-4">
                    <button type="submit" class="bg-brand-darkgreen text-white px-6 py-2 rounded-md hover:bg-brand-amber hover:text-brand-darkgreen transition-colors font-semibold">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Update Password --}}
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="p-6 border-b border-gray-100">
            <h2 class="text-lg font-semibold text-gray-800">Update Password</h2>
            <p class="text-sm text-gray-500 mt-1">Ensure your account is using a long, random password to stay secure.</p>
        </div>
        <div class="p-6">
            <form method="POST" action="{{ route('password.update') }}">
                @csrf
                @method('PUT')

                <div class="space-y-4">
                    <div>
                        <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                        <input id="current_password" name="current_password" type="password" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-darkgreen focus:border-brand-darkgreen" required>
                        @error('current_password')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="new_password" class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                            <input id="new_password" name="password" type="password" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-darkgreen focus:border-brand-darkgreen" required>
                            @error('password')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                            <input id="password_confirmation" name="password_confirmation" type="password" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-darkgreen focus:border-brand-darkgreen" required>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="bg-brand-darkgreen text-white px-6 py-2 rounded-md hover:bg-brand-amber hover:text-brand-darkgreen transition-colors font-semibold">
                        Update Password
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Security Status --}}
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="p-6 border-b border-gray-100">
            <h2 class="text-lg font-semibold text-gray-800">Security Status</h2>
        </div>
        <div class="p-6">
            <div class="space-y-4">
                {{-- Email Verification --}}
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                    <div class="flex items-center space-x-3">
                        @if($user->hasVerifiedEmail())
                            <span class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center">
                                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </span>
                            <div>
                                <p class="font-medium text-gray-800">Email Verified</p>
                                <p class="text-xs text-gray-500">Verified on {{ $user->email_verified_at->format('M d, Y') }}</p>
                            </div>
                        @else
                            <span class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center">
                                <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </span>
                            <div>
                                <p class="font-medium text-gray-800">Email Not Verified</p>
                                <p class="text-xs text-gray-500">Please verify your email to access all features</p>
                            </div>
                        @endif
                    </div>
                    @if(!$user->hasVerifiedEmail())
                        <form method="POST" action="{{ route('verification.send') }}">
                            @csrf
                            <button type="submit" class="text-sm text-brand-darkgreen hover:text-brand-amber font-medium underline">Resend</button>
                        </form>
                    @endif
                </div>

                {{-- 2FA Status --}}
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                    <div class="flex items-center space-x-3">
                        @if($user->hasTwoFactorEnabled())
                            <span class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center">
                                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            </span>
                            <div>
                                <p class="font-medium text-gray-800">Two-Factor Authentication</p>
                                <p class="text-xs text-green-600">Enabled</p>
                            </div>
                        @else
                            <span class="w-8 h-8 rounded-full bg-yellow-100 flex items-center justify-center">
                                <svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            </span>
                            <div>
                                <p class="font-medium text-gray-800">Two-Factor Authentication</p>
                                <p class="text-xs text-yellow-600">Not enabled - recommended</p>
                            </div>
                        @endif
                    </div>
                    <a href="{{ route('two-factor.index') }}" class="text-sm text-brand-darkgreen hover:text-brand-amber font-medium underline">
                        Manage
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Delete Account --}}
    <div class="bg-white rounded-lg shadow-lg overflow-hidden border border-red-200">
        <div class="p-6 border-b border-red-100">
            <h2 class="text-lg font-semibold text-red-800">Delete Account</h2>
            <p class="text-sm text-red-500 mt-1">Once your account is deleted, all of its resources and data will be permanently deleted.</p>
        </div>
        <div class="p-6">
            <form method="POST" action="{{ route('profile.destroy') }}" onsubmit="return confirm('Are you absolutely sure? This action cannot be undone.')">
                @csrf
                @method('DELETE')
                <div class="mb-4">
                    <label for="delete_password" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                    <input id="delete_password" name="password" type="password" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500" required>
                </div>
                <button type="submit" class="bg-red-600 text-white px-6 py-2 rounded-md hover:bg-red-700 transition-colors font-semibold">
                    Delete Account
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
