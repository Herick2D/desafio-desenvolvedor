<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InstrumentData;
use Illuminate\Http\Request;

class InstrumentDataController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'TckrSymb' => 'sometimes|string|max:100',
            'RptDt' => 'sometimes|date_format:Y-m-d',
        ]);

        $query = InstrumentData::query();

        $query->when($request->filled('TckrSymb'), function ($q) use ($request) {
            return $q->where('TckrSymb', $request->input('TckrSymb'));
        });

        $query->when($request->filled('RptDt'), function ($q) use ($request) {
            return $q->where('RptDt', $request->input('RptDt'));
        });

        $query->select([
            'RptDt',
            'TckrSymb',
            'MktNm',
            'SctyCtgyNm',
            'ISIN',
            'CrpnNm'
        ]);

        $data = $query->latest('RptDt')->paginate(20);

        return response()->json($data);
    }
}
