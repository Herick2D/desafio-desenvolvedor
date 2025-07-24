<?php

namespace App\Imports;

use App\Models\InstrumentData;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithStartRow;

class InstrumentDataImport implements ToCollection, WithChunkReading, WithCustomCsvSettings, WithStartRow
{
    public function __construct(private int $uploadId)
    {
    }

    public function startRow(): int
    {
        return 2;
    }

    public function collection(Collection $rows)
    {
        $dataToInsert = [];

        foreach ($rows as $row) {

            if (isset($row[0]) && $row[0] === 'TRAILER') {
                break;
            }

            if (empty($row[0]) || empty($row[1])) {
                continue;
            }

            $dataToInsert[] = [
                'upload_id'  => $this->uploadId,
                'RptDt'      => date('Y-m-d', strtotime($row[0])),
                'TckrSymb'   => $row[1],
                'MktNm'      => $row[11] ?? null,
                'SctyCtgyNm' => $row[14] ?? null,
                'ISIN'       => $row[2] ?? null,
                'CrpnNm'     => $row[12] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (!empty($dataToInsert)) {
            InstrumentData::insert($dataToInsert);
        }
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function getCsvSettings(): array
    {
        return [
            'delimiter' => ';'
        ];
    }
}
