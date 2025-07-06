<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\v2\Product;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Validator;

class IndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->can('viewAny', Product::class);
    }

    public function rules(): array
    {
        return [
            'search' => 'nullable|string',
            'categories' => 'required|array|exists:categories,slug',
            'color' => 'nullable|string',
            'price' => 'nullable|integer|min:0|max:99999',
            'on_sale' => 'nullable|in:1,true,0,false',
        ];
    }

    public function messages(): array
    {
        return [
            'categories.exists' => 'categories must be slug values from the categories endpoint.',
        ];
    }

    public function failedValidation(Validator|\Illuminate\Contracts\Validation\Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'errors' => $validator->errors(),
            ], 422)
        );
    }
}
