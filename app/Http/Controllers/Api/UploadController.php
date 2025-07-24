<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessFileJob;
use App\Models\Upload;
use Illuminate\Http\Request;

class UploadController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt,xlsx,xls', 'max:102400'],
        ]);

        $file = $request->file('file');

        $fileHash = hash_file('sha256', $file->getRealPath());

        $existingUpload = Upload::where('file_hash', $fileHash)->where('status', 'completed')->first();

        if ($existingUpload) {
            return response()->json([
                'message' => 'Este arquivo já foi enviado e processado anteriormente.',
                'upload' => $existingUpload
            ], 409);
        }

        $filePath = $file->store('uploads');

        $upload = Upload::create([
            'original_filename' => $file->getClientOriginalName(),
            'file_path' => $filePath,
            'filesize' => $file->getSize(),
            'status' => 'pending',
            'file_hash' => $fileHash,
        ]);

        ProcessFileJob::dispatch($upload);

        return response()->json([
            'message' => 'Arquivo recebido e agendado para processamento.',
            'upload_id' => $upload->id
        ], 202);
    }
}
