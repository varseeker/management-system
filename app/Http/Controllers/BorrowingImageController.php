<?php

namespace App\Http\Controllers;

use App\Support\BorrowingImageStorage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BorrowingImageController extends Controller
{
    public function show(string $path): BinaryFileResponse
    {
        $path = str_replace(['..', '\\'], ['', '/'], $path);
        $absolute = BorrowingImageStorage::absolutePath($path);

        abort_unless($absolute && is_file($absolute), 404);

        $mime = mime_content_type($absolute) ?: 'application/octet-stream';

        return response()->file($absolute, [
            'Content-Type' => $mime,
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ]);
    }
}
