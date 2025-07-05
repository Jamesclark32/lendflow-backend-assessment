<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Categories;

use App\Actions\Categories\IndexAction;
use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IndexController extends Controller
{
    public function __invoke(Request $request, IndexAction $action): JsonResponse
    {
        $this->authorize('viewAny', Category::class);

        return response()->json([
            'categories' => $action->getCategories(),
        ]);
    }
}
