@extends('layouts.app')

@section('title', $category->name . ' — LEBROn')

@section('header')
<x-page-header
    :title="$category->name"
    :subtitle="$category->description"
    :breadcrumbs="[
        ['label' => 'Home', 'url' => route('home')],
        ['label' => 'Categories', 'url' => route('categories.index')],
        ['label' => $category->name],
    ]"
>
    @auth
        @if(auth()->user()->isAdmin())
            <x-slot:actions>
                <a href="{{ route('categories.edit', $category) }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg border border-brand-border bg-white text-sm font-semibold text-brand-ink hover:bg-brand-cream transition-colors">
                    Edit Category
                </a>
            </x-slot:actions>
        @endif
    @endauth
</x-page-header>
@endsection

@section('content')
@if($books->count() > 0)
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        @foreach($books as $book)
            <x-book-card :book="$book" />
        @endforeach
    </div>

    <div class="mt-10">
        {{ $books->withQueryString()->links() }}
    </div>
@else
    <x-empty-state title="No books in {{ $category->name }}" description="There are currently no books listed under this category.">
        <x-slot:action>
            <a href="{{ route('books.index') }}" class="px-5 py-2.5 rounded-lg bg-brand-darkgreen text-white text-sm font-semibold hover:bg-brand-forest transition-colors">
                Browse All Books
            </a>
        </x-slot:action>
    </x-empty-state>
@endif
@endsection
