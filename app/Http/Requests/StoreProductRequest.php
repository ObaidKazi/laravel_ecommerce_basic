<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
class StoreProductRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255|unique:products,name',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'images' => 'nullable|array',
            'images.*' => 'required|string',
            'stock_quantity' => 'required|integer|min:0',
            'categories' => 'sometimes|array',
            'categories.*' => 'exists:categories,id'
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->has('images')) {
                foreach ($this->images as $index => $image) {
                    if (!$this->isValidBase64Image($image)) {
                        $validator->errors()->add("images.{$index}", 'The image must be a valid base64 encoded image (jpeg, png, jpg, gif).');
                    }
                }
            }
        });
    }

    protected function isValidBase64Image($base64String)
    {
        // Check if the string starts with data:image format
        if (!preg_match('/^data:image\/(jpeg|png|jpg|gif);base64,/', $base64String)) {
            return false;
        }

        // Extract the base64 encoded data
        $base64Data = substr($base64String, strpos($base64String, ',') + 1);
        
        // Check if it's valid base64
        if (!base64_decode($base64Data, true)) {
            return false;
        }
        
        return true;
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success'   => false,
            'message'   => 'Validation errors',
            'data'      => $validator->errors()
        ], 422));
    }
}