<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;

class HomeController extends Controller
{
    public function index()
    {
        $featuredBooks = Book::with('category')
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->orderBy('created_at', 'desc')
            ->take(8)
            ->get();
        
        $categories = Category::withCount('books')->get();
        
        return view('home', compact('featuredBooks', 'categories'));
    }
}
