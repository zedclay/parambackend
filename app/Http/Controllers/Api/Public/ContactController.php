<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index()
    {
        // This would typically come from a settings table
        return response()->json([
            'success' => true,
            'data' => [
                'address' => 'Sidi Bel Abbès, Algeria',
                'phone' => '+213 XX XXX XXXX',
                'email' => 'contact@institut-paramedical-sba.dz',
                'office_hours' => [
                    'fr' => 'Lundi - Vendredi: 8h00 - 17h00',
                    'ar' => 'الإثنين - الجمعة: 8:00 - 17:00',
                ],
            ],
        ]);
    }
}
