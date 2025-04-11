<?php

namespace App\Service;

use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Tinify\Source;
use function Tinify\setKey;

class IndexService
{
    public function imagesCompress( $request)
    {
        $data = $request->validated();
        if ($request->hasFile('photo')) {
            $photo = $request->file('photo');
            $filename = Str::random(15) . '.jpg';

            $image = Image::make($photo->getPathname())
                ->fit(70, 70, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                })
                ->encode('jpg', 90);

            $tempPath = storage_path('app/public/images/temp/' . $filename);
            $image->save($tempPath);

            setKey('1BC4cXMKstgLxcKJbWV6qnkfkzTzJ1VK');
            $source = Source::fromFile($tempPath);
            $optimizedPath = 'images/users/' . $filename;

            $finalStoragePath = storage_path('app/public/' . $optimizedPath);
            $source->toFile($finalStoragePath);
            unlink($tempPath);

            $publicStoragePath = public_path('storage/' . $optimizedPath);
            $publicDir = dirname($publicStoragePath);
            if (!file_exists($publicDir)) {
                mkdir($publicDir, 0755, true);
            }
            copy($finalStoragePath, $publicStoragePath);
            $data['photo'] = $optimizedPath;
        }else {
            $data['photo'] = $request->photo;
       }
       return $data;
    }
}
