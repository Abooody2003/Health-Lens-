<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SurgeryAnalysis;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SurgeryAnalysisController extends Controller
{
    /**
     * List authenticated user's surgery analyses
     */
    public function index(Request $request)
    {
        $analyses = SurgeryAnalysis::query()
            ->where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $analyses->items(),
            'meta' => [
                'current_page' => $analyses->currentPage(),
                'last_page'    => $analyses->lastPage(),
                'per_page'     => $analyses->perPage(),
                'total'        => $analyses->total(),
            ],
        ]);
    }

    /**
     * Submit a new surgery analysis (Orbscan data)
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'age'          => ['required', 'integer', 'min:1', 'max:120'],
            'gender'       => ['required', 'in:male,female'],
            'kmax'         => ['required', 'numeric', 'min:0'],
            'cct'          => ['required', 'numeric', 'min:0'],
            'astig_value'  => ['required', 'numeric'],

            // Orbscan images
            'anterior'  => ['required', 'image', 'max:5120', 'mimes:jpg,jpeg,png'],
            'axial'     => ['required', 'image', 'max:5120', 'mimes:jpg,jpeg,png'],
            'posterior' => ['required', 'image', 'max:5120', 'mimes:jpg,jpeg,png'],
            'pachy'     => ['required', 'image', 'max:5120', 'mimes:jpg,jpeg,png'],
        ]);

        $analysis = SurgeryAnalysis::create([
            'user_id'      => $request->user()->id,
            'age'          => $data['age'],
            'gender'       => $data['gender'],
            'kmax'         => $data['kmax'],
            'cct'          => $data['cct'],
            'astig_value'  => $data['astig_value'],
            'status'       => 'pending',
        ]);

        // Store Orbscan images as media
        foreach (['anterior', 'axial', 'posterior', 'pachy'] as $type) {
            $file = $request->file($type);
            $path = $file->store('orbscan', 'public');

            $analysis->media()->create([
                'type'       => 'orbscan_' . $type,
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
            ]);
        }

        // 🔮 AI processing hook (future)
        // dispatch(new ProcessSurgeryAnalysis($analysis));

        return response()->json([
            'success' => true,
            'message' => 'Analysis submitted successfully',
            'data' => [
                'id'     => $analysis->id,
                'status' => $analysis->status,
                'created_at' => $analysis->created_at,
            ],
        ], 202);
    }

    /**
     * Get single analysis (with media & results)
     */
    public function show(Request $request, $id)
    {
        $analysis = SurgeryAnalysis::with('media')
            ->where('user_id', $request->user()->id)
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $analysis,
        ]);
    }
}
