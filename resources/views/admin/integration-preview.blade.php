@extends('layouts.app')

@section('title', 'Integration Preview — LEBROn')

@section('header')
<x-page-header
    title="Integration Preview"
    subtitle="Wireframe mock screens of the six integration points. Nothing here calls a real API — every result is simulated in the browser."
    :breadcrumbs="[
        ['label' => 'Admin', 'url' => route('admin.dashboard')],
        ['label' => 'Integration Preview'],
    ]"
>
    <x-slot:actions>
        <x-badge variant="warning" dot>Mock data only</x-badge>
    </x-slot:actions>
</x-page-header>
@endsection

@section('content')
@php
    $tabs = [
        'ip1' => ['IP-1', 'Google Books API'],
        'ip2' => ['IP-2', 'PayMongo Checkout'],
        'ip3' => ['IP-3', 'Signed Webhooks'],
        'ip4' => ['IP-4', 'Queued Email'],
        'ip5' => ['IP-5', 'Sanctum REST API'],
        'ip6' => ['IP-6', 'Google Sign-In'],
    ];
    $panel = 'bg-white rounded-2xl border-2 border-dashed border-brand-border p-6';
    $btn = 'inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg bg-brand-darkgreen text-white text-sm font-semibold hover:bg-brand-forest transition-colors disabled:opacity-50';
    $btnGhost = 'inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg border border-brand-border bg-white text-sm font-medium text-brand-ink hover:bg-brand-cream transition-colors';
    $input = 'w-full rounded-lg border-brand-border bg-white text-sm focus:border-brand-darkgreen focus:ring-brand-darkgreen';
    $label = 'block text-xs font-semibold uppercase tracking-wider text-brand-muted mb-1';
    $note = 'text-xs text-brand-muted';
    $mono = 'font-mono text-xs bg-brand-ink text-green-200 rounded-lg p-4 overflow-x-auto whitespace-pre';
@endphp

<div id="mock-preview">
    {{-- Tab bar --}}
    <div role="tablist" aria-label="Integration points" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2 mb-6">
        @foreach($tabs as $id => [$code, $name])
            <button type="button" role="tab" id="tab-{{ $id }}" aria-controls="panel-{{ $id }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}" data-tab="{{ $id }}"
                    class="mock-tab text-left px-3 py-2.5 rounded-xl border border-brand-border bg-white hover:border-brand-darkgreen/50 transition-colors aria-selected:bg-brand-darkgreen aria-selected:text-white aria-selected:border-brand-darkgreen">
                <span class="block text-[11px] font-bold uppercase tracking-wider opacity-70">{{ $code }}</span>
                <span class="block text-sm font-semibold">{{ $name }}</span>
            </button>
        @endforeach
    </div>

    {{-- IP-1 Google Books ISBN lookup --}}
    <section role="tabpanel" id="panel-ip1" aria-labelledby="tab-ip1" class="mock-panel">
        <div class="grid lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 {{ $panel }}">
                <h2 class="font-serif text-lg font-bold text-brand-ink mb-1">Seller → Add Listing</h2>
                <p class="{{ $note }} mb-5">The seller types an ISBN; title, author, publisher and cover fill in automatically.</p>
                <div class="flex gap-2 mb-6">
                    <div class="flex-1">
                        <label for="m-isbn" class="{{ $label }}">ISBN-10 / ISBN-13</label>
                        <input id="m-isbn" class="{{ $input }} font-mono" value="9780140449136">
                    </div>
                    <button type="button" id="m-isbn-go" class="{{ $btn }} self-end">Look up</button>
                </div>
                <p id="m-isbn-status" class="text-sm mb-4 min-h-5" role="status" aria-live="polite"></p>
                <div class="flex gap-5">
                    <div id="m-cover" class="w-28 h-40 shrink-0 rounded-lg border-2 border-dashed border-brand-border bg-brand-cream flex items-center justify-center text-xs text-brand-muted text-center p-2">Cover</div>
                    <div class="flex-1 grid sm:grid-cols-2 gap-3">
                        <div class="sm:col-span-2"><label class="{{ $label }}" for="m-title">Title</label><input id="m-title" class="{{ $input }}" placeholder="—"></div>
                        <div><label class="{{ $label }}" for="m-author">Author</label><input id="m-author" class="{{ $input }}" placeholder="—"></div>
                        <div><label class="{{ $label }}" for="m-pub">Publisher</label><input id="m-pub" class="{{ $input }}" placeholder="—"></div>
                        <div><label class="{{ $label }}" for="m-price">Price (₱) — seller enters</label><input id="m-price" class="{{ $input }}" placeholder="0.00"></div>
                        <div><label class="{{ $label }}" for="m-stock">Stock — seller enters</label><input id="m-stock" class="{{ $input }}" placeholder="0"></div>
                    </div>
                </div>
            </div>
            <aside class="{{ $panel }} bg-brand-paper">
                <h3 class="font-semibold text-brand-ink mb-3">How it works</h3>
                <ol class="text-sm text-brand-muted space-y-2 list-decimal list-inside">
                    <li>ISBN checksum is validated</li>
                    <li>Server asks Google Books: <code class="text-xs">volumes?q=isbn:…</code></li>
                    <li>Result is cached for 24 h</li>
                    <li>Form fields are filled in</li>
                </ol>
                <p class="{{ $note }} mt-4">Try: <button type="button" class="m-isbn-sample underline" data-isbn="9780140449136">9780140449136</button>, <button type="button" class="m-isbn-sample underline" data-isbn="9780061120084">9780061120084</button>, or <button type="button" class="m-isbn-sample underline" data-isbn="1234567890">1234567890</button> (invalid).</p>
                <p class="{{ $note }} mt-3">Real code: <code>app/Integrations/IsbnLookup</code></p>
            </aside>
        </div>
    </section>

    {{-- IP-2 PayMongo checkout --}}
    <section role="tabpanel" id="panel-ip2" aria-labelledby="tab-ip2" class="mock-panel" hidden>
        <div class="grid lg:grid-cols-2 gap-6">
            <div class="{{ $panel }}">
                <h2 class="font-serif text-lg font-bold text-brand-ink mb-1">Buyer → Checkout</h2>
                <p class="{{ $note }} mb-5">One cart, split into one order per seller, paid in one checkout.</p>
                <ul class="divide-y divide-brand-border text-sm mb-4">
                    <li class="py-2 flex justify-between"><span>The Odyssey <span class="text-brand-muted">· Page &amp; Quill</span></span><span class="tabular-nums">₱450.00</span></li>
                    <li class="py-2 flex justify-between"><span>To Kill a Mockingbird <span class="text-brand-muted">· Bookhaven</span></span><span class="tabular-nums">₱520.00</span></li>
                    <li class="py-2 flex justify-between font-bold"><span>Total</span><span class="tabular-nums">₱970.00</span></li>
                </ul>
                <fieldset class="mb-5">
                    <legend class="{{ $label }}">Payment method</legend>
                    <label class="flex items-center gap-2 text-sm py-1"><input type="radio" name="m-pm" checked> Pay online (PayMongo)</label>
                    <label class="flex items-center gap-2 text-sm py-1"><input type="radio" name="m-pm"> Cash on delivery</label>
                </fieldset>
                <button type="button" id="m-pay-open" class="{{ $btn }} w-full">Place order &amp; pay ₱970.00</button>
            </div>

            <div class="{{ $panel }} relative">
                <span class="absolute top-3 right-3"><x-badge variant="info">Sandbox</x-badge></span>
                <div id="m-pay-idle" class="h-full flex flex-col items-center justify-center text-center text-brand-muted text-sm py-16">
                    <svg class="w-10 h-10 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h2m-6 4h18a1 1 0 001-1V6a1 1 0 00-1-1H3a1 1 0 00-1 1v12a1 1 0 001 1z"/></svg>
                    Hosted checkout page opens here after "Place order".
                </div>
                <div id="m-pay-sheet" hidden>
                    <p class="{{ $note }}">Checkout session <span class="font-mono">cs_mock_8f2a…</span></p>
                    <p class="text-2xl font-bold text-brand-ink mb-4">₱970.00</p>
                    <p class="{{ $label }}">Choose how to pay</p>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 mb-5" id="m-pay-methods">
                        @foreach(['GCash', 'Maya', 'GrabPay', 'QR Ph', 'Card'] as $m)
                            <button type="button" data-method="{{ $m }}" class="m-method px-3 py-3 rounded-lg border-2 border-brand-border text-sm font-semibold hover:border-brand-darkgreen aria-pressed:border-brand-darkgreen aria-pressed:bg-brand-cream" aria-pressed="{{ $loop->first ? 'true' : 'false' }}">{{ $m }}</button>
                        @endforeach
                    </div>
                    <div class="flex gap-2">
                        <button type="button" id="m-pay-ok" class="{{ $btn }} flex-1">Authorize test payment</button>
                        <button type="button" id="m-pay-fail" class="{{ $btnGhost }}">Fail it</button>
                    </div>
                </div>
                <div id="m-pay-result" class="text-center py-12" hidden role="status" aria-live="polite"></div>
            </div>
        </div>
        <p class="{{ $note }} mt-3">Real code: <code>app/Integrations/PayMongo/PaymentService.php</code> → <code>startCheckout()</code></p>
    </section>

    {{-- IP-3 Signed webhooks + scheduler --}}
    <section role="tabpanel" id="panel-ip3" aria-labelledby="tab-ip3" class="mock-panel" hidden>
        <div class="grid lg:grid-cols-5 gap-6">
            <div class="lg:col-span-3 {{ $panel }}">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-serif text-lg font-bold text-brand-ink">Incoming webhook — <code class="text-sm">POST /webhooks/paymongo</code></h2>
                </div>
                <div class="flex flex-wrap gap-2 mb-4">
                    <button type="button" class="m-hook {{ $btn }}" data-kind="paid">Send "payment.paid"</button>
                    <button type="button" class="m-hook {{ $btnGhost }}" data-kind="failed">Send "payment.failed"</button>
                    <button type="button" class="m-hook {{ $btnGhost }}" data-kind="forged">Send forged signature</button>
                    <button type="button" class="m-hook {{ $btnGhost }}" data-kind="expire">Run scheduler (expire)</button>
                </div>
                <div id="m-hook-steps" class="space-y-2 text-sm mb-4"></div>
                <pre id="m-hook-body" class="{{ $mono }}">Pick an event above to see the request.</pre>
            </div>
            <div class="lg:col-span-2 {{ $panel }}">
                <h3 class="font-semibold text-brand-ink mb-3">Order #1042 status</h3>
                <p class="text-3xl font-bold mb-1" id="m-hook-status">Awaiting payment</p>
                <p class="{{ $note }} mb-5" id="m-hook-time">Waiting for PayMongo…</p>
                <h3 class="font-semibold text-brand-ink mb-2">Event log</h3>
                <ul id="m-hook-log" class="text-xs font-mono space-y-1 text-brand-muted max-h-56 overflow-y-auto">
                    <li>— no events yet —</li>
                </ul>
            </div>
        </div>
        <p class="{{ $note }} mt-3">Real code: <code>app/Integrations/PayMongo/WebhookSignature.php</code>, <code>WebhookController.php</code>, scheduler in <code>routes/console.php</code></p>
    </section>

    {{-- IP-4 Queued email --}}
    <section role="tabpanel" id="panel-ip4" aria-labelledby="tab-ip4" class="mock-panel" hidden>
        <div class="grid lg:grid-cols-2 gap-6">
            <div class="{{ $panel }}">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-serif text-lg font-bold text-brand-ink">Mail queue</h2>
                    <button type="button" id="m-mail-add" class="{{ $btn }}">Trigger order event</button>
                </div>
                <table class="w-full text-sm">
                    <thead><tr class="text-left text-xs uppercase tracking-wider text-brand-muted"><th class="py-2">Email</th><th>To</th><th>Status</th><th class="text-right">Delivery</th></tr></thead>
                    <tbody id="m-mail-rows" class="divide-y divide-brand-border"></tbody>
                </table>
                <div class="mt-5 grid grid-cols-3 gap-3 text-center">
                    <div class="rounded-lg bg-brand-cream p-3"><p class="text-xl font-bold" id="m-mail-sent">0</p><p class="{{ $note }}">Sent</p></div>
                    <div class="rounded-lg bg-brand-cream p-3"><p class="text-xl font-bold" id="m-mail-rate">—</p><p class="{{ $note }}">Within 60 s</p></div>
                    <div class="rounded-lg bg-brand-cream p-3"><p class="text-xl font-bold" id="m-mail-avg">—</p><p class="{{ $note }}">Avg. seconds</p></div>
                </div>
            </div>
            <div class="{{ $panel }}">
                <h3 class="font-semibold text-brand-ink mb-3">Inbox preview (Mailtrap)</h3>
                <div class="rounded-xl border border-brand-border overflow-hidden">
                    <div class="bg-brand-cream px-4 py-2 text-xs text-brand-muted space-y-0.5">
                        <p><strong>From:</strong> LEBROn &lt;no-reply@lebron.test&gt;</p>
                        <p><strong>To:</strong> <span id="m-mail-to">juan@example.com</span></p>
                        <p><strong>Subject:</strong> <span id="m-mail-subj">Order #1042 placed</span></p>
                    </div>
                    <div class="p-5 text-sm space-y-3">
                        <p class="font-serif text-lg font-bold"><span class="text-brand-darkgreen">LEBR</span><span class="text-brand-amber">On</span></p>
                        <p id="m-mail-text">Hi Juan, thank you for your order! We'll let you know when the seller ships it.</p>
                        <div class="h-3 w-3/4 rounded bg-brand-border"></div>
                        <div class="h-3 w-1/2 rounded bg-brand-border"></div>
                        <span class="inline-block px-4 py-2 rounded-lg bg-brand-darkgreen text-white text-xs font-semibold">View order</span>
                    </div>
                </div>
            </div>
        </div>
        <p class="{{ $note }} mt-3">Real code: <code>app/Notifications/*</code> (ShouldQueue) + <code>app/Integrations/Email</code> delivery log</p>
    </section>

    {{-- IP-5 Sanctum REST API --}}
    <section role="tabpanel" id="panel-ip5" aria-labelledby="tab-ip5" class="mock-panel" hidden>
        <div class="{{ $panel }}">
            <h2 class="font-serif text-lg font-bold text-brand-ink mb-4">API console</h2>
            <div class="flex flex-col md:flex-row gap-2 mb-3">
                <label for="m-api-ep" class="sr-only">Endpoint</label>
                <select id="m-api-ep" class="{{ $input }} md:w-auto font-mono">
                    <option value="books">GET /api/v1/books</option>
                    <option value="me">GET /api/v1/auth/me</option>
                    <option value="orders">GET /api/v1/orders</option>
                    <option value="create">POST /api/v1/books</option>
                </select>
                <label class="flex items-center gap-2 text-sm px-2"><input type="checkbox" id="m-api-token" checked> Send <code>Authorization: Bearer 3|kX9…</code></label>
                <button type="button" id="m-api-send" class="{{ $btn }} md:ml-auto">Send</button>
            </div>
            <div class="flex items-center gap-3 mb-2 text-sm" role="status" aria-live="polite">
                <span id="m-api-code" class="font-mono font-bold">—</span>
                <span id="m-api-ms" class="text-brand-muted"></span>
            </div>
            <pre id="m-api-out" class="{{ $mono }} min-h-48">Press Send.</pre>
            <p class="{{ $note }} mt-3">Public: GET books / categories. Everything else needs a token → 401 without one. Real code: <code>routes/api.php</code>, <code>app/Integrations/RestApi</code>, docs at <a href="{{ route('admin.api-docs') }}" class="underline">Swagger UI</a>.</p>
        </div>
    </section>

    {{-- IP-6 Google sign-in --}}
    <section role="tabpanel" id="panel-ip6" aria-labelledby="tab-ip6" class="mock-panel" hidden>
        <div class="grid lg:grid-cols-2 gap-6">
            <div class="{{ $panel }} max-w-md w-full mx-auto">
                <p class="font-serif text-2xl font-bold text-center mb-1"><span class="text-brand-darkgreen">LEBR</span><span class="text-brand-amber">On</span></p>
                <p class="text-center {{ $note }} mb-5">Log in to your account</p>
                <div class="space-y-3 mb-4">
                    <div class="h-10 rounded-lg border border-brand-border bg-brand-paper"></div>
                    <div class="h-10 rounded-lg border border-brand-border bg-brand-paper"></div>
                    <div class="h-10 rounded-lg bg-brand-border"></div>
                </div>
                <div class="flex items-center gap-3 my-4 text-xs text-brand-muted"><span class="flex-1 border-t border-brand-border"></span>or<span class="flex-1 border-t border-brand-border"></span></div>
                <button type="button" id="m-g-open" class="{{ $btnGhost }} w-full">
                    <span class="font-bold text-blue-600">G</span> Continue with Google
                </button>
            </div>
            <div class="{{ $panel }}">
                <div id="m-g-idle" class="text-center text-sm text-brand-muted py-16">Google's account chooser appears here.</div>
                <div id="m-g-chooser" hidden>
                    <p class="font-semibold mb-1">Choose an account</p>
                    <p class="{{ $note }} mb-4">to continue to LEBROn</p>
                    <ul class="divide-y divide-brand-border border border-brand-border rounded-lg">
                        <li><button type="button" class="m-g-acct w-full text-left px-4 py-3 hover:bg-brand-cream text-sm" data-email="juan@example.com" data-exists="1"><strong>Juan Dela Cruz</strong><span class="block {{ $note }}">juan@example.com · already a LEBROn buyer</span></button></li>
                        <li><button type="button" class="m-g-acct w-full text-left px-4 py-3 hover:bg-brand-cream text-sm" data-email="maria.new@example.com" data-exists="0"><strong>Maria Santos</strong><span class="block {{ $note }}">maria.new@example.com · new to LEBROn</span></button></li>
                    </ul>
                </div>
                <div id="m-g-result" hidden role="status" aria-live="polite"></div>
            </div>
        </div>
        <p class="{{ $note }} mt-3">Real code: <code>app/Integrations/GoogleSignIn/GoogleSignInController.php</code> (Socialite OAuth 2.0)</p>
    </section>
</div>

<script>
(function () {
    var $ = function (id) { return document.getElementById(id); };
    var wait = function (ms) { return new Promise(function (r) { setTimeout(r, ms); }); };
    var esc = function (s) { var d = document.createElement('div'); d.textContent = s; return d.innerHTML; };

    // Tabs
    var tabs = document.querySelectorAll('.mock-tab');
    function show(id) {
        tabs.forEach(function (t) { t.setAttribute('aria-selected', t.dataset.tab === id ? 'true' : 'false'); });
        document.querySelectorAll('.mock-panel').forEach(function (p) { p.hidden = p.id !== 'panel-' + id; });
        history.replaceState(null, '', '#' + id);
    }
    tabs.forEach(function (t) { t.addEventListener('click', function () { show(t.dataset.tab); }); });
    if (location.hash && $('panel-' + location.hash.slice(1))) show(location.hash.slice(1));

    // IP-1
    var books = {
        '9780140449136': { t: 'The Odyssey', a: 'Homer', p: 'Penguin Classics', c: '#1f4d3a' },
        '9780061120084': { t: 'To Kill a Mockingbird', a: 'Harper Lee', p: 'Harper Perennial', c: '#8a5a1f' }
    };
    function validIsbn(s) {
        s = s.replace(/[-\s]/g, '');
        if (/^\d{13}$/.test(s)) { var sum = 0; for (var i = 0; i < 12; i++) sum += +s[i] * (i % 2 ? 3 : 1); return (10 - sum % 10) % 10 === +s[12]; }
        if (/^\d{9}[\dX]$/i.test(s)) { var t = 0; for (var j = 0; j < 10; j++) t += (s[j].toUpperCase() === 'X' ? 10 : +s[j]) * (10 - j); return t % 11 === 0; }
        return false;
    }
    async function lookup() {
        var isbn = $('m-isbn').value.replace(/[-\s]/g, ''), st = $('m-isbn-status');
        ['m-title', 'm-author', 'm-pub'].forEach(function (f) { $(f).value = ''; });
        $('m-cover').style.background = ''; $('m-cover').textContent = 'Cover'; $('m-cover').className = $('m-cover').className.replace(' text-white', '');
        if (!validIsbn(isbn)) { st.className = 'text-sm mb-4 min-h-5 text-red-600'; st.textContent = '✕ That ISBN fails the checksum — no request sent.'; return; }
        st.className = 'text-sm mb-4 min-h-5 text-brand-muted'; st.textContent = 'Asking Google Books…';
        $('m-isbn-go').disabled = true; var t0 = performance.now(); await wait(700); $('m-isbn-go').disabled = false;
        var b = books[isbn];
        if (!b) { st.className = 'text-sm mb-4 min-h-5 text-amber-700'; st.textContent = 'No book found for this ISBN — the seller types the details manually.'; return; }
        $('m-title').value = b.t; $('m-author').value = b.a; $('m-pub').value = b.p;
        $('m-cover').style.background = b.c; $('m-cover').textContent = b.t; $('m-cover').className += ' text-white';
        st.className = 'text-sm mb-4 min-h-5 text-green-700'; st.textContent = '✓ Filled from Google Books in ' + Math.round(performance.now() - t0) + ' ms (mock).';
    }
    $('m-isbn-go').addEventListener('click', lookup);
    document.querySelectorAll('.m-isbn-sample').forEach(function (b) { b.addEventListener('click', function () { $('m-isbn').value = b.dataset.isbn; lookup(); }); });

    // IP-2
    var method = 'GCash';
    $('m-pay-open').addEventListener('click', async function () {
        $('m-pay-idle').hidden = true; $('m-pay-result').hidden = true; $('m-pay-sheet').hidden = false;
    });
    document.querySelectorAll('.m-method').forEach(function (b) {
        b.addEventListener('click', function () {
            document.querySelectorAll('.m-method').forEach(function (x) { x.setAttribute('aria-pressed', 'false'); });
            b.setAttribute('aria-pressed', 'true'); method = b.dataset.method;
        });
    });
    async function finishPay(ok) {
        $('m-pay-sheet').hidden = true; var r = $('m-pay-result'); r.hidden = false;
        r.innerHTML = '<p class="text-brand-muted text-sm">Processing ' + esc(method) + ' payment…</p>';
        await wait(900);
        r.innerHTML = ok
            ? '<p class="text-4xl mb-2">✓</p><p class="font-bold text-green-700">Paid with ' + esc(method) + '</p><p class="text-xs text-brand-muted mt-2">Buyer returns to LEBROn; the webhook (IP-3) marks both orders paid.</p>'
            : '<p class="text-4xl mb-2">✕</p><p class="font-bold text-red-600">Payment failed</p><p class="text-xs text-brand-muted mt-2">Order stays "awaiting payment"; the buyer can try again.</p>';
    }
    $('m-pay-ok').addEventListener('click', function () { finishPay(true); });
    $('m-pay-fail').addEventListener('click', function () { finishPay(false); });

    // IP-3
    var hookLog = $('m-hook-log'), firstEvent = true;
    function logHook(text) {
        if (firstEvent) { hookLog.innerHTML = ''; firstEvent = false; }
        var li = document.createElement('li'); li.textContent = new Date().toLocaleTimeString() + '  ' + text; hookLog.prepend(li);
    }
    function step(ok, text) {
        return '<div class="flex items-center gap-2"><span class="w-5 h-5 rounded-full flex items-center justify-center text-xs text-white ' + (ok ? 'bg-green-600' : 'bg-red-600') + '">' + (ok ? '✓' : '✕') + '</span>' + esc(text) + '</div>';
    }
    document.querySelectorAll('.m-hook').forEach(function (b) {
        b.addEventListener('click', async function () {
            var k = b.dataset.kind, steps = $('m-hook-steps');
            if (k === 'expire') {
                $('m-hook-body').textContent = '$ php artisan payments:expire-unpaid\n\nChecking payments older than 60 minutes…\nExpired 1 payment, cancelled order #1042, restocked 2 books.';
                steps.innerHTML = step(true, 'Scheduler runs every 5 minutes');
                $('m-hook-status').textContent = 'Expired'; $('m-hook-status').className = 'text-3xl font-bold mb-1 text-brand-muted';
                $('m-hook-time').textContent = 'Unpaid too long — books put back on sale';
                logHook('scheduler  expired #1042'); return;
            }
            var type = k === 'failed' ? 'payment.failed' : 'payment.paid';
            var sig = k === 'forged' ? 't=1764300000,te=00badc0ffee…' : 't=1764300000,te=5f1c9a7e2b…';
            $('m-hook-body').textContent = 'POST /webhooks/paymongo\nPaymongo-Signature: ' + sig + '\n\n' + JSON.stringify({ data: { id: 'evt_mock_' + Math.random().toString(36).slice(2, 8), attributes: { type: type, data: { attributes: { reference_number: 'LEB-1042', amount: 97000 } } } } }, null, 2);
            steps.innerHTML = '<p class="text-brand-muted">Verifying…</p>';
            var t0 = performance.now(); await wait(500);
            if (k === 'forged') {
                steps.innerHTML = step(true, 'Request received') + step(false, 'HMAC-SHA256 signature does not match → 401, ignored');
                logHook('REJECTED   bad signature'); return;
            }
            var secs = (performance.now() - t0) / 1000 + 1.2;
            steps.innerHTML = step(true, 'Request received') + step(true, 'HMAC-SHA256 signature valid') + step(true, 'Event not seen before (idempotent)') + step(true, 'Order updated');
            var paid = k === 'paid';
            $('m-hook-status').textContent = paid ? 'Paid' : 'Payment failed';
            $('m-hook-status').className = 'text-3xl font-bold mb-1 ' + (paid ? 'text-green-700' : 'text-red-600');
            $('m-hook-time').textContent = 'Reflected ' + secs.toFixed(1) + ' s after payment (target ≤ 60 s)';
            logHook(type + '  → #1042');
        });
    });

    // IP-4
    var mails = [
        ['Order placed', 'juan@example.com', 'Order #1042 placed', 'Hi Juan, thank you for your order! We\'ll let you know when the seller ships it.'],
        ['New order (seller)', 'pageandquill@example.com', 'New order #1042', 'You have a new order for The Odyssey. Please prepare it for shipping.'],
        ['Order paid', 'juan@example.com', 'Payment received for #1042', 'We received your GCash payment of ₱970.00.'],
        ['Status changed', 'juan@example.com', 'Order #1042 shipped', 'Good news — your order is on its way!']
    ];
    var mi = 0, sent = [], rows = $('m-mail-rows');
    $('m-mail-add').addEventListener('click', async function () {
        var m = mails[mi++ % mails.length], tr = document.createElement('tr');
        tr.innerHTML = '<td class="py-2">' + esc(m[0]) + '</td><td class="text-brand-muted">' + esc(m[1]) + '</td><td class="m-st text-amber-700">Queued</td><td class="m-sec text-right tabular-nums">—</td>';
        rows.prepend(tr);
        await wait(600); tr.querySelector('.m-st').textContent = 'Sending…';
        await wait(700);
        var s = 1 + Math.random() * 3;
        tr.querySelector('.m-st').textContent = 'Sent'; tr.querySelector('.m-st').className = 'm-st text-green-700';
        tr.querySelector('.m-sec').textContent = s.toFixed(1) + ' s';
        sent.push(s);
        $('m-mail-sent').textContent = sent.length;
        $('m-mail-rate').textContent = Math.round(100 * sent.filter(function (x) { return x <= 60; }).length / sent.length) + '%';
        $('m-mail-avg').textContent = (sent.reduce(function (a, b) { return a + b; }, 0) / sent.length).toFixed(1);
        $('m-mail-to').textContent = m[1]; $('m-mail-subj').textContent = m[2]; $('m-mail-text').textContent = m[3];
    });

    // IP-5
    var api = {
        books: { auth: false, body: { data: [{ id: 1, title: 'The Odyssey', author: 'Homer', price: 450, seller: 'Page & Quill' }, { id: 2, title: 'To Kill a Mockingbird', author: 'Harper Lee', price: 520, seller: 'Bookhaven' }], meta: { total: 20, per_page: 15 } } },
        me: { auth: true, body: { data: { id: 7, name: 'Juan Dela Cruz', role: 'customer' } } },
        orders: { auth: true, body: { data: [{ id: 1042, status: 'pending', payment_status: 'paid', total_amount: 970 }] } },
        create: { auth: true, code: 201, body: { data: { id: 21, title: 'Noli Me Tangere', author: 'José Rizal', price: 380 } } }
    };
    $('m-api-send').addEventListener('click', async function () {
        var ep = api[$('m-api-ep').value], hasToken = $('m-api-token').checked;
        $('m-api-out').textContent = '…'; $('m-api-code').textContent = ''; $('m-api-ms').textContent = '';
        var ms = 40 + Math.round(Math.random() * 120); await wait(ms);
        var denied = ep.auth && !hasToken, code = denied ? 401 : (ep.code || 200);
        $('m-api-code').textContent = code + (denied ? ' Unauthorized' : code === 201 ? ' Created' : ' OK');
        $('m-api-code').className = 'font-mono font-bold ' + (denied ? 'text-red-600' : 'text-green-700');
        $('m-api-ms').textContent = ms + ' ms (target < 2 s)';
        $('m-api-out').textContent = JSON.stringify(denied ? { message: 'Unauthenticated.' } : ep.body, null, 2);
    });

    // IP-6
    $('m-g-open').addEventListener('click', function () { $('m-g-idle').hidden = true; $('m-g-result').hidden = true; $('m-g-chooser').hidden = false; });
    document.querySelectorAll('.m-g-acct').forEach(function (b) {
        b.addEventListener('click', async function () {
            $('m-g-chooser').hidden = true; var r = $('m-g-result'); r.hidden = false;
            r.innerHTML = '<p class="text-sm text-brand-muted py-16 text-center">Exchanging OAuth code with Google…</p>';
            await wait(800);
            var existing = b.dataset.exists === '1';
            r.innerHTML = '<div class="py-8 text-center"><p class="text-4xl mb-2">✓</p><p class="font-bold text-green-700">Signed in as ' + esc(b.dataset.email) + '</p><p class="text-sm text-brand-muted mt-2">'
                + (existing ? 'Email matched an existing account → linked to it. No duplicate account created.' : 'No account with this email → a new buyer account was created (email already verified by Google).')
                + '</p><p class="text-xs text-brand-muted mt-3">If two-factor authentication is on, the code is still asked next.</p></div>';
        });
    });
})();
</script>
@endsection
