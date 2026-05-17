@extends('layouts.app')

@section('title', 'AI Usage & Monitoring - PageTurner Admin')

@section('content')
<div class="bg-gray-50 min-h-screen py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8 pb-6 border-b border-gray-200">
            <div>
                <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight flex items-center gap-3">
                    <span class="text-brand-darkgreen">🤖</span> AI Usage & Monitoring
                </h1>
                <p class="text-gray-500 mt-1">Real-time usage statistics, cost tracking, and system resilience metrics.</p>
            </div>
            <div class="mt-4 md:mt-0 flex gap-4">
                <a href="{{ route('ai.chat') }}" 
                   class="inline-flex items-center px-4 py-2 border border-brand-darkgreen text-brand-darkgreen font-bold rounded-lg hover:bg-brand-darkgreen hover:text-white transition duration-150 shadow-sm">
                    💬 Open AI Chat
                </a>
                <a href="{{ route('books.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-brand-darkgreen text-white font-bold rounded-lg hover:bg-brand-amber hover:text-brand-darkgreen transition duration-150 shadow-sm">
                    📚 Book Catalog
                </a>
            </div>
        </div>

        <!-- Metrics Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            
            <!-- Today's Stats -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition p-6 relative overflow-hidden">
                <div class="absolute right-0 top-0 bg-brand-darkgreen/5 text-brand-darkgreen p-4 rounded-bl-3xl">
                    ⏱️
                </div>
                <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Today's Performance</h3>
                <div class="flex items-baseline space-x-2 mt-4">
                    <span class="text-4xl font-extrabold text-brand-darkgreen">{{ number_format($stats['today_requests']) }}</span>
                    <span class="text-sm font-bold text-gray-500">requests</span>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-50 flex justify-between text-sm text-gray-600">
                    <div>
                        <span class="block font-bold text-gray-800">{{ number_format($stats['today_tokens']) }}</span>
                        <span class="text-xs text-gray-400">Tokens used</span>
                    </div>
                    <div class="text-right">
                        <span class="block font-bold text-brand-amber font-mono">₱{{ number_format($stats['today_cost'] * 58, 2) }}</span>
                        <span class="text-xs text-gray-400">Estimated Cost</span>
                    </div>
                </div>
            </div>

            <!-- All Time Stats -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition p-6 relative overflow-hidden">
                <div class="absolute right-0 top-0 bg-brand-amber/10 text-brand-amber p-4 rounded-bl-3xl">
                    📊
                </div>
                <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-2">All-Time Statistics</h3>
                <div class="flex items-baseline space-x-2 mt-4">
                    <span class="text-4xl font-extrabold text-gray-900">{{ number_format($stats['total_requests']) }}</span>
                    <span class="text-sm font-bold text-gray-500">requests</span>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-50 flex justify-between text-sm text-gray-600">
                    <div>
                        <span class="block font-bold text-gray-800">{{ number_format($stats['total_tokens']) }}</span>
                        <span class="text-xs text-gray-400">Tokens used</span>
                    </div>
                    <div class="text-right">
                        <span class="block font-bold text-brand-amber font-mono">₱{{ number_format($stats['total_cost'] * 58, 2) }}</span>
                        <span class="text-xs text-gray-400">Estimated Cost</span>
                    </div>
                </div>
            </div>

            <!-- Provider Breakdown -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition p-6 relative overflow-hidden">
                <div class="absolute right-0 top-0 bg-blue-50 text-blue-600 p-4 rounded-bl-3xl">
                    🔌
                </div>
                <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Provider Infrastructure</h3>
                <div class="mt-4 space-y-2">
                    @forelse($stats['by_provider'] as $provider)
                        <div class="flex justify-between items-center text-sm py-1 border-b border-gray-50 last:border-0">
                            <span class="font-bold text-gray-700 uppercase flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full {{ $provider['provider'] === 'openai' ? 'bg-green-500' : ($provider['provider'] === 'gemini' ? 'bg-blue-500' : 'bg-brand-amber') }}"></span>
                                {{ $provider['provider'] }}
                            </span>
                            <span class="text-gray-500 font-mono text-xs">
                                {{ number_format($provider['count']) }} reqs
                            </span>
                        </div>
                    @empty
                        <p class="text-gray-500 italic text-sm py-2">No provider logs found.</p>
                    @endforelse
                </div>
            </div>

        </div>

        <!-- Recent Logs Table -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-gray-100 flex justify-between items-center">
                <h3 class="text-lg font-bold text-gray-900">Recent AI Transaction Logs</h3>
                <span class="px-3 py-1 bg-brand-darkgreen/10 text-brand-darkgreen rounded-full text-xs font-bold uppercase tracking-wider">
                    Live Audit
                </span>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-black text-gray-400 uppercase tracking-widest">Timestamp</th>
                            <th class="px-6 py-3 text-left text-xs font-black text-gray-400 uppercase tracking-widest">Provider / Model</th>
                            <th class="px-6 py-3 text-left text-xs font-black text-gray-400 uppercase tracking-widest">Feature Type</th>
                            <th class="px-6 py-3 text-left text-xs font-black text-gray-400 uppercase tracking-widest">Tokens</th>
                            <th class="px-6 py-3 text-left text-xs font-black text-gray-400 uppercase tracking-widest">Response Latency</th>
                            <th class="px-6 py-3 text-left text-xs font-black text-gray-400 uppercase tracking-widest">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @php
                            $recentLogs = \App\Models\AiUsageLog::latest()->take(10)->get();
                        @endphp
                        @forelse($recentLogs as $log)
                            <tr class="hover:bg-gray-50 transition duration-150">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-mono">
                                    {{ $log->created_at->format('M d, H:i:s') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span class="font-bold text-gray-800 uppercase text-xs flex items-center gap-1.5">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $log->provider === 'openai' ? 'bg-green-500' : ($log->provider === 'gemini' ? 'bg-blue-500' : 'bg-brand-amber') }}"></span>
                                        {{ $log->provider }}
                                    </span>
                                    <span class="block text-xxs text-gray-400 font-mono mt-0.5">{{ $log->model }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span class="px-2 py-1 bg-gray-100 text-gray-600 rounded-lg text-xs font-bold">
                                        {{ $log->feature }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-600">
                                    {{ number_format($log->total_tokens) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-600">
                                    {{ $log->response_time ? $log->response_time . 's' : 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($log->success)
                                        <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-bold rounded-full bg-green-50 text-green-700 border border-green-200">
                                            Success
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-bold rounded-full bg-red-50 text-red-700 border border-red-200" title="{{ $log->error_message }}">
                                            Failed
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 whitespace-nowrap text-sm text-gray-400 text-center italic">
                                    No transaction logs available yet. Start interacting with the AI chat bubble or write a review to generate records!
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
@endsection
