<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SurgeryAnalysis;
use Illuminate\Http\Request;

class SurgeryAnalysisController extends Controller
{
    public function index(Request $request)
    {
        $query = SurgeryAnalysis::with('user');

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        $analyses = $query->latest()->paginate(15);
        
        return view('admin.surgery-analyses.index', compact('analyses'));
    }

    public function show(SurgeryAnalysis $surgeryAnalysis)
    {
        $surgeryAnalysis->load(['user', 'media']);
        return view('admin.surgery-analyses.show', compact('surgeryAnalysis'));
    }
}

