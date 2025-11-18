<?php

namespace App\Http\Controllers;

use Illuminate\Http\UploadedFile;

abstract class Controller
{
        /**
     * Create a temp file using the uploaded file's contents and extension.
     *
     * @param  \Illuminate\Http\UploadedFile  $file
     * @return string  Full path to the created temp file
     */
    protected function createTempUploadFile(UploadedFile $file): string
    {
        $ext = $file->getClientOriginalExtension() ?: pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION);
        $target = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'upload_' . uniqid('', true) . ($ext ? '.' . $ext : '');
        // Use copy instead of file_get_contents for memory efficiency
        $source = $file->getRealPath() ?: $file->getPathname();
        if ($source === null) {
            // Fallback: move uploaded file to temp (this should rarely be needed)
            $file->move(dirname($target), basename($target));
        } else {
            // copy the uploaded temp file to our new path
            if (! @copy($source, $target)) {
                throw new \RuntimeException("Failed to create temp file: {$target}");
            }
        }

        return $target;
    }

    /**
     * Delete a temp file if it exists. Silent on failure.
     *
     * @param  string  $path
     * @return void
     */
    protected function cleanupTempFile(string $path): void
    {
        if ($path && file_exists($path)) {
            @unlink($path);
        }
    }
}
