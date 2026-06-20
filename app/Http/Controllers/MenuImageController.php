<?php

namespace App\Http\Controllers;

use App\Support\MenuImageStorage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class MenuImageController extends Controller
{
    public function show(string $path): BinaryFileResponse|Response
    {
        $path = MenuImageStorage::normalizePath(str_replace(['..', '\\'], ['', '/'], $path));

        abort_unless(MenuImageStorage::exists($path), 404);

        $absolute = MenuImageStorage::absolutePath($path);

        if ($absolute !== null && is_file($absolute)) {
            return response()->file($absolute, [
                'Content-Type' => MenuImageStorage::mimeType($path),
                'Cache-Control' => 'public, max-age=31536000, immutable',
            ]);
        }

        $contents = MenuImageStorage::contents($path);

        abort_unless($contents, 404);

        return response($contents, 200, [
            'Content-Type' => MenuImageStorage::mimeType($path),
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ]);
    }
}
