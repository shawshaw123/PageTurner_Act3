@extends('layouts.app')

@section('title', $category->name . ' - PageTurner')

@section('header')
<div class="flex justify-between items-center">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">{{ $category->name }}</h1>
        <p class="text-gray-600 mt-2">{{ $category->description }}</p>
    </div>
    
    @auth
        @if(auth()->user()->isAdmin())
            <div class="space-x-4">
                <a href="{{ route('categories.edit', $category) }}" class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600 transition">
                    Edit Category
                </a>
            </div>
        @endif
    @endauth
</div>
@endsection

@section('content')
@if($books->count() > 0)
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        @foreach($books as $book)
            <x-book-card :book="$book" />
        @endforeach
    </div>
    
    {{-- Pagination --}}
    <div class="mt-8">
        {{ $books->withQueryString()->links() }}
    </div>
@else
    <x-alert type="info">
        No books found in this category.
    </x-alert>
@endif
@endsection
