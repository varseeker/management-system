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

        abort_unless($absolute !== null && is_file($absolute), 404);

        return response()->file($absolute, [
            'Content-Type' => MenuImageStorage::mimeType($path),
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ]);
    }
}
