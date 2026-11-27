# Integrations

Code for the integration points in **Figure 4.1, System Integration Architecture of LEBROn**.
Each integration has its own folder here. Files Laravel requires in standard places (routes,
views, notifications, migrations, tests) are listed under each integration.

| IP | Figure 4.1 box | Folder | Status |
|----|----------------|--------|--------|
| IP-1 | IsbnService → Google Books API | [`IsbnLookup/`](IsbnLookup/) | Done (needs `GOOGLE_BOOKS_API_KEY`) |
| IP-2 | PaymentService → PayMongo Checkout | [`PayMongo/`](PayMongo/) | Done (needs PayMongo test keys) |
| IP-3 | WebhookController ← PayMongo webhook | [`PayMongo/`](PayMongo/) | Done (needs a public HTTPS URL, or use the simulator) |
| IP-4 | Mail notifier + queue worker → SMTP (Mailtrap) | [`Email/`](Email/) | Done (set Mailtrap SMTP to deliver to an inbox) |
| IP-5 | REST API /api/v1, Sanctum bearer token | [`RestApi/`](RestApi/) | Done |
| IP-6 | Socialite login ↔ Google OAuth 2.0 | [`GoogleSignIn/`](GoogleSignIn/) | Done (needs Google OAuth client) |
| — | Scheduler (cron): expire unpaid orders, return stock | [`PayMongo/ExpireUnpaidPaymentsCommand.php`](PayMongo/ExpireUnpaidPaymentsCommand.php) | Done |

Database tables from the figure: `payments`, `webhook_events`, `notification_logs`, `jobs`, `personal_access_tokens` (tokens),
plus `users.google_id`, `orders.payment_status`, `orders.paid_at`, `books.isbn_lookup_used`, `books.listing_seconds`.
Migration: `database/migrations/2026_11_27_100000_create_integration_tables.php`.

Keys go in `.env`; see the "LEBROn integrations" block in `.env.example`. Each integration
switches itself off cleanly (button hidden or option disabled) until its keys are set.

---

## IP-1: Google Books ISBN lookup (`IsbnLookup/`)

On **Add Listing** the seller types an ISBN and clicks **Look up**: the title, author, publisher,
description and cover are filled in from Google Books.

| File | What it does |
|------|--------------|
| `IsbnService.php` | HTTPS GET `googleapis.com/books/v1/volumes?q=isbn:…&key=…`; ISBN in, title/author/cover out; caches results |
| `Isbn.php` | Cleans up and validates ISBN-10/13, including the check digit |
| `IsbnLookupController.php` | `GET /books/isbn-lookup?isbn=…` (sellers and admins) used by the form |
| `CoverDownloader.php` | Saves the found cover; only HTTPS Google cover hosts, no redirects |
| `TestIsbnLookupCommand.php` | `php artisan isbn:test-lookup --file=isbns.txt`: Objective 2 hit rate (target ≥ 90% of 30 ISBNs) |
| `ListingTimeReportCommand.php` | `php artisan isbn:listing-report`: average listing time with vs without lookup (target ≥ 30% faster) |

Elsewhere: `resources/views/books/create.blade.php` (Look up button), `app/Http/Controllers/BookController.php`
(saves cover and listing time), `tests/Feature/IsbnLookupTest.php`.

## IP-2 and IP-3: PayMongo payments and webhook (`PayMongo/`)

At checkout the buyer chooses **Pay online with PayMongo** (GCash, Maya, GrabPay, QR Ph, card) and is
sent to PayMongo's hosted page. PayMongo then calls the webhook, and the orders become paid.

| File | What it does |
|------|--------------|
| `PaymentService.php` | **IP-2.** Creates the Checkout Session (HTTPS POST, secret key as Basic auth), maps paid/failed/expired onto the orders, returns stock |
| `WebhookController.php` | **IP-3.** `POST /webhooks/paymongo`: verifies the signature, stores the event once (`webhook_events`), marks paid or failed |
| `WebhookSignature.php` | HMAC-SHA256 check of the `Paymongo-Signature` header |
| `PaymentController.php` | Buyer pages: Pay now, return page (double-checks with PayMongo), cancelled page |
| `ExpireUnpaidPaymentsCommand.php` | **Scheduler.** `payments:expire-unpaid` every 5 minutes: expires sessions unpaid after `PAYMONGO_PAYMENT_WINDOW_MINUTES`, cancels the orders, returns stock |
| `SimulateWebhookCommand.php` | `php artisan paymongo:simulate <reference> paid|failed`: sends a correctly signed test webhook when PayMongo cannot reach your computer |

Rules: a seller cannot process or ship an online order before it is paid; completing a cash-on-delivery
order marks it paid. Elsewhere: `app/Http/Controllers/CartController.php`, `routes/web.php`,
`routes/console.php` (schedule), `resources/views/payments/result.blade.php`, `tests/Feature/PayMongoTest.php`.

## IP-4: Email notifications through the queue (`Email/`)

| File | What it does |
|------|--------------|
| `TracksDelivery.php` | Timestamps an email when it is queued |
| `LogNotificationDelivery.php` | Writes every sent or failed email to `notification_logs` with its delivery time |
| `DeliveryReportCommand.php` | `php artisan mail:delivery-report`: Objective 4 (target ≥ 95% within 60 s) |

The emails (queued, `ShouldQueue`) are in `app/Notifications/`: `OrderPlacedNotification` (buyer),
`NewOrderAdminNotification` (seller and admins), `OrderPaidNotification` (buyer and seller, new),
`OrderStatusChangedNotification` (buyer). Run the worker with `php artisan queue:work`
(or `composer run dev`). Tests: `tests/Feature/EmailDeliveryTest.php`.

## IP-5: REST API with Sanctum (`RestApi/`)

| File | What it does |
|------|--------------|
| `Controllers/AuthController.php` | `POST /auth/token`, `GET /auth/me`, `POST /auth/logout` |
| `Controllers/BookController.php`, `CategoryController.php`, `OrderController.php` | Books, categories, orders (online orders return the PayMongo `checkout_url`) |
| `Resources/` | JSON shape of a book, category and order |
| `OpenApiSpec.php`, `Controllers/ApiDocsController.php` | Swagger docs at `/admin/api-docs` (admins) |
| `Controllers/ApiTokenController.php` | API Tokens page |

Elsewhere: `routes/api.php`, `bootstrap/app.php` (401 as JSON), `tests/Feature/ApiTest.php`.

## IP-6: Google sign-in (`GoogleSignIn/`)

`GoogleSignInController.php`: `GET /auth/google/redirect` → Google → `GET /auth/google/callback`.
Accounts are linked by email (only addresses Google has verified); a new address becomes a buyer
account. Two-factor authentication still applies. Button: `resources/views/auth/partials/google-button.blade.php`
(login and register pages). Tests: `tests/Feature/GoogleSignInTest.php`.

---

## How to show each one

| IP | Demo |
|----|------|
| IP-1 | Log in as a seller → **Add Listing** → type `9780593135204` → **Look up** |
| IP-2 | Log in as a buyer → add a book → checkout → **Pay online with PayMongo** → PayMongo test page (test card `4343434343434345`) |
| IP-3 | After paying, the order shows **Paid**; **Admin** can see the event in `webhook_events`. Without a public URL: `php artisan paymongo:simulate <reference> paid` |
| IP-4 | Place an order with `php artisan queue:work` running → `php artisan mail:delivery-report` (and the Mailtrap inbox) |
| IP-5 | Admin → **API Docs** (Swagger) → `GET /orders` without token = 401, `POST /auth/token`, then 200 |
| IP-6 | Log out → **Sign in with Google** |
| Scheduler | `php artisan schedule:list`, or run `php artisan payments:expire-unpaid` |

All integration tests: `php artisan test`.
