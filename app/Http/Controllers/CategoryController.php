<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('books')->paginate(10);
        return view('categories.index', compact('categories'));
    }

    public function create()
    {
        \Illuminate\Support\Facades\Gate::authorize('create', Category::class);
        return view('categories.create');
    }

    public function store(Request $request)
    {
        \Illuminate\Support\Facades\Gate::authorize('create', Category::class);
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories',
            'description' => 'nullable|string',
        ]);

        Category::create($validated);

        return redirect()->route('categories.index')
            ->with('success', 'Category created successfully!');
    }

    public function show(Category $category)
    {
        $books = $category->books()->paginate(12);
        return view('categories.show', compact('category', 'books'));
    }

    public function edit(Category $category)
    {
        \Illuminate\Support\Facades\Gate::authorize('update', $category);
        return view('categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        \Illuminate\Support\Facades\Gate::authorize('update', $category);
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
            'description' => 'nullable|string',
        ]);

        $category->update($validated);

        return redirect()->route('categories.index')
            ->with('success', 'Category updated successfully!');
    }

    public function destroy(Category $category)
    {
        \Illuminate\Support\Facades\Gate::authorize('delete', $category);
        $category->delete();

        return redirect()->route('categories.index')
            ->with('success', 'Category deleted successfully!');
    }
}
