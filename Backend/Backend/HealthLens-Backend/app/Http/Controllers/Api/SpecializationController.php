<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Specialization;
use Illuminate\Http\Request;

class SpecializationController extends Controller
{
    /**
     * List all active specializations
     */
    public function index(Request $request)
    {
        $specializations = Specialization::query()
            ->where('is_active', true)
            ->withCount([
                'doctors' => function ($q) {
                    $q->where('is_active', true);
                }
            ])
            ->orderBy('display_order')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $specializations,
        ]);
    }
}
