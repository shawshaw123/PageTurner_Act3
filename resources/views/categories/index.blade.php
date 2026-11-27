@extends('layouts.app')

@section('title', 'Book Categories — PageTurner')

@section('header')
<x-page-header
    title="Book Categories"
    subtitle="Explore genres and curated collections across our bookstore."
    :breadcrumbs="[
        ['label' => 'Home', 'url' => route('home')],
        ['label' => 'Categories'],
    ]"
>
    @auth
        @if(auth()->user()->isAdmin())
            <x-slot:actions>
                <a href="{{ route('categories.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-brand-darkgreen text-white text-sm font-semibold hover:bg-brand-forest transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Add Category
                </a>
            </x-slot:actions>
        @endif
    @endauth
</x-page-header>
@endsection

@section('content')
@if($categories->count() > 0)
    <div class="bg-white rounded-2xl border border-brand-border shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-brand-border text-sm">
                <thead class="bg-brand-cream/70">
                    <tr>
                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-brand-muted">Category</th>
                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-brand-muted">Description</th>
                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-brand-muted">Books</th>
                        <th scope="col" class="px-6 py-3.5 text-right text-xs font-semibold uppercase tracking-wider text-brand-muted">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-brand-border/70">
                    @foreach($categories as $category)
                        <tr class="hover:bg-brand-paper/60 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <a href="{{ route('categories.show', $category) }}" class="font-serif font-semibold text-base text-brand-ink hover:text-brand-darkgreen transition-colors">
                                    {{ $category->name }}
                                </a>
                            </td>
                            <td class="px-6 py-4 text-brand-muted max-w-md">
                                {{ Str::limit($category->description, 110) ?: '—' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <x-badge variant="brand">{{ $category->books_count }} {{ Str::plural('book', $category->books_count) }}</x-badge>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right font-medium space-x-3">
                                <a href="{{ route('categories.show', $category) }}" class="text-brand-darkgreen hover:text-brand-amber transition-colors">Browse</a>
                                @auth
                                    @if(auth()->user()->isAdmin())
                                        <a href="{{ route('categories.edit', $category) }}" class="text-brand-muted hover:text-brand-ink transition-colors">Edit</a>
                                        <form action="{{ route('categories.destroy', $category) }}" method="POST" class="inline" data-confirm="Delete category '{{ $category->name }}'?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-800">Delete</button>
                                        </form>
                                    @endif
                                @endauth
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-8">
        {{ $categories->links() }}
    </div>
@else
    <x-empty-state title="No categories yet" description="Categories will appear here once added to the catalog." />
@endif
@endsection
