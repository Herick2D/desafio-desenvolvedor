<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class InstrumentData extends Model
{

    protected $connection = 'mongodb';

    protected $fillable = [
        'upload_id',
        'RptDt',
        'TckrSymb',
        'MktNm',
        'SctyCtgyNm',
        'ISIN',
        'CrpnNm',
    ];
}
