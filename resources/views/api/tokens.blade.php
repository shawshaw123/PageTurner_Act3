@extends('layouts.app')

@section('title', 'API Tokens — PageTurner')

@section('header')
<x-page-header
    title="API Tokens"
    subtitle="Create personal access tokens to authenticate with the PageTurner REST API."
    :breadcrumbs="[
        ['label' => 'Home', 'url' => route('home')],
        ['label' => 'API Tokens'],
    ]"
>
    @if(auth()->user()->isAdmin())
        <x-slot:actions>
            <a href="{{ route('admin.api-docs') }}" class="px-4 py-2 rounded-lg border border-brand-border bg-white text-sm font-medium text-brand-ink hover:bg-brand-cream transition-colors">
                REST API Docs
            </a>
        </x-slot:actions>
    @endif
</x-page-header>
@endsection

@section('content')
<div class="max-w-3xl space-y-6">
    @if(session('new_token'))
        <x-alert type="success" title="Your new API token">
            <p class="text-xs mb-3">Copy this token now. For your security, it will not be displayed again.</p>
            <div class="flex flex-col sm:flex-row gap-2">
                <input id="new-token" type="text" readonly value="{{ session('new_token') }}" aria-label="New API token"
                       class="flex-1 font-mono text-xs rounded-lg border-emerald-300 bg-white text-brand-ink">
                <button type="button" onclick="navigator.clipboard.writeText(document.getElementById('new-token').value); this.textContent='Copied!';"
                        class="px-4 py-2 rounded-lg bg-brand-darkgreen text-white text-xs font-semibold hover:bg-brand-forest transition-colors shrink-0">
                    Copy Token
                </button>
            </div>
        </x-alert>
    @endif

    <section class="bg-white rounded-2xl border border-brand-border shadow-xs p-6">
        <h2 class="font-serif text-lg font-bold text-brand-ink">Create a New Token</h2>
        <p class="text-xs text-brand-muted mt-0.5">Send tokens in the <code class="bg-brand-cream px-1.5 py-0.5 rounded">Authorization: Bearer &lt;token&gt;</code> header.</p>
        <form action="{{ route('api-tokens.store') }}" method="POST" class="mt-4 flex flex-col sm:flex-row gap-3">
            @csrf
            <div class="flex-1">
                <label for="name" class="sr-only">Token name</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required maxlength="100" placeholder="e.g. Postman, Mobile App, CI Script"
                       class="w-full rounded-lg border-brand-border text-sm focus:border-brand-darkgreen focus:ring-brand-darkgreen">
                @error('name')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <button type="submit" class="px-5 py-2 rounded-lg bg-brand-darkgreen text-white text-sm font-semibold hover:bg-brand-forest transition-colors shrink-0">
                Create Token
            </button>
        </form>
    </section>

    <section class="bg-white rounded-2xl border border-brand-border shadow-xs overflow-hidden">
        <div class="px-6 py-4 border-b border-brand-border">
            <h2 class="font-serif text-lg font-bold text-brand-ink">Active Tokens</h2>
        </div>
        @if($tokens->isEmpty())
            <div class="p-8 text-center text-sm text-brand-muted">You have not created any API tokens yet.</div>
        @else
            <ul class="divide-y divide-brand-border/70">
                @foreach($tokens as $token)
                    <li class="px-6 py-4 flex items-center justify-between gap-4 text-sm">
                        <div class="min-w-0">
                            <p class="font-semibold text-brand-ink truncate">{{ $token->name }}</p>
                            <p class="text-xs text-brand-muted mt-0.5">Created {{ $token->created_at->diffForHumans() }} &middot; {{ $token->last_used_at ? 'Last used ' . $token->last_used_at->diffForHumans() : 'Never used' }}</p>
                        </div>
                        <form action="{{ route('api-tokens.destroy', $token->id) }}" method="POST" data-confirm="Revoke token '{{ $token->name }}'? Any app using it will lose access.">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs font-semibold text-red-600 hover:text-red-800">Revoke</button>
                        </form>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>
</div>
@endsection
