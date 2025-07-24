<?php

namespace App\Jobs;

use App\Imports\InstrumentDataImport;
use App\Models\Upload;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class ProcessFileJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Upload $upload)
    {
    }

    public function handle(): void
    {

        dd('JOB: ENTREI NO JOB');

        $this->upload->update(['status' => 'processing']);

        try {
            $filePathOnDisk = storage_path('app/' . $this->upload->file_path);
            $fileHash = hash_file('sha256', $filePathOnDisk);

            $existingUpload = Upload::where('file_hash', $fileHash)->where('id', '!=', $this->upload->id)->first();

            if ($existingUpload) {
                $this->upload->update([
                    'status' => 'duplicate',
                    'file_hash' => $fileHash,
                    'error_message' => 'Arquivo duplicado. O original foi o upload ID: ' . $existingUpload->id
                ]);
                Storage::delete($this->upload->file_path);
                Log::warning('Upload duplicado detectado.', ['upload_id' => $this->upload->id, 'original_id' => $existingUpload->id]);
                return;
            }

            $this->upload->update(['file_hash' => $fileHash]);

            Excel::import(
                new InstrumentDataImport($this->upload->id),
                $this->upload->file_path
            );

            $totalRows = \App\Models\InstrumentData::where('upload_id', $this->upload->id)->count();

            $this->upload->update([
                'status' => 'completed',
                'total_rows' => $totalRows,
            ]);

            Log::info("Arquivo processado com sucesso.", ['upload_id' => $this->upload->id]);

        } catch (Throwable $e) {
            $this->upload->update([
                'status' => 'failed',
                'error_message' => $e->getMessage() . ' no arquivo ' . $e->getFile() . ' na linha ' . $e->getLine()
            ]);
            Log::error("Falha ao processar arquivo.", ['upload_id' => $this->upload->id, 'exception' => $e]);
        }
    }
}
