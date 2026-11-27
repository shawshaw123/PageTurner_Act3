@extends('layouts.app')

@section('title', 'API Tokens - PageTurner')

@section('header')
<h1 class="text-3xl font-bold text-gray-900">API Tokens</h1>
<p class="text-gray-500 mt-1">Tokens let apps use the
    @if(auth()->user()->isAdmin())<a href="{{ route('admin.api-docs') }}" class="text-brand-darkgreen underline hover:text-brand-amber">REST API</a>@else REST API @endif
    as you. Send one as <code class="bg-gray-100 px-1 rounded">Authorization: Bearer &lt;token&gt;</code>.</p>
@endsection

@section('content')
<div class="max-w-3xl space-y-6">
    @if(session('new_token'))
        <div class="bg-green-50 border border-green-300 rounded-lg p-5" role="status">
            <p class="font-semibold text-green-900">Your new token</p>
            <p class="text-sm text-green-800 mt-1">Copy it now. For your security it won't be shown again.</p>
            <div class="mt-3 flex gap-2">
                <input id="new-token" type="text" readonly value="{{ session('new_token') }}" class="flex-1 font-mono text-sm border-green-300 rounded-md bg-white" aria-label="New API token">
                <button type="button" onclick="navigator.clipboard.writeText(document.getElementById('new-token').value); this.textContent='Copied';" class="bg-brand-darkgreen text-white px-4 py-2 rounded-md text-sm hover:bg-brand-amber hover:text-brand-darkgreen">Copy</button>
            </div>
            @if(auth()->user()->isAdmin())
                <a href="{{ route('admin.api-docs') }}" class="inline-block mt-3 text-sm text-green-900 underline">Try it in Swagger (click Authorize and paste it)</a>
            @endif
        </div>
    @endif

    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold text-gray-900">Create a token</h2>
        <form action="{{ route('api-tokens.store') }}" method="POST" class="mt-4 flex flex-wrap gap-2 items-start">
            @csrf
            <div class="flex-1 min-w-[14rem]">
                <label for="name" class="sr-only">Token name</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required maxlength="100" placeholder="What is it for? e.g. Postman, mobile app" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-darkgreen focus:border-brand-darkgreen">
                @error('name')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            <button type="submit" class="bg-brand-darkgreen text-white px-4 py-2 rounded-md hover:bg-brand-amber hover:text-brand-darkgreen transition-colors">Create token</button>
        </form>
    </div>

    <div class="bg-white rounded-lg shadow">
        <h2 class="text-lg font-semibold text-gray-900 px-6 py-4 border-b">Your tokens</h2>
        @if($tokens->isEmpty())
            <p class="px-6 py-6 text-sm text-gray-500">You have no API tokens.</p>
        @else
            <ul class="divide-y divide-gray-100">
                @foreach($tokens as $token)
                    <li class="px-6 py-3 flex items-center justify-between gap-4 text-sm">
                        <div>
                            <p class="font-medium text-gray-900">{{ $token->name }}</p>
                            <p class="text-gray-500">Created {{ $token->created_at->diffForHumans() }} · {{ $token->last_used_at ? 'last used ' . $token->last_used_at->diffForHumans() : 'never used' }}</p>
                        </div>
                        <form action="{{ route('api-tokens.destroy', $token->id) }}" method="POST" onsubmit="return confirm('Revoke this token?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-800 font-medium">Revoke</button>
                        </form>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
@endsection
