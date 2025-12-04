<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AboutController extends Controller
{
    public function index()
    {
        // This would typically come from a settings/content table
        // For now, returning placeholder data
        return response()->json([
            'success' => true,
            'data' => [
                'mission' => [
                    'fr' => 'Mission de l\'institut...',
                    'ar' => 'مهمة المعهد...',
                ],
                'vision' => [
                    'fr' => 'Vision de l\'institut...',
                    'ar' => 'رؤية المعهد...',
                ],
                'history' => [
                    'fr' => 'Historique de l\'institut...',
                    'ar' => 'تاريخ المعهد...',
                ],
            ],
        ]);
    }
}
