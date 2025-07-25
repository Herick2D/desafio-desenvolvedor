<?php

namespace App\Jobs;

use App\Models\Upload;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ProcessFileJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Upload $upload)
    {
    }

    public function handle(): void
    {
        if ($this->upload->status !== 'pending') {
            return;
        }

        $this->upload->update(['status' => 'processing']);

        try {
            $filePathOnDisk = Storage::path($this->upload->file_path);
            if (!Storage::exists($this->upload->file_path)) {
                throw new \Exception('Arquivo não encontrado no disco: ' . $this->upload->file_path);
            }

            $fileHandle = fopen($filePathOnDisk, 'r');
            if ($fileHandle === false) {
                throw new \Exception('Não foi possível abrir o arquivo para leitura.');
            }

            $header = fgetcsv($fileHandle, 0, ';');
            $dataToInsert = [];
            $totalRows = 0;
            $chunkSize = 1000;

            while (($row = fgetcsv($fileHandle, 0, ';')) !== false) {
                if ($row[0] === 'TRAILER' || empty($row[1])) {
                    continue;
                }

                $dataToInsert[] = [
                    'upload_id'  => $this->upload->id,
                    'RptDt'      => date('Y-m-d', strtotime($row[0])),
                    'TckrSymb'   => $row[1],
                    'MktNm'      => $row[11] ?? null,
                    'SctyCtgyNm' => $row[14] ?? null,
                    'ISIN'       => $row[2] ?? null,
                    'CrpnNm'     => $row[12] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                if (count($dataToInsert) >= $chunkSize) {
                    \App\Models\InstrumentData::insert($dataToInsert);
                    $totalRows += count($dataToInsert);
                    $dataToInsert = [];
                }
            }

            if (!empty($dataToInsert)) {
                \App\Models\InstrumentData::insert($dataToInsert);
                $totalRows += count($dataToInsert);
            }

            fclose($fileHandle);

            $this->upload->update([
                'status' => 'completed',
                'total_rows' => $totalRows,
            ]);

        } catch (Throwable $e) {
            $this->upload->update(['status' => 'failed', 'error_message' => $e->getMessage()]);
        }
    }
}
