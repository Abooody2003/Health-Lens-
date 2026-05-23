<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SurgeryReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SurgeryReportController extends Controller
{
    /**
     * Get authenticated user's surgery reports (history)
     */
    public function index(Request $request)
    {
        $reports = SurgeryReport::query()
            ->where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->with('media')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $reports,
        ]);
    }

    /**
     * Store a new surgery report (save AI analysis results)
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            // Input data (optional - can be sent from frontend)
            'age'         => ['nullable', 'integer', 'min:1', 'max:120'],
            'gender'      => ['nullable', 'string', 'in:male,female'],
            'eye'         => ['nullable', 'string', 'in:OD,OS,left,right,Left,Right'],
            'kmax'        => ['nullable', 'numeric', 'min:0'],
            'cct'         => ['nullable', 'integer', 'min:0'],
            'astigmatism' => ['nullable', 'numeric'],

            // AI outputs (required, except ablation_depth_um which is currently optional)
            'kc_probability'      => ['required', 'numeric', 'min:0', 'max:1'],
            'recommended_surgery' => ['required', 'string', 'max:255'],
            'rsb_um'              => ['required', 'integer', 'min:0'],
            'ablation_depth_um'   => ['nullable', 'integer', 'min:0'],

            // Optional extras
            'warnings' => ['nullable'],

            // Optional images
            'anterior'  => ['nullable', 'image', 'max:10240', 'mimes:jpg,jpeg,png'],
            'axial'     => ['nullable', 'image', 'max:10240', 'mimes:jpg,jpeg,png'],
            'posterior' => ['nullable', 'image', 'max:10240', 'mimes:jpg,jpeg,png'],
            'pachy'     => ['nullable', 'image', 'max:10240', 'mimes:jpg,jpeg,png'],
        ]);

        // Normalize warnings: accept array or JSON string; default to []
        $warnings = $data['warnings'] ?? null;
        if (is_string($warnings)) {
            $decoded = json_decode($warnings, true);
            $warnings = is_array($decoded) ? $decoded : [];
        } elseif (!is_array($warnings) && !is_null($warnings)) {
            $warnings = [];
        }

        $report = SurgeryReport::create([
            'user_id'             => $request->user()->id,
            'age'                 => $data['age'] ?? null,
            'gender'              => $data['gender'] ?? null,
            'eye'                 => $data['eye'] ?? null,
            'kmax'                => $data['kmax'] ?? null,
            'cct'                 => $data['cct'] ?? null,
            'astigmatism'         => $data['astigmatism'] ?? null,
            'kc_probability'      => $data['kc_probability'],
            'recommended_surgery' => $data['recommended_surgery'],
            'rsb_um'              => $data['rsb_um'],
            'ablation_depth_um'   => $data['ablation_depth_um'] ?? null,
            'warnings'            => $warnings,
        ]);

        // Store optional images as polymorphic media
        $imageFields = [
            'anterior'  => 'report_anterior',
            'axial'     => 'report_axial',
            'posterior' => 'report_posterior',
            'pachy'     => 'report_pachy',
        ];

        foreach ($imageFields as $field => $type) {
            if ($request->hasFile($field)) {
                $file = $request->file($field);
                $path = $file->store("surgery-reports/{$report->id}", 'public');

                $report->media()->create([
                    'type'       => $type,
                    'file_path'  => $path,
                    'file_name'  => $file->getClientOriginalName(),
                    'mime_type'  => $file->getMimeType(),
                    'file_size'  => $file->getSize(),
                ]);
            }
        }

        $report->load('media');

        return response()->json([
            'success' => true,
            'message' => 'Surgery report saved successfully',
            'data' => $report,
        ], 201);
    }

    /**
     * Get a single surgery report by ID
     */
    public function show(Request $request, $id)
    {
        $report = SurgeryReport::where('user_id', $request->user()->id)
            ->with('media')
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $report,
        ]);
    }
}
