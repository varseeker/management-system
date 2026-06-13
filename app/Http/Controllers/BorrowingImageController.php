<?php

namespace App\Http\Controllers;

use App\Support\BorrowingImageStorage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class BorrowingImageController extends Controller
{
    public function show(string $path): BinaryFileResponse|Response
    {
        $path = str_replace(['..', '\\'], ['', '/'], $path);

        abort_unless(BorrowingImageStorage::exists($path), 404);

        $absolute = BorrowingImageStorage::absolutePath($path);

        if ($absolute !== null && is_file($absolute)) {
            return response()->file($absolute, [
                'Content-Type' => BorrowingImageStorage::mimeType($path),
                'Cache-Control' => 'public, max-age=31536000, immutable',
            ]);
        }

        $contents = BorrowingImageStorage::contents($path);

        abort_unless($contents, 404);

        return response($contents, 200, [
            'Content-Type' => BorrowingImageStorage::mimeType($path),
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ]);
    }
}
