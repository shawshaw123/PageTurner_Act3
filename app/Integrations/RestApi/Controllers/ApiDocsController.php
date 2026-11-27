<?php

namespace App\Integrations\RestApi\Controllers;

use App\Http\Controllers\Controller;
use App\Integrations\RestApi\OpenApiSpec;
use Illuminate\Http\JsonResponse;

/** Admin-only Swagger UI for the REST API. */
class ApiDocsController extends Controller
{
    /** GET /admin/api-docs — Swagger UI. */
    public function index()
    {
        return view('admin.api-docs');
    }

    /** GET /admin/api-docs/openapi.json — the OpenAPI 3 specification Swagger UI reads. */
    public function spec(): JsonResponse
    {
        return response()->json(OpenApiSpec::build(), 200, [], JSON_UNESCAPED_SLASHES);
    }
}
