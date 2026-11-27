<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>API Docs (Swagger) - {{ config('app.name') }} Admin</title>
    {{-- Swagger UI has its own styles; the site's Tailwind CSS is left out so it cannot restyle them. --}}
    @vite('resources/js/swagger.js')
    <style>
        body { margin: 0; font-family: system-ui, -apple-system, "Segoe UI", sans-serif; background: #fafafa; }
        .admin-bar { display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: .75rem 1.5rem; background: #31472e; color: #fff; }
        .admin-bar a { color: #fff; text-decoration: none; font-size: .875rem; }
        .admin-bar a:hover, .admin-bar a:focus-visible { color: #FFBF00; text-decoration: underline; }
        .admin-bar strong { font-size: 1.05rem; }
        .admin-bar .brand { color: #FFBF00; }
        .admin-bar nav { display: flex; gap: 1.25rem; }
        .swagger-ui .topbar { display: none; }
    </style>
</head>
<body>
    <header class="admin-bar">
        <strong>LEBR<span class="brand">On</span> · REST API (Swagger)</strong>
        <nav aria-label="Admin">
            <a href="{{ route('admin.dashboard') }}">&larr; Admin dashboard</a>
            <a href="{{ route('api-tokens.index') }}">API tokens</a>
            <a href="{{ route('admin.api-docs.spec') }}" target="_blank" rel="noopener">openapi.json</a>
        </nav>
    </header>
    <main>
        <div id="swagger-ui" data-spec-url="{{ route('admin.api-docs.spec') }}"></div>
    </main>
</body>
</html>
