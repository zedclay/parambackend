<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\Establishment;
use Illuminate\Http\Request;

class EstablishmentsController extends Controller
{
    public function index(Request $request)
    {
        $query = Establishment::with('speciality');

        if ($request->has('specialite_id')) {
            $query->where('specialite_id', $request->specialite_id);
        }

        $establishments = $query->get();

        return response()->json([
            'success' => true,
            'data' => $establishments,
        ]);
    }
}
