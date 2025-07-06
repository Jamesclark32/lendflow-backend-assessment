<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\v1\Products;

use App\Actions\Products\IndexAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\Product\IndexRequest;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

class IndexController extends Controller
{
    public function __invoke(IndexRequest $request, IndexAction $action): JsonResponse
    {
        $this->authorize('viewAny', Product::class);

        return response()->json([
            'products' => $action->getProducts(
                searchTerm: $request->validated('search', ''),
                filters: collect($request->validated())
            ),
        ])
            ->header('x-api-version', '1');
    }
}
