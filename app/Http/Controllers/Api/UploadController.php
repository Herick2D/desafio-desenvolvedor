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

        $filePath = $file->store('uploads');

        $upload = Upload::create([
            'original_filename' => $file->getClientOriginalName(),
            'file_path' => $filePath,
            'filesize' => $file->getSize(),
            'status' => 'pending',
        ]);

        ProcessFileJob::dispatch($upload);

        return response()->json([
            'message' => 'Arquivo recebido e agendado para processamento.',
            'upload_id' => $upload->id
        ], 202);
    }
}
