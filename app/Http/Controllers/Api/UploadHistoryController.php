<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Upload;
use Illuminate\Http\Request;

class UploadHistoryController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'filename' => 'sometimes|string|max:255',
            'date' => 'sometimes|date_format:Y-m-d',
        ]);

        $query = Upload::query();

        $query->when($request->has('filename'), function ($q) use ($request) {
            return $q->where('original_filename', 'like', '%' . $request->input('filename') . '%');
        });

        $query->when($request->has('date'), function ($q) use ($request) {
            return $q->whereDate('created_at', $request->input('date'));
        });

        $history = $query->latest()->paginate(15);

        return response()->json($history);
    }
}
