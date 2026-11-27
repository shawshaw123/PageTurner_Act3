<?php

namespace App\Integrations\RestApi;

use App\Models\Order;

/**
 * OpenAPI 3.0 description of the REST API in routes/api.php, shown with
 * Swagger UI on the admin "API Docs" page. tests/Feature/ApiTest.php checks
 * that every API route appears here, so the two cannot drift apart.
 */
class OpenApiSpec
{
    public static function build(): array
    {
        return [
            'openapi' => '3.0.3',
            'info' => [
                'title' => config('app.name') . ' REST API',
                'version' => '1.0.0',
                'description' => implode("\n\n", [
                    'Book marketplace API for books, categories and orders, secured with **Laravel Sanctum** bearer tokens.',
                    '**To try a protected endpoint:** run `POST /auth/token` below (the token is applied automatically), or click **Authorize** and paste a token from the API Tokens page.',
                    'Protected endpoints answer **401 Unauthenticated** without a valid token and **403** when your role may not perform the action. Validation errors answer **422**.',
                ]),
            ],
            'servers' => [['url' => url('/api/v1'), 'description' => 'This server']],
            'tags' => [
                ['name' => 'Authentication', 'description' => 'Get and revoke tokens'],
                ['name' => 'Books', 'description' => 'Book listings. Reading is public; changes need a seller or admin token.'],
                ['name' => 'Categories', 'description' => 'Reading is public; changes need an admin token.'],
                ['name' => 'Orders', 'description' => 'All order endpoints need a token.'],
            ],
            'components' => [
                'securitySchemes' => [
                    'bearerAuth' => ['type' => 'http', 'scheme' => 'bearer', 'description' => 'Sanctum personal access token, e.g. `1|abc...`'],
                ],
                'schemas' => self::schemas(),
                'responses' => self::responses(),
                'parameters' => [
                    'BookId' => ['name' => 'book', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer', 'example' => 1], 'description' => 'Book ID'],
                    'CategoryId' => ['name' => 'category', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer', 'example' => 1], 'description' => 'Category ID'],
                    'OrderId' => ['name' => 'order', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer', 'example' => 1], 'description' => 'Order ID'],
                    'PerPage' => ['name' => 'per_page', 'in' => 'query', 'schema' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 50, 'default' => 15]],
                ],
            ],
            'paths' => self::paths(),
        ];
    }

    private static function paths(): array
    {
        $auth = [['bearerAuth' => []]];
        $ref = fn (string $name) => ['$ref' => "#/components/schemas/{$name}"];
        $json = fn (array $schema, ?array $example = null) => ['application/json' => array_filter(['schema' => $schema, 'example' => $example])];
        $body = fn (string $schema, array $example) => ['required' => true, 'content' => $json($ref($schema), $example)];
        $one = fn (string $schema, string $description = 'OK') => ['description' => $description, 'content' => $json(['type' => 'object', 'properties' => ['data' => $ref($schema)]])];
        $page = fn (string $schema) => ['description' => 'OK (paginated)', 'content' => $json(['type' => 'object', 'properties' => [
            'data' => ['type' => 'array', 'items' => $ref($schema)],
            'links' => $ref('PaginationLinks'),
            'meta' => $ref('PaginationMeta'),
        ]])];
        $err = fn (string $name) => ['$ref' => "#/components/responses/{$name}"];
        $noContent = ['description' => 'Deleted (no content)'];

        return [
            '/auth/token' => [
                'post' => [
                    'tags' => ['Authentication'], 'summary' => 'Get a token', 'operationId' => 'createToken',
                    'description' => 'Exchange email and password for a bearer token. Accounts with two-factor authentication must create tokens on the website instead.',
                    'requestBody' => $body('TokenRequest', ['email' => 'snoppy@example.com', 'password' => 'password', 'device_name' => 'swagger']),
                    'responses' => [
                        '201' => ['description' => 'Token created', 'content' => $json($ref('TokenResponse'))],
                        '403' => ['description' => 'Email not verified, or the account uses two-factor authentication', 'content' => $json($ref('Message'))],
                        '422' => $err('ValidationError'),
                        '429' => ['description' => 'Too many attempts'],
                    ],
                ],
            ],
            '/auth/me' => [
                'get' => [
                    'tags' => ['Authentication'], 'summary' => 'Current account', 'operationId' => 'me', 'security' => $auth,
                    'responses' => ['200' => $one('User'), '401' => $err('Unauthenticated')],
                ],
            ],
            '/auth/logout' => [
                'post' => [
                    'tags' => ['Authentication'], 'summary' => 'Revoke the current token', 'operationId' => 'logout', 'security' => $auth,
                    'responses' => ['200' => ['description' => 'Token revoked', 'content' => $json($ref('Message'), ['message' => 'Token revoked.'])], '401' => $err('Unauthenticated')],
                ],
            ],
            '/books' => [
                'get' => [
                    'tags' => ['Books'], 'summary' => 'List books', 'operationId' => 'listBooks',
                    'description' => 'Public. Only active listings from approved sellers.',
                    'parameters' => [
                        ['name' => 'search', 'in' => 'query', 'schema' => ['type' => 'string'], 'description' => 'Matches title, author or ISBN'],
                        ['name' => 'category_id', 'in' => 'query', 'schema' => ['type' => 'integer']],
                        ['name' => 'seller_id', 'in' => 'query', 'schema' => ['type' => 'integer']],
                        ['name' => 'in_stock', 'in' => 'query', 'schema' => ['type' => 'boolean']],
                        ['$ref' => '#/components/parameters/PerPage'],
                    ],
                    'responses' => ['200' => $page('Book'), '422' => $err('ValidationError')],
                ],
                'post' => [
                    'tags' => ['Books'], 'summary' => 'Create a listing', 'operationId' => 'createBook', 'security' => $auth,
                    'description' => 'Approved sellers and admins. A seller owns the listings they create.',
                    'requestBody' => $body('BookInput', ['category_id' => 1, 'title' => 'Noli Me Tangere', 'author' => 'Jose Rizal', 'isbn' => '9780140449136', 'price' => 350, 'stock_quantity' => 5, 'description' => 'Good condition.']),
                    'responses' => ['201' => $one('Book', 'Created'), '401' => $err('Unauthenticated'), '403' => $err('Forbidden'), '422' => $err('ValidationError')],
                ],
            ],
            '/books/{book}' => [
                'parameters' => [['$ref' => '#/components/parameters/BookId']],
                'get' => [
                    'tags' => ['Books'], 'summary' => 'Get a book', 'operationId' => 'getBook',
                    'responses' => ['200' => $one('Book'), '404' => $err('NotFound')],
                ],
                'put' => [
                    'tags' => ['Books'], 'summary' => 'Update a listing', 'operationId' => 'replaceBook', 'security' => $auth,
                    'description' => 'The listing\'s seller or an admin. Send only the fields to change (same as PATCH).',
                    'requestBody' => $body('BookUpdate', ['price' => 320, 'stock_quantity' => 4]),
                    'responses' => ['200' => $one('Book'), '401' => $err('Unauthenticated'), '403' => $err('Forbidden'), '404' => $err('NotFound'), '422' => $err('ValidationError')],
                ],
                'patch' => [
                    'tags' => ['Books'], 'summary' => 'Update part of a listing', 'operationId' => 'updateBook', 'security' => $auth,
                    'description' => 'The listing\'s seller or an admin. Send only the fields to change.',
                    'requestBody' => $body('BookUpdate', ['price' => 320, 'stock_quantity' => 4]),
                    'responses' => ['200' => $one('Book'), '401' => $err('Unauthenticated'), '403' => $err('Forbidden'), '404' => $err('NotFound'), '422' => $err('ValidationError')],
                ],
                'delete' => [
                    'tags' => ['Books'], 'summary' => 'Delete a listing', 'operationId' => 'deleteBook', 'security' => $auth,
                    'responses' => ['204' => $noContent, '401' => $err('Unauthenticated'), '403' => $err('Forbidden'), '404' => $err('NotFound')],
                ],
            ],
            '/categories' => [
                'get' => [
                    'tags' => ['Categories'], 'summary' => 'List categories', 'operationId' => 'listCategories',
                    'responses' => ['200' => ['description' => 'OK', 'content' => $json(['type' => 'object', 'properties' => ['data' => ['type' => 'array', 'items' => $ref('Category')]]])]],
                ],
                'post' => [
                    'tags' => ['Categories'], 'summary' => 'Create a category', 'operationId' => 'createCategory', 'security' => $auth,
                    'description' => 'Admins only.',
                    'requestBody' => $body('CategoryInput', ['name' => 'Filipiniana', 'description' => 'Books by Filipino authors']),
                    'responses' => ['201' => $one('Category', 'Created'), '401' => $err('Unauthenticated'), '403' => $err('Forbidden'), '422' => $err('ValidationError')],
                ],
            ],
            '/categories/{category}' => [
                'parameters' => [['$ref' => '#/components/parameters/CategoryId']],
                'get' => [
                    'tags' => ['Categories'], 'summary' => 'Get a category', 'operationId' => 'getCategory',
                    'responses' => ['200' => $one('Category'), '404' => $err('NotFound')],
                ],
                'put' => [
                    'tags' => ['Categories'], 'summary' => 'Update a category', 'operationId' => 'replaceCategory', 'security' => $auth,
                    'description' => 'Admins only.',
                    'requestBody' => $body('CategoryInput', ['description' => 'Updated description']),
                    'responses' => ['200' => $one('Category'), '401' => $err('Unauthenticated'), '403' => $err('Forbidden'), '404' => $err('NotFound'), '422' => $err('ValidationError')],
                ],
                'patch' => [
                    'tags' => ['Categories'], 'summary' => 'Update part of a category', 'operationId' => 'updateCategory', 'security' => $auth,
                    'description' => 'Admins only.',
                    'requestBody' => $body('CategoryInput', ['description' => 'Updated description']),
                    'responses' => ['200' => $one('Category'), '401' => $err('Unauthenticated'), '403' => $err('Forbidden'), '404' => $err('NotFound'), '422' => $err('ValidationError')],
                ],
                'delete' => [
                    'tags' => ['Categories'], 'summary' => 'Delete a category', 'operationId' => 'deleteCategory', 'security' => $auth,
                    'description' => 'Admins only. Also deletes the books in the category.',
                    'responses' => ['204' => $noContent, '401' => $err('Unauthenticated'), '403' => $err('Forbidden'), '404' => $err('NotFound')],
                ],
            ],
            '/orders' => [
                'get' => [
                    'tags' => ['Orders'], 'summary' => 'List orders', 'operationId' => 'listOrders', 'security' => $auth,
                    'description' => 'Buyers see their own orders, sellers the orders they fulfil, admins all orders.',
                    'parameters' => [
                        ['name' => 'status', 'in' => 'query', 'schema' => ['type' => 'string', 'enum' => Order::STATUSES]],
                        ['$ref' => '#/components/parameters/PerPage'],
                    ],
                    'responses' => ['200' => $page('Order'), '401' => $err('Unauthenticated'), '422' => $err('ValidationError')],
                ],
                'post' => [
                    'tags' => ['Orders'], 'summary' => 'Place an order', 'operationId' => 'createOrder', 'security' => $auth,
                    'description' => 'Buyers only. Items from several sellers become one order per seller, linked by `checkout_reference`.',
                    'requestBody' => $body('OrderInput', ['items' => [['book_id' => 1, 'quantity' => 1]], 'shipping_address' => '123 Katipunan Ave, Quezon City', 'payment_method' => 'cash_on_delivery']),
                    'responses' => [
                        '201' => ['description' => 'Created: one order per seller. With `paymongo`, send the buyer to `payment.checkout_url`.', 'content' => $json(['type' => 'object', 'properties' => [
                            'data' => ['type' => 'array', 'items' => $ref('Order')],
                            'payment' => ['type' => 'object', 'description' => 'Only for payment_method "paymongo"', 'properties' => [
                                'checkout_reference' => ['type' => 'string'],
                                'status' => ['type' => 'string', 'example' => 'pending'],
                                'amount' => ['type' => 'number'],
                                'checkout_url' => ['type' => 'string', 'format' => 'uri', 'description' => 'PayMongo hosted payment page'],
                            ]],
                        ]])],
                        '502' => ['description' => 'PayMongo could not start the payment (the orders are cancelled)', 'content' => $json($ref('Message'))],
                        '401' => $err('Unauthenticated'),
                        '422' => ['description' => 'Validation error, or a book is out of stock or unavailable', 'content' => $json($ref('ValidationError'))],
                    ],
                ],
            ],
            '/orders/{order}' => [
                'parameters' => [['$ref' => '#/components/parameters/OrderId']],
                'get' => [
                    'tags' => ['Orders'], 'summary' => 'Get an order', 'operationId' => 'getOrder', 'security' => $auth,
                    'description' => 'The buyer, the order\'s seller, or an admin.',
                    'responses' => ['200' => $one('Order'), '401' => $err('Unauthenticated'), '403' => $err('Forbidden'), '404' => $err('NotFound')],
                ],
            ],
            '/orders/{order}/status' => [
                'parameters' => [['$ref' => '#/components/parameters/OrderId']],
                'patch' => [
                    'tags' => ['Orders'], 'summary' => 'Change order status', 'operationId' => 'updateOrderStatus', 'security' => $auth,
                    'description' => 'The order\'s seller or an admin. The buyer is notified by email. An unpaid PayMongo order cannot move to processing, shipped or completed (422). Completing a cash-on-delivery order marks it paid.',
                    'requestBody' => $body('OrderStatusInput', ['status' => 'processing']),
                    'responses' => ['200' => $one('Order'), '401' => $err('Unauthenticated'), '403' => $err('Forbidden'), '404' => $err('NotFound'), '422' => $err('ValidationError')],
                ],
            ],
        ];
    }

    private static function schemas(): array
    {
        $ref = fn (string $name) => ['$ref' => "#/components/schemas/{$name}"];

        return [
            'TokenRequest' => [
                'type' => 'object', 'required' => ['email', 'password'],
                'properties' => [
                    'email' => ['type' => 'string', 'format' => 'email'],
                    'password' => ['type' => 'string', 'format' => 'password'],
                    'device_name' => ['type' => 'string', 'maxLength' => 100, 'description' => 'A name to recognise the token by'],
                ],
            ],
            'TokenResponse' => [
                'type' => 'object',
                'properties' => [
                    'token' => ['type' => 'string', 'example' => '1|aBcDeFgHiJkLmNoPqRsTuVwXyZ'],
                    'token_type' => ['type' => 'string', 'example' => 'Bearer'],
                    'user' => $ref('User'),
                ],
            ],
            'User' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer'],
                    'name' => ['type' => 'string'],
                    'email' => ['type' => 'string', 'format' => 'email'],
                    'role' => ['type' => 'string', 'enum' => ['Admin', 'Seller', 'Buyer']],
                    'store_name' => ['type' => 'string', 'nullable' => true],
                    'seller_status' => ['type' => 'string', 'nullable' => true, 'enum' => ['pending', 'approved', 'suspended', null]],
                ],
            ],
            'Book' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer'],
                    'isbn' => ['type' => 'string'],
                    'title' => ['type' => 'string'],
                    'author' => ['type' => 'string'],
                    'publisher' => ['type' => 'string', 'nullable' => true],
                    'description' => ['type' => 'string', 'nullable' => true],
                    'price' => ['type' => 'number', 'format' => 'float'],
                    'stock_quantity' => ['type' => 'integer'],
                    'in_stock' => ['type' => 'boolean'],
                    'format' => ['type' => 'string', 'nullable' => true],
                    'is_active' => ['type' => 'boolean'],
                    'cover_image_url' => ['type' => 'string', 'format' => 'uri', 'nullable' => true],
                    'average_rating' => ['type' => 'number'],
                    'reviews_count' => ['type' => 'integer'],
                    'category' => ['type' => 'object', 'properties' => ['id' => ['type' => 'integer'], 'name' => ['type' => 'string']]],
                    'seller' => ['type' => 'object', 'nullable' => true, 'properties' => ['id' => ['type' => 'integer'], 'store_name' => ['type' => 'string']]],
                    'created_at' => ['type' => 'string', 'format' => 'date-time'],
                    'updated_at' => ['type' => 'string', 'format' => 'date-time'],
                ],
            ],
            'BookInput' => [
                'type' => 'object', 'required' => ['category_id', 'title', 'author', 'isbn', 'price', 'stock_quantity'],
                'properties' => self::bookFields(),
            ],
            'BookUpdate' => [
                'type' => 'object', 'description' => 'Any subset of the listing fields.',
                'properties' => self::bookFields(),
            ],
            'Category' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer'],
                    'name' => ['type' => 'string'],
                    'description' => ['type' => 'string', 'nullable' => true],
                    'books_count' => ['type' => 'integer', 'description' => 'Visible listings in this category'],
                    'created_at' => ['type' => 'string', 'format' => 'date-time'],
                    'updated_at' => ['type' => 'string', 'format' => 'date-time'],
                ],
            ],
            'CategoryInput' => [
                'type' => 'object',
                'properties' => [
                    'name' => ['type' => 'string', 'maxLength' => 255, 'description' => 'Required when creating; must be unique'],
                    'description' => ['type' => 'string', 'nullable' => true],
                ],
            ],
            'Order' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer'],
                    'checkout_reference' => ['type' => 'string', 'nullable' => true, 'description' => 'Shared by the orders created in one checkout'],
                    'status' => ['type' => 'string', 'enum' => Order::STATUSES],
                    'total_amount' => ['type' => 'number', 'format' => 'float'],
                    'payment_method' => ['type' => 'string', 'nullable' => true, 'enum' => [...Order::PAYMENT_METHODS, null]],
                    'payment_status' => ['type' => 'string', 'enum' => Order::PAYMENT_STATUSES, 'description' => 'Set by the PayMongo webhook for online orders'],
                    'paid_at' => ['type' => 'string', 'format' => 'date-time', 'nullable' => true],
                    'shipping_address' => ['type' => 'string', 'nullable' => true],
                    'notes' => ['type' => 'string', 'nullable' => true],
                    'buyer' => ['type' => 'object', 'properties' => ['id' => ['type' => 'integer'], 'name' => ['type' => 'string']]],
                    'seller' => ['type' => 'object', 'nullable' => true, 'properties' => ['id' => ['type' => 'integer'], 'store_name' => ['type' => 'string']]],
                    'items' => ['type' => 'array', 'items' => [
                        'type' => 'object',
                        'properties' => [
                            'book_id' => ['type' => 'integer'],
                            'title' => ['type' => 'string'],
                            'quantity' => ['type' => 'integer'],
                            'unit_price' => ['type' => 'number'],
                            'subtotal' => ['type' => 'number'],
                        ],
                    ]],
                    'created_at' => ['type' => 'string', 'format' => 'date-time'],
                    'updated_at' => ['type' => 'string', 'format' => 'date-time'],
                ],
            ],
            'OrderInput' => [
                'type' => 'object', 'required' => ['items', 'shipping_address', 'payment_method'],
                'properties' => [
                    'items' => ['type' => 'array', 'minItems' => 1, 'maxItems' => 50, 'items' => [
                        'type' => 'object', 'required' => ['book_id', 'quantity'],
                        'properties' => [
                            'book_id' => ['type' => 'integer'],
                            'quantity' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 100],
                        ],
                    ]],
                    'shipping_address' => ['type' => 'string', 'minLength' => 10, 'maxLength' => 255],
                    'payment_method' => ['type' => 'string', 'enum' => Order::PAYMENT_METHODS, 'description' => '"paymongo" = pay online (GCash, Maya, card, ...); the response gives the payment page URL'],
                ],
            ],
            'OrderStatusInput' => [
                'type' => 'object', 'required' => ['status'],
                'properties' => [
                    'status' => ['type' => 'string', 'enum' => Order::STATUSES],
                    'notes' => ['type' => 'string', 'maxLength' => 500, 'nullable' => true],
                ],
            ],
            'Message' => ['type' => 'object', 'properties' => ['message' => ['type' => 'string']]],
            'ValidationError' => [
                'type' => 'object',
                'properties' => [
                    'message' => ['type' => 'string', 'example' => 'The title field is required.'],
                    'errors' => ['type' => 'object', 'additionalProperties' => ['type' => 'array', 'items' => ['type' => 'string']], 'example' => ['title' => ['The title field is required.']]],
                ],
            ],
            'PaginationLinks' => [
                'type' => 'object',
                'properties' => [
                    'first' => ['type' => 'string', 'nullable' => true],
                    'last' => ['type' => 'string', 'nullable' => true],
                    'prev' => ['type' => 'string', 'nullable' => true],
                    'next' => ['type' => 'string', 'nullable' => true],
                ],
            ],
            'PaginationMeta' => [
                'type' => 'object',
                'properties' => [
                    'current_page' => ['type' => 'integer'],
                    'last_page' => ['type' => 'integer'],
                    'per_page' => ['type' => 'integer'],
                    'total' => ['type' => 'integer'],
                ],
            ],
        ];
    }

    private static function bookFields(): array
    {
        return [
            'category_id' => ['type' => 'integer'],
            'title' => ['type' => 'string', 'maxLength' => 255],
            'author' => ['type' => 'string', 'maxLength' => 255],
            'isbn' => ['type' => 'string', 'maxLength' => 20, 'description' => 'Must be unique'],
            'price' => ['type' => 'number', 'minimum' => 0],
            'stock_quantity' => ['type' => 'integer', 'minimum' => 0],
            'description' => ['type' => 'string', 'nullable' => true],
            'publisher' => ['type' => 'string', 'nullable' => true],
            'format' => ['type' => 'string', 'nullable' => true],
            'is_active' => ['type' => 'boolean'],
        ];
    }

    private static function responses(): array
    {
        $message = fn (string $description, string $example) => [
            'description' => $description,
            'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/Message'], 'example' => ['message' => $example]]],
        ];

        return [
            'Unauthenticated' => $message('No token, or the token is invalid or revoked', 'Unauthenticated.'),
            'Forbidden' => $message('Your role may not do this', 'This action is unauthorized.'),
            'NotFound' => $message('Not found', 'Not found.'),
            'ValidationError' => ['description' => 'Validation error', 'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/ValidationError']]]],
        ];
    }
}
