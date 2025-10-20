<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;

trait UploadImageTrait
{
    public function uploadImage($image, $path)
    {
        $name = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
        $imageData = $image->storeAs($path, $name, 'public');
        return $imageData;
    }

    public function uploadImageWithName($image, $path, $name = '')
    {
        if (empty($name)) {
            $name = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
        } else {
            $name = $name . '.' . $image->getClientOriginalExtension();
        }
        $imageData = $image->storeAs($path, $name, 'public');
        return $imageData;
    }

    public function uploadImageWithBase64($base64Image, $path, $name = '')
    {
        if (empty($name)) {
            $name = time() . '_' . uniqid();
        }
        $imageData = explode(',', $base64Image);
        if (count($imageData) == 2) {
            $ext = explode('/', $imageData[0])[1];
            if ($ext == 'png') {
                $name = $name . '.png';
            } else if ($ext == 'jpg') {
                $name = $name . '.jpg';
            } else if ($ext == 'jpeg') {
                $name = $name . '.jpeg';
            } else if ($ext == 'gif') {
                $name = $name . '.gif';
            } else if ($ext == 'webp') {
                $name = $name . '.webp';
            } else {
                $name = $name . '.png';
            }
            $decodedImage = base64_decode($imageData[1]);
            Storage::put('public/' . $path . '/' . $name, $decodedImage);
            $imageData = $path . '/' . $name;
        }
        return $imageData;
    }
}
