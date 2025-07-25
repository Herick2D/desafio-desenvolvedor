<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InstrumentData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class InstrumentDataController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'TckrSymb' => 'sometimes|string|max:100',
            'RptDt' => 'sometimes|date_format:Y-m-d',
        ]);

        $cacheKey = 'instrument_data_' . http_build_query($request->query());
        $cacheDuration = 600;
        $dataSource = 'CACHE';

        $data = Cache::remember($cacheKey, $cacheDuration, function () use ($request, &$dataSource, $cacheKey) {

            $dataSource = 'DATABASE';
            \Log::info('Cache miss! Buscando dados no banco de dados para a chave: ' . $cacheKey);

            $query = InstrumentData::query();

            $query->when($request->filled('TckrSymb'), function ($q) use ($request) {
                return $q->where('TckrSymb', $request->input('TckrSymb'));
            });

            $query->when($request->filled('RptDt'), function ($q) use ($request) {
                return $q->where('RptDt', $request->input('RptDt'));
            });

            $query->select([
                'RptDt', 'TckrSymb', 'MktNm', 'SctyCtgyNm', 'ISIN', 'CrpnNm'
            ]);

            return $query->latest('RptDt')->paginate(20)->toArray();
        });

        return response()->json($data)->header('X-Data-Source', $dataSource);
    }
}
