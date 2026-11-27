@extends('layouts.app')

@section('title', 'Profile & Security — LEBROn')

@section('header')
<x-page-header
    title="Profile &amp; Security Settings"
    subtitle="Manage your personal information, password, two-factor authentication, and data privacy."
    :breadcrumbs="[
        ['label' => 'Home', 'url' => route('home')],
        ['label' => 'Profile'],
    ]"
>
    <x-slot:actions>
        <a href="{{ route('user.data-portability.index') }}" class="px-4 py-2 rounded-lg border border-brand-border bg-white text-sm font-medium text-brand-ink hover:bg-brand-cream transition-colors">
            Data Portability
        </a>
    </x-slot:actions>
</x-page-header>
@endsection

@section('content')
<div class="max-w-4xl space-y-6">
    {{-- Profile Information --}}
    <section class="bg-white rounded-2xl border border-brand-border shadow-xs overflow-hidden">
        <div class="px-6 py-4 border-b border-brand-border">
            <h2 class="font-serif text-lg font-bold text-brand-ink">Profile Information</h2>
            <p class="text-xs text-brand-muted mt-0.5">Update your account's display name and email address.</p>
        </div>
        <div class="p-6">
            <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
                @csrf
                @method('PATCH')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-brand-ink mb-1">Full Name <span class="text-red-500">*</span></label>
                        <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required
                               class="w-full rounded-lg border-brand-border text-sm focus:border-brand-darkgreen focus:ring-brand-darkgreen">
                        @error('name')
                            <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-brand-ink mb-1">Email Address <span class="text-red-500">*</span></label>
                        <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required
                               class="w-full rounded-lg border-brand-border text-sm focus:border-brand-darkgreen focus:ring-brand-darkgreen">
                        @error('email')
                            <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                @if(session('status') === 'profile-updated')
                    <p class="text-xs font-medium text-emerald-700">Profile saved successfully.</p>
                @endif

                <button type="submit" class="px-5 py-2.5 rounded-xl bg-brand-darkgreen text-white text-sm font-semibold hover:bg-brand-forest transition-colors">
                    Save Changes
                </button>
            </form>
        </div>
    </section>

    {{-- Update Password --}}
    <section class="bg-white rounded-2xl border border-brand-border shadow-xs overflow-hidden">
        <div class="px-6 py-4 border-b border-brand-border">
            <h2 class="font-serif text-lg font-bold text-brand-ink">Update Password</h2>
            <p class="text-xs text-brand-muted mt-0.5">Use a strong, unique password to keep your account protected.</p>
        </div>
        <div class="p-6">
            <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label for="current_password" class="block text-sm font-medium text-brand-ink mb-1">Current Password <span class="text-red-500">*</span></label>
                    <input id="current_password" name="current_password" type="password" required
                           class="w-full rounded-lg border-brand-border text-sm focus:border-brand-darkgreen focus:ring-brand-darkgreen">
                    @error('current_password')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="new_password" class="block text-sm font-medium text-brand-ink mb-1">New Password <span class="text-red-500">*</span></label>
                        <input id="new_password" name="password" type="password" required
                               class="w-full rounded-lg border-brand-border text-sm focus:border-brand-darkgreen focus:ring-brand-darkgreen">
                        @error('password')
                            <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-brand-ink mb-1">Confirm New Password <span class="text-red-500">*</span></label>
                        <input id="password_confirmation" name="password_confirmation" type="password" required
                               class="w-full rounded-lg border-brand-border text-sm focus:border-brand-darkgreen focus:ring-brand-darkgreen">
                    </div>
                </div>

                <button type="submit" class="px-5 py-2.5 rounded-xl bg-brand-darkgreen text-white text-sm font-semibold hover:bg-brand-forest transition-colors">
                    Update Password
                </button>
            </form>
        </div>
    </section>

    {{-- Security Status --}}
    <section class="bg-white rounded-2xl border border-brand-border shadow-xs overflow-hidden">
        <div class="px-6 py-4 border-b border-brand-border">
            <h2 class="font-serif text-lg font-bold text-brand-ink">Security Status</h2>
        </div>
        <div class="p-6 space-y-3">
            <div class="flex flex-wrap items-center justify-between gap-3 p-4 rounded-xl bg-brand-paper border border-brand-border">
                <div>
                    <p class="text-sm font-semibold text-brand-ink">Email Verification</p>
                    <p class="text-xs text-brand-muted">
                        {{ $user->hasVerifiedEmail() ? 'Verified on ' . $user->email_verified_at->format('M d, Y') : 'Verify your email to unlock ordering and reviews.' }}
                    </p>
                </div>
                @if($user->hasVerifiedEmail())
                    <x-badge variant="success" dot>Verified</x-badge>
                @else
                    <form method="POST" action="{{ route('verification.send') }}">
                        @csrf
                        <button type="submit" class="text-xs font-semibold text-brand-darkgreen hover:text-brand-amber underline">Resend Email</button>
                    </form>
                @endif
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3 p-4 rounded-xl bg-brand-paper border border-brand-border">
                <div>
                    <p class="text-sm font-semibold text-brand-ink">Two-Factor Authentication</p>
                    <p class="text-xs text-brand-muted">{{ $user->hasTwoFactorEnabled() ? 'Active on your account.' : 'Recommended for stronger account protection.' }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <x-badge :variant="$user->hasTwoFactorEnabled() ? 'success' : 'warning'" dot>
                        {{ $user->hasTwoFactorEnabled() ? 'Enabled' : 'Disabled' }}
                    </x-badge>
                    <a href="{{ route('two-factor.index') }}" class="text-xs font-semibold text-brand-darkgreen hover:text-brand-amber underline">Manage</a>
                </div>
            </div>
        </div>
    </section>

    {{-- Delete Account --}}
    <section class="bg-white rounded-2xl border border-red-200 shadow-xs overflow-hidden">
        <div class="px-6 py-4 bg-red-50 border-b border-red-200">
            <h2 class="font-serif text-lg font-bold text-red-900">Delete Account</h2>
            <p class="text-xs text-red-700 mt-0.5">Once deleted, all of your account resources and data are permanently removed.</p>
        </div>
        <div class="p-6">
            <form method="POST" action="{{ route('profile.destroy') }}" data-confirm="Are you absolutely sure you want to delete your account? This cannot be undone.">
                @csrf
                @method('DELETE')
                <div class="max-w-sm mb-4">
                    <label for="delete_password" class="block text-sm font-medium text-brand-ink mb-1">Confirm Password <span class="text-red-500">*</span></label>
                    <input id="delete_password" name="password" type="password" required
                           class="w-full rounded-lg border-brand-border text-sm focus:border-red-500 focus:ring-red-500">
                </div>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-red-600 text-white text-sm font-semibold hover:bg-red-700 transition-colors">
                    Delete Account
                </button>
            </form>
        </div>
    </section>
</div>
@endsection
