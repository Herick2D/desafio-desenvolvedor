<?php

namespace App\Imports;

use App\Models\InstrumentData;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class InstrumentDataImport implements ToCollection, WithChunkReading, ShouldQueue, WithHeadingRow
{
    public function __construct(private int $uploadId)
    {
    }

    public function collection(Collection $rows)
    {
        $dataToInsert = [];
        $reportDate = now()->parse($rows[0]['rptdt'])->toDateString();

        foreach ($rows as $row) {
            if (isset($row['tckrsymb']) && !empty($row['tckrsymb'])) {
                $dataToInsert[] = [
                    'upload_id'  => $this->uploadId,
                    'RptDt'      => $reportDate,
                    'TckrSymb'   => $row['tckrsymb'],
                    'MktNm'      => $row['mktnm'],
                    'SctyCtgyNm' => $row['sctyctgynm'],
                    'ISIN'       => $row['isin'],
                    'CrpnNm'     => $row['crpnnm'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        if (!empty($dataToInsert)) {
            InstrumentData::insert($dataToInsert);
        }
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
