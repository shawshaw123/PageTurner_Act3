# Integrations

Code for LEBROn's integration points (IP-1 to IP-6 in Figure 4.1, *System Integration Architecture*).
Each integration has its own folder here. Some Laravel files must live in standard places
(routes, views, notifications, tests); those are listed under each integration.

| IP | Integration | Folder | Status |
|----|-------------|--------|--------|
| IP-1 | Google Books, book metadata by ISBN | `IsbnLookup/` | Not started |
| IP-2 | PayMongo checkout session (sandbox) | `PayMongo/` | Not started |
| IP-3 | PayMongo webhook (signed) | `PayMongo/` | Not started |
| IP-4 | Email notifications (SMTP, Mailtrap) | see below | Partly done |
| IP-5 | REST API with Sanctum tokens | [`RestApi/`](RestApi/) | **Done** |
| IP-6 | Google sign-in (Socialite) | `GoogleSignIn/` | Not started |

---

## IP-5: REST API (Laravel Sanctum): done

JSON API at `/api/v1`. The catalog is public; changes and all order endpoints need a bearer token
and answer **HTTP 401** without one.

**In this folder**

| File | What it does |
|------|--------------|
| [`RestApi/Controllers/AuthController.php`](RestApi/Controllers/AuthController.php) | `POST /auth/token` (email and password in, token out), `GET /auth/me`, `POST /auth/logout` |
| [`RestApi/Controllers/BookController.php`](RestApi/Controllers/BookController.php) | List, show, create, update and delete book listings |
| [`RestApi/Controllers/CategoryController.php`](RestApi/Controllers/CategoryController.php) | List, show, create, update and delete categories (changes: admins only) |
| [`RestApi/Controllers/OrderController.php`](RestApi/Controllers/OrderController.php) | List, show and place orders; sellers and admins change status |
| [`RestApi/Resources/`](RestApi/Resources/) | The JSON shape of a book, category and order |
| [`RestApi/OpenApiSpec.php`](RestApi/OpenApiSpec.php) | OpenAPI 3 description of every endpoint, shown in Swagger |
| [`RestApi/Controllers/ApiDocsController.php`](RestApi/Controllers/ApiDocsController.php) | Admin Swagger page and `openapi.json` |
| [`RestApi/Controllers/ApiTokenController.php`](RestApi/Controllers/ApiTokenController.php) | Website page where users create and revoke tokens |

**Elsewhere (standard Laravel locations)**

| File | What it does |
|------|--------------|
| `routes/api.php` | All 17 endpoints; the `auth:sanctum` group is the token protection |
| `bootstrap/app.php` | API errors are always JSON, so a missing token gives 401 (not a login redirect) |
| `config/sanctum.php`, `database/migrations/*_create_personal_access_tokens_table.php` | Sanctum setup and token table |
| `app/Models/User.php` | `HasApiTokens` |
| `app/Services/CheckoutService.php` | Checkout shared by the website and `POST /api/v1/orders` |
| `resources/views/admin/api-docs.blade.php`, `resources/js/swagger.js` | Swagger UI page |
| `resources/views/api/tokens.blade.php` | API Tokens page |
| `tests/Feature/ApiTest.php` | 21 tests, including: every protected endpoint returns 401 without a token, and every endpoint is in the Swagger spec |

**How to show it**

1. Log in as an admin and click **API Docs** in the top menu (`/admin/api-docs`).
2. `GET /orders` → **Try it out** → **Execute**: **401 Unauthenticated**.
3. `POST /auth/token` → **Execute**: **201** with a token; Swagger applies it automatically.
4. `GET /orders` again: **200** with the orders, and the request time is shown.
5. Run the tests: `php artisan test --filter=ApiTest`.

---

## IP-4: Email notifications: partly done

Laravel keeps notification classes in `app/Notifications/` (the class name is stored with each
saved notification, so they stay there).

| File | Email |
|------|-------|
| `app/Notifications/OrderPlacedNotification.php` | To the buyer when an order is placed |
| `app/Notifications/NewOrderAdminNotification.php` | To the seller and admins when an order is placed |
| `app/Notifications/OrderStatusChangedNotification.php` | To the buyer when the order status changes |

**Still to do:** the "order paid" email (needs IP-2/IP-3), sending through a queue worker, and a
Mailtrap test inbox. Locally `MAIL_MAILER=log`, so emails are written to `storage/logs/laravel.log`.

---

## Not started yet

- **IP-1 Google Books:** `IsbnLookup/IsbnService.php` (HTTP client: ISBN in, title, author and cover out) and autofill on the Add Listing form.
- **IP-2 and IP-3 PayMongo:** `PayMongo/PaymentService.php` (create checkout session, map status) and `PayMongo/WebhookController.php` (`POST /webhooks/paymongo`, verify the signature header, update the order to paid, failed or expired).
- **IP-6 Google sign-in:** `GoogleSignIn/` (Socialite redirect and callback, link accounts by email).
