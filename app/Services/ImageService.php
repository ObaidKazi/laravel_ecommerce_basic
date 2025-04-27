<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageService
{
    public function saveBase64Image(string $base64Image, string $directory = 'products'): string
    {
        // Extract the base64 content
        $image_parts = explode(";base64,", $base64Image);
        $image_type_aux = explode("image/", $image_parts[0]);
        $image_type = $image_type_aux[1] ?? 'jpeg';
        $image_base64 = isset($image_parts[1]) ? $image_parts[1] : $base64Image;

        // Generate a unique filename
        $filename = Str::uuid() . '.' . $image_type;
        
        // Save the image
        $path = $directory . '/' . $filename;
        Storage::disk('public')->put($path, base64_decode($image_base64));
        
        return $path;
    }

    public function deleteImage(string $path): bool
    {
        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->delete($path);
        }
        
        return false;
    }
}