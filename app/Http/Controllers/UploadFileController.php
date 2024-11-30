<?php

namespace App\Http\Controllers;

use App\Http\Requests\UploadRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;


class UploadFileController extends Controller
{

    public function upload(UploadRequest $request): JsonResponse
    {
        $file = $request->file('file');
        $fileName = $request->input('fileName');
        $chunk = $request->input('chunk');
        $totalChunks = $request->input('totalChunks');

        $tempFileName = 'chunk-' . $fileName;
        $chunkFolder = 'uploads/chunks/' . $tempFileName;
        $chunkPath = $chunkFolder . '/chunk-' . $chunk;
        Storage::disk('local')->put($chunkPath, file_get_contents($file));

        if ($chunk + 1 === (int) $totalChunks) {
            $this->mergeChunks($chunkFolder, $fileName, $totalChunks);
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * merge Chunks.
     *
     * @param string $chunkFolder
     * @param string $fileName
     * @param int $totalChunks
     */
    private function mergeChunks(string $chunkFolder, string $fileName, int $totalChunks): void
    {
        $finalFilePath = 'uploads/' . $fileName;
        $finalFile = fopen(Storage::disk('local')->path($finalFilePath), 'w');

        for ($i = 0; $i < $totalChunks; $i++) {
            $chunkPath = $chunkFolder . '/chunk-' . $i;
            $chunkData = Storage::disk('local')->get($chunkPath);
            fwrite($finalFile, $chunkData);

            Storage::disk('local')->delete($chunkPath);
        }
        fclose($finalFile);

        Storage::disk('local')->deleteDirectory($chunkFolder);
    }
}
