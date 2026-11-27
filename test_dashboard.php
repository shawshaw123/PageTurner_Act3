<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard Test - PageTurner</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-7xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">🎯 Lab 6 Dashboard Test</h1>
        
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Quick Actions Check</h2>
            
            <div class="space-y-4">
                <div class="p-4 bg-green-100 border border-green-200 rounded">
                    <h3 class="text-green-800 font-bold">✅ Should See:</h3>
                    <ul class="list-disc list-inside space-y-2">
                        <li>Page title: "Admin Dashboard - PageTurner"</li>
                        <li>Quick Actions section with "🚀 Quick Actions" title</li>
                        <li>Orange "Fix Migration" button</li>
                    </ul>
                </div>
                
                <div class="p-4 bg-blue-100 border border-blue-200 rounded">
                    <h3 class="text-blue-800 font-bold">🔍 Try These URLs:</h3>
                    <div class="space-y-2">
                        <a href="http://localhost:8000/admin/import" class="block p-3 bg-blue-500 text-white rounded hover:bg-blue-600">
                            📤 Import Books
                        </a>
                        <a href="http://localhost:8000/admin/export" class="block p-3 bg-green-500 text-white rounded hover:bg-green-600 mt-2">
                            📥 Export Data
                        </a>
                        <a href="http://localhost:8000/admin/backup" class="block p-3 bg-yellow-500 text-white rounded hover:bg-yellow-600 mt-2">
                            💾 Backup Management
                        </a>
                        <a href="http://localhost:8000/admin/audit" class="block p-3 bg-red-500 text-white rounded hover:bg-red-600 mt-2">
                            🔍 Audit Logs
                        </a>
                    </div>
                </div>
                
                <div class="p-4 bg-purple-100 border border-purple-200 rounded">
                    <h3 class="text-purple-800 font-bold">🎯 Quick Actions Should Be:</h3>
                    <div class="bg-white p-4 rounded border border-purple-300">
                        <div class="text-center mb-4">
                            <div class="inline-block p-4 bg-orange-500 text-white rounded-lg">
                                <h4 class="text-xl font-bold">🚀 Quick Actions</h4>
                                <p class="text-sm">Section with orange "Fix Migration" button</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="text-center">
                                <div class="text-6xl">📤</div>
                                <p class="text-sm text-gray-600">Data Management</p>
                            </div>
                            <div class="text-center">
                                <div class="text-6xl">💾</div>
                                <p class="text-sm text-gray-600">Backup System</p>
                            </div>
                            <div class="text-center">
                                <div class="text-6xl">🔍</div>
                                <p class="text-sm text-gray-600">Audit Logs</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="p-4 bg-gray-100 border border-gray-200 rounded">
                    <h3 class="text-gray-800 font-bold">🔧 Debug Info</h3>
                    <div class="bg-gray-800 p-4 rounded text-white">
                        <p class="text-sm">Current URL: <code id="current-url">--</code></p>
                        <p class="text-sm">If Quick Actions not visible, try refreshing page or using direct URLs above.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Update current URL display
        document.getElementById('current-url').textContent = window.location.href;
        
        // Auto-refresh every 5 seconds
        setTimeout(() => {
            location.reload();
        }, 5000);
    </script>
</body>
</html>
