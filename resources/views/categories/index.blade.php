@extends('layouts.app')

@section('title', 'Categories - PageTurner')

@section('header')
<h1 class="text-3xl font-bold text-gray-900">Book Categories</h1>
@endsection

@section('content')
@auth
    @if(auth()->user()->isAdmin())
        <div class="mb-6">
            <a href="{{ route('categories.create') }}" class="bg-brand-darkgreen text-white px-4 py-2 rounded hover:bg-brand-amber hover:text-brand-darkgreen transition-colors">
                Add New Category
            </a>
        </div>
    @endif
@endauth

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Category Name
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Description
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Books Count
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Actions
                </th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @foreach($categories as $category)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <a href="{{ route('categories.show', $category) }}" class="text-brand-darkgreen hover:text-brand-amber font-medium transition-colors">
                            {{ $category->name }}
                        </a>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-sm text-gray-500">{{ Str::limit($category->description, 100) }}</p>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-brand-amber/20 text-brand-darkgreen">
                            {{ $category->books_count }} books
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <a href="{{ route('categories.show', $category) }}" class="text-brand-darkgreen hover:text-brand-amber mr-3 transition-colors">View</a>
                        
                        @auth
                            @if(auth()->user()->isAdmin())
                                <a href="{{ route('categories.edit', $category) }}" class="text-brand-darkgreen hover:text-brand-amber mr-3 transition-colors">Edit</a>
                                <form action="{{ route('categories.destroy', $category) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to delete this category?')">
                                        Delete
                                    </button>
                                </form>
                            @endif
                        @endauth
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{-- Pagination --}}
<div class="mt-8">
    {{ $categories->links() }}
</div>
@endsection
