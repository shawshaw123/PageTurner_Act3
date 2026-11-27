@extends('layouts.app')

@section('title', 'Edit Category — PageTurner')

@section('header')
<x-page-header
    :title="'Edit Category'"
    :subtitle="$category->name"
    :breadcrumbs="[
        ['label' => 'Home', 'url' => route('home')],
        ['label' => 'Categories', 'url' => route('categories.index')],
        ['label' => $category->name, 'url' => route('categories.show', $category)],
        ['label' => 'Edit'],
    ]"
/>
@endsection

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-2xl border border-brand-border shadow-xs p-6 sm:p-8">
        <form action="{{ route('categories.update', $category) }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label for="name" class="block text-sm font-medium text-brand-ink mb-1">Category Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" id="name" value="{{ old('name', $category->name) }}" required
                       class="w-full rounded-lg border-brand-border text-sm focus:border-brand-darkgreen focus:ring-brand-darkgreen @error('name') border-red-400 @enderror">
                @error('name')
                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-brand-ink mb-1">Description</label>
                <textarea name="description" id="description" rows="4"
                          class="w-full rounded-lg border-brand-border text-sm focus:border-brand-darkgreen focus:ring-brand-darkgreen">{{ old('description', $category->description) }}</textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-brand-border">
                <a href="{{ route('categories.index') }}" class="px-5 py-2.5 rounded-xl border border-brand-border bg-white text-sm font-medium text-brand-ink hover:bg-brand-cream transition-colors">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-brand-darkgreen text-white text-sm font-semibold hover:bg-brand-forest transition-colors">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
