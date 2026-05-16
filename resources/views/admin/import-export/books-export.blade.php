@extends('layouts.app')

@section('title', 'Export Books')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-lg shadow-lg p-6 text-white">
        <div class="flex items-center">
            <svg class="w-10 h-10 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <div>
                <h1 class="text-3xl font-bold">Export Books</h1>
                <p class="text-blue-100 mt-2">Export books data in multiple formats with custom filters</p>
            </div>
        </div>
    </div>

    <!-- Export Form -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="p-6">
            <form action="{{ route('admin.import-export.books.process') }}" method="POST" class="space-y-6">
                @csrf

                <!-- Export Format -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Export Format
                        <span class="text-red-500">*</span>
                    </label>
                    <div class="grid grid-cols-3 gap-4">
                        <label class="relative">
                            <input type="radio" name="format" value="xlsx" class="peer sr-only" checked>
                            <div class="p-4 border-2 rounded-lg cursor-pointer transition-all peer-checked:border-blue-500 peer-checked:bg-blue-50 hover:border-gray-300">
                                <div class="text-center">
                                    <svg class="w-8 h-8 mx-auto mb-2 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-5L9 2H4z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span class="text-sm font-medium">Excel</span>
                                    <span class="text-xs text-gray-500 block">.xlsx</span>
                                </div>
                            </div>
                        </label>

                        <label class="relative">
                            <input type="radio" name="format" value="csv" class="peer sr-only">
                            <div class="p-4 border-2 rounded-lg cursor-pointer transition-all peer-checked:border-blue-500 peer-checked:bg-blue-50 hover:border-gray-300">
                                <div class="text-center">
                                    <svg class="w-8 h-8 mx-auto mb-2 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-5L9 2H4z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span class="text-sm font-medium">CSV</span>
                                    <span class="text-xs text-gray-500 block">.csv</span>
                                </div>
                            </div>
                        </label>

                        <label class="relative">
                            <input type="radio" name="format" value="pdf" class="peer sr-only">
                            <div class="p-4 border-2 rounded-lg cursor-pointer transition-all peer-checked:border-blue-500 peer-checked:bg-blue-50 hover:border-gray-300">
                                <div class="text-center">
                                    <svg class="w-8 h-8 mx-auto mb-2 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-5L9 2H4z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span class="text-sm font-medium">PDF</span>
                                    <span class="text-xs text-gray-500 block">.pdf</span>
                                </div>
                            </div>
                        </label>
                    </div>
                    @error('format')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Filters -->
                <div class="bg-gray-50 rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Filters (Optional)</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Category Filter -->
                        <div>
                            <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                            <select id="category_id" name="category_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                <option value="">All Categories</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Stock Status Filter -->
                        <div>
                            <label for="stock_status" class="block text-sm font-medium text-gray-700 mb-1">Stock Status</label>
                            <select id="stock_status" name="stock_status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                <option value="">All Books</option>
                                <option value="in_stock">In Stock</option>
                                <option value="out_of_stock">Out of Stock</option>
                                <option value="low_stock">Low Stock (less than 10)</option>
                            </select>
                        </div>

                        <!-- Price Range Filter -->
                        <div>
                            <label for="price_min" class="block text-sm font-medium text-gray-700 mb-1">Minimum Price</label>
                            <input type="number" id="price_min" name="price_min" step="0.01" min="0" placeholder="0.00" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label for="price_max" class="block text-sm font-medium text-gray-700 mb-1">Maximum Price</label>
                            <input type="number" id="price_max" name="price_max" step="0.01" min="0" placeholder="9999.99" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <!-- Date Range Filter -->
                        <div>
                            <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">Created From</label>
                            <input type="date" id="date_from" name="date_from" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">Created To</label>
                            <input type="date" id="date_to" name="date_to" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>

                    <!-- Search Filter -->
                    <div class="mt-4">
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                        <input type="text" id="search" name="search" placeholder="Search by title, author, or ISBN..." 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>

                <!-- Column Selection -->
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Select Columns to Export</h3>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                        <label class="flex items-center">
                            <input type="checkbox" name="columns[]" value="id" checked class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <span class="ml-2 text-sm text-gray-700">ID</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="columns[]" value="isbn" checked class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <span class="ml-2 text-sm text-gray-700">ISBN</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="columns[]" value="title" checked class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <span class="ml-2 text-sm text-gray-700">Title</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="columns[]" value="author" checked class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <span class="ml-2 text-sm text-gray-700">Author</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="columns[]" value="price" checked class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <span class="ml-2 text-sm text-gray-700">Price</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="columns[]" value="stock" checked class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <span class="ml-2 text-sm text-gray-700">Stock</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="columns[]" value="category" checked class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <span class="ml-2 text-sm text-gray-700">Category</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="columns[]" value="description" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <span class="ml-2 text-sm text-gray-700">Description</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="columns[]" value="created_at" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <span class="ml-2 text-sm text-gray-700">Created Date</span>
                        </label>
                    </div>
                    @error('columns')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Export Info -->
                <div class="bg-blue-50 border-l-4 border-blue-400 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800">Export Information</h3>
                            <div class="mt-2 text-sm text-blue-700">
                                <p>• Large exports will be processed in the background and available for download within 24 hours.</p>
                                <p>• Export files will expire after 7 days for security reasons.</p>
                                <p>• You can track export progress on the Data Management dashboard.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end space-x-3">
                    <a href="{{ route('admin.import-export.index') }}" class="px-6 py-3 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                        Cancel
                    </a>
                    <button type="submit" class="px-6 py-3 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Export Books
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Recent Exports -->
    @if(isset($recentExports) && $recentExports->count() > 0)
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Recent Exports</h3>
        </div>
        <div class="p-6">
            <div class="space-y-4">
                @foreach($recentExports as $export)
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div class="flex items-center space-x-4">
                            <div class="flex-shrink-0">
                                @if($export->status === 'completed')
                                    <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                @elseif($export->status === 'failed')
                                    <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                @else
                                    <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-yellow-600 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                        </svg>
                                    </div>
                                @endif
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">
                                            Books Export ({{ strtoupper($export->format) }})
                                        </p>
                                        <div class="mt-1 flex items-center space-x-4 text-xs text-gray-500">
                                            <span>{{ $export->created_at->format('M j, Y g:i A') }}</span>
                                            @if($export->total_records > 0)
                                                <span>{{ $export->total_records }} records</span>
                                            @endif
                                            @if($export->duration)
                                                <span>{{ $export->duration }}s</span>
                                            @endif
                                            @if($export->expires_at)
                                                <span>Expires {{ $export->expires_at->diffForHumans() }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                            {{ $export->status === 'completed' ? 'bg-green-100 text-green-800' : '' }}
                                            {{ $export->status === 'failed' ? 'bg-red-100 text-red-800' : '' }}
                                            {{ $export->status === 'processing' ? 'bg-yellow-100 text-yellow-800' : '' }}">
                                            {{ ucfirst($export->status) }}
                                        </span>
                                        @if($export->isCompleted())
                                            <a href="{{ route('admin.import-export.download', $export->id) }}" 
                                               class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 hover:bg-blue-200 transition">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                </svg>
                                                Download
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
